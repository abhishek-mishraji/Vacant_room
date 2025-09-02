<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../admin/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_dashboard.php');
    exit;
}

$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$day = $_POST['day'] ?? 'Mon';
$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : 1;
$status = $_POST['status'] === 'Vacant' ? 'Vacant' : 'Occupied';

// Validate input
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
if ($room_id <= 0 || !in_array($day, $days) || $slot < 1 || $slot > 8) {
    die("❌ Invalid input.");
}

// Always update (rows must exist in schedules table)
$stmt = $conn->prepare("
    UPDATE schedules
    SET status = ?
    WHERE room_id = ? AND day_of_week = ? AND slot_id = ?
");
$stmt->execute([$status, $room_id, $day, $slot]);

// Redirect back to admin dashboard, preserving filters
$building_id = isset($_POST['building_id']) ? (int)$_POST['building_id'] : 0;
header("Location: admin_dashboard.php?building_id=$building_id&day=$day&slot=$slot&saved_room=$room_id");
exit;
