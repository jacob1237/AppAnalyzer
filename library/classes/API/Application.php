<?php

namespace API;

use GuzzleHttp\Client;


final class Application
{
    /**
     * @var Client
     */
    protected static $client;

    public static function factory($url)
    {
        if (empty(self::$client)) {
            self::$client = new Client();
        }

        $parts = parse_url($url);

        switch (strtolower($parts['host']))
        {
            case 'play.google.com':
                $app = new \API\Application\Store\GooglePlay($url, self::$client);
                break;

            case 'itunes.apple.com':
                $app = new \API\Application\Store\AppleStore($url, self::$client);
                break;

            default:
                throw new \Exception('Unable to find appropriate store adaptor');
        }

        return $app;
    }
}