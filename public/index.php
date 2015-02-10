<?php

define('APPLICATION_ROOT', realpath(__DIR__ . '/../'));

require APPLICATION_ROOT . '/vendor/autoload.php';
require APPLICATION_ROOT . '/app/main.php';


$groups = array();

if (!empty($_POST['submit']) && !empty($_POST['list'])) {
    $groups = Sorter::groupApps(explode("\r\n", trim($_POST['list'])));
    $errors = Sorter::getLastErrors();
}

// Show HTML page
include APPLICATION_ROOT . '/app/template.phtml';