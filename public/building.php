<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['building_id'])) {
    die("❌ No building selected.");
}

$building_id = (int) $_GET['building_id'];

// Default filters
$day = $_GET['day'] ?? 'Mon';
$slot = isset($_GET['slot']) ? (int)$_GET['slot'] : 1;

// Validation
$valid_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
if (!in_array($day, $valid_days)) $day = 'Mon';
if ($slot < 1 || $slot > 8) $slot = 1;

$slots = [
    1 => '09:00-10:00',
    2 => '10:00-11:00',
    3 => '11:00-12:00',
    4 => '12:00-13:00',
    5 => '13:00-14:00',
    6 => '14:00-15:00',
    7 => '15:00-16:00',
    8 => '16:00-17:00'
];

// Fetch building info
$stmt = $conn->prepare("SELECT building_name FROM buildings WHERE building_id = ?");
$stmt->execute([$building_id]);
$building = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$building) {
    die("❌ Building not found.");
}

// Fetch rooms
$stmt = $conn->prepare("
    SELECT r.floor_no, r.room_number, s.status
    FROM rooms r
    JOIN schedules s ON r.room_id = s.room_id
    WHERE r.building_id = ? AND s.day_of_week = ? AND s.slot_id = ?
    ORDER BY r.floor_no, r.room_number
");
$stmt->execute([$building_id, $day, $slot]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by floor
$floors = [];
foreach ($rooms as $room) {
    $floors[$room['floor_no']][] = $room;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page specific CSS -->
<link rel="stylesheet" href="assets/css/building.css">

<div class="building-page">
    <div class="building-header">
        <div class="building-icon">
            <svg width="48" height="48" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="12" y="24" width="40" height="28" rx="4" fill="#e3f0ff" stroke="#007bff" stroke-width="2" />
                <rect x="24" y="36" width="6" height="8" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                <rect x="34" y="36" width="6" height="8" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                <rect x="24" y="48" width="16" height="8" fill="#007bff" stroke="#007bff" stroke-width="1" />
                <rect x="30" y="48" width="4" height="8" fill="#fff" stroke="#007bff" stroke-width="1" />
                <rect x="20" y="28" width="24" height="6" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                <rect x="28" y="16" width="8" height="8" fill="#007bff" stroke="#007bff" stroke-width="1" />
                <rect x="26" y="12" width="12" height="6" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
            </svg>
        </div>
        <h2><?= htmlspecialchars($building['building_name']) ?></h2>
    </div>

    <div class="filter-section">
        <h3>Select Time Slot</h3>
        <!-- Filter Form -->
        <form method="get" class="filter-form">
            <input type="hidden" name="building_id" value="<?= $building_id ?>">

            <div class="form-group">
                <label for="day-select">Day:</label>
                <select id="day-select" name="day" class="form-control">
                    <?php foreach ($valid_days as $d): ?>
                        <option value="<?= $d ?>" <?= $d === $day ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="slot-select">Time:</label>
                <select id="slot-select" name="slot" class="form-control">
                    <?php foreach ($slots as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $id === $slot ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="filter-button">Show Rooms</button>
        </form>
    </div>

    <div class="status-legend">
        <div class="legend-item">
            <div class="legend-color vacant"></div>
            <span>Vacant</span>
        </div>
        <div class="legend-item">
            <div class="legend-color occupied"></div>
            <span>Occupied</span>
        </div>
    </div>

    <div class="floors-container">
        <?php if (empty($floors)): ?>
            <div class="no-rooms">
                <p>No rooms found for this building at the selected time.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($floors as $floor_no => $floorRooms): ?>
            <div class="floor-section">
                <h3 class="floor-title">Floor <?= $floor_no ?></h3>
                <div class="rooms-grid">
                    <?php foreach ($floorRooms as $room): ?>
                        <div class="room-card <?= strtolower($room['status']) ?>">
                            <div class="room-number"><?= htmlspecialchars($room['room_number']) ?></div>
                            <div class="room-status"><?= $room['status'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="navigation-buttons">
        <a href="index.php" class="back-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
            Back to Buildings
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>