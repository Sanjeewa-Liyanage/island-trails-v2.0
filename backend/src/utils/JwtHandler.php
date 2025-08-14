<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler{
    private static $secretKey = "island-trails";
    private static $issuer = "island-trails_issuer";
    private static $audience =  "island-trails_audience";
    private static $issueAt;
    private static $expire;

    public static function generateToken($user){
        self::$issueAt = time();
        self::$expire = self::$issueAt + (60 * 60 * 24); // Token valid for 24 hours
        $payLoad = [
            "iss" => self::$issuer,
            "aud" => self::$audience,
            "iat" => self::$issueAt,
            "exp"=> self::$expire,
            "data" =>[
                "id" => $user['id'] ?? null,
                "username" => $user['name'] ?? null,
                "email" => $user['email'] ?? null,
                "role" => $user['role'] ?? null,
            ]
        ];
        return JWT::encode($payLoad, self::$secretKey, 'HS256');

    }    public static function decodeToken($token){
        try{
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return [
                'valid' => true,
                'data' => (array)$decoded->data,
            ];
        }catch(Exception $ex){
            return[
                'valid'=> false,
                'data'=> $ex->getMessage(),
            ];
        }
    }
    
    /**
     * Extract and validate JWT token from Authorization header
     * 
     * @return array Token data or error information
     */
    public static function getTokenFromHeader() {
        // Handle both CLI and web environments
        $headers = function_exists('getallheaders') ? getallheaders() : self::getAllHeadersManual();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            return self::decodeToken($token);
        }
        
        return [
            'valid' => false,
            'data' => 'No token found in Authorization header'
        ];
    }
    
    /**
     * Manual implementation of getallheaders for environments where it's not available
     */
    private static function getAllHeadersManual() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}