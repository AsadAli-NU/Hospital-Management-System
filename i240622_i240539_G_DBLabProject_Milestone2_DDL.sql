-- ============================================================
-- IVOR PAINE MEMORIAL HOSPITAL
-- Database Lab Project — Milestone 2
-- DDL Script (Schema Creation)
-- ============================================================

-- Create database if it does not exist
IF NOT EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'IvorPaineHospital')
BEGIN
    CREATE DATABASE [IvorPaineHospital];
END
GO

-- Switch to the target database
USE [IvorPaineHospital];
GO

-- ============================================================
-- STEP 0: Drop circular FK constraints BEFORE dropping tables.
-- Ward <-> Nurse form a circular reference.
-- SQL Server cannot drop either table while the other's FK exists.
-- We must explicitly drop these two constraints first.
-- ============================================================
IF OBJECT_ID('dbo.Ward', 'U') IS NOT NULL
BEGIN
    IF EXISTS (
        SELECT 1 FROM sys.foreign_keys
        WHERE name = 'FK_Ward_DaySister' AND parent_object_id = OBJECT_ID('dbo.Ward')
    )
        ALTER TABLE Ward DROP CONSTRAINT FK_Ward_DaySister;

    IF EXISTS (
        SELECT 1 FROM sys.foreign_keys
        WHERE name = 'FK_Ward_NightSister' AND parent_object_id = OBJECT_ID('dbo.Ward')
    )
        ALTER TABLE Ward DROP CONSTRAINT FK_Ward_NightSister;
END;

-- ============================================================
-- STEP 1: Drop all tables in correct reverse-dependency order.
-- Child tables must be dropped before their parent tables.
-- ============================================================
DROP TABLE IF EXISTS PatientTreatment;
DROP TABLE IF EXISTS DoctorExperience;
DROP TABLE IF EXISTS DoctorProgress;
DROP TABLE IF EXISTS PatientDoctor;
DROP TABLE IF EXISTS Patient;
DROP TABLE IF EXISTS CareUnit;
DROP TABLE IF EXISTS Consultant;
DROP TABLE IF EXISTS Doctor;
DROP TABLE IF EXISTS Nurse;
DROP TABLE IF EXISTS Ward;
DROP TABLE IF EXISTS Specialty;
DROP TABLE IF EXISTS Treatment;
DROP TABLE IF EXISTS Complaint;

-- ============================================================
-- TABLE: Specialty
-- Stores medical specialties (e.g., Cardiology, Neurology)
-- ============================================================
CREATE TABLE Specialty (
    SpecialtyID     INT             IDENTITY(1,1),
    SpecialtyName   VARCHAR(100)    NOT NULL UNIQUE,
    Description     VARCHAR(255),
    CONSTRAINT PK_Specialty PRIMARY KEY (SpecialtyID)
);

-- ============================================================
-- TABLE: Ward
-- Represents hospital wards; each ward caters to one specialty.
-- DaySisterID and NightSisterID are set after Nurse is created.
-- ============================================================
CREATE TABLE Ward (
    WardName        VARCHAR(50)     NOT NULL,
    SpecialtyID     INT             NOT NULL,
    DaySisterID     INT             DEFAULT NULL,
    NightSisterID   INT             DEFAULT NULL,
    CONSTRAINT PK_Ward PRIMARY KEY (WardName),
    CONSTRAINT FK_Ward_Specialty FOREIGN KEY (SpecialtyID)
        REFERENCES Specialty(SpecialtyID)
        ON UPDATE CASCADE ON DELETE NO ACTION
);

-- ============================================================
-- TABLE: Nurse
-- Hospital nursing staff; each nurse is allocated to a ward.
-- ============================================================
CREATE TABLE Nurse (
    StaffNo         INT             IDENTITY(1,1),
    FirstName       VARCHAR(50)     NOT NULL,
    LastName        VARCHAR(50)     NOT NULL,
    Role            VARCHAR(50)     NOT NULL,
    WardName        VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_Nurse PRIMARY KEY (StaffNo),
    CONSTRAINT FK_Nurse_Ward FOREIGN KEY (WardName)
        REFERENCES Ward(WardName)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);

