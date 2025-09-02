<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/traffic_tracker.php';

// Get traffic statistics
$stats = get_traffic_stats();

// If no stats, handle gracefully
if (!$stats) {
    $stats = [
        'total_visits' => 0,
        'unique_visitors' => 0,
        'today_visits' => 0,
        'today_unique' => 0,
        'popular_pages' => [],
        'daily_visits' => []
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="assets/css/style.css">
<style>
    .traffic-stats {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .stat-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        flex: 1;
        min-width: 200px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #007bff;
        margin: 10px 0;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .traffic-charts {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 40px;
    }

    .chart-container {
        flex: 1;
        min-width: 300px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 20px;
    }

    .chart-title {
        font-size: 1.2rem;
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
    }

    .popular-pages {
        width: 100%;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 20px;
        margin-bottom: 40px;
    }

    .pages-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pages-table th,
    .pages-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .pages-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }

    .page-url {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .visits-count {
        font-weight: 600;
        color: #007bff;
    }

    @media (max-width: 768px) {
        .stat-cards {
            flex-direction: column;
        }

        .traffic-charts {
            flex-direction: column;
        }
    }
</style>

<div class="traffic-stats">
    <h1>Website Traffic Statistics</h1>
    <p>Real-time analytics of visitor activity on Find Vacant Room</p>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_visits']) ?></div>
            <div class="stat-label">Total Visits</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['unique_visitors']) ?></div>
            <div class="stat-label">Unique Visitors</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['today_visits']) ?></div>
            <div class="stat-label">Visits Today</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['today_unique']) ?></div>
            <div class="stat-label">Unique Visitors Today</div>
        </div>
    </div>

    <!-- <div class="popular-pages">
        <h2 class="chart-title">Most Popular Pages</h2>

        <?php if (empty($stats['popular_pages'])): ?>
            <p>No page visit data available yet.</p>
        <?php else: ?>
            <table class="pages-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Visits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['popular_pages'] as $page): ?>
                        <tr>
                            <td class="page-url"><?= htmlspecialchars($page['page_url']) ?></td>
                            <td class="visits-count"><?= number_format($page['visits']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div> -->

    <div class="traffic-charts">
        <div class="chart-container">
            <h2 class="chart-title">Daily Visits (Last 7 Days)</h2>

            <?php if (empty($stats['daily_visits'])): ?>
                <p>No daily visit data available yet.</p>
            <?php else: ?>
                <div id="daily-visits-chart" style="height: 300px;"></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($stats['daily_visits'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Format data for chart
            const dailyData = <?= json_encode($stats['daily_visits']) ?>;
            const dates = dailyData.map(item => item.date);
            const visits = dailyData.map(item => parseInt(item.visits));

            // Daily visits chart
            const dailyChart = new ApexCharts(document.querySelector("#daily-visits-chart"), {
                series: [{
                    name: 'Visits',
                    data: visits
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: dates,
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                colors: ['#007bff'],
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " visits"
                        }
                    }
                }
            });

            dailyChart.render();
        });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>