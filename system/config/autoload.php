<?php
/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 10:39
 */


/**
 * Определяет и подключает PHP-файл содержащий указанный класс
 * @param string $class_name
 * @return boolean
 */

function autoLoadCoreClass($class_name){
    $class_name = strtolower($class_name);
    $class_file = false;
    if (strpos($class_name, 'cms') === 0) {
        $class_name = substr($class_name, 3);
        $class_file = 'system/objects/' . $class_name . '.php';
    } else{
        $class_file = 'system/objects/' . $class_name . '.php';
    }
    if (!$class_file){ return false; }
    include_once PATH . '/' . $class_file;
    return true;
}
