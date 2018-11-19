<?php

/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 13:40
 */


// Запись не найдена в базе - 101
// Успещное удаление записи - 300
// Ошибка при удалени записи -301
// Запись успешно добавлена  - 400
// Не удалось записать в таблицу  - 401
// Не существующий контроллер 501
// Не существующий экшен - 601
// Не правильный API KEY - 701


class vendor extends cmsCore
{
    private $conn;
    private $table_name = "vendor";
    private $table_name_products = "products";

    private $output_success = array();
    private $output_error = array();
    public static $getVersion = 0;

    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $request;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->conn = $this->connectDB();
        if(!method_exists('vendor', $action)){
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
    public function setSuccess($status, $api_request_result)
    {
        $this->output_success ['response'] = array(
            'Success' => $api_request_result,
            'status' => $status
        );
        echo $this->renderJSONAPI($this->output_success);
        die();
    }


    /**
     * Устанавливает ошибку запроса
     * @param integer $error_code
     * @param string $error_msg
     * @param array $request_params
     */
    public function setError($error_code, $error_msg = '', $request_params = '')
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
        echo $this->renderJSONAPI($this->output_error);
        die();
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
     * @input quantity:int
     * @retunrn array
     *
     **/
    function get($request)
    {
        $query = "SELECT *
                FROM
                    " . $this->table_name . " v
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


    function getVendor($request)
    {
        $query = "SELECT *
                FROM
                    " . $this->table_name . " v
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


    function getByID($request)
    {

        $query = "SELECT *
                FROM
                    " . $this->table_name . " v
                
               WHERE
                v.id = :id
            LIMIT
                0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $request->get('id'));
        $stmt->execute();
        $row[] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row["0"]["id"])) {
            return $row;
        } else {
            if ($request->get('id')) {
                $this->setError(101, "Vendor is not found", "empty");
            } else {
                $this->setError(101, "Vendor is not found", "ID");
            }
        }
    }


    /**
     *
     * Метод для удаления данных по ID
     * @input ID:int
     * @retunrn message
     *
     **/
    function delete($request)
    {
        $prod = $this->getByID($request);

        if (!empty($prod['0']['id'])) {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam("id", $request->get("id"));
            if ($stmt->execute()) {
                $this->setSuccess(300, "Vendor was successful deleted from " . $this->table_name);
            } else {
                $this->setError(301, "Error deleting Vendor from " . $this->table_name, "ID");
            }
        } else {
            if ($request->get("id")) {
                $this->setError(301, "Error deleting Vendor ID = " . $request->get("id"), "");
            } else {
                $this->setError(301, "Error deleting Vendor", "");
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
    function create($request)
    {
        $query = "INSERT INTO 
            " . $this->table_name . "
            SET
                name =:title, vid=:vendor_id, description=:description";

        $stmt = $this->conn->prepare($query);
        $this->title = htmlspecialchars(strip_tags($request->get('title')));
        $this->vendor_id = htmlspecialchars(strip_tags($request->get('vendor_id')));
        $this->description = htmlspecialchars(strip_tags($request->get('description')));
        $this->quantity = htmlspecialchars(strip_tags($request->get('quantity')));
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":description", $this->description);
        if ($this->title) {
            try {
                $stmt->execute();
                $this->setSuccess(400, "Vendor was added successfull");
            } catch (PDOException $e) {
                $this->setError(401, 'Insert Error: ' . $e->getMessage());
            }
        } else {
            $this->setError(401, 'Error adding Vendor', 'title');
        }
    }


    /**
     *
     * Метод RUN - рендер результирующего массива
     *
     **/
    public function run($array)
    {
        if (is_array($array) and count($array) > 0 && empty($array['message']) && empty($array['error'])) {
            $products_arr = array();
            $products_arr["response"] = array();
            $products_arr["response"]["count"] = count($array);
            if (count($array) > 1) {
                $products_arr["response"]["items"] = $array;
            } else {
                $products_arr["response"]["item"] = $array['0'];
            }
            $this->renderJSONAPI($products_arr);
        } elseif ($array['message'] || $array["error"]) {
            $this->renderJSONAPI($array);
        } else {
            $this->setError(101, "No vendor found.");
        }

    }
}