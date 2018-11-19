<?php
/**
 * Created by PhpStorm.
 * User: igorsaakyan
 * Date: 10.08.17
 * Time: 17:05
 */

class Product extends API{
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $created;

    public function __construct($db){
        $this->conn = $db;
    }

    function read() {
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
        return $stmt;
    }
    function readOne(){
        $query = "SELECT v.name as vendor_name, p.title, p.id, p.short_description, p.price_retail
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    vendor v
                        ON p.vendor_id = v.vid
               WHERE
                p.id = ?
            LIMIT
                0,1";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            $this->name = $row['title'];
            $this->price = $row['price_retail'];
            $this->description = $row['short_description'];
            $this->category_name = $row['vendor_name'];
        }
        else {
            echo json_encode(
                array("message" => "No products found with id=".$this->id)
            );
            die();
        }

    }

    function create() {
        $query = "INSERT INTO 
            " . $this->table_name . "
            SET
                name=:name, price=:price, description=:description, category_id=: category_id, created=: created";

        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->category_id=htmlspecialchars(strip_tags($this->category_id));
        $this->created=htmlspecialchars(strip_tags($this->created));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created", $this->created);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }


    function update() {
        $query = "UPDATE
                " . $this->table_name . "
            SET
                name = :name,
                price = :price,
                description = :description,
                category_id = :category_id
            WHERE
                id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->category_id=htmlspecialchars(strip_tags($this->category_id));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        } else {
            return false;
        }
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true; 
        } 
        return false;
    }

    function search($keywords){
        $query = "SELECT
                    c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
                FROM
                    " . $this->table_name . " p
                    LEFT JOIN
                        categories c
                            ON p.category_id = c.id
                WHERE
                    p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?
                ORDER BY
                    p.created DESC";
    
        $stmt = $this->conn->prepare($query);
    
        $keywords=htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        $stmt->execute();
 
        return $stmt;
    }


    public function count() {
        $query = "SELECT COUNT (*) as total_rows FROM " . $this->table_name . ""; 

        $stmt = $this->conn->prepare($query); 
        $stmt->execute(); 
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        return $row['total_rows']; 
    }
}