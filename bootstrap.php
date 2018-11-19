<?php
/**
 * Created by PhpStorm.
 * User: IgorS
 * Date: 10.08.2017
 * Time: 08:43
 */

// Определяем корень
define('PATH', dirname(__FILE__));
define('LANG_API_ERROR1', "SOKICH");
define('ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR));

// Устанавливаем кодировку
mb_internal_encoding('UTF-8');

// Подключаем автозагрузчик классов
require_once PATH . '/system/config/autoload.php';

// Устанавливаем обработчик автозагрузки классов
spl_autoload_register('autoLoadCoreClass');

//// Инициализируем конфиг
//$config = cmsConfig::getInstance();

// Инициализируем ядро
$core = cmsCore::getInstance();

// Подключаем базу
$db = $core->connectDB();