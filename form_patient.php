<?php
// ============================================================
// FORM: PATIENT RECORD
// Input: Patient No
// Shows: Patient name, DOB, Doctor No, Doctor Name, Consultant,
//        Medical History (Complaint, Treatment, Doctor,
//        Date Started, Date Ended)
// ============================================================
require_once 'db.php';
$pageTitle = 'Patient Record';

$patientNo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$patient   = null;
$history   = [];
$doctors   = [];
$allPatients = db_query($conn, "SELECT PatientNo, FirstName + ' ' + LastName AS Name FROM Patient ORDER BY PatientNo");

if ($patientNo > 0) {
    // Patient base info with primary doctor and consultant
    $patient = db_query_one($conn,
        "SELECT p.PatientNo,
                p.FirstName + ' ' + p.LastName AS PatientName,
                p.DateOfBirth,
                DATEDIFF(YEAR, p.DateOfBirth, GETDATE()) AS Age,
                p.DateAdmitted,
                p.BedNo,
                p.WardName,
                p.CareUnitID,
                -- Primary doctor
                pd_doc.DoctorStaffNo   AS DoctorNo,
                d.FirstName + ' ' + d.LastName AS DoctorName,
                d.Position       AS DoctorPosition,
                -- Consultant: pick the first consultant linked to any of this patient's doctors
                con_doc.StaffNo  AS ConsultantNo,
                con_d.FirstName + ' ' + con_d.LastName AS ConsultantName,
                s.SpecialtyName  AS ConsultantSpecialty
         FROM   Patient p
         -- Primary doctor
         LEFT JOIN PatientDoctor pd_doc ON pd_doc.PatientNo = p.PatientNo AND pd_doc.Role = 'Primary'
         LEFT JOIN Doctor d             ON d.StaffNo = pd_doc.DoctorStaffNo
         -- Find ONE consultant among ALL treating doctors (TOP 1 prevents row duplication)
         OUTER APPLY (
             SELECT TOP 1 c.StaffNo
             FROM PatientDoctor con_pd
             JOIN Consultant c ON c.StaffNo = con_pd.DoctorStaffNo
             WHERE con_pd.PatientNo = p.PatientNo
             ORDER BY c.StaffNo
         ) AS con_doc
         LEFT JOIN Doctor con_d         ON con_d.StaffNo = con_doc.StaffNo
         LEFT JOIN Specialty s          ON s.SpecialtyID = (
             SELECT TOP 1 SpecialtyID FROM Consultant WHERE StaffNo = con_doc.StaffNo
         )
         WHERE p.PatientNo = ?",
        [$patientNo]
    );

    // Prevent rendering the form if an SQL error occurs
    if (isset($patient['error'])) {
        die("<div class='alert alert-error' style='margin:24px;'><strong>Database Error:</strong> " . htmlspecialchars(print_r($patient['error'], true)) . "</div>");
    }

    // Medical history: complaint + treatment + doctor + dates
    $history = db_query($conn,
        "SELECT pt.RecordID,
                c.ComplaintCode,
                c.Description  AS Complaint,
                t.TreatmentCode,
                t.Description  AS Treatment,
                d.FirstName + ' ' + d.LastName AS Doctor,
                d.Position,
                pt.DateStarted,
                pt.DateEnded
         FROM   PatientTreatment pt
         JOIN   Complaint  c ON c.ComplaintCode  = pt.ComplaintCode
         JOIN   Treatment  t ON t.TreatmentCode  = pt.TreatmentCode
         JOIN   Doctor     d ON d.StaffNo         = pt.DoctorStaffNo
         WHERE  pt.PatientNo = ?
         ORDER  BY pt.DateStarted",
        [$patientNo]
    );

    // All doctors assigned to this patient
    $doctors = db_query($conn,
        "SELECT d.StaffNo, d.FirstName + ' ' + d.LastName AS DoctorName,
                d.Position, pd.Role,
                CASE WHEN con.StaffNo IS NOT NULL THEN 'Yes' ELSE 'No' END AS IsConsultant
         FROM PatientDoctor pd
         JOIN Doctor d ON d.StaffNo = pd.DoctorStaffNo
         LEFT JOIN Consultant con ON con.StaffNo = d.StaffNo
         WHERE pd.PatientNo = ?
         ORDER BY pd.Role DESC",
        [$patientNo]
    );
}

include 'header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Dashboard</a><span>›</span> Patient Record
</div>
<div class="page-header">
    <h1>Patient Record</h1>
    <p class="subtitle">Enter a Patient Number to retrieve their full medical record</p>
</div>

<div class="search-box">
    <h3>🔍 Input Patient No</h3>
    <form method="GET" action="form_patient.php">
        <div class="search-row">
            <div class="form-group">
                <label for="id">Patient Number</label>
                <input type="number" id="id" name="id" min="1"
                       value="<?= $patientNo ?: '' ?>"
                       placeholder="e.g. 1" required>
            </div>
            <div class="form-group">
                <label for="sel_patient">— or Select Patient —</label>
                <select id="sel_patient" onchange="document.getElementById('id').value=this.value;">
                    <option value="">Choose a patient…</option>
                    <?php foreach ($allPatients as $ap): ?>
                    <option value="<?= $ap['PatientNo'] ?>"
                        <?= ($ap['PatientNo'] == $patientNo) ? 'selected' : '' ?>>
                        #<?= $ap['PatientNo'] ?> — <?= htmlspecialchars($ap['Name']) ?>
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
</div>

