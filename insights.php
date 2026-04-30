<?php
include 'db.php';
include 'header.php';

// 1. Fetch latest and previous years
$years_query = "SELECT DISTINCT Year FROM placements ORDER BY Year DESC LIMIT 2";
$years_res = $conn->query($years_query);
$latest_years = [];
while ($row = $years_res->fetch_assoc()) {
    $latest_years[] = $row['Year'];
}
$curr_year = $latest_years[0] ?? null;
$prev_year = $latest_years[1] ?? null;

// 2. Top Performing Institute in the current year
$top_inst_name = 'N/A';
$top_inst_pkg = 0;
if ($curr_year) {
    $top_inst_query = "SELECT Institute, AvgPackage_LPA FROM placements WHERE Year = '$curr_year' ORDER BY AvgPackage_LPA DESC LIMIT 1";
    $top_inst_res = $conn->query($top_inst_query)->fetch_assoc();
    if ($top_inst_res) {
        $top_inst_name = $top_inst_res['Institute'];
        $top_inst_pkg = round($top_inst_res['AvgPackage_LPA'], 2);
    }
}

// 3. Highest Growth Branch (Current vs Prev)
$growth_branch_name = 'N/A';
$growth_branch_pct = 0;
$mom_branches = [];
$mom_growths = [];
$selected_institute = $_GET['institute'] ?? 'All';

// Fetch all institutes for dropdown
$inst_query = "SELECT DISTINCT Institute FROM placements ORDER BY Institute";
$inst_result = $conn->query($inst_query);
$all_institutes = [];
if ($inst_result) {
    while($row = $inst_result->fetch_assoc()) {
        $all_institutes[] = $row['Institute'];
    }
}

if ($curr_year && $prev_year) {
    $inst_filter_b1 = "";
    $inst_filter_b2 = "";
    if ($selected_institute !== 'All') {
        $safe_inst = $conn->real_escape_string($selected_institute);
        $inst_filter_b1 = " AND Institute = '$safe_inst'";
        $inst_filter_b2 = " AND Institute = '$safe_inst'";
    }

    $momentum_query = "
        SELECT b1.Branch, 
               ((b1.avg_pkg - b2.avg_pkg) / b2.avg_pkg) * 100 as growth 
        FROM (SELECT Branch, AVG(AvgPackage_LPA) as avg_pkg FROM placements WHERE Year = '$curr_year' $inst_filter_b1 GROUP BY Branch) b1
        JOIN (SELECT Branch, AVG(AvgPackage_LPA) as avg_pkg FROM placements WHERE Year = '$prev_year' $inst_filter_b2 GROUP BY Branch) b2
        ON b1.Branch = b2.Branch
        ORDER BY growth DESC
    ";
    $momentum_res = $conn->query($momentum_query);
    if ($momentum_res) {
        $first = true;
        while ($row = $momentum_res->fetch_assoc()) {
            if ($first) {
                $growth_branch_name = $row['Branch'];
                $growth_branch_pct = round($row['growth'], 1);
                $first = false;
            }
            $mom_branches[] = $row['Branch'];
            $mom_growths[] = round($row['growth'], 2);
        }
    }
}

// 4. Institute Trajectory Data
$traj_institutes_to_show = [];
$traj_inst1 = 'None';
$traj_inst2 = 'None';
$traj_inst3 = 'None';

if (isset($_GET['traj_inst1'])) {
    $traj_inst1 = $_GET['traj_inst1'];
    $traj_inst2 = $_GET['traj_inst2'] ?? 'None';
    $traj_inst3 = $_GET['traj_inst3'] ?? 'None';
    if ($traj_inst1 !== 'None') $traj_institutes_to_show[] = $traj_inst1;
    if ($traj_inst2 !== 'None') $traj_institutes_to_show[] = $traj_inst2;
    if ($traj_inst3 !== 'None') $traj_institutes_to_show[] = $traj_inst3;
} else {
    // Default to Top 3
    if ($curr_year) {
        $top_query = "SELECT Institute FROM placements WHERE Year = '$curr_year' ORDER BY AvgPackage_LPA DESC LIMIT 3";
        $top_res = $conn->query($top_query);
        if ($top_res) {
            while ($row = $top_res->fetch_assoc()) {
                $traj_institutes_to_show[] = $row['Institute'];
            }
        }
        $traj_inst1 = $traj_institutes_to_show[0] ?? 'None';
        $traj_inst2 = $traj_institutes_to_show[1] ?? 'None';
        $traj_inst3 = $traj_institutes_to_show[2] ?? 'None';
    }
}
$traj_institutes_to_show = array_unique($traj_institutes_to_show);

