<?php
// This script adds the visitor_id column to the site_traffic table
// Run this script once to update your database structure

require_once __DIR__ . '/../config/db.php';

// Check if connection is available
if (!isset($conn) || !$conn) {
    die("Database connection not available");
}

try {
    // Check if the column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM site_traffic LIKE 'visitor_id'");
    $column_exists = $stmt->rowCount() > 0;

    if (!$column_exists) {
        // Add the visitor_id column
        $conn->exec("ALTER TABLE site_traffic ADD COLUMN visitor_id VARCHAR(50)");

        // Add an index on visitor_id
        $conn->exec("CREATE INDEX idx_visitor_id ON site_traffic(visitor_id)");

        echo "Success: visitor_id column added to site_traffic table.<br>";
    } else {
        echo "Notice: visitor_id column already exists in site_traffic table.<br>";
    }

    echo "Database update completed successfully.";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
