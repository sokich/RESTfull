<?php 

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charstet=UTF-8"); 

include_once '../config/core.php'; 
include_once '../shared/utilities.php'; 
include_once '../confif/database.php'; 
include_once '../objects/product.php'; 

$utilities = new Utilities(); 

$database = new Database(); 
$db = $database->getConnection(); 
$product = new Product($db); 

$stmt = $product->readPagin($from_record_num, $records_per_page); 
$num = $stmt->rowCount(); 

if ($num > 0) {

    $products_arr=array();
    $products_arr['records'] = array(); 
    $products_arr['paging'] = array(); 

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row); 

        $product_item=array(
            "id" => $id,
            "name" => $name,
            "description" => html_entity_decode($description),
            "price" => $category_id, 
            "category_id" => $category_id, 
            "category_name" => $category_name
        ); 
        array_push($products_arr["records"], $product_item); 
    }

    $total_rows=$product->count(); 
    $page_url = "{$home_url}product/read_pagin.php?"; 
    $paging=$utilities->getPaging($page, $total_rows, $records_per_page, $page_url); 
    $products_arr["paging"]=$paging; 

    echo json_encode($products_arr); 
} else {
    echo json_encode(
        array("message:" => "No products found.")
    ); 
}