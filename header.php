<?php
// header.php — shared across all pages
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ivor Paine Memorial Hospital<?php echo isset($pageTitle) ? ' — ' . htmlspecialchars($pageTitle) : ''; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+3:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            <div class="brand-cross">✚</div>
            <div class="brand-text">
                <span class="brand-name">Ivor Paine Memorial Hospital</span>
                <span class="brand-sub">Hospital Management System</span>
            </div>
        </div>
        <nav class="main-nav">
            <a href="index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Dashboard</a>
            <div class="nav-group">
                <span class="nav-label">Forms</span>
                <a href="form_patient.php" class="<?= $currentPage === 'form_patient' ? 'active' : '' ?>">Patient Record</a>
                <a href="form_ward.php" class="<?= $currentPage === 'form_ward' ? 'active' : '' ?>">Ward Record</a>
                <a href="form_consultant.php" class="<?= $currentPage === 'form_consultant' ? 'active' : '' ?>">Consultant Team</a>
            </div>
            <div class="nav-group">
                <span class="nav-label">Reports</span>
                <a href="reports.php" class="<?= $currentPage === 'reports' ? 'active' : '' ?>">All Reports</a>
            </div>
        </nav>
    </div>
</div>

<div class="page-wrap">
