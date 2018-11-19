<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json, charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../objects/product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$data = json_decode(file_get_contents("php://input"));

$product->id = $data->id;
$product->name = $data->name;
$product->description = $data->description;
$product->category_id = $data->category_id;

if ($product->update()) {
    echo '{';
        echo '"Message:" "Product was updated."';
    echo '}';
} else {
    echo '{';
        echo '"Message:" "Unable to update product."';
    echo '}';
}