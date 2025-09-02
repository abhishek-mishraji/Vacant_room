<?php
// Traffic tracking module
function track_page_visit()
{
    global $conn;

    if (!$conn) {
        return false; // No DB connection
    }

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get visitor information
    $page_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $visitor_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $session_id = session_id();

    // Insert visit into database
    $stmt = $conn->prepare("
        INSERT INTO site_traffic (page_url, visitor_ip, user_agent, session_id) 
        VALUES (?, ?, ?, ?)
    ");

    try {
        $stmt->execute([$page_url, $visitor_ip, $user_agent, $session_id]);
        return true;
    } catch (PDOException $e) {
        // Silent fail - don't disrupt user experience if tracking fails
        error_log('Traffic tracking error: ' . $e->getMessage());
        return false;
    }
}

// Get traffic statistics
function get_traffic_stats()
{
    global $conn;

    if (!$conn) {
        return null;
    }

    try {
        // Total visits
        $stmt = $conn->query("SELECT COUNT(*) as total FROM site_traffic");
        $total_visits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Unique visitors (by IP)
        $stmt = $conn->query("SELECT COUNT(DISTINCT visitor_ip) as unique_visitors FROM site_traffic");
        $unique_visitors = $stmt->fetch(PDO::FETCH_ASSOC)['unique_visitors'];

        // Visits today
        $stmt = $conn->query("SELECT COUNT(*) as today FROM site_traffic WHERE DATE(visit_time) = CURDATE()");
        $today_visits = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

        // Unique visitors today
        $stmt = $conn->query("SELECT COUNT(DISTINCT visitor_ip) as today_unique FROM site_traffic WHERE DATE(visit_time) = CURDATE()");
        $today_unique = $stmt->fetch(PDO::FETCH_ASSOC)['today_unique'];

        // Page popularity
        $stmt = $conn->query("
            SELECT page_url, COUNT(*) as visits 
            FROM site_traffic 
            GROUP BY page_url 
            ORDER BY visits DESC 
            LIMIT 5
        ");
        $popular_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Visits per day (last 7 days)
        $stmt = $conn->query("
            SELECT DATE(visit_time) as date, COUNT(*) as visits 
            FROM site_traffic 
            WHERE visit_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(visit_time) 
            ORDER BY date DESC
        ");
        $daily_visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_visits' => $total_visits,
            'unique_visitors' => $unique_visitors,
            'today_visits' => $today_visits,
            'today_unique' => $today_unique,
            'popular_pages' => $popular_pages,
            'daily_visits' => $daily_visits
        ];
    } catch (PDOException $e) {
        error_log('Traffic stats error: ' . $e->getMessage());
        return null;
    }
}
