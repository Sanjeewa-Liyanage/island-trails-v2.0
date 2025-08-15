<?php

class User extends Model{
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    
    public function __construct($id = null, $name = null, $email = null, $password = null, $role = null) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function emailExists($email) {
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function create(){
        // Check if email already exists
        if ($this->emailExists($this->email)) {
            return false; // or throw an exception: throw new Exception("Email already exists");
        }
        
        $conn = DatabaseConnection::getConnection();
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $this->role);
        $success = $stmt->execute();
        return $success;        
    }


    public function update(){}
    public function delete(){
        $conn = DatabaseConnection::getConnection();
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $success = $stmt->execute();
        return $success;
    }

    public function read(){
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function readAll(){
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}




