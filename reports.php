<?php
// ============================================================
// REPORTS PAGE — All 12 Required Queries
// ============================================================
require_once 'db.php';
$pageTitle = 'Reports & Queries';

$reportNum  = isset($_GET['r']) ? (int)$_GET['r'] : 0;
$results    = null;
$error      = null;

// ── Parameters for specific queries ──
$paramDoctorId   = isset($_GET['doctor_id'])   ? (int)$_GET['doctor_id']   : 0;
$paramPatientId  = isset($_GET['patient_id'])  ? (int)$_GET['patient_id']  : 0;
$paramComplaint  = isset($_GET['complaint'])   ? trim($_GET['complaint'])   : '';
$paramDateFrom   = isset($_GET['date_from'])   ? trim($_GET['date_from'])   : '';
$paramDateTo     = isset($_GET['date_to'])     ? trim($_GET['date_to'])     : '';

// ── Query Metadata ──
$reports = [
    1  => [
        'title' => 'Consultants and Their Doctor Teams',
        'desc'  => 'A list of consultants and the doctors in their team.',
        'params'=> []
    ],
    2  => [
        'title' => 'Wards with Sisters, Care Units & Staff Nurses',
        'desc'  => 'A list of wards with respective sisters, care units and staff nurses in charge of care units.',
        'params'=> []
    ],
    3  => [
        'title' => 'Patients — Complaints, Treatments & Dates',
        'desc'  => 'A list of patients and their complaints, treatments and dates of treatment.',
        'params'=> []
    ],
    4  => [
        'title' => 'Junior Housemen — Patients & Care Unit Staff Nurses',
        'desc'  => 'A list of junior housemen and their patients and the staff nurse for the care unit of that patient.',
        'params'=> []
    ],
    5  => [
        'title' => 'Consultants with Unique Specialty',
        'desc'  => 'A list of consultants who are the only one with their specialty.',
        'params'=> []
    ],
    6  => [
        'title' => 'Complaints, Treatments & Doctor Experience History',
        'desc'  => 'A list of complaints, treatments given for that complaint and experience history of the doctor giving that treatment.',
        'params'=> []
    ],
    7  => [
        'title' => 'Patients with More Than One Complaint & Their Treatments',
        'desc'  => 'A list of patients who have more than one complaint and their treatments.',
        'params'=> []
    ],
    8  => [
        'title' => 'Patients Grouped by Treatment within Complaint',
        'desc'  => 'A list of patients grouped by treatment within complaint.',
        'params'=> []
    ],
    9  => [
        'title' => 'Performance History for a Particular Doctor',
        'desc'  => 'A performance history (all review grades) for a selected doctor.',
        'params'=> ['doctor_id']
    ],
    10 => [
        'title' => 'Full Medical Details for a Particular Patient',
        'desc'  => 'Full medical details for a selected patient.',
        'params'=> ['patient_id']
    ],
    11 => [
        'title' => 'Treatments for a Complaint Between Two Dates',
        'desc'  => 'A list of treatments given for a particular complaint between two given dates, ordered by treatment.',
        'params'=> ['complaint', 'date_from', 'date_to']
    ],
    12 => [
        'title' => 'Staff Positions and Count',
        'desc'  => 'A list of the different positions held by staff and a count of the number of staff in each position.',
        'params'=> []
    ],
];

// ── Helper: run query and store results ──
function runReport($conn, $sql, $params = []) {
    return db_query($conn, $sql, $params);
}

