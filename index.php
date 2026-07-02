<?php
require_once 'db.php';
$pageTitle = 'Dashboard';

// Fetch summary stats
$stats = [];
$statsQueries = [
    'Patients'    => "SELECT COUNT(*) AS cnt FROM Patient",
    'Doctors'     => "SELECT COUNT(*) AS cnt FROM Doctor",
    'Nurses'      => "SELECT COUNT(*) AS cnt FROM Nurse",
    'Wards'       => "SELECT COUNT(*) AS cnt FROM Ward",
    'Consultants' => "SELECT COUNT(*) AS cnt FROM Consultant",
    'Treatments'  => "SELECT COUNT(*) AS cnt FROM PatientTreatment",
];
foreach ($statsQueries as $label => $sql) {
    $row = db_query_one($conn, $sql);
    $stats[$label] = $row ? $row['cnt'] : 0;
}

// Recent patients
$recentPatients = db_query($conn,
    "SELECT TOP 8 p.PatientNo, p.FirstName + ' ' + p.LastName AS PatientName,
            p.WardName, p.BedNo, p.DateAdmitted,
            DATEDIFF(YEAR, p.DateOfBirth, GETDATE()) AS Age
     FROM Patient p
     ORDER BY p.DateAdmitted DESC"
);

include 'header.php';
?>

<div class="page-header">
    <h1>✚ Hospital Dashboard</h1>
    <p class="subtitle">Ivor Paine Memorial Hospital — Database Management System</p>
</div>

<!-- Stats -->
<div class="dashboard-grid">
    <?php foreach ($stats as $label => $val): ?>
    <div class="stat-card">
        <div class="stat-val"><?= $val ?></div>
        <div class="stat-label"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Links -->
<div class="section-title">Quick Access</div>
<div class="quick-links">
    <a href="form_patient.php" class="quick-link">
        <div class="ql-icon">🧑‍⚕️</div>
        <div class="ql-text">
            <h3>Patient Record</h3>
            <p>Look up a patient's full medical record including complaints, treatments and assigned doctors.</p>
        </div>
    </a>
    <a href="form_ward.php" class="quick-link">
        <div class="ql-icon">🏥</div>
        <div class="ql-text">
            <h3>Ward Record</h3>
            <p>View a ward's nurses, care units and current patient admission list.</p>
        </div>
    </a>
    <a href="form_consultant.php" class="quick-link">
        <div class="ql-icon">👨‍🔬</div>
        <div class="ql-text">
            <h3>Consultant Team Record</h3>
            <p>Look up a doctor's full profile: experience history and performance grades.</p>
        </div>
    </a>
    <a href="reports.php" class="quick-link">
        <div class="ql-icon">📋</div>
        <div class="ql-text">
            <h3>All Reports (12)</h3>
            <p>Run any of the 12 required analytical queries and export results.</p>
        </div>
    </a>
</div>

<!-- Recent Admissions -->
<div class="section-title">Recent Admissions</div>
<div class="card">
    <div class="card-header">
        <span class="card-icon">📅</span>
        <h2>Latest Patient Admissions</h2>
        <span class="card-desc">Most recently admitted patients</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Patient No</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Ward</th>
                        <th>Bed</th>
                        <th>Date Admitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPatients)): ?>
                    <tr><td colspan="7" class="no-data">No patient records found.</td></tr>
                    <?php else: foreach ($recentPatients as $p): ?>
                    <tr>
                        <td><span class="badge badge-navy"><?= $p['PatientNo'] ?></span></td>
                        <td><strong><?= htmlspecialchars($p['PatientName']) ?></strong></td>
                        <td><?= $p['Age'] ?></td>
                        <td><?= htmlspecialchars($p['WardName']) ?></td>
                        <td><?= htmlspecialchars($p['BedNo']) ?></td>
                        <td><?= $p['DateAdmitted'] ?></td>
                        <td>
                            <a href="form_patient.php?id=<?= $p['PatientNo'] ?>" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
