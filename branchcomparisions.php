<?php 
include 'db.php'; 
include 'header.php'; 

$inst_filter = $_GET['inst'] ?? 'All';
$year_filter = $_GET['year'] ?? 'All';

$where = "1=1";
if ($inst_filter != 'All') $where .= " AND Institute = '" . $conn->real_escape_string($inst_filter) . "'";
if ($year_filter != 'All') $where .= " AND Year = '" . $conn->real_escape_string($year_filter) . "'";
?>

<style>
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid var(--border);
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.card-header h2 {
    border: none;
    padding: 0;
    margin: 0;
}

.filter-form {
    display: flex;
    gap: 10px;
}

.filter-form select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid var(--border);
}

.chart-container {
    position: relative;
    height: 60vh;
    width: 100%;
}
</style>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-code-branch"></i> Branch Wise Median Salary</h2>
        <form method="GET" class="filter-form">
            <select name="inst" onchange="this.form.submit()">
                <option value="All">All Institutes</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Institute FROM placements ORDER BY Institute");
                while($row = $res->fetch_assoc()) {
                    $sel = ($inst_filter == $row['Institute']) ? 'selected' : '';
                    echo "<option value='{$row['Institute']}' $sel>{$row['Institute']}</option>";
                }
                ?>
            </select>
            <select name="year" onchange="this.form.submit()">
                <option value="All">All Years</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Year FROM placements ORDER BY Year DESC");
                while($row = $res->fetch_assoc()) {
                    $sel = ($year_filter == $row['Year']) ? 'selected' : '';
                    echo "<option value='{$row['Year']}' $sel>{$row['Year']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <div class="chart-container">
        <canvas id="branchChart"></canvas>
    </div>
</div>

<?php
$query = "
SELECT 
    Branch, 
    AVG(MedianPackage_LPA) AS med_pkg,
    AVG(AvgPackage_LPA) AS avg_pkg
FROM placements 
WHERE $where 
GROUP BY Branch 
ORDER BY med_pkg DESC
";

$result = $conn->query($query);
$branches = []; $medians = []; $avgs = [];
while($row = $result->fetch_assoc()) {
    $branches[] = $row['Branch'];
    $medians[] = round($row['med_pkg'], 2);
    $avgs[] = round($row['avg_pkg'], 2);
}
?>

<script>
const ctx2 = document.getElementById('branchChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($branches); ?>,
        datasets: [
        {
            label: 'Median Salary (LPA)',
            data: <?php echo json_encode($medians); ?>,
            backgroundColor: '#4f46e5',
            borderRadius: 6
        },
        {
            label: 'Average Salary (LPA)',
            data: <?php echo json_encode($avgs); ?>,
            backgroundColor: '#22c55e',
            borderRadius: 6
        }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
            legend: { display: true } 
        } 
    }
});
</script>

<?php include 'footer.php'; ?>