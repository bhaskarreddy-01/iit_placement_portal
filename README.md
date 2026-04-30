#  IIT Placement Analytics Portal

## 1. PROJECT OVERVIEW

The **IIT Placement Analytics Portal** is a web-based dashboard designed to analyze and visualize placement data across IITs.
It provides insights into salaries, placement percentages, sector distribution, and trends using interactive charts and filters.

The system helps users:

* Compare institutes and branches
* Analyze placement trends over years
* Explore sector-wise hiring distribution
* View rankings and insights

---

## 2. FEATURES

###  Dashboard

* Displays overall statistics like:

  * Number of institutes
  * Average package
  * Highest package
* Includes FAQ section with dynamic answers from database
  (Refer: )

---

###  Trends Analysis

* Year-over-Year (YoY) analysis of average packages
* Filter by institute and branch
  (Refer: )

---

###  Branch Comparison

* Compare median and average salaries across branches
* Filter by institute and year
  (Refer: )

---

###  Rankings

* Displays top placements based on highest package
* Shows top 10 ranked entries
  (Refer: )

---

###  Sector Analysis

* Shows sector-wise distribution of placed students
* Doughnut chart visualization
  (Refer: )

---

###  Insights

* Identifies:

  * Top performing institute
  * Highest growth branch
  * Placement trends
* Includes trajectory and sector shift charts
  (Refer: )

---

### ⚖️ Comparison Tool

* Head-to-head institute comparison
* Branch-wise institute comparison
* Dynamic chart updates
  (Refer: )

---

###  UI Features

* Dark Mode toggle
* Responsive design
* Print/export functionality
  (Refer: )

---

## 3. TECHNOLOGY STACK

### Frontend

* HTML, CSS (Glassmorphism UI)
* JavaScript
* Chart.js (for visualization)

### Backend

* PHP

### Database

* MySQL

---

## 4. DATABASE DETAILS

Database Name: `iit_placements_db`

Main Tables:

1. **placements**

   * Institute
   * Branch
   * Year
   * AvgPackage_LPA
   * MedianPackage_LPA
   * Highest_Domestic_LPA
   * Highest_International_LPA
   * Placement_Percentage

2. **branch_sector**

   * Institute
   * Branch
   * Sector
   * Students_Placed

Connection handled via:
(Refer: )

---

## 5. FILE STRUCTURE

* `index.php` → Main dashboard
* `trends.php` → YoY trends visualization
* `branchcomparisions.php` → Branch salary comparison
* `rankings.php` → Top placement rankings
* `sectoranalysis.php` → Sector distribution analysis
* `insights.php` → Advanced analytics & insights
* `compare.php` → Institute comparison tool
* `header.php` → Navigation bar, UI styles, dark mode
* `footer.php` → Footer section
* `db.php` → Database connection

---

## 6. HOW TO RUN THE PROJECT

1. Install XAMPP / WAMP
2. Place project folder in:

   * `htdocs` (XAMPP) or `www` (WAMP)
3. Import database:

   * Create database `iit_placements_db`
   * Import SQL data
4. Start Apache and MySQL
5. Open browser:

   ```
   http://localhost/your-folder-name/
   ```

---

## 7. KEY FUNCTIONALITIES

* Dynamic filtering using GET parameters
* Real-time chart updates using JavaScript
* SQL aggregation queries for analytics
* Interactive UI with smooth animations

---

## 8. FUTURE IMPROVEMENTS

* Add login/authentication system
* Export data to CSV/Excel
* AI-based prediction of placement trends
* More detailed institute-level reports

---

## 9. CONCLUSION

This project provides a complete data-driven analytics platform for IIT placements, combining:

* Backend data processing (PHP + MySQL)
* Frontend visualization (Chart.js)
* Interactive user experience

It is useful for students, researchers, and analysts to understand placement trends effectively.

---
