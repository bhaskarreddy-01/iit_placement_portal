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
        <h2 style="border: none; padding: 0; margin: 0;"><i class="fas fa-trophy"></i> Top Placements</h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <select name="inst" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Institutes</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Institute FROM placements ORDER BY Institute");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Institute']}' ".(($inst_filter==$row['Institute'])?'selected':'').">{$row['Institute']}</option>";
                ?>
            </select>
            <select name="branch" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Branches</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Branch FROM placements ORDER BY Branch");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Branch']}' ".(($branch_filter==$row['Branch'])?'selected':'').">{$row['Branch']}</option>";
                ?>
            </select>
            <select name="year" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                <option value="All">All Years</option>
                <?php
                $res = $conn->query("SELECT DISTINCT Year FROM placements ORDER BY Year DESC");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['Year']}' ".(($year_filter==$row['Year'])?'selected':'').">{$row['Year']}</option>";
                ?>
            </select>
        </form>
    </div>

    <table>
        <tr><th>Rank</th><th>Institute</th><th>Year</th><th>Branch</th><th>Package (LPA)</th></tr>
        <?php
        $query = "SELECT Institute, Year, Branch, Highest_Domestic_LPA FROM placements WHERE $where ORDER BY Highest_Domestic_LPA DESC LIMIT 10";
        $result = $conn->query($query);
        $rank = 1;
        while($row = $result->fetch_assoc()) {
            $medal = $rank;
            if($rank == 1) $medal = '<i class="fas fa-medal" style="color: #fbbf24; font-size: 1.5rem;"></i>';
            if($rank == 2) $medal = '<i class="fas fa-medal" style="color: #94a3b8; font-size: 1.5rem;"></i>';
            if($rank == 3) $medal = '<i class="fas fa-medal" style="color: #b45309; font-size: 1.5rem;"></i>';

            echo "<tr>
                    <td style='font-weight:bold; text-align:center;'>{$medal}</td>
                    <td style='font-weight:600; color:var(--primary)'>{$row['Institute']}</td>
                    <td>{$row['Year']}</td>
                    <td><span style='background:#e2e8f0; padding:4px 8px; border-radius:4px; font-size:12px; color: black;'>{$row['Branch']}</span></td>
                    <td style='font-weight:bold; color:#10b981;'>₹{$row['Highest_Domestic_LPA']}</td>
                  </tr>";
            $rank++;
        }
        if($result->num_rows == 0) echo "<tr><td colspan='5' style='text-align:center;'>No data found for selected filters.</td></tr>";
        ?>
    </table>
</div>
<?php include 'footer.php'; ?>