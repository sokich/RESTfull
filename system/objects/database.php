<?php
/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 16:20
 */
class Database extends API {
    private $host = "localhost";
    private $db_name = "220v";
    private $username = "220v";
    private $password = "220v";
    public $conn;

    public function getConnection () {
        $this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}