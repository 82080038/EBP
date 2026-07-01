<?php

class Transaction
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

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
