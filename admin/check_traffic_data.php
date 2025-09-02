<?php
// Script to check traffic data
require_once __DIR__ . '/../config/db.php';

try {
    // Check site_traffic table
    $stmt = $conn->query('SELECT COUNT(*) as count FROM site_traffic');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Number of records in site_traffic: " . $result['count'] . "<br>";

    // Check if summary tables exist
    $stmt = $conn->query("SHOW TABLES LIKE 'traffic_daily_summary'");
    $daily_exists = $stmt->rowCount() > 0;
    echo "traffic_daily_summary table exists: " . ($daily_exists ? 'Yes' : 'No') . "<br>";

    $stmt = $conn->query("SHOW TABLES LIKE 'traffic_page_summary'");
    $page_exists = $stmt->rowCount() > 0;
    echo "traffic_page_summary table exists: " . ($page_exists ? 'Yes' : 'No') . "<br>";

    // Debug the get_traffic_stats function
    require_once __DIR__ . '/../includes/traffic_tracker.php';
    $stats = get_traffic_stats();

    echo "<pre>";
    print_r($stats);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
