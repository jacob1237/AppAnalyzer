<?php

namespace API\Application\Store;

use \API\Application\AbstractApplication;
use \API\Application\ApplicationInterface;
use API\Application\Exception\InvalidPageException;
use API\Application\Exception\InvalidUrlException;

libxml_use_internal_errors(true);


class AppleStore extends AbstractApplication implements ApplicationInterface
{
    public function __construct($url, $client)
    {
        parent::__construct($this->normalizeURL($url), $client);
    }

    /**
     * URL validation and transform
     * Used for URL modifying to get only english application pages
     *
     * @param $url string URL
     * @return string Modified URL
     * @throws \Exception
     */
    protected function normalizeURL($url)
    {
        $parts = parse_url($url);

        // Check if URL is valid
        $path = explode('/', $parts['path']);
        if ((count($path) != 5) || ($parts['query'] != 'mt=8')) {
            throw new InvalidUrlException('Invalid iTunes URL');
        }

        // Change URL language to get only english content
        if ($path[1] != 'en')
        {
            $path[1] = 'en';

            // Build modified URL
            $parts['path'] = implode('/', $path);

            // http_build_url() alternative
            $url = "{$parts['scheme']}://{$parts['host']}{$parts['path']}?{$parts['query']}";
        }

        return $url;
    }

    /**
     * Parse Apple application page and load needed values
     *
     * @param $html string HTML code
     * @throws InvalidPageException
     */
    protected function parse($html)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);

        $xpath = new \DOMXPath($doc);

        $title = $xpath->query("//div[@id='title']/div[@class='left']/h1");
        $developer = $xpath->query("//div[@id='title']/div[@class='left']/h2");
        $description = $xpath->query("//div[@class='product-review']/p");

        if (empty($title) || empty($developer) || empty($description)) {
            throw new InvalidPageException('Invalid page format');
        }

        $this->title = $title->item(0)->textContent;
        $this->developer = ltrim($developer->item(0)->textContent, 'By ');
        $this->description = $description->item(0)->textContent;
        $this->source = 'apple';
    }
}