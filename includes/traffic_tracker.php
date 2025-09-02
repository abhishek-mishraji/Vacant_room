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

    // Generate or retrieve unique visitor ID using cookies
    $visitor_id = get_or_create_visitor_id();

    // Get visitor information
    $page_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $visitor_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $session_id = session_id();

    // Insert visit into database
    $stmt = $conn->prepare("
        INSERT INTO site_traffic (page_url, visitor_ip, user_agent, session_id, visitor_id) 
        VALUES (?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([$page_url, $visitor_ip, $user_agent, $session_id, $visitor_id]);
        return true;
    } catch (PDOException $e) {
        // Silent fail - don't disrupt user experience if tracking fails
        error_log('Traffic tracking error: ' . $e->getMessage());
        return false;
    }
}

// Function to get or create a unique visitor ID
function get_or_create_visitor_id()
{
    $cookie_name = 'fvr_visitor_id';
    $cookie_duration = 60 * 60 * 24 * 365; // 1 year in seconds

    // Check if the cookie exists
    if (isset($_COOKIE[$cookie_name])) {
        return $_COOKIE[$cookie_name];
    }

    // Generate a new unique ID
    $visitor_id = uniqid('v_', true);

    // Set the cookie (secure if HTTPS is detected)
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $http_only = true; // Better security
    $same_site = 'Lax'; // Better security while allowing normal navigation

    // Set the cookie with modern parameters
    if (PHP_VERSION_ID >= 70300) {
        // PHP 7.3.0 or higher - use the array options
        setcookie($cookie_name, $visitor_id, [
            'expires' => time() + $cookie_duration,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $http_only,
            'samesite' => $same_site
        ]);
    } else {
        // Older PHP versions
        setcookie($cookie_name, $visitor_id, time() + $cookie_duration, '/');
    }

    return $visitor_id;
}

// Get traffic statistics
function get_traffic_stats()
{
    global $conn;

    if (!$conn) {
        return null;
    }

    try {
        // Check if summary tables exist
        $stmt = $conn->query("SHOW TABLES LIKE 'traffic_daily_summary'");
        $summary_tables_exist = $stmt->rowCount() > 0;

        if ($summary_tables_exist) {
            // Use hybrid approach - raw data for today, summary tables for historical data

            // Total visits (combine today's raw data with historical summaries)
            $stmt = $conn->query("
                SELECT 
                    (SELECT COUNT(*) FROM site_traffic WHERE DATE(visit_time) = CURDATE()) +
                    IFNULL((SELECT SUM(total_visits) FROM traffic_daily_summary), 0) as total
            ");
            $total_visits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Unique visitors calculation is complex with aggregated data
            // We'll use raw data for today and summary data for history
            // Note: This is an approximation as we can't deduplicate across days in summary tables
            $stmt = $conn->query("
                SELECT 
                    (SELECT COUNT(DISTINCT 
                        CASE WHEN visitor_id IS NOT NULL AND visitor_id != '' 
                            THEN visitor_id ELSE visitor_ip END
                    ) FROM site_traffic WHERE DATE(visit_time) = CURDATE()) +
                    IFNULL((SELECT SUM(unique_visitors) FROM traffic_daily_summary), 0) as unique_total
            ");
            $unique_visitors = $stmt->fetch(PDO::FETCH_ASSOC)['unique_total'];

            // Today's metrics - use raw data
            $stmt = $conn->query("
                SELECT COUNT(*) as today 
                FROM site_traffic 
                WHERE DATE(visit_time) = CURDATE()
            ");
            $today_visits = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

            $stmt = $conn->query("
                SELECT COUNT(DISTINCT 
                    CASE WHEN visitor_id IS NOT NULL AND visitor_id != '' 
                        THEN visitor_id ELSE visitor_ip END
                ) as today_unique 
                FROM site_traffic 
                WHERE DATE(visit_time) = CURDATE()
            ");
            $today_unique = $stmt->fetch(PDO::FETCH_ASSOC)['today_unique'];

            // Popular pages - combine today's raw data with historical summaries
            $stmt = $conn->query("
                SELECT page_url, SUM(visits) as visits
                FROM (
                    SELECT page_url, COUNT(*) as visits
                    FROM site_traffic
                    WHERE DATE(visit_time) = CURDATE()
                    GROUP BY page_url
                    
                    UNION ALL
                    
                    SELECT page_url, SUM(visit_count) as visits
                    FROM traffic_page_summary
                    GROUP BY page_url
                ) combined_data
                GROUP BY page_url
                ORDER BY visits DESC
                LIMIT 5
            ");
            $popular_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Daily visits for the last 7 days - combine today's raw data with historical summaries
            $stmt = $conn->query("
                SELECT visit_date as date, visits
                FROM (
                    SELECT CURDATE() as visit_date, COUNT(*) as visits
                    FROM site_traffic
                    WHERE DATE(visit_time) = CURDATE()
                    
                    UNION ALL
                    
                    SELECT summary_date as visit_date, total_visits as visits
                    FROM traffic_daily_summary
                    WHERE summary_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      AND summary_date < CURDATE()
                ) combined_data
                ORDER BY visit_date DESC
                LIMIT 7
            ");
            $daily_visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fall back to original queries if summary tables don't exist

            // Total visits
            $stmt = $conn->query("SELECT COUNT(*) as total FROM site_traffic");
            $total_visits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Unique visitors (by visitor_id if available, fallback to IP)
            $stmt = $conn->query("
                SELECT COUNT(DISTINCT 
                    CASE 
                        WHEN visitor_id IS NOT NULL AND visitor_id != '' THEN visitor_id 
                        ELSE visitor_ip 
                    END
                ) as unique_visitors 
                FROM site_traffic
            ");
            $unique_visitors = $stmt->fetch(PDO::FETCH_ASSOC)['unique_visitors'];

            // Visits today
            $stmt = $conn->query("SELECT COUNT(*) as today FROM site_traffic WHERE DATE(visit_time) = CURDATE()");
            $today_visits = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

            // Unique visitors today
            $stmt = $conn->query("
                SELECT COUNT(DISTINCT 
                    CASE 
                        WHEN visitor_id IS NOT NULL AND visitor_id != '' THEN visitor_id 
                        ELSE visitor_ip 
                    END
                ) as today_unique 
                FROM site_traffic 
                WHERE DATE(visit_time) = CURDATE()
            ");
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
        }

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
