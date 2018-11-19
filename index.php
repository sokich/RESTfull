<?php
/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 08:27
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once 'bootstrap.php';

//Запускаем роутинг
$core->route($_SERVER['REQUEST_URI']);

//Запускаем контроллер
$core->runController();
