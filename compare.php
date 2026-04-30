<?php 
include 'db.php'; 
include 'header.php'; 

// Fetch all placement data into a PHP array, then pass it to JavaScript
$query = "SELECT Institute, Year, Branch, AvgPackage_LPA, Highest_Domestic_LPA, Placement_Percentage FROM placements";
$result = $conn->query($query);

$all_data = [];
$institutes = [];
$years = [];
$branches = [];

while($row = $result->fetch_assoc()) {
    $all_data[] = $row;
    $institutes[$row['Institute']] = true;
    $years[$row['Year']] = true;
    $branches[$row['Branch']] = true;
}

// Get unique, sorted lists for dropdowns
$institutes = array_keys($institutes); sort($institutes);
$years = array_keys($years); rsort($years); // Newest year first
$branches = array_keys($branches); sort($branches);
?>

<div class="card">
    <h2><i class="fas fa-balance-scale"></i> Head-to-Head Institute Comparison</h2>
    <p>Select two institutes, a year, and a metric to instantly compare their branch-wise performance.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px;">
        
        <div>
            <label style="font-weight: 600; font-size: 14px;">Institute A:</label>
            <select id="inst1" onchange="updateChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <?php foreach($institutes as $inst) { echo "<option value='$inst'>$inst</option>"; } ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 14px;">Institute B:</label>
            <select id="inst2" onchange="updateChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <?php 
                // Set the second institute as default for Inst 2
                foreach($institutes as $index => $inst) { 
                    $selected = ($index == 1) ? 'selected' : '';
                    echo "<option value='$inst' $selected>$inst</option>"; 
                } 
                ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 14px;">Academic Year:</label>
            <select id="yearSelect" onchange="updateChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <?php foreach($years as $year) { echo "<option value='$year'>$year</option>"; } ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 14px;">Comparison Metric:</label>
            <select id="metricSelect" onchange="updateChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <option value="AvgPackage_LPA">Average Package (LPA)</option>
                <option value="Highest_Domestic_LPA">Highest Package (LPA)</option>
                <option value="Placement_Percentage">Placement Percentage (%)</option>
            </select>
        </div>
    </div>

    <div style="position: relative; height:60vh; width:100%">
        <canvas id="comparisonChart"></canvas>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <h2><i class="fas fa-chart-bar"></i> Branch-wise Institute Comparison</h2>
    <p>Select a branch, year, and metric to compare performance across all institutes.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px;">
        
        <div>
            <label style="font-weight: 600; font-size: 14px;">Branch:</label>
            <select id="branchSelect2" onchange="updateBranchChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <?php foreach($branches as $branch) { echo "<option value='$branch'>$branch</option>"; } ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 14px;">Academic Year:</label>
            <select id="yearSelect2" onchange="updateBranchChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <?php foreach($years as $year) { echo "<option value='$year'>$year</option>"; } ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 14px;">Comparison Metric:</label>
            <select id="metricSelect2" onchange="updateBranchChart()" style="width:100%; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                <option value="AvgPackage_LPA">Average Package (LPA)</option>
                <option value="Highest_Domestic_LPA">Highest Package (LPA)</option>
                <option value="Placement_Percentage">Placement Percentage (%)</option>
            </select>
        </div>
    </div>

    <div style="position: relative; height:60vh; width:100%">
        <canvas id="branchComparisonChart"></canvas>
    </div>
</div>

<script>
// Load the PHP data directly into a JavaScript Object
const rawData = <?php echo json_encode($all_data); ?>;
let comparisonChart = null; // Store chart instance to destroy/recreate it
let branchComparisonChart = null; // Store chart instance for the second chart

// --- Helper Functions ---

/**
 * Converts a database column name into a human-readable label for charts.
 * @param {string} metric - The database column name (e.g., 'AvgPackage_LPA')
 * @returns {string} The formatted label for UI display
 */
function getMetricLabel(metric) {
    if (metric === 'AvgPackage_LPA') return 'Average Package (₹ LPA)';
    if (metric === 'Placement_Percentage') return 'Placement %';
    if (metric === 'Highest_Domestic_LPA') return 'Highest Domestic (₹ LPA)';
    return metric;
}

