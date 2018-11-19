<?php

/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 8:38
 */

// Запись не найдена в базе - 101
// Успещное удаление записи - 300
// Ошибка при удалени записи -301
// Запись успешно добавлена  - 400
// Не удалось записать в таблицу  - 401
// Не существующий контроллер 501
// Не существующий экшен - 601
// Не правильный API KEY - 701


class product extends cmsCore
{
    private $conn;
    private $table_name = "products";
    private $table_name_vendor = "vendor";

    private $output_success = array();
    private $output_error = array();
    public static $getVersion = 0;
    public $key = null;

    public $id;
    public $name;
    public $title;
    public $description;
    public $price;
    public $vendor_id;
    public $quantity;
    public $category_name;
    public $request;
    public $currency = 1;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->conn = $this->connectDB();
        if(!method_exists('product', $action)){
            $this->setError(601,"This action is not Valid",'action');
        }
        $this->request = new cmsRequest($_REQUEST);
        $stmt = $this->$action($this->request);

        $this->run($stmt);
    }

    public function index(){
        $this->setError(601,"This action is not Valid",'action');
    }


    /**
     * Результат запроса
     * @param array $api_request_result
     */
    public function setSuccess($status, $api_request_result )
    {
        $this->output_success ['response']= array(
            'Success' => $api_request_result,
            'status' => $status
        );
        echo $this->renderJSONAPI($this->output_success);die();
    }



    /**
     * Устанавливает ошибку запроса
     * @param integer $error_code
     * @param string $error_msg
     * @param array $request_params
     */
    public function setError($error_code, $error_msg = '', $request_params='')
    {
        if ($error_msg) {
            $this->output_error['error'] = array(
                'error_code' => ($error_code ? $error_code : 0),
                'error_msg' => $error_msg,
                'request_params' => ($request_params ? $request_params : 0)
            );
        } else {
            $this->output_error['error'] = array(
                'error_code' => $error_code,
                'error_msg' => constant('LANG_API_ERROR' . $error_code),
                'request_params' => $request_params
            );
        }
        echo $this->renderJSONAPI($this->output_error);die();
    }


    public function renderJSONAPI($data, $with_header = false)
    {
        if (ob_get_length()) {
            ob_end_clean();
        }
        if ($with_header) {
            header('Content-type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_FORCE_OBJECT);
    }



    /**
     *
     * Метод для получения данных
     * @retunrn array
     *
     **/
    function get($request)
    {
        $query = "SELECT v.name as vendor_name, p.title, p.id, p.short_description, p.price_retail
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    vendor v
                        ON p.vendor_id = v.vid
                ORDER BY 
                   p.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $product_item = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item[] = array(
                "id" => $row["id"],
                "name" => $row['title'],
                "description" => html_entity_decode($row['short_description']),
                "price" => $row['price_retail'],
                "vendor_name" => $row['vendor_name']
            );
        }
        return $product_item;
    }


    /**
     *
     * Метод для выбора  марок, имеющих > N товаров
     * @input quantity:int
     * @retunrn array
     *
     **/
    function getVendor($request)
    {
        $query = "SELECT *
                FROM
                    " . $this->table_name_vendor . " v
                    LEFT JOIN
                    products p
                        ON p.vendor_id = v.vid
                    WHERE 
                     p.quantity > :quantity   
       ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $request->get('quantity'));
        $stmt->execute();
        $product_item = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item[] = array(
                "vid" => $row['vid'],
                "vendor_name" => $row['name']
            );
        }
        return $this->unique_multidim_array($product_item, 'vid');
    }


    /**
     *
     * Метод для выбора  товара по его ID
     * @input id:int
     * @retunrn array
     *
     **/
    function getByID($request)
    {
        $query = "SELECT v.name as vendor_name, p.title, p.id, p.short_description, p.price_retail
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    vendor v
                        ON p.vendor_id = v.vid
               WHERE
                p.id = :id
            LIMIT
                0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $request->get('id'));
        $stmt->execute();
        $row[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($row["0"]["id"])){
            return $row;
        }
        else {
            if($request->get('id')){
                $this->setError(101,"Product is not found","empty");
            }
            else{
                $this->setError(101,"Product is not found","ID");
            }

        }

    }


    /**
     *
     * Метод для выборки всех товаров определенной марки.
     * @input name:string
     * @retunrn array
     *
     **/
    function getByMark($request)
    {
        $query = "SELECT v.name as vendor_name, p.title, p.id, p.short_description, p.price_retail
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    vendor v
                        ON p.vendor_id = v.vid
                        WHERE v.name = :name
                      
                ORDER BY
                    p.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $request->get('name'));
        $stmt->execute();
        $product_item = array();
        if($request->get('currency')){
            $this->currency = $this->gettCurrency($request->get('currency'));
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            extract($row);
            $product_item[] = array(
                "id" => $row["id"],
                "name" => $row['title'],
                "description" => html_entity_decode($row['short_description']),
                "price" => $row['price_retail']/$this->currency,
                "vendor_name" => $row['vendor_name']
            );
        }
        if($request->get('name')){
            return $product_item;
        }
        else {
            $this->setError(101, "Product is not found", "name");
        }
    }

    /**
     *
     * Метод для удаления данных по ID
     * @input ID:int
     * @retunrn message
     *
     **/

    function delete($request) {
        $prod = $this->getByID($request);
        if(!empty($prod['0']['id'])){
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $this->id=htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam("id", $request->get("id"));
            if ($stmt->execute()) {
                $this->setSuccess(300, "Product was successful deleted from ".$this->table_name);
            }else{
                $this->setError(301,"Error deleting Product from ".$this->table_name,"ID");
            }
        }
        else{
            if($request->get("id")){
                $this->setError(301,"Error deleting Product ID = ".$request->get("id"),"");
            }
            else {
                $this->setError(301,"Error deleting Product","");
            }
        }
    }


    /**
     *
     * Метод для добавления записи в табл.
     * @input name:string , price:int
     * @retunrn message
     *
     **/

    function create($request) {
        $query = "INSERT INTO 
            " . $this->table_name . "
            SET
                title=:title, vendor_id=:vendor_id, price_retail=:price, short_description=:description, quantity=:quantity";
        $stmt = $this->conn->prepare($query);
        $this->title=htmlspecialchars(strip_tags($request->get('title')));
        $this->vendor_id=htmlspecialchars(strip_tags($request->get('vendor_id')));
        $this->price=htmlspecialchars(strip_tags($request->get('price')));
        $this->description=htmlspecialchars(strip_tags($request->get('description')));
        $this->quantity=htmlspecialchars(strip_tags($request->get('quantity')));
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":quantity", $this->quantity);
        if($this->title and $this->price){
            try {
                $stmt->execute();
                $this->setSuccess(400, "Product was added successfull");
            }
            catch (PDOException $e) {
                $this->setError(401, 'Insert Error: ' . $e->getMessage());
            }
        }
        else {
            $this->setError(401,'Error adding Product','name and price');
        }
    }



    /**
     *
     * Метод RUN
     *
     **/

    public function run($array)
    {
        if (is_array($array) and count($array) >0 && empty($array['message']) && empty($array['error'])) {
            $products_arr = array();
            $products_arr["response"] = array();
            $products_arr["response"]["count"] = count($array);
            if(count($array) > 1){
                $products_arr["response"]["items"] = $array;
            }
            else{
                $products_arr["response"]["item"] = $array['0'];
            }
            $this->renderJSONAPI($products_arr);
        }
        elseif($array['message'] || $array["error"]){
            $this->renderJSONAPI($array);
        }
        else {
            $this->setError(101,"No products found.");
        }

    }
}