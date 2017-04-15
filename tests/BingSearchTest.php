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
            new Response(200, [], file_get_contents(__DIR__ . '/BingSearchIcelandResponse.xml'))
        ]);

        $this->bingSearch = new BingSearch('test', $mock);
    }

    public function testSearch()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/island/startseite/verein/3574',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile')
        );
    }

    public function testSearchWithUrlPattern()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/iceland-u21/startseite/verein/22414',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', '/\/22414$/')
        );
    }

    public function testSearchWithInTitle()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/iceland-u17/startseite/verein/26034',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', null, 'U17')
        );
    }

    public function testSearchWithNotInTitle()
    {
        $this->assertEquals(
            'http://www.transfermarkt.co.uk/',
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', null, null, 'Iceland')
        );
    }

    public function testSearchWithNoMatch()
    {
        $this->assertNull(
            $this->bingSearch->search('site:transfermarkt.co.uk intitle:Iceland Club\'s profile', '/^[0-9]$/', null, 'Iceland')
        );
    }
}