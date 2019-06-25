<?php

namespace MichaelKing0\BingWebSearch;

class BingSearch extends WebSearch
{
    // amount of results the client wants returned
    protected $amountOfResults = 1;
    /** @var \Closure|null */
    protected $urlFilter;
    /** @var \Closure|null */
    protected $titleFilter;

    // api settings
    protected $count = 10;
    protected $offset = 0;

    public function __construct($accountKey, $handler = null)
    {
        parent::__construct($handler);

        $this->additionalHeaders = [
            "Ocp-Apim-Subscription-Key" => $accountKey
        ];
    }

    public function setAmountOfResults($amount)
    {
        $this->amountOfResults = $amount;
        return $this;
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function setUrlFilter(\Closure $filter)
    {
        $this->urlFilter = $filter;
        return $this;
    }

    public function setTitleFilter(\Closure $filter)
    {
        $this->titleFilter = $filter;
        return $this;
    }

    private function removeFormatting($string)
    {
        $string = preg_replace('/\<.*?\>/', '', $string);
        $string = html_entity_decode($string);
        return $string;
    }

    public function search($phrase, $urlPattern = null, $inTitle = null, $notInTitle = null, $notInUrlPattern = null)
    {
        $url = 'https://api.cognitive.microsoft.com/bing/v5.0/search?q='. urlencode($phrase) . '&count=' . $this->count . '&offset=' . $this->offset;

        $response = $this->makeRequest($url);

        $json = json_decode($response);

        $results = [];

        foreach ($json->webPages->value as $entry) {
            if (count($results) >= $this->amountOfResults) {
                break;
            }

            $url = $entry->url;

            if (strpos($url, 'http') !== 0) {
                $url = 'http://' . $url;
            }
            $title = $entry->name;

            if ($urlPattern) {
                if (!preg_match($urlPattern, $url)) {
                    continue;
                }
            }
            if ($inTitle) {
                if (strpos($title, $inTitle) === false) {
                    continue;
                }
            }
            if ($notInTitle) {
                if (strpos($title, $notInTitle) !== false) {
                    continue;
                }
            }
            if ($notInUrlPattern) {
                if (preg_match($notInUrlPattern, $url)) {
                    continue;
                }
            }

            if ($this->urlFilter) {
                if (!call_user_func($this->urlFilter, $url)) {
                    continue;
                }
            }

            if ($this->titleFilter) {
                if (!call_user_func($this->titleFilter, $title)) {
                    continue;
                }
            }

            // if we make it this far, this is the entry we want. Return the URL
            $results[] = (string)$url;
        }

        if (count($results)) {
            return ($this->amountOfResults == 1) ? $results[0] : array_splice($results, 0, $this->amountOfResults);
        }

        return null;
    }
}
