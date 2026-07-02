<?php
// ============================================================
// FORM & REPORT: WARD RECORD
// Input: Ward Name (List Item / dropdown)
// Shows: Ward Name, Specialty, Day Sister, Night Sister,
//        Staff Nurses, Non-Registered Nurses,
//        Patient table: PatientNo, Name, CareUnit, BedNo,
//                       Consultant, DateAdmitted
// ============================================================
require_once 'db.php';
$pageTitle = 'Ward Record';

$wards = db_query($conn, "SELECT WardName FROM Ward ORDER BY WardName");
$selectedWard = isset($_GET['ward']) ? trim($_GET['ward']) : '';

$ward     = null;
$nurses   = [];
$patients = [];

if ($selectedWard !== '') {
    // Ward base info
    $ward = db_query_one($conn,
        "SELECT w.WardName,
                s.SpecialtyName,
                ds.FirstName + ' ' + ds.LastName AS DaySister,
                ns.FirstName + ' ' + ns.LastName AS NightSister
         FROM   Ward w
         JOIN   Specialty s   ON s.SpecialtyID   = w.SpecialtyID
         LEFT JOIN Nurse ds   ON ds.StaffNo       = w.DaySisterID
         LEFT JOIN Nurse ns   ON ns.StaffNo       = w.NightSisterID
         WHERE  w.WardName = ?",
        [$selectedWard]
    );

    // Prevent rendering the form if an SQL error occurs
    if (isset($ward['error'])) {
        die("<div class='alert alert-error' style='margin:24px;'><strong>Database Error:</strong> " . htmlspecialchars(print_r($ward['error'], true)) . "</div>");
    }

    // All nurses on this ward, grouped by role
    $nurses = db_query($conn,
        "SELECT n.StaffNo, n.FirstName + ' ' + n.LastName AS NurseName,
                n.Role,
                cu.CareUnitID AS CareUnitInCharge
         FROM   Nurse n
         LEFT JOIN CareUnit cu ON cu.InChargeNurseID = n.StaffNo AND cu.WardName = n.WardName
         WHERE  n.WardName = ?
         ORDER  BY n.Role, n.LastName",
        [$selectedWard]
    );

    // Patients in this ward with care unit and consultant info
    $patients = db_query($conn,
        "SELECT p.PatientNo,
                p.FirstName + ' ' + p.LastName AS PatientName,
                p.CareUnitID,
                p.BedNo,
                p.DateAdmitted,
                -- Consultant assigned via Primary Doctor
                con_d.FirstName + ' ' + con_d.LastName AS Consultant,
                spe.SpecialtyName AS ConsultantSpecialty
         FROM   Patient p
         -- ONLY join the primary doctor to prevent duplicate patient rows
         LEFT JOIN PatientDoctor pd ON pd.PatientNo = p.PatientNo AND pd.Role = 'Primary'
         LEFT JOIN Consultant con   ON con.StaffNo = pd.DoctorStaffNo
         LEFT JOIN Doctor con_d     ON con_d.StaffNo = con.StaffNo
         LEFT JOIN Specialty spe    ON spe.SpecialtyID = con.SpecialtyID
         WHERE  p.WardName = ?
         ORDER  BY p.CareUnitID, p.BedNo",
        [$selectedWard]
    );
}

// Group nurses by role
$daySisters  = array_filter($nurses, fn($n) => $n['Role'] === 'Day Sister');
$nightSisters= array_filter($nurses, fn($n) => $n['Role'] === 'Night Sister');
$staffNurses = array_filter($nurses, fn($n) => $n['Role'] === 'Staff Nurse');
$nonReg      = array_filter($nurses, fn($n) => $n['Role'] === 'Non-Registered Nurse');

include 'header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Dashboard</a><span>›</span> Ward Record
</div>
<div class="page-header">
    <h1>Ward Record</h1>
    <p class="subtitle">Select a ward to view all nursing staff and current patient admissions</p>
</div>