$traj_data = [];
$traj_years = [];

if (!empty($traj_institutes_to_show)) {
    $in_clause = "'" . implode("','", $traj_institutes_to_show) . "'";
    $traj_query = "SELECT Year, Institute, AvgPackage_LPA FROM placements WHERE Institute IN ($in_clause) ORDER BY Year";
    $traj_res = $conn->query($traj_query);
    $temp_years = [];
    if ($traj_res) {
        while ($row = $traj_res->fetch_assoc()) {
            $temp_years[$row['Year']] = true;
            $traj_data[$row['Institute']][$row['Year']] = $row['AvgPackage_LPA'];
        }
    }
    $traj_years = array_keys($temp_years);
    sort($traj_years);
}

// 5. Existing Core vs IT Sector Shift Data
$shift_query = "SELECT Year, Sector, SUM(Students_Placed) as total FROM branch_sector WHERE Sector IN ('IT/Software', 'Core Engineering') GROUP BY Year, Sector ORDER BY Year";
$shift_result = $conn->query($shift_query);

$shift_years_data = [];
$it_data = [];
$core_data = [];

if ($shift_result) {
    while ($row = $shift_result->fetch_assoc()) {
        $shift_years_data[$row['Year']] = true;
        if ($row['Sector'] == 'IT/Software')
            $it_data[$row['Year']] = $row['total'];
        if ($row['Sector'] == 'Core Engineering')
            $core_data[$row['Year']] = $row['total'];
    }
}
$shift_years = array_keys($shift_years_data);
$final_it = array_map(function ($y) use ($it_data) {
    return (int)($it_data[$y] ?? 0); }, $shift_years);
$final_core = array_map(function ($y) use ($core_data) {
    return (int)($core_data[$y] ?? 0); }, $shift_years);
?>

