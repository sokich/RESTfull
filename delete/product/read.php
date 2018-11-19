<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../objects/database.php';
include_once '../objects/product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);


if($_REQUEST["id"] or $_POST["id"]){
    $products_arr=array();
    $products_arr["response"]=array();
    $products_arr["response"]["item"]=array();

    $product -> id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : die();
    $stmt = $product -> readOne();
    //$products_arr["response"]["count"] =  $stmt->rowCount();


    $product_arr = array(
        "id" => $product->id,
        "name" => $product->name,
        "description" => $product->description,
        "price" => $product->price,
        "category_name" => $product->category_name
    );
    array_push($products_arr["response"]["item"], $product_arr);

    print_r(json_encode($products_arr));


}
else {


    $stmt = $product->read();
    $num = $stmt->rowCount();

    if($num>0){
        $products_arr=array();
        $products_arr["response"]=array();
        $products_arr["response"]["count"] = $num;
        $products_arr["response"]["items"] = array();


        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);

            $product_item=array(
                "id" => $id,
                "name" => $title,
                "description" => html_entity_decode($short_description),
                "price" => $price_retail,
                "vendor_name" => $vendor_name
            );
            array_push($products_arr["response"]["items"], $product_item);
        }
        echo json_encode($products_arr);
    } else{
        echo json_encode(
            array("message" => "No products found.")
        );
    }


}
