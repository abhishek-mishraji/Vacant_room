-- ====================================
-- Create Database
-- ====================================
CREATE DATABASE IF NOT EXISTS find_vacant_room;
USE find_vacant_room;

-- ====================================
-- Tables
-- ====================================

-- Buildings Table
CREATE TABLE buildings (
    building_id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(100) NOT NULL
);

-- Rooms Table
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    floor_no INT NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    FOREIGN KEY (building_id) REFERENCES buildings(building_id)
);

-- Schedules Table
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    day_of_week ENUM('Mon','Tue','Wed','Thu','Fri') NOT NULL,
    slot_id INT NOT NULL, -- 1=9-10, 2=10-11, ... 8=4-5
    status ENUM('Vacant','Occupied') DEFAULT 'Occupied',
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
);

-- Admins Table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL -- store hashed passwords
);

-- ====================================
-- Insert Default Data
-- ====================================

-- Insert 20 Buildings
INSERT INTO buildings (building_name)
SELECT CONCAT('Building ', n) 
FROM (
    SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
    UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
    UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
) t;

-- Insert Rooms (2000 total)
INSERT INTO rooms (building_id, floor_no, room_number)
SELECT b.building_id, f.floor_no, CONCAT(f.floor_no, LPAD(r.room_no,2,'0')) AS room_number
FROM buildings b
CROSS JOIN (
    SELECT 0 AS floor_no UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
) f
CROSS JOIN (
    SELECT 1 AS room_no UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) r;

-- Insert Schedules (80,000 total, default Occupied)
INSERT INTO schedules (room_id, day_of_week, slot_id, status)
SELECT r.room_id, d.day, s.slot, 'Occupied'
FROM rooms r
CROSS JOIN (
    SELECT 'Mon' AS day UNION ALL SELECT 'Tue' UNION ALL SELECT 'Wed' UNION ALL SELECT 'Thu' UNION ALL SELECT 'Fri'
) d
CROSS JOIN (
    SELECT 1 AS slot UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
) s;

-- Insert Default Admin User (username=admin, password=admin123 hashed with SHA2)
INSERT INTO admins (username, password)
VALUES ('admin', SHA2('admin123', 256));
