<?php 
include 'db.php'; 
include 'header.php'; 

// ── Dashboard Stats ───────────────────────────────────────────────────────────
$inst_count  = $conn->query("SELECT COUNT(DISTINCT Institute) AS c FROM placements")->fetch_assoc()['c'];
$avg_package = $conn->query("SELECT AVG(AvgPackage_LPA) AS a FROM placements")->fetch_assoc()['a'];
$highest_pkg = $conn->query("SELECT MAX(Highest_Domestic_LPA) AS h FROM placements")->fetch_assoc()['h'];

// ── FAQ Queries (each tied to a real question users would ask) ────────────────

// Q1 – Which IIT has the highest average package?
$q1 = $conn->query("
    SELECT Institute, ROUND(AVG(AvgPackage_LPA), 2) AS avg_pkg
    FROM placements
    GROUP BY Institute
    ORDER BY avg_pkg DESC
    LIMIT 1
")->fetch_assoc();

// Q2 – Which IIT recorded the highest single domestic package?
$q2 = $conn->query("
    SELECT Institute, Branch, Year, MAX(Highest_Domestic_LPA) AS max_pkg
    FROM placements
    GROUP BY Institute, Branch, Year
    ORDER BY max_pkg DESC
    LIMIT 1
")->fetch_assoc();

// Q3 – Which branch has the best average package across all IITs?
$q3 = $conn->query("
    SELECT Branch, ROUND(AVG(AvgPackage_LPA), 2) AS avg_pkg
    FROM placements
    GROUP BY Branch
    ORDER BY avg_pkg DESC
    LIMIT 1
")->fetch_assoc();

// Q4 – Which sector hired the most students overall?
$q4 = $conn->query("
    SELECT Sector, SUM(Students_Placed) AS total_placed
    FROM branch_sector
    GROUP BY Sector
    ORDER BY total_placed DESC
    LIMIT 1
")->fetch_assoc();

// Q5 – Which IIT has the best average placement percentage?
$q5 = $conn->query("
    SELECT Institute, ROUND(AVG(Placement_Percentage), 2) AS avg_pct
    FROM placements
    GROUP BY Institute
    ORDER BY avg_pct DESC
    LIMIT 1
")->fetch_assoc();

// Q6 – What is the highest international package, and where?
$q6 = $conn->query("
    SELECT Institute, Branch, Year, MAX(Highest_International_LPA) AS intl_pkg
    FROM placements
    GROUP BY Institute, Branch, Year
    ORDER BY intl_pkg DESC
    LIMIT 1
")->fetch_assoc();

// Q7 – Which year had the best overall average package?
$q7 = $conn->query("
    SELECT Year, ROUND(AVG(AvgPackage_LPA), 2) AS avg_pkg
    FROM placements
    GROUP BY Year
    ORDER BY avg_pkg DESC
    LIMIT 1
")->fetch_assoc();

// Q8 – What is the average median package across all IITs?
$q8 = $conn->query("
    SELECT ROUND(AVG(MedianPackage_LPA), 2) AS avg_median
    FROM placements
")->fetch_assoc();

// Q9 – Which branch has the highest placement percentage?
$q9 = $conn->query("
    SELECT Branch, ROUND(AVG(Placement_Percentage), 2) AS avg_pct
    FROM placements
    GROUP BY Branch
    ORDER BY avg_pct DESC
    LIMIT 1
")->fetch_assoc();

// ── Build FAQ array ──────────────────────────────────────────────────────────
$faqs = [
    [
        'icon'     => '🏆',
        'question' => 'Which IIT has the highest average placement package?',
        'answer'   => '<strong>' . htmlspecialchars($q1['Institute']) . '</strong> leads all IITs with an average package of <strong>₹' . number_format($q1['avg_pkg'], 2) . ' LPA</strong> across all years and branches recorded in this dataset.',
        'color'    => '#f59e0b',
    ],
    [
        'icon'     => '💰',
        'question' => 'What is the highest domestic package ever recorded?',
        'answer'   => 'The highest domestic package of <strong>₹' . number_format($q2['max_pkg'], 2) . ' LPA</strong> was recorded at <strong>' . htmlspecialchars($q2['Institute']) . '</strong> for the <strong>' . htmlspecialchars($q2['Branch']) . '</strong> branch in <strong>' . $q2['Year'] . '</strong>.',
        'color'    => '#10b981',
    ],
    [
        'icon'     => '📚',
        'question' => 'Which engineering branch offers the best average package?',
        'answer'   => '<strong>' . htmlspecialchars($q3['Branch']) . '</strong> consistently tops the charts with an average package of <strong>₹' . number_format($q3['avg_pkg'], 2) . ' LPA</strong> across all IITs and years in our data.',
        'color'    => '#3b82f6',
    ],
    [
        'icon'     => '🏢',
        'question' => 'Which industry sector hires the most IIT students?',
        'answer'   => 'The <strong>' . htmlspecialchars($q4['Sector']) . '</strong> sector has absorbed the highest number of IIT graduates, with <strong>' . number_format($q4['total_placed']) . ' students</strong> placed across all institutes and years tracked.',
        'color'    => '#8b5cf6',
    ],
    [
        'icon'     => '📈',
        'question' => 'Which IIT has the best placement percentage?',
        'answer'   => '<strong>' . htmlspecialchars($q5['Institute']) . '</strong> achieves the best placement rate with an average of <strong>' . number_format($q5['avg_pct'], 2) . '%</strong> of eligible students placed, making it the most consistent performer.',
        'color'    => '#06b6d4',
    ],
    [
        'icon'     => '🌍',
        'question' => 'What is the highest international package recorded?',
        'answer'   => 'The top international offer of <strong>₹' . number_format($q6['intl_pkg'], 2) . ' LPA</strong> was bagged by a <strong>' . htmlspecialchars($q6['Branch']) . '</strong> student from <strong>' . htmlspecialchars($q6['Institute']) . '</strong> in <strong>' . $q6['Year'] . '</strong>.',
        'color'    => '#ef4444',
    ],
    [
        'icon'     => '📅',
        'question' => 'Which year had the best overall placement packages?',
        'answer'   => '<strong>' . $q7['Year'] . '</strong> was the strongest placement year on record, with an overall average package of <strong>₹' . number_format($q7['avg_pkg'], 2) . ' LPA</strong> across all IITs and branches.',
        'color'    => '#64748b',
    ],
    [
        'icon'     => '⚖️',
        'question' => 'What is the average median package across all IITs?',
        'answer'   => 'The average median package across all tracked IITs and branches is <strong>₹' . number_format($q8['avg_median'], 2) . ' LPA</strong>, providing a realistic expectation for most graduates.',
        'color'    => '#8b5cf6',
    ],
    [
        'icon'     => '🎯',
        'question' => 'Which branch has the highest placement percentage?',
        'answer'   => '<strong>' . htmlspecialchars($q9['Branch']) . '</strong> leads in employability, with an average placement rate of <strong>' . number_format($q9['avg_pct'], 2) . '%</strong>.',
        'color'    => '#14b8a6',
    ],
];
?>

<!-- ── Hero Banner ─────────────────────────────────────────────────────────── -->
<div class="card" style="text-align:center; background:linear-gradient(135deg, var(--primary), var(--secondary)); color:white;">
    <h1 style="color:white; font-size:2.5rem; margin-bottom:10px;">
        <i class="fas fa-graduation-cap"></i> IIT Placement Intelligence
    </h1>
    <p style="font-size:1.1rem; opacity:0.9;">Uncovering the data behind India's premier engineering institutes.</p>
</div>

<!-- ── Stat Cards ──────────────────────────────────────────────────────────── -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px;">
    <div class="card" style="text-align:center; border-top:5px solid #10b981;">
        <i class="fas fa-university fa-3x" style="color:#10b981; margin-bottom:15px;"></i>
        <h3 style="color:#64748b; margin:0;">Institutes Tracked</h3>
        <h1 style="font-size:3rem; margin:10px 0; color:var(--dark);"><?php echo $inst_count; ?></h1>
    </div>
    <div class="card" style="text-align:center; border-top:5px solid #3b82f6;">
        <i class="fas fa-wallet fa-3x" style="color:#3b82f6; margin-bottom:15px;"></i>
        <h3 style="color:#64748b; margin:0;">Overall Avg Package</h3>
        <h1 style="font-size:3rem; margin:10px 0; color:var(--dark);">
            ₹<?php echo number_format($avg_package, 2); ?> <span style="font-size:1rem">LPA</span>
        </h1>
    </div>
    <div class="card" style="text-align:center; border-top:5px solid #f59e0b;">
        <i class="fas fa-rupee-sign fa-3x" style="color:#f59e0b; margin-bottom:15px;"></i>
        <h3 style="color:#64748b; margin:0;">Highest Recorded Domestic</h3>
        <h1 style="font-size:3rem; margin:10px 0; color:var(--dark);">
            ₹<?php echo number_format($highest_pkg, 2); ?> <span style="font-size:1rem">LPA</span>
        </h1>
    </div>
</div>

<!-- ── FAQ Section ─────────────────────────────────────────────────────────── -->
<div class="card" style="margin-top:30px; background-color: #0f172a; border: 1px solid #1e293b; color: #f8fafc; border-radius: 12px; padding: 25px;">
    <h2 style="margin-bottom:6px; color: #f1f5f9; font-weight: 700;">
        <i class="fas fa-question-circle" style="color: #3b82f6;"></i>&nbsp; Frequently Asked Questions
    </h2>
    <p style="color:#94a3b8; margin-bottom:24px; font-size:0.95rem;">
        Every answer below is fetched live from the database.
    </p>

    <div id="faq-list">
        <?php foreach ($faqs as $i => $faq): ?>
        <div class="faq-item" style="
                border:1px solid #334155;
                border-radius:10px;
                margin-bottom:12px;
                overflow:hidden;
                background: #1e293b;
                transition:box-shadow 0.2s;">

            <!-- Question row (clickable) -->
            <button
                class="faq-trigger"
                onclick="toggleFaq(<?php echo $i; ?>)"
                aria-expanded="false"
                style="
                    width:100%;
                    display:flex;
                    align-items:center;
                    gap:14px;
                    padding:16px 20px;
                    background:#1e293b;
                    border:none;
                    cursor:pointer;
                    text-align:left;
                    font-size:1rem;
                    font-weight:600;
                    color:#f1f5f9;
                    transition:background 0.2s;">

                <!-- Coloured icon badge -->
                <span style="
                        flex-shrink:0;
                        width:36px; height:36px;
                        border-radius:8px;
                        background:<?php echo $faq['color']; ?>20;
                        display:flex; align-items:center; justify-content:center;
                        font-size:1.2rem;">
                    <?php echo $faq['icon']; ?>
                </span>

                <span style="flex:1;"><?php echo htmlspecialchars($faq['question']); ?></span>

                <!-- Chevron arrow -->
                <span class="faq-chevron" id="chevron-<?php echo $i; ?>" style="
                        flex-shrink:0;
                        font-size:0.85rem;
                        color:#94a3b8;
                        transition:transform 0.25s;">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </button>

            <!-- Answer panel (collapsed by default) -->
            <div class="faq-body" id="faq-body-<?php echo $i; ?>" style="
                    max-height:0;
                    overflow:hidden;
                    transition:max-height 0.35s ease;
                    padding:0 20px;">
                <div style="
                        padding:14px 0 18px;
                        font-size:0.97rem;
                        color:#cbd5e1;
                        line-height:1.7;
                        border-top:1px solid #334155;">

                    <!-- Coloured left accent bar -->
                    <div style="border-left:4px solid <?php echo $faq['color']; ?>; padding-left:14px;">
                        <?php echo $faq['answer']; ?>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Accordion JavaScript ────────────────────────────────────────────────── -->
<script>
function toggleFaq(index) {
    const body    = document.getElementById('faq-body-'  + index);
    const chevron = document.getElementById('chevron-'   + index);
    const trigger = body.previousElementSibling;
    const isOpen  = body.style.maxHeight && body.style.maxHeight !== '0px';

    // Close every panel
    document.querySelectorAll('.faq-body').forEach(function(b) {
        b.style.maxHeight = '0px';
    });
    document.querySelectorAll('.faq-chevron').forEach(function(c) {
        c.style.transform = 'rotate(0deg)';
    });
    document.querySelectorAll('.faq-trigger').forEach(function(t) {
        t.style.background = '#1e293b';
        t.setAttribute('aria-expanded', 'false');
    });

    // If the clicked panel was closed, open it
    if (!isOpen) {
        body.style.maxHeight = (body.scrollHeight + 40) + 'px';
        chevron.style.transform = 'rotate(180deg)';
        trigger.style.background = '#334155';
        trigger.setAttribute('aria-expanded', 'true');
    }
}
</script>

<?php include 'footer.php'; ?>