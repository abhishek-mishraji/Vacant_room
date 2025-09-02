<?php
// This script creates and populates summary tables for traffic statistics
// Run this script first to create the tables

require_once __DIR__ . '/../config/db.php';

try {
    // Create daily summary table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS traffic_daily_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            summary_date DATE NOT NULL,
            total_visits INT NOT NULL DEFAULT 0,
            unique_visitors INT NOT NULL DEFAULT 0,
            UNIQUE KEY (summary_date)
        )
    ");

    // Create page popularity table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS traffic_page_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            summary_date DATE NOT NULL,
            page_url VARCHAR(255) NOT NULL,
            visit_count INT NOT NULL DEFAULT 0,
            unique_visitors INT NOT NULL DEFAULT 0,
            UNIQUE KEY (summary_date, page_url)
        )
    ");

    echo "Summary tables created successfully.<br>";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