<div class="search-box">
    <h3>🏥 Select Ward Name</h3>
    <form method="GET" action="form_ward.php">
        <div class="search-row">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label for="ward">Ward Name</label>
                <select id="ward" name="ward" required>
                    <option value="">— Select a Ward —</option>
                    <?php foreach ($wards as $w): ?>
                    <option value="<?= htmlspecialchars($w['WardName']) ?>"
                        <?= ($w['WardName'] === $selectedWard) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($w['WardName']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">View Ward</button>
            </div>
        </div>
    </form>
</div>

<?php if ($selectedWard && !$ward): ?>
<div class="alert alert-error">Ward "<strong><?= htmlspecialchars($selectedWard) ?></strong>" not found.</div>

<?php elseif ($ward): ?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-icon">🏥</span>
        <h2>IVOR PAINE MEMORIAL HOSPITAL — WARD RECORD</h2>
        <button onclick="window.print()" class="btn btn-sm btn-gold" style="margin-left:auto;">🖨 Print</button>
    </div>
    <div class="card-body">

        <div class="info-panel" style="margin-bottom:24px;">
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Ward Name</div>
                <div class="val"><strong><?= htmlspecialchars($ward['WardName']) ?></strong></div>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Specialty</div>
                <div class="val"><?= htmlspecialchars($ward['SpecialtyName']) ?></div>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Day Sister</div>
                <div class="val"><strong><?= htmlspecialchars($ward['DaySister'] ?? '—') ?></strong></div>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <div class="lbl">Night Sister</div>
                <div class="val"><strong><?= htmlspecialchars($ward['NightSister'] ?? '—') ?></strong></div>
            </div>
        </div>

        <div class="info-panel" style="margin-bottom:24px;">
            <div class="info-item" style="grid-column:span 4;">
                <div class="lbl">Staff Nurses</div>
                <div class="val">
                    <?php if (empty($staffNurses)): ?>
                    <em>None</em>
                    <?php else: ?>
                        <?php foreach ($staffNurses as $n): ?>
                        <span class="badge badge-primary" style="margin:2px;">
                            <?= htmlspecialchars($n['NurseName']) ?>
                            <?php if ($n['CareUnitInCharge']): ?>
                            <em>(CU-<?= $n['CareUnitInCharge'] ?>)</em>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item" style="grid-column:span 4;">
                <div class="lbl">Non-Registered Nurses</div>
                <div class="val">
                    <?php if (empty($nonReg)): ?>
                    <em>None recorded</em>
                    <?php else: ?>
                        <?php foreach ($nonReg as $n): ?>
                        <span class="badge badge-neutral" style="margin:2px;"><?= htmlspecialchars($n['NurseName']) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="border-top:2px solid var(--border); margin:20px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Patient Information</h3>

        <?php if (empty($patients)): ?>
        <p class="no-data">No patients currently admitted to this ward.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Patient No</th>
                        <th>Patient Name</th>
                        <th>Care Unit</th>
                        <th>Bed No</th>
                        <th>Consultant</th>
                        <th>Date Admitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><span class="badge badge-navy"><?= $p['PatientNo'] ?></span></td>
                        <td><strong><?= htmlspecialchars($p['PatientName']) ?></strong></td>
                        <td>CU-<?= $p['CareUnitID'] ?></td>
                        <td><?= htmlspecialchars($p['BedNo']) ?></td>
                        <td>
                            <?php if ($p['Consultant']): ?>
                                <?= htmlspecialchars($p['Consultant']) ?>
                                <br><small style="color:var(--text-light);"><?= htmlspecialchars($p['ConsultantSpecialty'] ?? '') ?></small>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= $p['DateAdmitted'] ?></td>
                        <td><a href="form_patient.php?id=<?= $p['PatientNo'] ?>" class="btn btn-sm btn-outline">Patient Record</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php
        $careUnits = db_query($conn,
            "SELECT cu.CareUnitID,
                    n.FirstName + ' ' + n.LastName AS InChargeNurse,
                    COUNT(p.PatientNo) AS PatientCount
             FROM CareUnit cu
             JOIN Nurse n ON n.StaffNo = cu.InChargeNurseID
             LEFT JOIN Patient p ON p.CareUnitID = cu.CareUnitID
             WHERE cu.WardName = ?
             GROUP BY cu.CareUnitID, n.FirstName, n.LastName
             ORDER BY cu.CareUnitID",
            [$selectedWard]
        );
        if (!empty($careUnits)):
        ?>
        <div style="border-top:2px solid var(--border); margin:24px 0 18px;"></div>
        <h3 style="font-family:'Playfair Display',serif; color:var(--navy); margin-bottom:14px; font-size:1.05rem;">Care Units</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Care Unit ID</th><th>Staff Nurse In Charge</th><th>Patient Count</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($careUnits as $cu): ?>
                    <tr>
                        <td><span class="badge badge-gold">CU-<?= $cu['CareUnitID'] ?></span></td>
                        <td><?= htmlspecialchars($cu['InChargeNurse']) ?></td>
                        <td><?= $cu['PatientCount'] ?></td>
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