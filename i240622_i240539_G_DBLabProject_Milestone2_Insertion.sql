-- ============================================================
-- IVOR PAINE MEMORIAL HOSPITAL
-- Database Lab Project — Milestone 2
-- Initial Data Insertion Script

-- Switch to the target database
USE [IvorPaineHospital];
GO

-- ============================================================
-- NOTE: Tables are populated in dependency order so that all
-- FK references are always satisfied at insert time.
-- SET FOREIGN_KEY_CHECKS is a MySQL-only directive and is not
-- used in SQL Server — correct insertion order replaces it.
-- ============================================================

-- ============================================================
-- 1. SPECIALTY (10 records)
-- ============================================================
INSERT INTO Specialty (SpecialtyName, Description) VALUES
('Cardiology',       'Diagnosis and treatment of heart diseases and disorders'),
('Neurology',        'Disorders of the nervous system, brain, and spinal cord'),
('Orthopedics',      'Musculoskeletal system, bones, joints, ligaments, tendons'),
('Pediatrics',       'Medical care of infants, children, and adolescents'),
('Oncology',         'Diagnosis and treatment of cancer'),
('General Surgery',  'Surgical procedures on abdominal organs and soft tissue'),
('Pulmonology',      'Diseases of the respiratory tract and lungs'),
('Nephrology',       'Kidney diseases and renal replacement therapy'),
('Gastroenterology', 'Diseases of the digestive system and gastrointestinal tract'),
('Dermatology',      'Skin, hair, nail diseases and cosmetic disorders');

-- ============================================================
-- 2. WARD (10 records — DaySisterID / NightSisterID set later)
-- ============================================================
INSERT INTO Ward (WardName, SpecialtyID) VALUES
('Cardiac Ward',     1),
('Neuro Ward',       2),
('Ortho Ward',       3),
('Pediatric Ward',   4),
('Oncology Ward',    5),
('Surgical Ward',    6),
('Pulmo Ward',       7),
('Renal Ward',       8),
('Gastro Ward',      9),
('Derm Ward',        10);

-- ============================================================
-- 3. NURSE (30 records)
--    StaffNo  1-10 : Day Sisters    (one per ward)
--    StaffNo 11-20 : Night Sisters  (one per ward)
--    StaffNo 21-30 : Staff Nurses   (one per ward — IN CHARGE of CareUnit)
--    Case brief: "A staff nurse is in charge of each care-unit."
-- ============================================================
INSERT INTO Nurse (FirstName, LastName, Role, WardName) VALUES
-- Day Sisters (StaffNo 1-10)
('Ayesha',   'Khan',      'Day Sister',    'Cardiac Ward'),
('Hira',     'Ahmed',     'Day Sister',    'Neuro Ward'),
('Nadia',    'Farooq',    'Day Sister',    'Ortho Ward'),
('Amna',     'Baig',      'Day Sister',    'Pediatric Ward'),
('Farah',    'Sheikh',    'Day Sister',    'Oncology Ward'),
('Uzma',     'Chaudhry',  'Day Sister',    'Surgical Ward'),
('Noor',     'Ali',       'Day Sister',    'Pulmo Ward'),
('Asma',     'Siddiqui',  'Day Sister',    'Renal Ward'),
('Tahira',   'Rehman',    'Day Sister',    'Gastro Ward'),
('Kiran',    'Yousaf',    'Day Sister',    'Derm Ward'),
-- Night Sisters (StaffNo 11-20)
('Sana',     'Malik',     'Night Sister',  'Cardiac Ward'),
('Zainab',   'Hussain',   'Night Sister',  'Neuro Ward'),
('Rida',     'Qureshi',   'Night Sister',  'Ortho Ward'),
('Sadia',    'Iqbal',     'Night Sister',  'Pediatric Ward'),
('Maryam',   'Raza',      'Night Sister',  'Oncology Ward'),
('Iram',     'Butt',      'Night Sister',  'Surgical Ward'),
('Saba',     'Nawaz',     'Night Sister',  'Pulmo Ward'),
('Rabia',    'Zahid',     'Night Sister',  'Renal Ward'),
('Bushra',   'Mirza',     'Night Sister',  'Gastro Ward'),
('Lubna',    'Anwar',     'Night Sister',  'Derm Ward'),
-- Staff Nurses (StaffNo 21-30) — assigned as CareUnit in-charge nurses
('Rukhsana', 'Pervez',    'Staff Nurse',   'Cardiac Ward'),
('Samina',   'Tariq',     'Staff Nurse',   'Neuro Ward'),
('Ghazala',  'Ashraf',    'Staff Nurse',   'Ortho Ward'),
('Nasreen',  'Hameed',    'Staff Nurse',   'Pediatric Ward'),
('Shahida',  'Jameel',    'Staff Nurse',   'Oncology Ward'),
('Fouzia',   'Sohail',    'Staff Nurse',   'Surgical Ward'),
('Nageen',   'Riaz',      'Staff Nurse',   'Pulmo Ward'),
('Azra',     'Latif',     'Staff Nurse',   'Renal Ward'),
('Shazia',   'Mehmood',   'Staff Nurse',   'Gastro Ward'),
('Madiha',   'Zafar',     'Staff Nurse',   'Derm Ward');

