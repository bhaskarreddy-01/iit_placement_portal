<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IIT Analytics Portal</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* CSS Variables for Light/Dark Mode */
        :root {
            --primary: #4f46e5;
            --secondary: #3b82f6;
            --dark: #0f172a;
            --light: #f8fafc;
            --card-bg: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }

        /* Dark Mode Override Classes */
        body.dark-mode {
            --dark: #ffffff;
            --light: #0f172a;
            --card-bg: #1e293b;
            --text: #f1f5f9;
            --border: #334155;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            background-color: var(--light); 
            color: var(--text); 
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* Glassmorphism Navbar */
        .navbar { 
            background: linear-gradient(135deg, #0f172a, #1e293b);
            padding: 15px 30px; 
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            flex-wrap: wrap; /* Helps with mobile responsiveness */
        }
        
        .navbar a { 
            color: #cbd5e1; 
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500;
            padding: 8px 15px; 
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .navbar a i { margin-right: 6px; }
        .navbar a:hover { 
            background: rgba(255,255,255,0.1); 
            color: white; 
            transform: translateY(-2px);
        }
        
        /* Floating Action Buttons */
        .action-btns { 
            margin-left: auto; 
            display: flex; 
            gap: 10px; 
        }
        
        .btn { 
            background: rgba(255,255,255,0.1); 
            border: none; 
            color: white; 
            padding: 8px 15px; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: 0.3s;
        }
        
        .btn:hover { 
            background: var(--primary); 
        }

        .container { 
            padding: 40px 20px; 
            max-width: 1200px; 
            margin: auto; 
            animation: fadeIn 0.5s; 
        }
        
        /* Advanced Card Styling */
        .card { 
            background: var(--card-bg); 
            padding: 30px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            margin-bottom: 30px; 
            border: 1px solid var(--border);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .card h2 { 
            margin-top: 0; 
            color: var(--text); 
            border-bottom: 2px solid var(--border); 
            padding-bottom: 10px;
        }
        
        /* Table Styling */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            border-radius: 8px; 
            overflow: hidden; 
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid var(--border); 
        }
        th { 
            background-color: var(--primary); 
            color: white; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 14px;
        }
        tr:hover { 
            background-color: rgba(79, 70, 229, 0.05); /* Subtle primary color highlight */
        }
        
        /* Animations */
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        /* Print Media Query */
        @media print {
            .navbar, .btn { display: none !important; }
            body { background: white; color: black; }
            .card { box-shadow: none; border: 1px solid #ccc; page-break-inside: avoid; }
        }
    </style>
    
    <script>
        // JavaScript for Dark Mode Toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            let isDark = document.body.classList.contains('dark-mode');
            
            // Save user preference to local storage
            localStorage.setItem('darkMode', isDark); 
            
            // Swap out the moon/sun icon
            document.getElementById('themeIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Check local storage on page load to maintain the user's theme choice across pages
        window.addEventListener('DOMContentLoaded', (event) => {
            if(localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
                document.getElementById('themeIcon').className = 'fas fa-sun';
            }
        });
    </script>
</head>
<body>

<div class="navbar">
    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="trends.php"><i class="fas fa-chart-line"></i> YoY Trends</a>
    <a href="branchcomparisions.php"><i class="fas fa-code-branch"></i> Branches</a>
    <a href="rankings.php"><i class="fas fa-trophy"></i> Rankings</a>
    <a href="sectoranalysis.php"><i class="fas fa-chart-pie"></i> Sectors</a>
    <a href="insights.php"><i class="fas fa-lightbulb"></i> Insights</a>
    <a href="compare.php" style="background: var(--primary); color: white;"><i class="fas fa-balance-scale"></i> Compare Tool</a>
    
    <div class="action-btns">
        <button class="btn" onclick="toggleDarkMode()" title="Toggle Dark Mode">
            <i id="themeIcon" class="fas fa-moon"></i>
        </button>
        <button class="btn" onclick="window.print()" title="Export/Print Report">
            <i class="fas fa-print"></i>
        </button>
    </div>
</div>

<div class="container">