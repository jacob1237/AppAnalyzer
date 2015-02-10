<?php

define('APPLICATION_ROOT', realpath(__DIR__ . '/../'));

require APPLICATION_ROOT . '/vendor/autoload.php';
require APPLICATION_ROOT . '/app/main.php';


$groups = array();

if (!empty($_POST['submit']) && !empty($_POST['list']))
{
    $list = array_unique(explode("\r\n", trim($_POST['list'])));

    $groups = SimilarDetector::groupApps($list);
    $errors = SimilarDetector::getLastErrors();
}

// Show HTML page
include APPLICATION_ROOT . '/app/template.phtml';