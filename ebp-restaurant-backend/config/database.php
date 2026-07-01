<?php

class Database
{

    private $host = "localhost";
    private $socket = "/opt/lampp/var/mysql/mysql.sock";
    private $dbname = "ebp_restaurant_db";
    private $username = "root";
    private $password = "root";


    public function connect()
    {

        try {

            $pdo = new PDO(

                "mysql:host={$this->host};unix_socket={$this->socket};dbname={$this->dbname};charset=utf8mb4",

                $this->username,
                $this->password

            );


            $pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );


            return $pdo;


        } catch(PDOException $e){

            die(
                json_encode([
                    "success"=>false,
                    "message"=>$e->getMessage()
                ])
            );

        }

    }

}