// ── SQL Definitions ──
if ($reportNum >= 1 && $reportNum <= 12) {
    switch ($reportNum) {

        // ── Q1: Consultants and their teams ──
        case 1:
            $results = runReport($conn,
                "SELECT
                    con_d.FirstName + ' ' + con_d.LastName  AS Consultant,
                    con_d.Position                           AS ConsultantPosition,
                    s.SpecialtyName,
                    d.StaffNo                                AS DoctorStaffNo,
                    d.FirstName + ' ' + d.LastName           AS DoctorName,
                    d.Position                               AS DoctorPosition,
                    d.DateJoinedTeam
                 FROM Consultant c                          -- the team leader
                 JOIN Doctor con_d ON con_d.StaffNo = c.StaffNo
                 JOIN Specialty s  ON s.SpecialtyID = c.SpecialtyID
                 -- find all consultants who name this person as their team leader
                 JOIN Consultant c2 ON c2.ConsultantTeamLeaderID = c.StaffNo
                 JOIN Doctor d      ON d.StaffNo = c2.StaffNo
                 WHERE c.ConsultantTeamLeaderID IS NULL     -- only actual leaders
                 ORDER BY Consultant, DoctorPosition, DoctorName"
            );
            break;

        // ── Q2: Wards, sisters, care units, staff nurses ──
        case 2:
            $results = runReport($conn,
                "SELECT
                    w.WardName,
                    sp.SpecialtyName                          AS Specialty,
                    ds.FirstName + ' ' + ds.LastName          AS DaySister,
                    ns.FirstName + ' ' + ns.LastName          AS NightSister,
                    cu.CareUnitID,
                    sn.FirstName + ' ' + sn.LastName          AS StaffNurseInCharge
                 FROM Ward w
                 JOIN Specialty sp   ON sp.SpecialtyID = w.SpecialtyID
                 LEFT JOIN Nurse ds  ON ds.StaffNo = w.DaySisterID
                 LEFT JOIN Nurse ns  ON ns.StaffNo = w.NightSisterID
                 LEFT JOIN CareUnit cu ON cu.WardName = w.WardName
                 LEFT JOIN Nurse sn  ON sn.StaffNo = cu.InChargeNurseID
                 ORDER BY w.WardName, cu.CareUnitID"
            );
            break;

        // ── Q3: Patients — complaints, treatments, dates ──
        case 3:
            $results = runReport($conn,
                "SELECT
                    p.PatientNo,
                    p.FirstName + ' ' + p.LastName  AS PatientName,
                    p.WardName,
                    c.ComplaintCode,
                    c.Description  AS Complaint,
                    t.TreatmentCode,
                    t.Description  AS Treatment,
                    d.FirstName + ' ' + d.LastName  AS Doctor,
                    pt.DateStarted,
                    pt.DateEnded
                 FROM PatientTreatment pt
                 JOIN Patient   p ON p.PatientNo      = pt.PatientNo
                 JOIN Complaint c ON c.ComplaintCode  = pt.ComplaintCode
                 JOIN Treatment t ON t.TreatmentCode  = pt.TreatmentCode
                 JOIN Doctor    d ON d.StaffNo         = pt.DoctorStaffNo
                 ORDER BY p.PatientNo, pt.DateStarted"
            );
            break;

        // ── Q4: Junior Housemen, patients, care unit staff nurses ──
        case 4:
            $results = runReport($conn,
                "SELECT
                    d.StaffNo                               AS DoctorStaffNo,
                    d.FirstName + ' ' + d.LastName          AS JuniorHouseman,
                    p.PatientNo,
                    p.FirstName + ' ' + p.LastName          AS PatientName,
                    pd.Role                                 AS DoctorRole,
                    p.WardName,
                    p.CareUnitID,
                    sn.FirstName + ' ' + sn.LastName        AS CareUnitStaffNurse
                 FROM Doctor d
                 JOIN PatientDoctor pd ON pd.DoctorStaffNo = d.StaffNo
                 JOIN Patient       p  ON p.PatientNo      = pd.PatientNo
                 JOIN CareUnit      cu ON cu.CareUnitID    = p.CareUnitID
                 JOIN Nurse         sn ON sn.StaffNo       = cu.InChargeNurseID
                 WHERE d.Position = 'Junior Houseman'
                 ORDER BY JuniorHouseman, PatientName"
            );
            break;

        // ── Q5: Consultants with unique specialty ──
        case 5:
            $results = runReport($conn,
                "SELECT
                    d.FirstName + ' ' + d.LastName  AS Consultant,
                    d.Position,
                    s.SpecialtyName
                 FROM Consultant c
                 JOIN Doctor    d ON d.StaffNo    = c.StaffNo
                 JOIN Specialty s ON s.SpecialtyID = c.SpecialtyID
                 WHERE c.SpecialtyID IN (
                    SELECT SpecialtyID FROM Consultant
                    GROUP BY SpecialtyID HAVING COUNT(*) = 1
                 )
                 ORDER BY s.SpecialtyName"
            );
            break;

        // ── Q6: Complaints, treatments, doctor experience history ──
        case 6:
            $results = runReport($conn,
                "SELECT
                    c.ComplaintCode,
                    c.Description                           AS Complaint,
                    t.TreatmentCode,
                    t.Description                           AS Treatment,
                    d.StaffNo                               AS DoctorStaffNo,
                    d.FirstName + ' ' + d.LastName          AS Doctor,
                    d.Position,
                    de.FromDate,
                    de.ToDate,
                    de.Position                             AS PreviousPosition,
                    de.Establishment
                 FROM PatientTreatment pt
                 JOIN Complaint c        ON c.ComplaintCode = pt.ComplaintCode
                 JOIN Treatment t        ON t.TreatmentCode = pt.TreatmentCode
                 JOIN Doctor    d        ON d.StaffNo        = pt.DoctorStaffNo
                 LEFT JOIN DoctorExperience de ON de.DoctorStaffNo = d.StaffNo
                 ORDER BY c.ComplaintCode, t.TreatmentCode, d.StaffNo, de.FromDate"
            );
            break;

        // ── Q7: Patients with >1 complaint and their treatments ──
        case 7:
            $results = runReport($conn,
                "SELECT
                    p.PatientNo,
                    p.FirstName + ' ' + p.LastName  AS PatientName,
                    c.ComplaintCode,
                    c.Description                   AS Complaint,
                    t.Description                   AS Treatment,
                    pt.DateStarted,
                    pt.DateEnded
                 FROM Patient p
                 JOIN PatientTreatment pt ON pt.PatientNo = p.PatientNo
                 JOIN Complaint c         ON c.ComplaintCode = pt.ComplaintCode
                 JOIN Treatment t         ON t.TreatmentCode = pt.TreatmentCode
                 WHERE p.PatientNo IN (
                    SELECT PatientNo FROM PatientTreatment
                    GROUP BY PatientNo HAVING COUNT(DISTINCT ComplaintCode) > 1
                 )
                 ORDER BY p.PatientNo, c.ComplaintCode"
            );
            break;

        // ── Q8: Patients grouped by treatment within complaint ──
        case 8:
            $results = runReport($conn,
                "SELECT
                    c.ComplaintCode,
                    c.Description                   AS Complaint,
                    t.TreatmentCode,
                    t.Description                   AS Treatment,
                    p.PatientNo,
                    p.FirstName + ' ' + p.LastName  AS PatientName,
                    pt.DateStarted,
                    pt.DateEnded
                 FROM PatientTreatment pt
                 JOIN Complaint c ON c.ComplaintCode = pt.ComplaintCode
                 JOIN Treatment t ON t.TreatmentCode = pt.TreatmentCode
                 JOIN Patient   p ON p.PatientNo     = pt.PatientNo
                 ORDER BY c.ComplaintCode, t.TreatmentCode, p.PatientNo"
            );
            break;

        // ── Q9: Performance history for a particular doctor ──
        case 9:
            if ($paramDoctorId > 0) {
                $results = runReport($conn,
                    "SELECT
                        d.StaffNo,
                        d.FirstName + ' ' + d.LastName  AS DoctorName,
                        d.Position,
                        dp.ReviewDate,
                        dp.PerfGrade
                     FROM DoctorProgress dp
                     JOIN Doctor d ON d.StaffNo = dp.DoctorStaffNo
                     WHERE dp.DoctorStaffNo = ?
                     ORDER BY dp.ReviewDate",
                    [$paramDoctorId]
                );
            }
            break;

        // ── Q10: Full medical details for a particular patient ──
        case 10:
            if ($paramPatientId > 0) {
                $results = runReport($conn,
                    "SELECT
                        p.PatientNo,
                        p.FirstName + ' ' + p.LastName          AS PatientName,
                        p.DateOfBirth,
                        DATEDIFF(YEAR, p.DateOfBirth, GETDATE()) AS Age,
                        p.DateAdmitted,
                        p.BedNo,
                        p.WardName,
                        p.CareUnitID,
                        c.ComplaintCode,
                        c.Description                           AS Complaint,
                        t.TreatmentCode,
                        t.Description                           AS Treatment,
                        d.StaffNo                               AS DoctorStaffNo,
                        d.FirstName + ' ' + d.LastName          AS Doctor,
                        d.Position,
                        CASE WHEN con.StaffNo IS NOT NULL THEN 'Yes' ELSE 'No' END AS IsConsultant,
                        pt.DateStarted,
                        pt.DateEnded
                     FROM Patient p
                     LEFT JOIN PatientTreatment pt ON pt.PatientNo    = p.PatientNo
                     LEFT JOIN Complaint c          ON c.ComplaintCode = pt.ComplaintCode
                     LEFT JOIN Treatment t          ON t.TreatmentCode = pt.TreatmentCode
                     LEFT JOIN Doctor d             ON d.StaffNo        = pt.DoctorStaffNo
                     LEFT JOIN Consultant con        ON con.StaffNo      = d.StaffNo
                     WHERE p.PatientNo = ?
                     ORDER BY pt.DateStarted",
                    [$paramPatientId]
                );
            }
            break;

        // ── Q11: Treatments for complaint between dates ──
        case 11:
            if ($paramComplaint !== '' && $paramDateFrom !== '' && $paramDateTo !== '') {
                $results = runReport($conn,
                    "SELECT
                        c.ComplaintCode,
                        c.Description                   AS Complaint,
                        t.TreatmentCode,
                        t.Description                   AS Treatment,
                        p.PatientNo,
                        p.FirstName + ' ' + p.LastName  AS PatientName,
                        d.FirstName + ' ' + d.LastName  AS Doctor,
                        pt.DateStarted,
                        pt.DateEnded
                     FROM PatientTreatment pt
                     JOIN Complaint c ON c.ComplaintCode = pt.ComplaintCode
                     JOIN Treatment t ON t.TreatmentCode = pt.TreatmentCode
                     JOIN Patient   p ON p.PatientNo     = pt.PatientNo
                     JOIN Doctor    d ON d.StaffNo        = pt.DoctorStaffNo
                     WHERE pt.ComplaintCode = ?
                       AND pt.DateStarted  >= ?
                       AND pt.DateStarted  <= ?
                     ORDER BY t.TreatmentCode, pt.DateStarted",
                    [$paramComplaint, $paramDateFrom, $paramDateTo]
                );
            }
            break;

        // ── Q12: Staff positions and count ──
        case 12:
            $results = runReport($conn,
                "SELECT Position, COUNT(*) AS StaffCount,
                        'Doctor' AS StaffType
                 FROM Doctor
                 GROUP BY Position
                 UNION ALL
                 SELECT Role AS Position, COUNT(*) AS StaffCount,
                        'Nurse' AS StaffType
                 FROM Nurse
                 GROUP BY Role
                 ORDER BY StaffType, StaffCount DESC"
            );
            break;
    }
}

