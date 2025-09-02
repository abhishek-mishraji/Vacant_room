<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

// Fetch buildings
$stmt = $conn->query("SELECT building_id, building_name FROM buildings ORDER BY building_id");
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine default day & slot
$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$today = $days[date('w')];
if (!in_array($today, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'])) {
    $today = 'Mon'; // fallback to Monday if weekend
}

$currentHour = (int)date('G'); // 0–23
$slot = 1;
if ($currentHour >= 9 && $currentHour < 17) {
    $slot = $currentHour - 8; // map 9am→1, 10am→2, … 16→8
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/index.css">
<link rel="stylesheet" href="/assets/css/ads.css">

<div class="hero-section">
    <div class="hero-content">
        <h1>Find Available Rooms</h1>
        <p>Select a building to view available rooms for classes and meetings</p>
    </div>
</div>

<div class="buildings-section">
    <h2 class="section-title">Pick a Block</h2>

    <div class="buildings enhanced-buildings">
        <?php foreach ($buildings as $b): ?>
            <a class="building-card"
                href="building.php?building_id=<?= $b['building_id'] ?>&day=<?= $today ?>&slot=<?= $slot ?>">
                <div class="building-icon">
                    <!-- Realistic Building SVG Icon -->
                    <svg width="40" height="40" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="12" y="24" width="40" height="28" rx="4" fill="#e3f0ff" stroke="#007bff"
                            stroke-width="2" />
                        <rect x="24" y="36" width="6" height="8" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                        <rect x="34" y="36" width="6" height="8" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                        <rect x="24" y="48" width="16" height="8" fill="#007bff" stroke="#007bff" stroke-width="1" />
                        <rect x="30" y="48" width="4" height="8" fill="#fff" stroke="#007bff" stroke-width="1" />
                        <rect x="20" y="28" width="24" height="6" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                        <rect x="28" y="16" width="8" height="8" fill="#007bff" stroke="#007bff" stroke-width="1" />
                        <rect x="26" y="12" width="12" height="6" fill="#b3d1ff" stroke="#007bff" stroke-width="1" />
                    </svg>
                </div>
                <div class="building-name">
                    <?= htmlspecialchars($b['building_name']) ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="info-section">
    <div class="info-card">
        <div class="info-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="#007bff" stroke-width="2" />
                <path d="M12 8V12" stroke="#007bff" stroke-width="2" stroke-linecap="round" />
                <circle cx="12" cy="16" r="1" fill="#007bff" />
            </svg>
        </div>
        <h3>Quick Access</h3>
        <p>Find vacant rooms based on your current time and schedule.</p>
    </div>
    <div class="info-card">
        <div class="info-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="6" width="18" height="15" rx="2" stroke="#007bff" stroke-width="2" />
                <path d="M3 10H21" stroke="#007bff" stroke-width="2" />
                <path d="M8 3V6" stroke="#007bff" stroke-width="2" stroke-linecap="round" />
                <path d="M16 3V6" stroke="#007bff" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <h3>Real-Time Updates</h3>
        <p>Room availability status is regularly updated throughout the day.</p>
    </div>
</div>

<!-- Advertisement Section -->
<div class="ad-section">
    <!-- Banner Ad 468x60 -->
    <div class="ad-container">
        <div class="ad-label">Advertisement</div>
        <script type="text/javascript">
            atOptions = {
                'key': 'e1f2b480bca0a38321f98c241ef40f19',
                'format': 'iframe',
                'height': 60,
                'width': 468,
                'params': {}
            };
            document.write('<scr' + 'ipt type="text/javascript" src="//www.highperformancedformats.com/' +
                atOptions.key + '/invoke.js"></scr' + 'ipt>');
        </script>
    </div>

    <!-- Smartlink as text link -->
    <div class="sponsored-link-container">
        <a href="https://www.revenuecpmgate.com/ek1ksa68?key=63728b9e552639ff849828f2977ebf4c" target="_blank"
            class="sponsored-link">
            Sponsored: Discover more resources
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>