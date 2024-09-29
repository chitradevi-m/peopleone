<?php
require_once 'config.php';

function db_connect() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
        exit;
    }

    return $conn;
}

?>