<?php

namespace Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use MichaelKing0\BingWebSearch\BingSearch;
use PHPUnit\Framework\TestCase;

class BingSearchTest extends TestCase
{
    /** @var BingSearch */
    private $bingSearch;

    public function setUp()
    {
        parent::setUp();

        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/BingSearchIcelandResponse.json'))
        ]);

        $this->bingSearch = new BingSearch('test', $mock);
    }

    public function testSearchBasic()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/island/startseite/verein/3574',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile')
        );
    }

    public function testSearchWithUrlPattern()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/island/marktwertanalyse/verein/3574',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', '/marktwertanalyse\/verein\/3574/')
        );
    }

    public function testSearchWithInTitle()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/island/marktwertanalyse/verein/3574',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', null, 'Market value analysis')
        );
    }

    public function testSearchWithNotInTitle()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', null, null, 'Iceland')
        );
    }

    public function testSearchWithNoMatch()
    {
        $this->assertNull(
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', '/^[0-9]$/', null, 'Iceland')
        );
    }

    public function testSearchWithMoreResults()
    {
        $result = $this->bingSearch->setAmountOfResults(2)->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile');

        $this->assertEquals('http://www.transfermarkt.co.uk/island/startseite/verein/3574', $result[0]);
        $this->assertEquals('http://www.transfermarkt.co.uk/island-u19/startseite/verein/25785', $result[1]);
    }

    public function testSearchWithCustomCallback()
    {
        $result = $this->bingSearch
            ->setTitleFilter(function($title) {
                return false;
            })
            ->setAmountOfResults(2)
            ->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile');

        $this->assertNull($result);
    }

    public function testSearchWithCustomCallbackInvert()
    {
        $result = $this->bingSearch
            ->setTitleFilter(function($title) {
                return true;
            })
            ->setAmountOfResults(2)
            ->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile');

        $this->assertEquals('http://www.transfermarkt.co.uk/island/startseite/verein/3574', $result[0]);
        $this->assertEquals('http://www.transfermarkt.co.uk/island-u19/startseite/verein/25785', $result[1]);
    }
}