-- ============================================================
-- Add Day/Night Sister FKs back to Ward (circular reference
-- resolved via ALTER TABLE after Nurse is created).
-- SQL Server requires NO ACTION on both sides of circular FKs.
-- ============================================================
ALTER TABLE Ward
    ADD CONSTRAINT FK_Ward_DaySister FOREIGN KEY (DaySisterID)
        REFERENCES Nurse(StaffNo)
        ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE Ward
    ADD CONSTRAINT FK_Ward_NightSister FOREIGN KEY (NightSisterID)
        REFERENCES Nurse(StaffNo)
        ON UPDATE NO ACTION ON DELETE NO ACTION;

-- ============================================================
-- TABLE: CareUnit
-- Sub-units within a ward; each has one staff nurse in charge.
-- ============================================================
CREATE TABLE CareUnit (
    CareUnitID          INT             IDENTITY(1,1),
    WardName            VARCHAR(50)     NOT NULL,
    InChargeNurseID     INT             NOT NULL,
    CONSTRAINT PK_CareUnit PRIMARY KEY (CareUnitID),
    CONSTRAINT FK_CareUnit_Ward FOREIGN KEY (WardName)
        REFERENCES Ward(WardName)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FK_CareUnit_Nurse FOREIGN KEY (InChargeNurseID)
        REFERENCES Nurse(StaffNo)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);

-- ============================================================
-- TABLE: Patient
-- Hospital patients; each is admitted to a specific ward and
-- grouped into exactly one care unit.
-- Age is a derived attribute — computed from DateOfBirth.
-- ============================================================
CREATE TABLE Patient (
    PatientNo       INT             IDENTITY(1,1),
    FirstName       VARCHAR(50)     NOT NULL,
    LastName        VARCHAR(50)     NOT NULL,
    DateOfBirth     DATE            NOT NULL,
    DateAdmitted    DATE            NOT NULL,
    BedNo           VARCHAR(10)     NOT NULL,
    WardName        VARCHAR(50)     NOT NULL,
    CareUnitID      INT             NOT NULL,
    CONSTRAINT PK_Patient PRIMARY KEY (PatientNo),
    CONSTRAINT FK_Patient_Ward FOREIGN KEY (WardName)
        REFERENCES Ward(WardName)
        ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT FK_Patient_CareUnit FOREIGN KEY (CareUnitID)
        REFERENCES CareUnit(CareUnitID)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);

-- ============================================================
-- TABLE: Doctor
-- Hospital doctors; forms the base for Consultant (ISA/EER).
-- ============================================================
CREATE TABLE Doctor (
    StaffNo         INT             IDENTITY(1,1),
    FirstName       VARCHAR(50)     NOT NULL,
    LastName        VARCHAR(50)     NOT NULL,
    Position        VARCHAR(50)     NOT NULL,
    DateJoinedTeam  DATE            NOT NULL,
    CONSTRAINT PK_Doctor PRIMARY KEY (StaffNo),
    CONSTRAINT CHK_Doctor_Position CHECK (
        Position IN (
            'Student',
            'Junior Houseman',
            'Senior Houseman',
            'Assistant Registrar',
            'Registrar'
        )
    )
);

