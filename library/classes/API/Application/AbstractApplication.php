<?php

namespace API\Application;


abstract class AbstractApplication implements ApplicationInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string Initial application URL
     */
    protected $url;

    /**
     * @var string Application title
     */
    protected $title;

    /**
     * @var string Developer name
     */
    protected $developer;

    /**
     * @var string Application description
     */
    protected $description;

    /**
     * @var string Application source (store name)
     */
    protected $source;

    public function __construct($url, $client)
    {
        $this->url = $url;
        $this->client = $client;

        $response = $this->client->get($url);
        $this->parse((string)$response->getBody());
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDeveloper()
    {
        return $this->developer;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getUrl()
    {
        return $this->url;
    }

    abstract protected function parse($html);
}