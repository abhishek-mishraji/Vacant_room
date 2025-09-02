<?php
// This script should be run as a cron job (e.g., daily or weekly)
// It purges old traffic data while keeping recent data for statistics

require_once __DIR__ . '/../config/db.php';

// Define your retention period (e.g., 90 days)
$retention_days = 90;

try {
    // Delete records older than retention period
    $stmt = $conn->prepare("
        DELETE FROM site_traffic 
        WHERE visit_time < DATE_SUB(NOW(), INTERVAL ? DAY)
    ");

    $stmt->execute([$retention_days]);

    $deleted_count = $stmt->rowCount();

    // Log the cleanup
    error_log("Traffic data cleanup: Removed $deleted_count records older than $retention_days days.");

    // Optionally optimize the table after large deletions
    if ($deleted_count > 1000) {
        $conn->exec("OPTIMIZE TABLE site_traffic");
        error_log("Optimized site_traffic table after large deletion.");
    }

    echo "Successfully cleaned up old traffic data. Removed $deleted_count records.";
} catch (PDOException $e) {
    error_log("Traffic data cleanup error: " . $e->getMessage());
    echo "Error during cleanup: " . $e->getMessage();
}
