<?php
class DatabaseConnection {
    private static $conn;

    public static function getConnection(){
        if (self::$conn == null){
            try {
                $host = "island-trails-sanjeevaliyanage980-435e.d.aivencloud.com";
                $dbname = "defaultdb";
                $port = "18321";
                $username = "avnadmin";
                $password = "AVNS_cnkBo1AqETaOaFbtLR-";

                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;sslmode=require";

                $options = [
                    PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];

                self::$conn = new PDO($dsn, $username, $password, $options);

            } catch(PDOException $e){
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
?>