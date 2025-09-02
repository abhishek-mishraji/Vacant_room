<?php
// This script shows database table sizes and row counts to help monitor database growth
// Accessible to admins only

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

// Ensure only admins can access this page
// if (!is_admin_logged_in()) {
//     header('Location: ../public/login.php');
//     exit;
// }

$tables_info = [];
$total_size = 0;
$total_rows = 0;

try {
    // Get database name
    $stmt = $conn->query("SELECT DATABASE() as db_name");
    $db_name = $stmt->fetch(PDO::FETCH_ASSOC)['db_name'];

    // Get table information
    $stmt = $conn->query("
        SELECT 
            table_name,
            table_rows,
            data_length,
            index_length,
            (data_length + index_length) as total_size
        FROM information_schema.tables
        WHERE table_schema = '$db_name'
        ORDER BY total_size DESC
    ");

    $tables_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    foreach ($tables_info as $table) {
        $total_size += $table['total_size'];
        $total_rows += $table['table_rows'];
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Format bytes to human-readable format
function format_size($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Header
require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../public/assets/css/admin.css">
<style>
    .db-stats {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }

    .stats-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .stats-table th,
    .stats-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .stats-table th {
        background-color: #f5f5f5;
        font-weight: 600;
    }

    .stats-table tr:hover {
        background-color: #f9f9f9;
    }

    .stats-summary {
        margin: 20px 0;
        padding: 15px;
        background-color: #f0f7ff;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
    }

    .summary-item {
        text-align: center;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #007bff;
    }

    .summary-label {
        font-size: 0.9rem;
        color: #555;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="db-stats">
    <h1>Database Size Monitor</h1>
    <p>Overview of database tables and their sizes</p>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="stats-summary">
        <div class="summary-item">
            <div class="summary-value"><?= count($tables_info) ?></div>
            <div class="summary-label">Tables</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?= number_format($total_rows) ?></div>
            <div class="summary-label">Total Rows</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?= format_size($total_size) ?></div>
            <div class="summary-label">Total Size</div>
        </div>
    </div>

    <?php if ($total_size > 50 * 1024 * 1024): // If over 50MB 
    ?>
        <div class="alert alert-warning">
            <strong>Note:</strong> Your database size is getting large. Consider implementing data retention policies or
            archiving older data.
        </div>
    <?php endif; ?>

    <table class="stats-table">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Rows</th>
                <th>Data Size</th>
                <th>Index Size</th>
                <th>Total Size</th>
                <th>% of DB</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables_info as $table): ?>
                <tr>
                    <td><?= htmlspecialchars($table['table_name']) ?></td>
                    <td><?= number_format($table['table_rows']) ?></td>
                    <td><?= format_size($table['data_length']) ?></td>
                    <td><?= format_size($table['index_length']) ?></td>
                    <td><?= format_size($table['total_size']) ?></td>
                    <td><?= round(($table['total_size'] / $total_size) * 100, 2) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h2>Database Management Tips</h2>
        <ul>
            <li>Use the <a href="cleanup_traffic_data.php">data cleanup tool</a> to remove old traffic data.</li>
            <li>Consider <a href="create_traffic_summary_tables.php">setting up summary tables</a> to aggregate
                statistics.</li>
            <li>Run the <a href="aggregate_traffic_data.php">data aggregation tool</a> daily to maintain summary
                statistics.</li>
            <li>For large databases, set up a scheduled task to run cleanup scripts automatically.</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>