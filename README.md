# Ivor Paine Memorial Hospital — Database System
## CS204 Database Systems | FAST-NUCES Islamabad
## Milestone 3 — PHP/HTML Frontend

---

## Setup Instructions

### 1. XAMPP Setup
- Make sure XAMPP is installed and Apache is running.
- Copy the entire `hospital/` folder into: `C:\xampp\htdocs\hospital\`

### 2. SQL Server Driver for PHP
The `sqlsrv` extension is required. If not installed in XAMPP:
1. Download the Microsoft SQLSRV PHP drivers from:
   https://github.com/microsoft/msphpsql/releases
2. Copy `php_sqlsrv_XX_ts.dll` and `php_pdo_sqlsrv_XX_ts.dll`
   into `C:\xampp\php\ext\`
3. Add to `C:\xampp\php\php.ini`:
   ```
   extension=php_sqlsrv_83_ts_x64.dll
   extension=php_pdo_sqlsrv_83_ts_x64.dll
   ```
4. Restart Apache.

### 3. Database
- Server: `REHANS-PC\SQLEXPRESS`
- Database name: `IvorPaineHospital`
- Username: `sa` | Password: `hooper123`
- Run `Milestone2_DDL.sql` first, then `Milestone2_Insertion.sql`

### 4. Access the App
Open: http://localhost/hospital/

---

## Pages & Features

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `index.php` | Summary stats, recent admissions |
| Patient Record | `form_patient.php` | **Form 1** — Input Patient No |
| Ward Record | `form_ward.php` | **Form 2** — Select Ward Name |
| Consultant Team | `form_consultant.php` | **Form 3** — Input Staff No |
| Reports | `reports.php` | All 12 required queries |

---

## All 12 Queries Implemented

1. Consultants and their doctor teams
2. Wards with sisters, care units & staff nurses
3. Patients — complaints, treatments & dates
4. Junior housemen, their patients & care unit staff nurses
5. Consultants with unique specialty
6. Complaints, treatments & doctor experience history
7. Patients with more than one complaint & their treatments
8. Patients grouped by treatment within complaint
9. Performance history for a particular doctor *(parameterised)*
10. Full medical details for a particular patient *(parameterised)*
11. Treatments for a complaint between two dates *(parameterised)*
12. Staff positions and count

---

## Files

```
hospital/
├── db.php              — SQL Server connection & helper functions
├── header.php          — Shared navigation header
├── footer.php          — Shared page footer
├── style.css           — Complete stylesheet (no external CSS)
├── index.php           — Dashboard
├── form_patient.php    — Patient Record Form/Report
├── form_ward.php       — Ward Record Form/Report
├── form_consultant.php — Consultant Team Record Form
└── reports.php         — All 12 analytical reports
```
