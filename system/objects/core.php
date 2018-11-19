<?php

/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 13:58
 */

class cmsCore extends API {

    private static $instance;
    private $key = "ASDad4qtDAS2t342";

	public $uri            = '';
	public $uri_before_remap = '';
    public $uri_absolute   = '';
    public $uri_controller = '';
    public $uri_controller_before_remap = '';
    public $uri_action     = '';
    public $uri_params     = array();
    public $uri_query      = array();

    public $controller = '';

	public $link;
	public $request;

    public $db;

    private static $includedFiles = array();
    private static $start_time;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct(){

        $this->request = new cmsRequest($_REQUEST);
        $this->checkKey($this->request->get("api_key"));

    }

    private function checkKey($key){
        if($this->key != $key){
            $unless_controller = array(
                'error_code' => 701,
                'error_msg' => 'Incorrect API KEY value',
                'request_params' => 'API_KEY'
            );
            echo json_encode($unless_controller,JSON_FORCE_OBJECT);die();
        }
    }
    public function gettCurrency($currency){
        $today = date("d/m/Y");
        $today2 = date("d_m_Y");
        if(!file_exists(ROOT.'/data/cb-'.$today2.'.xml')){
            $fp = fopen(ROOT.'/data/cb-'.$today2.'.xml', 'w');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
                'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$today);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_exec($ch);
            fclose($fp);
            curl_close ($ch);
        }
        $cbxml = simplexml_load_file(ROOT.'/data/cb-'.$today2.'.xml');
        if($currency == "USD") { return $cbxml->Valute[10]->Value->__toString();}
            elseif($currency == "KZT") { return $cbxml->Valute[13]->Value->__toString()/$cbxml->Valute[13]->Nominal->__toString();}
        elseif($currency == "BYN") { return $cbxml->Valute[4]->Value->__toString();}
        else
            return 1;
    }


    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function isModelExists($controller){

        $model_file = cmsConfig::get('root_path').'system/controllers/'.$controller.'/model.php';

        return file_exists($model_file);

    }

    /**
     * Возвращает объект модели из указанного файла (без расширения)
     * @param string $controller Контроллер модели
     * @param string $delimitter Разделитель слов в названии класса
     */
    public static function getModel($controller, $delimitter='_'){

        $model_class = 'model' . string_to_camel($delimitter, $controller);

        if (!class_exists($model_class, false)) {

            $model_file = cmsConfig::get('root_path').'system/controllers/'.$controller.'/model.php';

            if (is_readable($model_file)){
                include_once($model_file);
            } else {
                self::error(ERR_MODEL_NOT_FOUND . ': '. $model_file);
            }
        }
        return new $model_class();

    }



    /**
     * Проверяет существования контроллера
     * @param string $controller_name
     * @return bool
     */
    public static function isControllerExists($controller_name){

        return is_dir(cmsConfig::get('root_path').'system/controllers/'.$controller_name);

    }

    /**
     * Создает и возвращает объект контроллера
     * @param str $controller_name
     * @param cmsRequest $request
     * @return controller_class
     */
    public static function getController($controller_name, $request=null){


        $config = cmsConfig::getInstance();

        $ctrl_file = $config->root_path . 'system/controllers/'.$controller_name.'/frontend.php';
        if (!file_exists($ctrl_file)){
            $unless_controller = array(
                'error_code' => 501,
                'error_msg' => 'This controller is nov valid',
                'request_params' => 'product or vendor'
            );
            echo json_encode($unless_controller,JSON_FORCE_OBJECT);die();
        }

        if (!class_exists($controller_name, false)) {
            include_once($ctrl_file);
        }

        $custom_file = $config->root_path . 'system/controllers/'.$controller_name.'/custom.php';

        if(!file_exists($custom_file)){
            $controller_class = $controller_name;
        } else {
            $controller_class = $controller_name . '_custom';
            if (!class_exists($controller_class, false)){
                include_once($custom_file);
            }
        }

        if (!$request) { $request = new cmsRequest(array(), cmsRequest::CTX_INTERNAL); }

        return new $controller_class($request);

    }



    /**
     * Определяет контроллер, действие и параметры для запуска по полученному URI
     * @param string $uri
     */
    public function route($uri){

		$config = cmsConfig::getInstance();


        $uri = trim(urldecode($uri));
		$uri = mb_substr($uri, mb_strlen( $config->root ));


        if (!$uri) { return; }

        // если в URL присутствует знак вопроса, значит есть
        // в нем есть GET-параметры которые нужно распарсить
        // и добавить в массив $_REQUEST
        $pos_que  = mb_strpos($uri, '?');
        if ($pos_que !== false){

            // получаем строку запроса
            $query_data = array();
            $query_str  = mb_substr($uri, $pos_que+1);

            // удаляем строку запроса из URL
            $uri = mb_substr($uri, 0, $pos_que);

            // парсим строку запроса
            parse_str($query_str, $query_data);

            $this->uri_query = $query_data;

            // добавляем к полученным данным $_REQUEST
            // именно в таком порядке, чтобы POST имел преимущество над GET
            $_REQUEST = array_merge($query_data, $_REQUEST);

        }

        $this->uri = $this->uri_before_remap = $uri;
        $this->uri_absolute = $config->root . $uri;

        // разбиваем URL на сегменты
        $segments = explode('/', $uri);

        // Определяем контроллер из первого сегмента
        if (isset($segments[1])) { $this->uri_controller = $segments[1]; }

        // Определяем действие из второго сегмента
        if (isset($segments[2])) { $this->uri_action = $segments[2]; }

        // Определяем параметры действия из всех остальных сегментов
        if (sizeof($segments)>3){
            $this->uri_params = array_slice($segments, 3);
        }

       // var_dump($segments);
        return true;

    }




    /**
     * Запускает выбранное действие контроллера
     */
    public function runController(){

        $config = cmsConfig::getInstance();

        // контроллер и экшен по-умолчанию
        if (!$this->uri_controller){ $this->uri_controller = $config->ct_autoload;	}
        if (!$this->uri_action) { $this->uri_action = 'index'; }


        if (!self::isControllerExists($this->uri_controller)) {
            $this->uri_action     = $this->uri_controller;
            $this->uri_controller = $config->ct_default;
        }

        $this->controller = $this->uri_controller;

        if ($this->controller && !preg_match('/^[a-z]{1}[a-z0-9_]*$/', $this->controller)){
            //self::error404();
        }

        // загружаем контроллер
        $controller = self::getController($this->controller, $this->uri_action);


        // сохраняем в контроллере название текущего экшена
        $controller->current_action = $this->uri_action;

    }





    /**
     * Показывает сообщение об ошибке и завершает работу
     * @param string $message
     */
    public static function error($message, $details=''){

        if(ob_get_length()) { ob_end_clean(); }
        header('HTTP/1.0 503 Service Unavailable');
        header('Status: 503 Service Unavailable');
        echo '<h1>503 Service Unavailable</h1>';
        die();

    }

    /**
     * Показывает сообщение об ошибке 404 и завершает работу
     */
    public static function error404(){

        if(ob_get_length()) { ob_end_clean(); }

        header("HTTP/1.0 404 Not Found");
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");

        die();

    }


    /**
     * Устанавливает соединение с БД
     *
     */
    public function connectDB(){
        $database = new Database();
        $db = $database->getConnection();
        return $db;
    }


}
