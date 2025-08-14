<?php

class Packages extends Model {
    public $id;
    public $title;
    public $description;
    public $price;
    public $duration;
    public $image_url;
    public $created_at;

    public function __construct($id = null, $title = null, $description = null, $price = null, $duration = null, $image_url = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->duration = $duration;
        $this->image_url = $image_url;
    }

    public function create() {
        $conn = DatabaseConnection::getConnection();
        $sql = "INSERT INTO packages (title, description, price, duration, image_url) VALUES (:title, :description, :price, :duration, :image_url)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':image_url', $this->image_url);
        
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->lastInsertId();
        }
        
        return $success;
    }

    public function update() {
        $conn = DatabaseConnection::getConnection();
        $sql = "UPDATE packages SET title = :title, description = :description, price = :price, duration = :duration, image_url = :image_url WHERE id = :id";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':image_url', $this->image_url);
        
        return $stmt->execute();
    }

    public function delete() {
        $conn = DatabaseConnection::getConnection();
        $sql = "DELETE FROM packages WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    public function read() {
        $conn = DatabaseConnection::getConnection();
        
        if ($this->id) {
            
            $sql = "SELECT * FROM packages WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->title = $result['title'];
                $this->description = $result['description'];
                $this->price = $result['price'];
                $this->duration = $result['duration'];
                $this->image_url = $result['image_url'];
                $this->created_at = $result['created_at'];
                return $result;
            }
            return false;
        }
        return false; 
    }

    public function readAll() {
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT * FROM packages ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}