-- ============================================================
-- 4. UPDATE Ward with Day/Night Sister IDs
--    StaffNo 1-10 = Day Sisters, 11-20 = Night Sisters
-- ============================================================
UPDATE Ward SET DaySisterID = 1,  NightSisterID = 11 WHERE WardName = 'Cardiac Ward';
UPDATE Ward SET DaySisterID = 2,  NightSisterID = 12 WHERE WardName = 'Neuro Ward';
UPDATE Ward SET DaySisterID = 3,  NightSisterID = 13 WHERE WardName = 'Ortho Ward';
UPDATE Ward SET DaySisterID = 4,  NightSisterID = 14 WHERE WardName = 'Pediatric Ward';
UPDATE Ward SET DaySisterID = 5,  NightSisterID = 15 WHERE WardName = 'Oncology Ward';
UPDATE Ward SET DaySisterID = 6,  NightSisterID = 16 WHERE WardName = 'Surgical Ward';
UPDATE Ward SET DaySisterID = 7,  NightSisterID = 17 WHERE WardName = 'Pulmo Ward';
UPDATE Ward SET DaySisterID = 8,  NightSisterID = 18 WHERE WardName = 'Renal Ward';
UPDATE Ward SET DaySisterID = 9,  NightSisterID = 19 WHERE WardName = 'Gastro Ward';
UPDATE Ward SET DaySisterID = 10, NightSisterID = 20 WHERE WardName = 'Derm Ward';

-- ============================================================
-- 5. CARE UNIT (10 records — 1 per ward)
--    InChargeNurseID references Staff Nurses (StaffNo 21-30)
--    CareUnitID will auto-assign as 1-10
-- ============================================================
INSERT INTO CareUnit (WardName, InChargeNurseID) VALUES
('Cardiac Ward',    21),
('Neuro Ward',      22),
('Ortho Ward',      23),
('Pediatric Ward',  24),
('Oncology Ward',   25),
('Surgical Ward',   26),
('Pulmo Ward',      27),
('Renal Ward',      28),
('Gastro Ward',     29),
('Derm Ward',       30);

-- ============================================================
-- 6. DOCTOR (12 records — exceeds minimum of 10)
--    Positions use the five ranks defined in the case brief:
--    Student | Junior Houseman | Senior Houseman |
--    Assistant Registrar | Registrar
-- ============================================================
INSERT INTO Doctor (FirstName, LastName, Position, DateJoinedTeam) VALUES
('Omar',      'Farooq',    'Registrar',           '2015-03-10'),
('Hassan',    'Malik',     'Registrar',           '2012-07-22'),
('Bilal',     'Ahmed',     'Registrar',           '2018-01-15'),
('Tariq',     'Hussain',   'Registrar',           '2010-09-05'),
('Kamran',    'Sheikh',    'Assistant Registrar', '2019-06-30'),
('Zubair',    'Qureshi',   'Senior Houseman',     '2020-11-14'),
('Imran',     'Siddiqui',  'Junior Houseman',     '2021-02-28'),
('Faisal',    'Baig',      'Junior Houseman',     '2017-04-19'),
('Adnan',     'Raza',      'Junior Houseman',     '2022-08-01'),
('Usman',     'Khan',      'Student',             '2023-01-15'),
('Hamid',     'Chaudhry',  'Student',             '2023-03-20'),
('Saad',      'Nawaz',     'Junior Houseman',     '2022-12-01');

