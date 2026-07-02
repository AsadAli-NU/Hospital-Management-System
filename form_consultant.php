<?php
// ============================================================
// FORM: CONSULTANT TEAM RECORD
// Input: Staff No (doctor)
// Shows: Staff No, Name, Position, Date Joined Team
//        Previous Experience (From, To, Position, Establishment)
//        Progress (Date, Performance Grade)
// ============================================================
require_once 'db.php';
$pageTitle = 'Consultant Team Record';

$staffNo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$doctor  = null;
$team    = [];
$exp     = [];
$prog    = [];

$allDoctors = db_query($conn,
    "SELECT d.StaffNo, d.FirstName + ' ' + d.LastName AS Name, d.Position,
            CASE WHEN c.StaffNo IS NOT NULL THEN 1 ELSE 0 END AS IsConsultant
     FROM Doctor d LEFT JOIN Consultant c ON c.StaffNo = d.StaffNo
     ORDER BY IsConsultant DESC, d.Position, d.LastName"
);

if ($staffNo > 0) {
    $doctor = db_query_one($conn,
        "SELECT d.StaffNo,
                d.FirstName + ' ' + d.LastName AS Name,
                d.Position,
                d.DateJoinedTeam,
                CASE WHEN c.StaffNo IS NOT NULL THEN 'Yes' ELSE 'No' END AS IsConsultant,
                s.SpecialtyName,
                ldr.FirstName + ' ' + ldr.LastName AS TeamLeader
         FROM   Doctor d
         LEFT JOIN Consultant c   ON c.StaffNo = d.StaffNo
         LEFT JOIN Specialty s    ON s.SpecialtyID = c.SpecialtyID
         LEFT JOIN Doctor ldr     ON ldr.StaffNo = c.ConsultantTeamLeaderID
         WHERE  d.StaffNo = ?",
        [$staffNo]
    );

    // Prevent rendering the form if an SQL error occurs
    if (isset($doctor['error'])) {
        die("<div class='alert alert-error' style='margin:24px;'><strong>Database Error:</strong> " . htmlspecialchars(print_r($doctor['error'], true)) . "</div>");
    }

    if ($doctor) {
        // Show the team this doctor leads (if they are a team leader/consultant)
        // OR show all members of the team this doctor belongs to (including the leader)
        $team = db_query($conn,
            "-- Members led by this doctor (this doctor is the leader)
             SELECT d.StaffNo, d.FirstName + ' ' + d.LastName AS DoctorName,
                    d.Position, d.DateJoinedTeam
             FROM Consultant c
             JOIN Doctor d ON d.StaffNo = c.StaffNo
             WHERE c.ConsultantTeamLeaderID = ?
             UNION
             -- The team leader of this doctor (if this doctor is a team member)
             SELECT ldr.StaffNo, ldr.FirstName + ' ' + ldr.LastName,
                    ldr.Position, ldr.DateJoinedTeam
             FROM Consultant c2
             JOIN Doctor ldr ON ldr.StaffNo = c2.ConsultantTeamLeaderID
             WHERE c2.StaffNo = ? AND c2.ConsultantTeamLeaderID IS NOT NULL
             UNION
             -- Peers: other members who share the same team leader as this doctor
             SELECT d3.StaffNo, d3.FirstName + ' ' + d3.LastName,
                    d3.Position, d3.DateJoinedTeam
             FROM Consultant c3
             JOIN Doctor d3 ON d3.StaffNo = c3.StaffNo
             WHERE c3.ConsultantTeamLeaderID = (
                 SELECT ConsultantTeamLeaderID FROM Consultant WHERE StaffNo = ?
             ) AND c3.StaffNo <> ?
             ORDER BY Position",
            [$staffNo, $staffNo, $staffNo, $staffNo]
        );

        // Previous Experience
        $exp = db_query($conn,
            "SELECT FromDate, ToDate, Position, Establishment
             FROM DoctorExperience
             WHERE DoctorStaffNo = ?
             ORDER BY FromDate",
            [$staffNo]
        );

        // Performance Progress
        $prog = db_query($conn,
            "SELECT ReviewDate, PerfGrade
             FROM DoctorProgress
             WHERE DoctorStaffNo = ?
             ORDER BY ReviewDate",
            [$staffNo]
        );
    }
}

include 'header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Dashboard</a><span>›</span> Consultant Team Record
</div>
<div class="page-header">
    <h1>Consultant Team Record</h1>
    <p class="subtitle">Enter a Staff Number to view a doctor's complete profile, experience and performance history</p>
</div>