// Fetch dropdown data for param-based reports
$allDoctorsForQ9 = db_query($conn, "SELECT StaffNo, FirstName + ' ' + LastName AS Name, Position FROM Doctor ORDER BY Position, LastName");
$allPatientsForQ10= db_query($conn, "SELECT PatientNo, FirstName + ' ' + LastName AS Name FROM Patient ORDER BY PatientNo");
$allComplaints   = db_query($conn, "SELECT ComplaintCode, Description FROM Complaint ORDER BY ComplaintCode");

include 'header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Dashboard</a><span>›</span> Reports &amp; Queries
</div>
<div class="page-header">
    <h1>Reports &amp; Analytical Queries</h1>
    <p class="subtitle">All 12 required hospital management reports</p>
</div>

<div class="report-grid">
    <?php foreach ($reports as $num => $rep): ?>
    <div class="report-card">
        <div class="report-card-head">
            <div class="report-num"><?= $num ?></div>
            <h3><?= htmlspecialchars($rep['title']) ?></h3>
        </div>
        <div class="report-card-body">
            <p><?= htmlspecialchars($rep['desc']) ?></p>

            <?php if (empty($rep['params'])): ?>
            <a href="reports.php?r=<?= $num ?>" class="btn btn-primary btn-sm">▶ Run Report</a>
            <?php else: ?>
            <form method="GET" action="reports.php" style="margin-top:4px;">
                <input type="hidden" name="r" value="<?= $num ?>">

                <?php if (in_array('doctor_id', $rep['params'])): ?>
                <div class="form-group" style="margin-bottom:8px;">
                    <label>Doctor</label>
                    <select name="doctor_id" required>
                        <option value="">Select…</option>
                        <?php foreach ($allDoctorsForQ9 as $dd): ?>
                        <option value="<?= $dd['StaffNo'] ?>"
                            <?= ($paramDoctorId == $dd['StaffNo'] && $reportNum == $num) ? 'selected' : '' ?>>
                            #<?= $dd['StaffNo'] ?> — <?= htmlspecialchars($dd['Name']) ?> (<?= $dd['Position'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (in_array('patient_id', $rep['params'])): ?>
                <div class="form-group" style="margin-bottom:8px;">
                    <label>Patient</label>
                    <select name="patient_id" required>
                        <option value="">Select…</option>
                        <?php foreach ($allPatientsForQ10 as $pp): ?>
                        <option value="<?= $pp['PatientNo'] ?>"
                            <?= ($paramPatientId == $pp['PatientNo'] && $reportNum == $num) ? 'selected' : '' ?>>
                            #<?= $pp['PatientNo'] ?> — <?= htmlspecialchars($pp['Name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (in_array('complaint', $rep['params'])): ?>
                <div class="form-group" style="margin-bottom:8px;">
                    <label>Complaint</label>
                    <select name="complaint" required>
                        <option value="">Select…</option>
                        <?php foreach ($allComplaints as $cc): ?>
                        <option value="<?= $cc['ComplaintCode'] ?>"
                            <?= ($paramComplaint == $cc['ComplaintCode'] && $reportNum == $num) ? 'selected' : '' ?>>
                            <?= $cc['ComplaintCode'] ?> — <?= htmlspecialchars($cc['Description']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-gap" style="margin-bottom:8px;">
                    <div class="form-group" style="flex:1;">
                        <label>From Date</label>
                        <input type="date" name="date_from"
                               value="<?= ($reportNum == $num) ? htmlspecialchars($paramDateFrom) : '' ?>" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>To Date</label>
                        <input type="date" name="date_to"
                               value="<?= ($reportNum == $num) ? htmlspecialchars($paramDateTo) : '' ?>" required>
                    </div>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-sm">▶ Run Report</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($reportNum > 0 && isset($reports[$reportNum])): ?>
<div class="result-section">
    <div class="card">
        <div class="card-header">
            <span class="card-icon">📊</span>
            <h2>Report <?= $reportNum ?>: <?= htmlspecialchars($reports[$reportNum]['title']) ?></h2>
            <?php if (is_array($results) && !isset($results['error'])): ?>
            <span class="result-count"><?= count($results) ?> row(s)</span>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-sm btn-gold" style="margin-left:12px;">🖨 Print</button>
        </div>
        <div class="card-body" style="padding:0 0 0 0;">

        <?php
        // Check if param-based report was run without params
        $needsParams = !empty($reports[$reportNum]['params']);
        $hasParams = match($reportNum) {
            9  => $paramDoctorId > 0,
            10 => $paramPatientId > 0,
            11 => $paramComplaint !== '' && $paramDateFrom !== '' && $paramDateTo !== '',
            default => true
        };
        ?>

        <?php if ($needsParams && !$hasParams): ?>
        <div style="padding:32px;" class="no-data">Please provide the required parameters above and run the report.</div>
        
        <?php elseif (isset($results['error'])): ?>
        <div class="alert alert-error" style="margin:24px;">
            <strong>Database Error:</strong><br>
            <pre style="margin-top:8px; white-space:pre-wrap; font-family:monospace;"><?= htmlspecialchars(print_r($results['error'], true)) ?></pre>
        </div>

        <?php elseif (!is_array($results) || count($results) === 0): ?>
        <div class="no-data">No results found for the given criteria.</div>

        <?php else:
            // Render table dynamically using column names from first row
            $cols = array_keys($results[0]);
        ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($cols as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                    <tr>
                        <?php foreach ($cols as $col): ?>
                        <td><?php
                            $val = $row[$col];
                            if ($val === null) {
                                echo '<span style="color:var(--text-light);font-style:italic;">—</span>';
                            } elseif (in_array($col, ['PerfGrade'])) {
                                $g = htmlspecialchars($val);
                                $cl = match(true) {
                                    $val === 'A'              => 'badge-success',
                                    str_starts_with($val,'B') => 'badge-primary',
                                    default                   => 'badge-warning',
                                };
                                echo "<span class='badge $cl'>$g</span>";
                            } elseif (in_array($col, ['DateEnded']) && $val === null) {
                                echo '<span class="badge badge-success">Ongoing</span>';
                            } elseif (str_ends_with($col,'Code') || $col === 'StaffNo' || $col === 'PatientNo' || $col === 'CareUnitID') {
                                echo '<span class="badge badge-navy">' . htmlspecialchars($val) . '</span>';
                            } else {
                                echo htmlspecialchars($val);
                            }
                        ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        </div></div></div>
<?php endif; ?>

<?php include 'footer.php'; ?>