-- ============================================================
-- 7. CONSULTANT (6 records)
--    StaffNo 1-4 are senior Registrars acting as Consultants.
--    ConsultantTeamLeaderID = NULL means the doctor is a
--    team leader themselves.
-- ============================================================
INSERT INTO Consultant (StaffNo, SpecialtyID, ConsultantTeamLeaderID) VALUES
(1, 1, NULL),   -- Omar Farooq    — Cardiology,       team leader
(2, 2, NULL),   -- Hassan Malik   — Neurology,        team leader
(3, 3, 1),      -- Bilal Ahmed    — Orthopedics,      led by Omar
(4, 5, 2),      -- Tariq Hussain  — Oncology,         led by Hassan
(5, 6, 1),      -- Kamran Sheikh  — General Surgery,  led by Omar
(6, 7, 2);      -- Zubair Qureshi — Pulmonology,      led by Hassan

-- ============================================================
-- 8. PATIENT (32 records — exceeds minimum of 30)
--    CareUnitID matches the ward the patient is admitted to:
--      Cardiac=1, Neuro=2, Ortho=3, Pediatric=4, Oncology=5,
--      Surgical=6, Pulmo=7, Renal=8, Gastro=9, Derm=10
-- ============================================================
INSERT INTO Patient (FirstName, LastName, DateOfBirth, DateAdmitted, BedNo, WardName, CareUnitID) VALUES
('Ali',       'Hassan',    '1980-05-14', '2025-01-10', 'C-01', 'Cardiac Ward',   1),
('Sara',      'Malik',     '1995-03-22', '2025-01-12', 'C-02', 'Cardiac Ward',   1),
('Ahmed',     'Qureshi',   '1972-08-30', '2025-01-15', 'C-03', 'Cardiac Ward',   1),
('Fatima',    'Siddiqui',  '2000-11-05', '2025-01-18', 'N-01', 'Neuro Ward',     2),
('Zara',      'Ahmed',     '1990-07-19', '2025-01-20', 'N-02', 'Neuro Ward',     2),
('Bilal',     'Farooq',    '1965-12-01', '2025-01-22', 'N-03', 'Neuro Ward',     2),
('Hira',      'Khan',      '2010-04-15', '2025-01-25', 'P-01', 'Pediatric Ward', 4),
('Omar',      'Baig',      '2012-09-08', '2025-01-27', 'P-02', 'Pediatric Ward', 4),
('Amna',      'Raza',      '2008-06-23', '2025-02-01', 'P-03', 'Pediatric Ward', 4),
('Tariq',     'Hussain',   '1955-02-14', '2025-02-03', 'O-01', 'Ortho Ward',     3),
('Nadia',     'Sheikh',    '1978-10-30', '2025-02-05', 'O-02', 'Ortho Ward',     3),
('Kamran',    'Iqbal',     '1988-07-07', '2025-02-07', 'O-03', 'Ortho Ward',     3),
('Sana',      'Butt',      '1960-01-19', '2025-02-10', 'ON-01','Oncology Ward',  5),
('Asim',      'Nawaz',     '1970-04-25', '2025-02-12', 'ON-02','Oncology Ward',  5),
('Rabia',     'Chaudhry',  '1982-09-14', '2025-02-14', 'ON-03','Oncology Ward',  5),
('Imran',     'Yousaf',    '1975-03-03', '2025-02-17', 'S-01', 'Surgical Ward',  6),
('Lubna',     'Anwar',     '1992-08-18', '2025-02-19', 'S-02', 'Surgical Ward',  6),
('Usman',     'Rehman',    '1985-12-12', '2025-02-21', 'S-03', 'Surgical Ward',  6),
('Kiran',     'Mirza',     '1999-05-27', '2025-02-24', 'PL-01','Pulmo Ward',     7),
('Hamid',     'Zahid',     '1967-10-10', '2025-02-26', 'PL-02','Pulmo Ward',     7),
('Bushra',    'Ali',       '1993-02-08', '2025-03-01', 'PL-03','Pulmo Ward',     7),
('Saad',      'Siddiqui',  '1958-06-30', '2025-03-03', 'R-01', 'Renal Ward',     8),
('Maryam',    'Farooq',    '1976-11-22', '2025-03-05', 'R-02', 'Renal Ward',     8),
('Faisal',    'Qadir',     '1984-04-04', '2025-03-07', 'R-03', 'Renal Ward',     8),
('Tahira',    'Noor',      '2001-07-15', '2025-03-10', 'G-01', 'Gastro Ward',    9),
('Adnan',     'Butt',      '1963-09-28', '2025-03-12', 'G-02', 'Gastro Ward',    9),
('Rida',      'Malik',     '1987-01-17', '2025-03-14', 'G-03', 'Gastro Ward',    9),
('Zainab',    'Hussain',   '1997-06-06', '2025-03-17', 'D-01', 'Derm Ward',      10),
('Hassan',    'Sheikh',    '1971-03-21', '2025-03-19', 'D-02', 'Derm Ward',      10),
('Farah',     'Iqbal',     '2003-08-12', '2025-03-21', 'D-03', 'Derm Ward',      10),
('Zahid',     'Raza',      '1969-12-25', '2025-03-24', 'C-04', 'Cardiac Ward',   1),
('Iram',      'Nawaz',     '1994-05-09', '2025-03-26', 'N-04', 'Neuro Ward',     2);

