<?php
// One-time script to create the traffic table
require_once __DIR__ . '/../config/db.php';

$sql = file_get_contents(__DIR__ . '/../sql/traffic_table.sql');

try {
    $conn->exec($sql);
    echo "Traffic table created successfully!";
} catch (PDOException $e) {
    echo "Error creating traffic table: " . $e->getMessage();
}
