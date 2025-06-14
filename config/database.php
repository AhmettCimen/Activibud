<?php
class Database
{
    //localde çalışrken
    private $local_host = "localhost";
    private $local_db_name = "testActivity2";
    private $local_username = "root";
    private $local_password = "";

    //hosting için
    private $prod_host = "95.130.171.20";
    private $prod_db_name = "dbstorage23360859073";
    private $prod_username = "dbusr23360859073";
    private $prod_password = "8nKKcCvzFJDV";

    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {

            if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
                $this->conn = new PDO(
                    "mysql:host=" . $this->local_host . ";dbname=" . $this->local_db_name,
                    $this->local_username,
                    $this->local_password
                );
            } else {

                $this->conn = new PDO(
                    "mysql:host=" . $this->prod_host . ";dbname=" . $this->prod_db_name,
                    $this->prod_username,
                    $this->prod_password
                );
            }

            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Bağlantı hatası: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>