-- ============================================================
-- 9. COMPLAINT (15 records)
-- ============================================================
INSERT INTO Complaint (ComplaintCode, Description) VALUES
('CMP001', 'Chest pain and palpitations'),
('CMP002', 'Chronic headaches and migraines'),
('CMP003', 'Fractured femur — right leg'),
('CMP004', 'High fever and respiratory distress in child'),
('CMP005', 'Stage II lung carcinoma'),
('CMP006', 'Acute appendicitis'),
('CMP007', 'Chronic obstructive pulmonary disease (COPD)'),
('CMP008', 'Chronic kidney disease — Stage 3'),
('CMP009', 'Irritable bowel syndrome (IBS)'),
('CMP010', 'Severe eczema with secondary infection'),
('CMP011', 'Hypertension and arrhythmia'),
('CMP012', 'Epileptic seizures'),
('CMP013', 'Osteoarthritis — knee'),
('CMP014', 'Colon adenocarcinoma'),
('CMP015', 'Acute renal failure');

-- ============================================================
-- 10. TREATMENT (15 records)
-- ============================================================
INSERT INTO Treatment (TreatmentCode, Description) VALUES
('TRT001', 'Electrocardiogram monitoring and beta-blocker therapy'),
('TRT002', 'MRI scan and prophylactic migraine medication'),
('TRT003', 'Surgical fixation with internal rod placement'),
('TRT004', 'Intravenous antibiotics and antipyretics'),
('TRT005', 'Chemotherapy — Cisplatin and Etoposide protocol'),
('TRT006', 'Emergency laparoscopic appendectomy'),
('TRT007', 'Bronchodilator therapy and oxygen supplementation'),
('TRT008', 'Haemodialysis three times weekly'),
('TRT009', 'Dietary management, antispasmodics, probiotics'),
('TRT010', 'Topical corticosteroids and oral antihistamines'),
('TRT011', 'ACE inhibitor and anti-arrhythmic medication'),
('TRT012', 'Anti-epileptic drug (AED) adjustment — Valproate'),
('TRT013', 'Physiotherapy and intra-articular corticosteroid injection'),
('TRT014', 'Surgical resection followed by adjuvant chemotherapy'),
('TRT015', 'IV fluid resuscitation and continuous renal monitoring');

-- ============================================================
-- 11. PATIENT-DOCTOR ASSIGNMENTS (22 records)
-- ============================================================
INSERT INTO PatientDoctor (PatientNo, DoctorStaffNo, Role) VALUES
(1,  1,  'Primary'),
(1,  5,  'Treating'),
(2,  1,  'Primary'),
(3,  1,  'Treating'),
(4,  2,  'Primary'),
(4,  9,  'Treating'),
(5,  2,  'Primary'),
(6,  2,  'Treating'),
(7,  7,  'Primary'),
(8,  7,  'Primary'),
(9,  8,  'Treating'),
(10, 3,  'Primary'),
(11, 3,  'Primary'),
(12, 3,  'Treating'),
(13, 4,  'Primary'),
(13, 6,  'Treating'),
(14, 4,  'Primary'),
(16, 5,  'Primary'),
(19, 6,  'Primary'),
(22, 8,  'Primary'),
(25, 7,  'Primary'),
(28, 8,  'Primary');

