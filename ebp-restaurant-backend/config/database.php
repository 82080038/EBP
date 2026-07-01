<?php

class Database
{

    private $host = "localhost";
    private $dbname = "ebp_restaurant_erp";
    private $username = "root";
    private $password = "";


    public function connect()
    {

        try {

            $pdo = new PDO(

                "mysql:host={$this->host};
                 dbname={$this->dbname};
                 charset=utf8mb4",

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
