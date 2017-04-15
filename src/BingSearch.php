<?php

namespace MichaelKing0\BingWebSearch;

class BingSearch extends WebSearch
{
    protected $amountOfResults = 1;

    public function __construct($accountKey, $handler = null)
    {
        parent::__construct($handler);

        $this->additionalHeaders = [
            "Authorization" => "Basic " . base64_encode($accountKey . ":" . $accountKey)
        ];
    }

    public function setAmountOfResults($amount)
    {
        $this->amountOfResults = $amount;
        return $this;
    }

    public function search($phrase, $urlPattern = null, $inTitle = null, $notInTitle = null, $notInUrlPattern = null)
    {
        $url = 'https://api.datamarket.azure.com/Bing/Search/v1/Web?Query=%27'. urlencode($phrase) .'%27';

        $response = $this->makeRequest($url);
        $xml = simplexml_load_string($response);

        $results = [];

        foreach ($xml->entry as $entry) {
            $data = $entry->content->children('http://schemas.microsoft.com/ado/2007/08/dataservices/metadata')[0];
            $data = $data->children('http://schemas.microsoft.com/ado/2007/08/dataservices');

            if ($urlPattern) {
                if (!preg_match($urlPattern, $data->Url)) {
                    continue;
                }
            }
            if ($inTitle) {
                if (strpos($data->Title, $inTitle) === false) {
                    continue;
                }
            }
            if ($notInTitle) {
                if (strpos($data->Title, $notInTitle) !== false) {
                    continue;
                }
            }
            if ($notInUrlPattern) {
                if (preg_match($notInUrlPattern, $data->Url)) {
                    continue;
                }
            }

            // if we make it this far, this is the entry we want. Return the URL
            $results[] = (string)$data->Url;
        }

        if (count($results)) {
            return ($this->amountOfResults == 1) ? $results[0] : array_splice($results, 0, $this->amountOfResults);
        }

        return null;
    }
}