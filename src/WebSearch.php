<?php

namespace MichaelKing0\BingWebSearch;

use GuzzleHttp\Client;

abstract class WebSearch
{
    protected $client;
    protected $additionalHeaders = [];

    public function __construct($handler = null)
    {
        $this->client = new Client();

        if ($handler) {
            $this->client = new Client(['handler' => $handler]);
        }
    }

    public function setHandler($handler)
    {
        $this->client = new Client(['handler' => $handler]);
    }

    public function makeRequest($url)
    {
        return $this->client->get($url, [
            'headers' => $this->additionalHeaders
        ])->getBody()->getContents();
    }

    abstract public function search($phrase);
}