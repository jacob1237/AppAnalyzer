<?php

namespace API\Application;


interface ApplicationInterface
{
    public function __construct($url, $client);

    public function getTitle();
    public function getDescription();
    public function getDeveloper();
    public function getSource();
    public function getUrl();
}
