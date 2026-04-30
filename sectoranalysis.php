<?php 
include 'db.php'; 
include 'header.php'; 

$inst_filter = $_GET['inst'] ?? 'All';
$branch_filter = $_GET['branch'] ?? 'All';
$year_filter = $_GET['year'] ?? 'All';

$where = "1=1";
if ($inst_filter != 'All') $where .= " AND Institute = '" . $conn->real_escape_string($inst_filter) . "'";
if ($branch_filter != 'All') $where .= " AND Branch = '" . $conn->real_escape_string($branch_filter) . "'";
if ($year_filter != 'All') $where .= " AND Year = '" . $conn->real_escape_string($year_filter) . "'";
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
        <h2 style="border: none; padding: 0; margin: 0;"><i class="fas fa-chart-pie"></i> Sector Distribution</h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <select name="inst" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Institutes</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Institute FROM branch_sector ORDER BY Institute");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Institute']}' ".(($inst_filter==$row['Institute'])?'selected':'').">{$row['Institute']}</option>";
                ?>
            </select>
            <select name="branch" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Branches</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Branch FROM branch_sector ORDER BY Branch");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Branch']}' ".(($branch_filter==$row['Branch'])?'selected':'').">{$row['Branch']}</option>";
                ?>
            </select>
            <select name="year" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Years</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Year FROM branch_sector ORDER BY Year DESC");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Year']}' ".(($year_filter==$row['Year'])?'selected':'').">{$row['Year']}</option>";
                ?>
            </select>
        </form>
    </div>

    <div style="position: relative; height:50vh; width:100%; display: flex; justify-content: center;">
        <canvas id="sectorChart"></canvas>
    </div>
</div>

<?php
$query = "SELECT Sector, SUM(Students_Placed) as total_students FROM branch_sector WHERE $where GROUP BY Sector";
$result = $conn->query($query);
$sectors = []; $students = [];
while($row = $result->fetch_assoc()) {
    $sectors[] = $row['Sector'];
    $students[] = $row['total_students'];
}

// If no data exists for the specific filter, pass an empty array to prevent JS errors
if(empty($sectors)) {
    echo "<p style='text-align:center; color:red;'>No placement data found for this specific filter combination.</p>";
}
?>

<script>
const ctx3 = document.getElementById('sectorChart').getContext('2d');
new Chart(ctx3, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($sectors); ?>,
        datasets: [{
            data: <?php echo json_encode($students); ?>,
            backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'],
            borderWidth: 0, hoverOffset: 10
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right' } } }
});
</script>
<?php include 'footer.php'; ?>