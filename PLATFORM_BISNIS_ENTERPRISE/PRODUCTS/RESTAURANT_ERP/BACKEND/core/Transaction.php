<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

class Transaction
{

    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }



    public function begin()
    {

        $this->db->beginTransaction();

    }



    public function commit()
    {

        $this->db->commit();

    }



    public function rollback()
    {

        $this->db->rollBack();

    }

}
