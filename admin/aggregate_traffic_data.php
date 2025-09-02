<?php
// This script aggregates daily traffic data into summary tables
// It should be run once per day (via cron job, typically after midnight)

require_once __DIR__ . '/../config/db.php';

try {
    // Get yesterday's date (or another date to process)
    $process_date = date('Y-m-d', strtotime('-1 day'));

    // Begin transaction for consistency
    $conn->beginTransaction();

    // 1. Aggregate daily visits and unique visitors
    $stmt = $conn->prepare("
        INSERT INTO traffic_daily_summary (summary_date, total_visits, unique_visitors)
        SELECT 
            DATE(visit_time) as day,
            COUNT(*) as total_visits,
            COUNT(DISTINCT CASE WHEN visitor_id IS NOT NULL AND visitor_id != '' 
                              THEN visitor_id ELSE visitor_ip END) as unique_visitors
        FROM site_traffic
        WHERE DATE(visit_time) = ?
        GROUP BY DATE(visit_time)
        ON DUPLICATE KEY UPDATE
            total_visits = VALUES(total_visits),
            unique_visitors = VALUES(unique_visitors)
    ");
    $stmt->execute([$process_date]);

    // 2. Aggregate page popularity
    $stmt = $conn->prepare("
        INSERT INTO traffic_page_summary (summary_date, page_url, visit_count, unique_visitors)
        SELECT 
            DATE(visit_time) as day,
            page_url,
            COUNT(*) as visit_count,
            COUNT(DISTINCT CASE WHEN visitor_id IS NOT NULL AND visitor_id != '' 
                              THEN visitor_id ELSE visitor_ip END) as unique_visitors
        FROM site_traffic
        WHERE DATE(visit_time) = ?
        GROUP BY DATE(visit_time), page_url
        ON DUPLICATE KEY UPDATE
            visit_count = VALUES(visit_count),
            unique_visitors = VALUES(unique_visitors)
    ");
    $stmt->execute([$process_date]);

    // 3. Optionally delete raw data after successful aggregation
    // Uncomment the following lines if you want to delete raw data after aggregation
    /*
    $stmt = $conn->prepare("
        DELETE FROM site_traffic 
        WHERE DATE(visit_time) = ? 
    ");
    $stmt->execute([$process_date]);
    */

    // Commit the transaction
    $conn->commit();

    echo "Successfully aggregated traffic data for $process_date";
} catch (PDOException $e) {
    // Rollback the transaction on error
    $conn->rollBack();
    error_log("Traffic aggregation error: " . $e->getMessage());
    echo "Error during aggregation: " . $e->getMessage();
}