-- ============================================================
-- 12. PATIENT TREATMENT — Ternary (20 records)
-- ============================================================
INSERT INTO PatientTreatment (PatientNo, ComplaintCode, TreatmentCode, DoctorStaffNo, DateStarted, DateEnded) VALUES
(1,  'CMP001', 'TRT001', 1, '2025-01-10', '2025-01-25'),
(1,  'CMP011', 'TRT011', 5, '2025-01-12', NULL),
(2,  'CMP001', 'TRT001', 1, '2025-01-12', '2025-01-30'),
(3,  'CMP011', 'TRT011', 1, '2025-01-15', '2025-02-01'),
(4,  'CMP002', 'TRT002', 2, '2025-01-18', '2025-02-05'),
(4,  'CMP012', 'TRT012', 9, '2025-01-20', NULL),
(5,  'CMP002', 'TRT002', 2, '2025-01-20', '2025-02-10'),
(6,  'CMP012', 'TRT012', 2, '2025-01-22', NULL),
(7,  'CMP004', 'TRT004', 7, '2025-01-25', '2025-02-02'),
(8,  'CMP004', 'TRT004', 7, '2025-01-27', '2025-02-08'),
(10, 'CMP003', 'TRT003', 3, '2025-02-03', '2025-03-10'),
(11, 'CMP013', 'TRT013', 3, '2025-02-05', NULL),
(12, 'CMP013', 'TRT013', 3, '2025-02-07', '2025-03-07'),
(13, 'CMP005', 'TRT005', 4, '2025-02-10', NULL),
(14, 'CMP014', 'TRT014', 4, '2025-02-12', NULL),
(16, 'CMP006', 'TRT006', 5, '2025-02-17', '2025-02-20'),
(19, 'CMP007', 'TRT007', 6, '2025-02-24', NULL),
(22, 'CMP008', 'TRT008', 8, '2025-03-03', NULL),
(25, 'CMP009', 'TRT009', 7, '2025-03-10', '2025-03-28'),
(28, 'CMP010', 'TRT010', 8, '2025-03-17', '2025-04-05');

-- ============================================================
-- 13. DOCTOR PROGRESS (15 records)
-- ============================================================
INSERT INTO DoctorProgress (DoctorStaffNo, ReviewDate, PerfGrade) VALUES
(1,  '2023-06-01', 'A'),
(1,  '2024-06-01', 'A'),
(2,  '2023-06-15', 'A'),
(2,  '2024-06-15', 'B+'),
(3,  '2023-07-01', 'B+'),
(3,  '2024-07-01', 'A'),
(4,  '2023-07-10', 'B'),
(4,  '2024-07-10', 'B+'),
(5,  '2023-08-01', 'B+'),
(5,  '2024-08-01', 'A'),
(6,  '2024-01-10', 'B'),
(7,  '2024-02-15', 'B+'),
(8,  '2023-12-01', 'B'),
(9,  '2024-03-20', 'C'),
(10, '2024-04-10', 'C');

-- ============================================================
-- 14. DOCTOR EXPERIENCE (15 records)
-- ============================================================
INSERT INTO DoctorExperience (DoctorStaffNo, FromDate, ToDate, Position, Establishment) VALUES
(1, '2008-02-01', '2012-08-31', 'Junior Doctor',      'Shaukat Khanum Memorial Hospital'),
(1, '2012-09-01', '2015-03-09', 'Registrar',          'Aga Khan University Hospital'),
(2, '2006-03-01', '2010-07-01', 'Intern',             'Pakistan Institute of Medical Sciences'),
(2, '2010-08-01', '2012-07-21', 'Senior Registrar',   'Services Hospital Lahore'),
(3, '2012-05-01', '2015-12-31', 'Junior Doctor',      'Holy Family Hospital Rawalpindi'),
(3, '2016-01-01', '2018-01-14', 'Registrar',          'Benazir Bhutto Hospital'),
(4, '2004-01-01', '2008-06-30', 'Junior Doctor',      'Mayo Hospital Lahore'),
(4, '2008-07-01', '2010-09-04', 'Senior Registrar',   'Punjab Institute of Cardiology'),
(5, '2014-01-01', '2017-12-31', 'Junior Doctor',      'Rawalpindi General Hospital'),
(5, '2018-01-01', '2019-06-29', 'Registrar',          'Civil Hospital Karachi'),
(6, '2016-03-01', '2019-10-31', 'Junior Doctor',      'Fatima Memorial Hospital'),
(6, '2019-11-01', '2020-11-13', 'Registrar',          'Doctors Hospital Lahore'),
(7, '2018-06-01', '2021-02-27', 'Intern',             'Jinnah Hospital Lahore'),
(8, '2013-04-01', '2017-04-18', 'Junior Doctor',      'Liaquat National Hospital'),
(9, '2020-01-01', '2022-07-31', 'Intern',             'District Headquarters Hospital Rawalpindi');

-- ============================================================
-- END OF INSERTION SCRIPT
-- ============================================================