<div class="search-box">
    <h3>👨‍🔬 Input Staff No</h3>
    <form method="GET" action="form_consultant.php">
        <div class="search-row">
            <div class="form-group">
                <label for="id">Staff Number</label>
                <input type="number" id="id" name="id" min="1"
                       value="<?= $staffNo ?: '' ?>" placeholder="e.g. 1" required>
            </div>
            <div class="form-group" style="flex:1; min-width:200px;">
                <label for="sel_doc">— or Select Doctor —</label>
                <select id="sel_doc" onchange="document.getElementById('id').value=this.value;">
                    <option value="">Choose a doctor…</option>
                    <?php foreach ($allDoctors as $doc): ?>
                    <option value="<?= $doc['StaffNo'] ?>"
                        <?= ($doc['StaffNo'] == $staffNo) ? 'selected' : '' ?>>
                        #<?= $doc['StaffNo'] ?> — <?= htmlspecialchars($doc['Name']) ?>
                        (<?= $doc['Position'] ?>)<?= $doc['IsConsultant'] ? ' ★' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">View Record</button>
            </div>
        </div>
    </form>
    <p style="font-size:0.8rem; color:var(--text-light); margin-top:8px;">★ = Consultant</p>
</div>

<?php if ($staffNo > 0 && !$doctor): ?>
<div class="alert alert-error">No doctor found with Staff No <strong><?= $staffNo ?></strong>.</div>

<?php elseif ($doctor): ?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-icon">👨‍🔬</span>
        <h2>IVOR PAINE MEMORIAL HOSPITAL — CONSULTANT TEAM RECORD</h2>
        <button onclick="window.print()" class="btn btn-sm btn-gold" style="margin-left:auto;">🖨 Print</button>
    </div>
    <div class="card-body">

        <div class="info-panel" style="margin-bottom:24px;">
            <div class="info-item">
                <div class="lbl">Staff No</div>
                <div class="val"><strong><?= $doctor['StaffNo'] ?></strong></div>
            </div>
            <div class="info-item" style="grid-column:span 3;">
                <div class="lbl">Name</div>
                <div class="val"><strong><?= htmlspecialchars($doctor['Name']) ?></strong></div>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Position</div>
                <div class="val">
                    <span class="badge badge-primary"><?= htmlspecialchars($doctor['Position']) ?></span>
                </div>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Date Joined Team</div>
                <div class="val"><?= $doctor['DateJoinedTeam'] ?></div>
            </div>
            <?php if ($doctor['IsConsultant'] === 'Yes'): ?>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Consultant Specialty</div>
                <div class="val"><span class="badge badge-gold"><?= htmlspecialchars($doctor['SpecialtyName'] ?? '—') ?></span></div>
            </div>
            <?php endif; ?>
            <?php if ($doctor['TeamLeader']): ?>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Team Leader</div>
                <div class="val"><?= htmlspecialchars($doctor['TeamLeader']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($team)): ?>
        <div style="border-top:2px solid var(--border); margin:20px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Team Members</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Staff No</th>
                        <th>Doctor Name</th>
                        <th>Position</th>
                        <th>Date Joined Team</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($team as $tm): ?>
                    <tr>
                        <td><?= $tm['StaffNo'] ?></td>
                        <td><strong><?= htmlspecialchars($tm['DoctorName']) ?></strong></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($tm['Position']) ?></span></td>
                        <td><?= $tm['DateJoinedTeam'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="border-top:2px solid var(--border); margin:24px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Previous Experience</h3>

        <?php if (empty($exp)): ?>
        <p class="no-data">No previous experience records found.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Position</th>
                        <th>Establishment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exp as $e): ?>
                    <tr>
                        <td><?= $e['FromDate'] ?></td>
                        <td><?= $e['ToDate'] ?? '<span class="badge badge-success">Present</span>' ?></td>
                        <td><?= htmlspecialchars($e['Position']) ?></td>
                        <td><?= htmlspecialchars($e['Establishment']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="border-top:2px solid var(--border); margin:24px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Progress</h3>

        <?php if (empty($prog)): ?>
        <p class="no-data">No performance review records found.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Review Date</th>
                        <th>Performance Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prog as $pg): ?>
                    <?php
                        $grade = $pg['PerfGrade'];
                        $badgeClass = match(true) {
                            $grade === 'A'  => 'badge-success',
                            str_starts_with($grade, 'B') => 'badge-primary',
                            default         => 'badge-warning',
                        };
                    ?>
                    <tr>
                        <td><?= $pg['ReviewDate'] ?></td>
                        <td><span class="badge <?= $badgeClass ?>" style="font-size:0.95rem; padding:4px 14px;"><?= htmlspecialchars($grade) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php endif; ?>

<?php include 'footer.php'; ?>