<?php
// Script to add test data for past days
require_once __DIR__ . '/../config/db.php';

try {
    // Generate visits for the past 6 days (we already have today)
    for ($i = 1; $i <= 6; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $visits = rand(5, 20); // Random number of visits

        // Insert into daily summary
        $stmt = $conn->prepare("
            INSERT INTO traffic_daily_summary (summary_date, total_visits, unique_visitors)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE total_visits = VALUES(total_visits), unique_visitors = VALUES(unique_visitors)
        ");
        $stmt->execute([$date, $visits, ceil($visits * 0.7)]);

        echo "Added $visits visits for $date<br>";
    }

    echo "Test data added successfully.";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
