<?php

namespace API\Application\Store;

use \API\Application\AbstractApplication;
use \API\Application\ApplicationInterface;
use API\Application\Exception\InvalidPageException;
use API\Application\Exception\InvalidUrlException;

libxml_use_internal_errors(true);


class GooglePlay extends AbstractApplication implements ApplicationInterface
{
    public function __construct($url, $client)
    {
        parent::__construct($this->normalizeURL($url), $client);
    }

    /**
     * URL validation and transform
     * Used to get page in English
     *
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    private function normalizeUrl($url)
    {
        $parts = parse_url($url);

        // Check if URL is valid
        if (!preg_match('|store/apps/details|', $parts['path'])) {
            throw new InvalidUrlException('Invalid GooglePlay URL');
        }

        parse_str($parts['query'], $query);

        // Change URL language to get only english content
        if (!isset($query['hl']) || ($query['hl'] != 'en'))
        {
            $query['hl'] = 'en';

            // This procedure can be replaced by http_build_query()
            $reduced = array();
            foreach ($query as $key => $value) {
                $reduced[] = "{$key}={$value}";
            }

            $parts['query'] = implode('&', $reduced);

            // http_build_url() alternative
            $url = "{$parts['scheme']}://{$parts['host']}{$parts['path']}?{$parts['query']}";
        }

        return $url;
    }

    /**
     * Parse GooglePlay application page and load needed values
     *
     * @param $html string HTML code
     * @throws InvalidPageException
     */
    protected function parse($html)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);

        $xpath = new \DOMXPath($doc);

        $title = $xpath->query("//div[@class='document-title']/div");
        $developer = $xpath->query("//a[@class='document-subtitle primary']/span");
        $description = $xpath->query("//div[@class='id-app-orig-desc']");

        if (empty($title) || empty($developer) || empty($description)) {
            throw new InvalidPageException('Invalid page format');
        }

        $this->title = $title->item(0)->textContent;
        $this->developer = $developer->item(0)->textContent;
        $this->description = $description->item(0)->textContent;
        $this->source = 'google';
    }
}