/**
 * Filters an array of data objects based on a set of key-value pairs.
 * @param {Array} data - The array of objects to filter
 * @param {Object} filters - Key-value pairs to match (e.g., { Year: '2023' })
 * @returns {Array} The filtered array
 */
function filterData(data, filters) {
    return data.filter(item => {
        return Object.keys(filters).every(key => item[key] == filters[key]);
    });
}

/**
 * Destroys an existing Chart.js instance if it exists.
 * This prevents the "canvas already in use" error and visual glitches.
 * @param {Object} chartInstance - The Chart.js object to destroy
 */
function resetChart(chartInstance) {
    if (chartInstance) {
        chartInstance.destroy();
    }
}

/**
 * Renders the Head-to-Head Comparison Chart.
 * Compares two selected institutes across all available branches for a specific year and metric.
 */
function updateChart() {
    // 1. Get user selections from the UI dropdowns
    const inst1 = document.getElementById('inst1').value;
    const inst2 = document.getElementById('inst2').value;
    const year = document.getElementById('yearSelect').value;
    const metric = document.getElementById('metricSelect').value;

    // 2. Filter the raw data for both institutes for the selected year
    const data1 = filterData(rawData, { Institute: inst1, Year: year });
    const data2 = filterData(rawData, { Institute: inst2, Year: year });

    // 3. Find all unique branches that either institute offers
    let allBranches = [...new Set([...data1.map(d => d.Branch), ...data2.map(d => d.Branch)])];
    
    // 4. Map the selected metric's value to the branches (default to 0 if branch not found)
    const metricData1 = allBranches.map(branch => {
        let record = data1.find(d => d.Branch === branch);
        return record ? parseFloat(record[metric]) : 0;
    });

    const metricData2 = allBranches.map(branch => {
        let record = data2.find(d => d.Branch === branch);
        return record ? parseFloat(record[metric]) : 0;
    });

    // 5. Get formatted axis label
    let metricLabel = getMetricLabel(metric);

    // 6. Get the canvas context and destroy any existing chart to prepare for a new one
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    resetChart(comparisonChart);

    comparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allBranches,
            datasets: [
                {
                    label: inst1,
                    data: metricData1,
                    backgroundColor: '#4f46e5',
                    borderRadius: 4
                },
                {
                    label: inst2,
                    data: metricData2,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: `Comparing ${inst1} vs ${inst2} (${year})` },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: metricLabel }
                }
            },
            animation: { duration: 800, easing: 'easeOutBounce' }
        }
    });
}

// Run once on page load to show initial chart
window.onload = function() {
    updateChart();
    updateBranchChart();
};

/**
 * Renders the Branch-wise Institute Comparison Chart.
 * Compares all institutes against each other for a single selected branch, year, and metric.
 */
function updateBranchChart() {
    // 1. Get user selections from the UI dropdowns
    const branch = document.getElementById('branchSelect2').value;
    const year = document.getElementById('yearSelect2').value;
    const metric = document.getElementById('metricSelect2').value;

    // 2. Filter the raw data to only include records matching the selected branch and year
    const filteredData = filterData(rawData, { Branch: branch, Year: year });

    // 3. Sort the filtered data alphabetically by Institute Name for consistent display
    filteredData.sort((a, b) => a.Institute.localeCompare(b.Institute));

    // 4. Extract the institute names for the X-axis and metric values for the Y-axis
    const labels = filteredData.map(d => d.Institute);
    const dataPoints = filteredData.map(d => parseFloat(d[metric]) || 0);

    // 5. Get formatted axis label
    let metricLabel = getMetricLabel(metric);

    // 6. Get the canvas context and destroy any existing chart
    const ctx = document.getElementById('branchComparisonChart').getContext('2d');
    resetChart(branchComparisonChart);

    const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];
    const backgroundColors = labels.map((_, index) => colors[index % colors.length]);

    branchComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: metricLabel,
                data: dataPoints,
                backgroundColor: backgroundColors,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: `Institute Comparison for ${branch} (${year})` },
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: metricLabel }
                }
            },
            animation: { duration: 800, easing: 'easeOutBounce' }
        }
    });
}
</script>

<?php include 'footer.php'; ?>