<style>
    .insights-grid {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .insight-box {
        flex: 1;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        border-bottom: 4px solid #3498db;
    }

    .insight-box h3 {
        margin-top: 0;
        color: #7f8c8d;
        font-size: 16px;
    }

    .insight-box h1 {
        margin: 10px 0 0;
        color: #2c3e50;
        font-size: 28px;
    }

    .insight-box p {
        margin: 5px 0 0;
        color: #27ae60;
        font-weight: bold;
    }

    .chart-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .chart-box {
        flex: 1;
        min-width: 45%;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="insights-grid">
    <div class="insight-box" style="border-color: #f1c40f;">
        <h3>Top Performing Institute (<?php echo $curr_year ?: 'N/A'; ?>)</h3>
        <h1><?php echo $top_inst_name; ?></h1>
        <p>₹<?php echo $top_inst_pkg; ?> LPA Avg</p>
    </div>
    <div class="insight-box" style="border-color: #2ecc71;">
        <h3>Highest Growth Branch</h3>
        <h1><?php echo $growth_branch_name; ?></h1>
        <p><?php echo $growth_branch_pct >= 0 ? '+' : ''; ?><?php echo $growth_branch_pct; ?>% YoY Growth</p>
    </div>
    <div class="insight-box" style="border-color: #9b59b6;">
        <h3>Data Latest As Of</h3>
        <h1><?php echo $curr_year ?: 'N/A'; ?></h1>
        <p>Compared against <?php echo $prev_year ?: 'N/A'; ?></p>
    </div>
</div>

<div class="chart-container">
    <div class="chart-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;">Institute Trajectory</h2>
            <form method="GET" action="" style="margin: 0; display: flex; gap: 10px;">
                <?php if(isset($_GET['institute'])): ?>
                    <input type="hidden" name="institute" value="<?php echo htmlspecialchars($_GET['institute']); ?>">
                <?php endif; ?>
                <select name="traj_inst1" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; max-width: 150px;">
                    <option value="None">-- Select --</option>
                    <?php foreach($all_institutes as $inst): ?>
                        <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo $traj_inst1 === $inst ? 'selected' : ''; ?>><?php echo htmlspecialchars($inst); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="traj_inst2" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; max-width: 150px;">
                    <option value="None">-- Select --</option>
                    <?php foreach($all_institutes as $inst): ?>
                        <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo $traj_inst2 === $inst ? 'selected' : ''; ?>><?php echo htmlspecialchars($inst); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="traj_inst3" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; max-width: 150px;">
                    <option value="None">-- Select --</option>
                    <?php foreach($all_institutes as $inst): ?>
                        <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo $traj_inst3 === $inst ? 'selected' : ''; ?>><?php echo htmlspecialchars($inst); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <canvas id="trajChart" height="120"></canvas>
    </div>
</div>

<div class="chart-container">
    <div class="chart-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;">Branch Momentum (YoY Growth %)</h2>
            <form method="GET" action="" style="margin: 0;">
                <?php if(isset($_GET['traj_inst1'])): ?>
                    <input type="hidden" name="traj_inst1" value="<?php echo htmlspecialchars($_GET['traj_inst1']); ?>">
                    <input type="hidden" name="traj_inst2" value="<?php echo htmlspecialchars($_GET['traj_inst2'] ?? 'None'); ?>">
                    <input type="hidden" name="traj_inst3" value="<?php echo htmlspecialchars($_GET['traj_inst3'] ?? 'None'); ?>">
                <?php endif; ?>
                <select name="institute" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px;">
                    <option value="All">All Institutes</option>
                    <?php foreach($all_institutes as $inst): ?>
                        <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo $selected_institute === $inst ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($inst); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <canvas id="momChart" height="120"></canvas>
    </div>
</div>


<div class="card">
    <h2>Core vs IT Sector Shift (Last Available Data)</h2>
    <canvas id="shiftChart" height="100"></canvas>
</div>

<script>
    // Top 3 Trajectory Chart
    const trajCtx = document.getElementById('trajChart').getContext('2d');
    const colors = ['#e74c3c', '#8e44ad', '#2980b9'];
    const datasets = [];
    <?php
    $c = 0;
    foreach ($traj_institutes_to_show as $inst) {
        $inst_data = [];
        foreach ($traj_years as $y) {
            $inst_data[] = isset($traj_data[$inst][$y]) ? round($traj_data[$inst][$y], 2) : 0;
        }
        echo "datasets.push({ label: '$inst', data: " . json_encode($inst_data) . ", borderColor: colors[$c], backgroundColor: colors[$c], fill: false, tension: 0.2 });\n";
        $c++;
    }
    ?>
    new Chart(trajCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($traj_years); ?>,
            datasets: datasets
        }
    });

    // Branch Momentum Chart
    const momCtx = document.getElementById('momChart').getContext('2d');
    new Chart(momCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($mom_branches); ?>,
            datasets: [{
                label: 'YoY Growth (%)',
                data: <?php echo json_encode($mom_growths); ?>,
                backgroundColor: <?php echo json_encode(array_map(function ($g) {
                    return $g >= 0 ? '#2ecc71' : '#e74c3c'; }, $mom_growths)); ?>
            }]
        }
    });

    // Sector Shift Chart (Existing)
    const shiftCtx = document.getElementById('shiftChart').getContext('2d');
    new Chart(shiftCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($shift_years); ?>,
            datasets: [
                { label: 'IT Sector Placements', data: <?php echo json_encode($final_it); ?>, backgroundColor: '#3498db' },
                { label: 'Core Sector Placements', data: <?php echo json_encode($final_core); ?>, backgroundColor: '#e74c3c' }
            ]
        },
        options: { scales: { x: { stacked: true }, y: { stacked: true } } }
    });

</script>

<?php include 'footer.php'; ?>