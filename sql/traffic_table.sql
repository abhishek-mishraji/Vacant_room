-- ====================================
-- Traffic Tracking Table
-- ====================================
USE find_vacant_room;

-- Create the traffic table if it doesn't exist
CREATE TABLE IF NOT EXISTS site_traffic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(255) NOT NULL,
    visitor_ip VARCHAR(45) NOT NULL,
    user_agent TEXT,
    visit_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    session_id VARCHAR(255),
    visitor_id VARCHAR(50)
);

-- Create an index on visit_time for faster querying
CREATE INDEX idx_visit_time ON site_traffic(visit_time);

-- Create an index on visitor_id for faster unique visitor counting
CREATE INDEX idx_visitor_id ON site_traffic(visitor_id);