-- ============================================================
-- TABLE: Consultant
-- EER specialization of Doctor (ISA, disjoint, partial).
-- ============================================================
CREATE TABLE Consultant (
    StaffNo                 INT         NOT NULL,
    SpecialtyID             INT         NOT NULL,
    ConsultantTeamLeaderID  INT         DEFAULT NULL,
    CONSTRAINT PK_Consultant PRIMARY KEY (StaffNo),
    CONSTRAINT FK_Consultant_Doctor FOREIGN KEY (StaffNo)
        REFERENCES Doctor(StaffNo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FK_Consultant_Specialty FOREIGN KEY (SpecialtyID)
        REFERENCES Specialty(SpecialtyID)
        ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT FK_Consultant_TeamLeader FOREIGN KEY (ConsultantTeamLeaderID)
        REFERENCES Doctor(StaffNo)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);

-- ============================================================
-- TABLE: PatientDoctor
-- Bridge table: M:N between Patient and Doctor.
-- ============================================================
CREATE TABLE PatientDoctor (
    PatientNo       INT             NOT NULL,
    DoctorStaffNo   INT             NOT NULL,
    Role            VARCHAR(20)     NOT NULL DEFAULT 'Treating',
    CONSTRAINT PK_PatientDoctor PRIMARY KEY (PatientNo, DoctorStaffNo),
    CONSTRAINT FK_PD_Patient FOREIGN KEY (PatientNo)
        REFERENCES Patient(PatientNo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FK_PD_Doctor FOREIGN KEY (DoctorStaffNo)
        REFERENCES Doctor(StaffNo)
        ON UPDATE NO ACTION ON DELETE CASCADE,
    CONSTRAINT CHK_PD_Role CHECK (Role IN ('Primary', 'Treating'))
);

-- ============================================================
-- TABLE: Complaint
-- ============================================================
CREATE TABLE Complaint (
    ComplaintCode   VARCHAR(10)     NOT NULL,
    Description     VARCHAR(255)    NOT NULL,
    CONSTRAINT PK_Complaint PRIMARY KEY (ComplaintCode)
);

-- ============================================================
-- TABLE: Treatment
-- ============================================================
CREATE TABLE Treatment (
    TreatmentCode   VARCHAR(10)     NOT NULL,
    Description     VARCHAR(255)    NOT NULL,
    CONSTRAINT PK_Treatment PRIMARY KEY (TreatmentCode)
);

-- ============================================================
-- TABLE: PatientTreatment
-- Ternary relationship: Patient + Complaint + Treatment.
-- ============================================================
CREATE TABLE PatientTreatment (
    RecordID        INT             IDENTITY(1,1),
    PatientNo       INT             NOT NULL,
    ComplaintCode   VARCHAR(10)     NOT NULL,
    TreatmentCode   VARCHAR(10)     NOT NULL,
    DoctorStaffNo   INT             NOT NULL,
    DateStarted     DATE            NOT NULL,
    DateEnded       DATE            DEFAULT NULL,
    CONSTRAINT PK_PatientTreatment PRIMARY KEY (RecordID),
    CONSTRAINT FK_PT_Patient FOREIGN KEY (PatientNo)
        REFERENCES Patient(PatientNo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT FK_PT_Complaint FOREIGN KEY (ComplaintCode)
        REFERENCES Complaint(ComplaintCode)
        ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT FK_PT_Treatment FOREIGN KEY (TreatmentCode)
        REFERENCES Treatment(TreatmentCode)
        ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT FK_PT_Doctor FOREIGN KEY (DoctorStaffNo)
        REFERENCES Doctor(StaffNo)
        ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT CHK_PT_Dates CHECK (DateEnded IS NULL OR DateEnded >= DateStarted)
);

-- ============================================================
-- TABLE: DoctorProgress
-- Weak entity; tracks performance reviews of doctors.
-- ============================================================
CREATE TABLE DoctorProgress (
    ProgressID      INT             IDENTITY(1,1),
    DoctorStaffNo   INT             NOT NULL,
    ReviewDate      DATE            NOT NULL,
    PerfGrade       CHAR(2)         NOT NULL,
    CONSTRAINT PK_DoctorProgress PRIMARY KEY (ProgressID),
    CONSTRAINT FK_DP_Doctor FOREIGN KEY (DoctorStaffNo)
        REFERENCES Doctor(StaffNo)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- ============================================================
-- TABLE: DoctorExperience
-- Weak entity; tracks prior employment/experience of doctors.
-- ============================================================
CREATE TABLE DoctorExperience (
    ExperienceID    INT             IDENTITY(1,1),
    DoctorStaffNo   INT             NOT NULL,
    FromDate        DATE            NOT NULL,
    ToDate          DATE            DEFAULT NULL,
    Position        VARCHAR(100)    NOT NULL,
    Establishment   VARCHAR(150)    NOT NULL,
    CONSTRAINT PK_DoctorExperience PRIMARY KEY (ExperienceID),
    CONSTRAINT FK_DE_Doctor FOREIGN KEY (DoctorStaffNo)
        REFERENCES Doctor(StaffNo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT CHK_DE_Dates CHECK (ToDate IS NULL OR ToDate >= FromDate)
);

-- ============================================================
-- END OF DDL SCRIPT
-- ============================================================