<?php if ($patientNo > 0 && !$patient): ?>
<div class="alert alert-error">No patient found with Patient No <strong><?= $patientNo ?></strong>.</div>

<?php elseif ($patient): ?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-icon">🏥</span>
        <h2>IVOR PAINE MEMORIAL HOSPITAL — PATIENT RECORD</h2>
        <button onclick="window.print()" class="btn btn-sm btn-gold" style="margin-left:auto;">🖨 Print</button>
    </div>
    <div class="card-body">

        <div class="info-panel" style="margin-bottom:24px;">
            <div class="info-item">
                <div class="lbl">Patient No</div>
                <div class="val"><strong><?= $patient['PatientNo'] ?></strong></div>
            </div>
            <div class="info-item">
                <div class="lbl">Doctor No</div>
                <div class="val"><?= $patient['DoctorNo'] ?? '—' ?></div>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <div class="lbl">Doctor Name</div>
                <div class="val"><strong><?= htmlspecialchars($patient['DoctorName'] ?? '—') ?></strong>
                    <?php if ($patient['DoctorPosition']): ?>
                    <span class="badge badge-primary" style="margin-left:6px;"><?= htmlspecialchars($patient['DoctorPosition']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <div class="lbl">Consultant</div>
                <div class="val">
                    <?php if ($patient['ConsultantName']): ?>
                        <strong><?= htmlspecialchars($patient['ConsultantName']) ?></strong>
                        <span class="badge badge-gold" style="margin-left:6px;"><?= htmlspecialchars($patient['ConsultantSpecialty']) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </div>
            </div>
        </div>

        <div class="info-panel" style="margin-bottom:24px;">
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Patient Name</div>
                <div class="val"><strong><?= htmlspecialchars($patient['PatientName']) ?></strong></div>
            </div>
            <div class="info-item">
                <div class="lbl">Date of Birth</div>
                <div class="val"><?= $patient['DateOfBirth'] ?></div>
            </div>
            <div class="info-item">
                <div class="lbl">Age</div>
                <div class="val"><?= $patient['Age'] ?> years</div>
            </div>
            <div class="info-item">
                <div class="lbl">Date Admitted</div>
                <div class="val"><?= $patient['DateAdmitted'] ?></div>
            </div>
            <div class="info-item">
                <div class="lbl">Bed No</div>
                <div class="val"><?= htmlspecialchars($patient['BedNo']) ?></div>
            </div>
            <div class="info-item">
                <div class="lbl">Ward</div>
                <div class="val"><?= htmlspecialchars($patient['WardName']) ?></div>
            </div>
            <div class="info-item">
                <div class="lbl">Care Unit</div>
                <div class="val"><?= $patient['CareUnitID'] ?></div>
            </div>
        </div>

        <div style="border-top:2px solid var(--border); margin:20px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Medical History</h3>

        <?php if (empty($history)): ?>
        <p class="no-data">No treatment records found for this patient.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Complaint Code</th>
                        <th>Complaint</th>
                        <th>Treatment Code</th>
                        <th>Treatment</th>
                        <th>Doctor</th>
                        <th>Date Started</th>
                        <th>Date Ended</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><span class="badge badge-navy"><?= $h['ComplaintCode'] ?></span></td>
                        <td><?= htmlspecialchars($h['Complaint']) ?></td>
                        <td><span class="badge badge-gold"><?= $h['TreatmentCode'] ?></span></td>
                        <td><?= htmlspecialchars($h['Treatment']) ?></td>
                        <td>
                            <?= htmlspecialchars($h['Doctor']) ?>
                            <br><small style="color:var(--text-light);"><?= $h['Position'] ?></small>
                        </td>
                        <td><?= $h['DateStarted'] ?></td>
                        <td>
                            <?php if ($h['DateEnded']): ?>
                                <?= $h['DateEnded'] ?>
                            <?php else: ?>
                                <span class="badge badge-success">Ongoing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($doctors)): ?>
        <div style="border-top:2px solid var(--border); margin:24px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Assigned Doctors</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Staff No</th>
                        <th>Doctor Name</th>
                        <th>Position</th>
                        <th>Role</th>
                        <th>Consultant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctors as $dr): ?>
                    <tr>
                        <td><?= $dr['StaffNo'] ?></td>
                        <td><strong><?= htmlspecialchars($dr['DoctorName']) ?></strong></td>
                        <td><?= htmlspecialchars($dr['Position']) ?></td>
                        <td>
                            <span class="badge <?= $dr['Role'] === 'Primary' ? 'badge-navy' : 'badge-primary' ?>">
                                <?= $dr['Role'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $dr['IsConsultant'] === 'Yes' ? 'badge-gold' : 'badge-neutral' ?>">
                                <?= $dr['IsConsultant'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div></div><?php endif; ?>

<?php include 'footer.php'; ?>