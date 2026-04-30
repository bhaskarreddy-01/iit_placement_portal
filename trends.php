<?php 
include 'db.php'; 
include 'header.php'; 

// required data
$inst_filter = $_GET['inst'] ?? 'All';
$branch_filter = $_GET['branch'] ?? 'All';


$where = "1=1";
if ($inst_filter != 'All') $where .= " AND Institute = '" . $conn->real_escape_string($inst_filter) . "'";
if ($branch_filter != 'All') $where .= " AND Branch = '" . $conn->real_escape_string($branch_filter) . "'";
?>

<style>
/* Card Header  */
.trend-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid var(--border);
    padding-bottom: 15px;
    margin-bottom: 20px;
}

/* Title  */
.trend-title {
    border: none;
    padding: 0;
    margin: 0;
}

/* Filter Form  */
.trend-form {
    display: flex;
    gap: 10px;
}

/* Dropdown  */
.trend-select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid var(--border);
}

/* Chart Container */
.trend-chart-container {
    position: relative;
    height: 60vh;
    width: 100%;
}
</style>

<div class="card">
    <div class="trend-header">
        <h2 class="trend-title"><i class="fas fa-chart-line"></i> YoY Trends (Average Package)</h2>
        
        <form method="GET" class="trend-form">
            <select name="inst" onchange="this.form.submit()" class="trend-select">
                <option value="All">All Institutes</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Institute FROM placements ORDER BY Institute");
                while($row = $res->fetch_assoc()) {
                    $sel = ($inst_filter == $row['Institute']) ? 'selected' : '';
                    echo "<option value='{$row['Institute']}' $sel>{$row['Institute']}</option>";
                }
                ?>
            </select>
            <select name="branch" onchange="this.form.submit()" class="trend-select">
                <option value="All">All Branches</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Branch FROM placements ORDER BY Branch");
                while($row = $res->fetch_assoc()) {
                    $sel = ($branch_filter == $row['Branch']) ? 'selected' : '';
                    echo "<option value='{$row['Branch']}' $sel>{$row['Branch']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <div class="trend-chart-container">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<?php
$query = "SELECT Year, AVG(AvgPackage_LPA) as avg_pkg FROM placements WHERE $where GROUP BY Year ORDER BY Year";
$result = $conn->query($query);
$years = []; $packages = [];
while($row = $result->fetch_assoc()) {
    $years[] = $row['Year'];
    $packages[] = round($row['avg_pkg'], 2);
}
?>

<script>
const ctx = document.getElementById('trendChart').getContext('2d');
let gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(79, 70, 229, 0.5)');
gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($years); ?>,
        datasets: [{
            label: 'Average Package (LPA)',
            data: <?php echo json_encode($packages); ?>,
            borderColor: '#4f46e5',
            backgroundColor: gradient,
            borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#4f46e5',
            fill: true, tension: 0.4
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>

<?php include 'footer.php'; ?>