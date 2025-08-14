<?php
require_once 'src/utils/ApiResourceBase.php';
require_once 'src/utils/JwtHandler.php';
require_once 'src/classes/User.php';
require_once 'src/database/connection.php';

class UserApi extends ApiResourceBase{
   public function __construct(){
    $this -> setRoles([
        "create" => ["admin", "user"],
        "login" => ["customer", "admin", null],
        "readAll" => ["admin"],
        "read" => ["admin"],
        "delete" => ["admin"],
    ]);
   }

   
   public function checkRoles($role, $action) {
       if ($action === 'signUp') {
           return true; 
       }
       return parent::checkRoles($role, $action);
   }

   public function signUp($data){
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->role = 'customer'; 

        if ($user->create()) {
            return ['status' => 'success', 'message' => 'User created successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to create user.'];
        }
   }
   public function delete($data){
    $user = $this->getAuthenticatedUser();
        if (!$user) {
            return [
                "message" => "Invalid or expired token. Please log in again.",
                "status" => "error"
            ];
        }

        if(!$this->checkRoles($user['role'], 'delete')){
            return [
                "message" => "Unauthorized: Admin access required",
                "status" => "error",
            ];
        }

        if (!isset($data['id'])) {
            return ['status' => 'error', 'message' => 'User ID is required'];
        }

        $user = new User();
        $user->id = $data['id'];
        if ($user->delete()) {
            return ['status' => 'success', 'message' => 'User deleted successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to delete user.'];
        }
   }
   
   public function login($data){
     if (!isset($data['email'])) {
        return [ "status" => "error", "message" => "Email is required" ];
    }
    if (!isset($data['password'])) {
        return [ "status" => "error", "message" => "Password is required" ];
    }
    $conn = DatabaseConnection::getConnection();
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $data['email']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        if (password_verify($data['password'], $result['password'])) {
            
            $result['password'] = "";
            
            
            $token = JwtHandler::generateToken($result);

            return [
                "status" => "success",
                "message" => "Login successful",
                "token" => $token,
                "user" => $result
            ];
        } else {
            return [
                "status" => "error",
                "message" => "Invalid password"
            ];
        }
    } else {
        return [
            "status" => "error",
            "message" => "User not found"
        ];
    }

   }

   public function readAll($data = null){
        $user = new User();
        $users = $user->readAll();
        
        if ($users !== false) {
            return [
                'status' => 'success', 
                'message' => 'Users retrieved successfully',
                'data' => $users
            ];
        } else {
            return [
                'status' => 'error', 
                'message' => 'Failed to retrieve users'
            ];
        }
   }

   public function read($data){
        if (!isset($data['id'])) {
            return [
                'status' => 'error', 
                'message' => 'User ID is required'
            ];
        }

        $user = new User();
        $user->id = $data['id'];
        $userData = $user->read();
        
        if ($userData) {
            return [
                'status' => 'success', 
                'message' => 'User retrieved successfully',
                'data' => $userData
            ];
        } else {
            return [
                'status' => 'error', 
                'message' => 'User not found'
            ];
        }
   }

    
}