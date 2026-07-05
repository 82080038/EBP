<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleUserController
{
    // Simple endpoint to get users without middleware
    public function getUsers($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT u.user_id, u.username, u.email, u.full_name, u.status, u.created_at
                FROM users u
                ORDER BY u.username";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($users);
    }
}
