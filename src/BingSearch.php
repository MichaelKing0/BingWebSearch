<?php

namespace MichaelKing0\BingWebSearch;

class BingSearch extends WebSearch
{
    public function __construct($accountKey, $handler = null)
    {
        parent::__construct($handler);

        $this->additionalHeaders = [
            "Authorization" => "Basic " . base64_encode($accountKey . ":" . $accountKey)
        ];
    }

    public function search($phrase, $urlPattern = null, $inTitle = null, $notInTitle = null, $notInUrlPattern = null)
    {
        $url = 'https://api.datamarket.azure.com/Bing/Search/v1/Web?Query=%27'. urlencode($phrase) .'%27';

        $response = $this->makeRequest($url);
        $xml = simplexml_load_string($response);

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
            return (string)$data->Url;
        }

        return null;
    }
}