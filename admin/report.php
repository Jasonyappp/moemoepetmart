<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Sales Report - Admin';

// Fetch available years from completed orders
$stmt_years = $_db->query("
    SELECT DISTINCT YEAR(order_date) AS year 
    FROM orders 
    WHERE order_status = 'Completed' 
    ORDER BY year DESC
");
$available_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

if (empty($available_years)) {
    $available_years = [date('Y'), date('Y')-1, date('Y')-2];
}

$default_year = max($available_years);

// Current view
$view = $_GET['view'] ?? 'sales';

// Selected year for monthly
$selected_year = (int)($_GET['year'] ?? $default_year);

// Yearly Sales
$stmt_yearly = $_db->query("
    SELECT YEAR(order_date) AS year, SUM(total_amount) AS total
    FROM orders
    WHERE order_status = 'Completed'
    GROUP BY year
    ORDER BY year DESC
");
$yearly_sales = $stmt_yearly->fetchAll(PDO::FETCH_ASSOC);

// Monthly Sales
$stmt_monthly = $_db->prepare("
    SELECT MONTH(order_date) AS month, SUM(total_amount) AS total
    FROM orders
    WHERE order_status = 'Completed' AND YEAR(order_date) = ?
    GROUP BY month
    ORDER BY month
");
$stmt_monthly->execute([$selected_year]);
$monthly_data = $stmt_monthly->fetchAll(PDO::FETCH_ASSOC);

$monthly_sales = array_fill(1, 12, 0.0);
foreach ($monthly_data as $row) {
    $monthly_sales[(int)$row['month']] = (float)$row['total'];
}

// Top Selling Products (synchronized with products page)
$stmt_top = $_db->query("
    SELECT p.product_name, SUM(oi.quantity) AS total_sales
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status = 'Completed'
    GROUP BY p.product_id, p.product_name
    ORDER BY total_sales DESC
    LIMIT 5
");
$top_products = $stmt_top->fetchAll(PDO::FETCH_ASSOC);

// Unified Daily Sales Data
$daily_sales = [];
$days_in_month = 31;
$daily_month_name = 'December';
$daily_year = date('Y');
$daily_month_num = date('m');

if ($view === 'sales' || $view === 'daily') {
    if ($view === 'daily') {
        $daily_year = (int)($_GET['year'] ?? date('Y'));
        $daily_month_num = sprintf("%02d", (int)($_GET['month'] ?? date('m')));
    } else {
        $daily_year = date('Y');
        $daily_month_num = date('m');
    }

    $stmt_daily = $_db->prepare("
        SELECT DAY(order_date) AS day, SUM(total_amount) AS total
        FROM orders
        WHERE order_status = 'Completed' 
          AND YEAR(order_date) = ? 
          AND MONTH(order_date) = ?
        GROUP BY DAY(order_date)
        ORDER BY day
    ");
    $stmt_daily->execute([$daily_year, $daily_month_num]);
    $daily_data = $stmt_daily->fetchAll(PDO::FETCH_ASSOC);

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, ltrim($daily_month_num, '0'), $daily_year);
    $daily_sales = array_fill(1, $days_in_month, 0.0);
    foreach ($daily_data as $row) {
        $daily_sales[(int)$row['day']] = (float)$row['total'];
    }

    $months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
               '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
    $daily_month_name = $months[$daily_month_num];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 25px 0;
            justify-content: center;
            border-bottom: 3px solid #ffeef8;
            padding-bottom: 15px;
        }
        .report-tabs a {
            padding: 12px 30px;
            background: #fff8fb;
            border: 3px solid #ff69b4;
            border-radius: 30px;
            color: #ff1493;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255,105,180,0.1);
        }
        .report-tabs a:hover {
            background: #ff69b4;
            color: white;
            transform: translateY(-3px);
        }
        .report-tabs a.active {
            background: #ff1493;
            color: white;
            box-shadow: 0 8px 25px rgba(255,20,147,0.4);
        }
        .year-selector {
            text-align: center;
            margin: 20px 0;
        }
        .year-selector select, .year-selector button {
            padding: 10px 20px;
            font-size: 1.1rem;
            border: 3px solid #ff69b4;
            border-radius: 15px;
            background: white;
            color: #ff1493;
            margin: 0 5px;
        }
        .year-selector button {
            background: #ff69b4;
            color: white;
            cursor: pointer;
        }
        .clickable-card {
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            cursor: pointer;
        }
        .clickable-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(255, 105, 180, 0.3);
        }
        .card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(255, 105, 180, 0.9));
            color: white;
            padding: 15px;
            font-size: 1rem;
            font-weight: bold;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .clickable-card:hover .card-overlay {
            opacity: 1;
        }
        .dashboard-card canvas {
            max-height: 300px !important;
            width: 100% !important;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
        <ul>
            <li><a href="/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/admin/product_list.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/member_list.php"><i class="fas fa-users"></i> Members</a></li>
            <li><a href="/admin/order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/admin/review_list.php"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/admin/chat_list.php"><i class="fas fa-comments"></i> Chats</a></li>
            <li><a href="/admin/report.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="/admin/profile.php"><i class="fas fa-user-cog"></i> My Profile ♛</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-welcome-header">
            <h1>Sales Report • Moe Moe Pet Mart</h1>
        </div>

        <!-- Report Tabs -->
        <div class="report-tabs">
            <a href="?view=sales" class="<?= $view === 'sales' ? 'active' : '' ?>">📊 Sales Overview</a>
            <a href="?view=yearly" class="<?= $view === 'yearly' ? 'active' : '' ?>">🗓️ Yearly Comparison</a>
            <a href="?view=monthly&year=<?= $selected_year ?>" class="<?= $view === 'monthly' ? 'active' : '' ?>">📈 Monthly Breakdown</a>
            <a href="?view=daily&year=<?= date('Y') ?>&month=<?= date('m') ?>" class="<?= $view === 'daily' ? 'active' : '' ?>">📅 Daily Report</a>
            <a href="?view=top_products" class="<?= $view === 'top_products' ? 'active' : '' ?>">🏆 Top Products</a>
        </div>

        <!-- Year Selector - ONLY for Monthly Report -->
        <?php if ($view === 'monthly'): ?>
        <div class="year-selector">
            <label><strong>Select Year for Monthly Report:</strong></label><br><br>
            <select onchange="location.href='?view=monthly&year='+this.value">
                <?php foreach ($available_years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Month & Year Selector - ONLY for Daily Report -->
        <?php if ($view === 'daily'): 
            $months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
                       '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
        ?>
        <div class="year-selector">
            <label><strong>Select Month & Year for Daily Report:</strong></label><br><br>
            <select id="monthSelect">
                <?php foreach ($months as $num => $name): ?>
                    <option value="<?= $num ?>" <?= $num == $daily_month_num ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <select id="yearSelectDaily">
                <?php foreach ($available_years as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $daily_year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="updateDailyReport()">Show Daily Sales ♡</button>
        </div>
        <?php endif; ?>

        <!-- Charts Grid -->
        <div class="dashboard-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; padding: 10px 0;">

            <?php if ($view === 'sales' || $view === 'yearly'): ?>
            <div class="dashboard-card <?= $view === 'sales' ? 'clickable-card' : '' ?>" 
                 <?php if ($view === 'sales'): ?>onclick="location.href='?view=yearly'"<?php endif; ?>>
                <h3>Yearly Sales Comparison</h3>
                <canvas id="yearlyChart"></canvas>
                <?php if ($view === 'sales'): ?><div class="card-overlay">Click to view full Yearly Report →</div><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($view === 'sales' || $view === 'monthly'): ?>
            <div class="dashboard-card <?= $view === 'sales' ? 'clickable-card' : '' ?>" 
                 <?php if ($view === 'sales'): ?>onclick="location.href='?view=monthly&year=<?= $selected_year ?>'"<?php endif; ?>>
                <h3>Monthly Sales (<?= $selected_year ?>)</h3>
                <canvas id="monthlyChart"></canvas>
                <?php if ($view === 'sales'): ?><div class="card-overlay">Click to view full Monthly Report →</div><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($view === 'sales' || $view === 'top_products'): ?>
            <div class="dashboard-card <?= $view === 'sales' ? 'clickable-card' : '' ?>" 
                 <?php if ($view === 'sales'): ?>onclick="location.href='?view=top_products'"<?php endif; ?>>
                <h3>Top Selling Products (All Time)</h3>
                <canvas id="topProductsChart"></canvas>
                <?php if ($view === 'sales'): ?><div class="card-overlay">Click to view full Top Products Report →</div><?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Daily Sales Card - Unified -->
            <?php if ($view === 'sales' || $view === 'daily'): ?>
            <div class="dashboard-card <?= $view === 'sales' ? 'clickable-card' : '' ?>" 
                 <?php if ($view === 'sales'): ?>onclick="location.href='?view=daily&year=<?= $daily_year ?>&month=<?= ltrim($daily_month_num, '0') ?>'"<?php endif; ?>>
                <h3>Daily Sales - <?= $daily_month_name ?> <?= $daily_year ?><?= $view === 'sales' ? ' (Current)' : '' ?></h3>
                <canvas id="<?= $view === 'sales' ? 'overviewDailyChart' : 'dailyChart' ?>"></canvas>
                <?php if ($view === 'sales'): ?><div class="card-overlay">Click to view full Daily Report →</div><?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
function updateDailyReport() {
    const month = document.getElementById('monthSelect').value;
    const year = document.getElementById('yearSelectDaily').value;
    window.location = `?view=daily&year=${year}&month=${month}`;
}

const responsiveOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' } }
};

const yearlyData = <?= json_encode($yearly_sales) ?>;
const monthlySales = <?= json_encode(array_values($monthly_sales)) ?>;
const topProducts = <?= json_encode($top_products) ?>;
const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Yearly Bar Chart
<?php if ($view === 'sales' || $view === 'yearly'): ?>
new Chart(document.getElementById('yearlyChart'), {
    type: 'bar',
    data: { labels: yearlyData.map(d => d.year), datasets: [{ label: 'Total Sales (RM)', data: yearlyData.map(d => d.total), backgroundColor: '#ff69b4', borderColor: '#ff1493', borderWidth: 2 }] },
    options: { ...responsiveOptions, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>

// Monthly Line Chart
<?php if ($view === 'sales' || $view === 'monthly'): ?>
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels: monthLabels, datasets: [{ label: 'Monthly Sales (RM)', data: monthlySales.slice(1), borderColor: '#ff1493', backgroundColor: 'rgba(255, 105, 180, 0.2)', fill: true, tension: 0.4, pointBackgroundColor: '#ff69b4' }] },
    options: { ...responsiveOptions, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>

// Top Products Doughnut Chart
<?php if ($view === 'sales' || $view === 'top_products'): ?>
new Chart(document.getElementById('topProductsChart'), {
    type: 'doughnut',
    data: { 
        labels: topProducts.map(p => p.product_name), 
        datasets: [{ 
            data: topProducts.map(p => p.total_sales), 
            backgroundColor: ['#ff69b4', '#ff1493', '#ff8fab', '#ffb6c1', '#ffc0cb'], 
            borderWidth: 3, 
            borderColor: '#fff' 
        }] 
    },
    options: { 
        ...responsiveOptions, 
        plugins: { 
            legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + ' units sold';
                    }
                }
            }
        } 
    }
});
<?php endif; ?>

// Unified Daily Bar Chart
<?php if ($view === 'sales' || $view === 'daily'): ?>
new Chart(document.getElementById('<?= $view === 'sales' ? 'overviewDailyChart' : 'dailyChart' ?>'), {
    type: 'bar',
    data: {
        labels: Array.from({length: <?= $days_in_month ?>}, (_, i) => i + 1),
        datasets: [{
            label: 'Daily Sales (RM)',
            data: <?= json_encode(array_values($daily_sales)) ?>,
            backgroundColor: '#ff8fab',
            borderColor: '#ff1493',
            borderWidth: 2
        }]
    },
    options: {
        ...responsiveOptions,
        scales: {
            y: { beginAtZero: true },
            x: { title: { display: true, text: 'Day of Month' } }
        }
    }
});
<?php endif; ?>
</script>

</body>
</html>