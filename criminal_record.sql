-- ============================================================
--   CRIMINAL RECORD MANAGEMENT SYSTEM — Fresh Database
-- ============================================================

DROP DATABASE IF EXISTS criminal_record_db;
CREATE DATABASE criminal_record_db;
USE criminal_record_db;

-- TABLE 1: Criminal
CREATE TABLE Criminal (
    criminal_id   INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50) NOT NULL,
    last_name     VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender        VARCHAR(10),
    nationality   VARCHAR(50),
    address       TEXT,
    phone         VARCHAR(20),
    status        VARCHAR(20) DEFAULT 'Wanted'
);

-- TABLE 2: Crime
CREATE TABLE Crime (
    crime_id      INT AUTO_INCREMENT PRIMARY KEY,
    crime_type    VARCHAR(100) NOT NULL,
    description   TEXT,
    date_occurred DATE,
    location      VARCHAR(200),
    severity      VARCHAR(20)
);

-- TABLE 3: Criminal_Crime  [M:N Junction]
CREATE TABLE Criminal_Crime (
    criminal_id  INT,
    crime_id     INT,
    role         VARCHAR(50),
    arrest_date  DATE,
    PRIMARY KEY (criminal_id, crime_id),
    FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE,
    FOREIGN KEY (crime_id)    REFERENCES Crime(crime_id)       ON DELETE CASCADE
);

-- TABLE 4: Officer
CREATE TABLE Officer (
    officer_id   INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    badge_number VARCHAR(20) UNIQUE,
    rank         VARCHAR(50),
    department   VARCHAR(100),
    phone        VARCHAR(20)
);

-- TABLE 5: Case_Record
CREATE TABLE Case_Record (
    case_id      INT AUTO_INCREMENT PRIMARY KEY,
    crime_id     INT,
    case_status  VARCHAR(50) DEFAULT 'Open',
    open_date    DATE,
    close_date   DATE,
    description  TEXT,
    FOREIGN KEY (crime_id) REFERENCES Crime(crime_id) ON DELETE CASCADE
);

-- TABLE 6: Officer_Case  [M:N Junction]
CREATE TABLE Officer_Case (
    officer_id    INT,
    case_id       INT,
    assigned_date DATE,
    role          VARCHAR(50),
    PRIMARY KEY (officer_id, case_id),
    FOREIGN KEY (officer_id) REFERENCES Officer(officer_id)  ON DELETE CASCADE,
    FOREIGN KEY (case_id)    REFERENCES Case_Record(case_id) ON DELETE CASCADE
);

-- TABLE 7: Victim
CREATE TABLE Victim (
    victim_id      INT AUTO_INCREMENT PRIMARY KEY,
    crime_id       INT,
    first_name     VARCHAR(50),
    last_name      VARCHAR(50),
    age            INT,
    gender         VARCHAR(10),
    contact_number VARCHAR(20),
    statement      TEXT,
    FOREIGN KEY (crime_id) REFERENCES Crime(crime_id) ON DELETE CASCADE
);

-- TABLE 8: Court_Hearing
CREATE TABLE Court_Hearing (
    hearing_id        INT AUTO_INCREMENT PRIMARY KEY,
    case_id           INT,
    hearing_date      DATE,
    judge_name        VARCHAR(100),
    verdict           VARCHAR(50) DEFAULT 'Pending',
    court_name        VARCHAR(100),
    next_hearing_date DATE,
    FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE
);

-- TABLE 9: Prison
CREATE TABLE Prison (
    prison_id   INT AUTO_INCREMENT PRIMARY KEY,
    prison_name VARCHAR(100),
    location    VARCHAR(200),
    capacity    INT
);

-- TABLE 10: Imprisonment
CREATE TABLE Imprisonment (
    imprisonment_id INT AUTO_INCREMENT PRIMARY KEY,
    criminal_id     INT,
    prison_id       INT,
    cell_number     VARCHAR(20),
    start_date      DATE,
    end_date        DATE,
    sentence_years  INT,
    FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE,
    FOREIGN KEY (prison_id)   REFERENCES Prison(prison_id)     ON DELETE CASCADE
);

-- ============================================================
--  SAMPLE DATA
-- ============================================================

INSERT INTO Officer (first_name, last_name, badge_number, rank, department, phone) VALUES
('Kamran',  'Siddiqui', 'B-1001', 'Inspector',     'CID Rawalpindi', '0300-0011001'),
('Nadia',   'Iqbal',    'B-1002', 'Sub-Inspector', 'FIA Islamabad',  '0311-0022002'),
('Zubair',  'Rashid',   'B-1003', 'DSP',           'CTD Lahore',     '0321-0033003'),
('Hina',    'Baig',     'B-1004', 'ASI',           'CID Karachi',    '0333-0044004'),
('Tariq',   'Mehmood',  'B-1005', 'Inspector',     'ANF Peshawar',   '0345-0055005');

INSERT INTO Prison (prison_name, location, capacity) VALUES
('Adiala Jail',      'Rawalpindi', 3000),
('Camp Jail',        'Lahore',     2500),
('Central Prison',   'Karachi',    4000),
('District Jail',    'Peshawar',   1500),
('Kot Lakhpat Jail', 'Lahore',     2000);

INSERT INTO Criminal (first_name, last_name, date_of_birth, gender, nationality, address, phone, status) VALUES
('Ali',    'Hassan', '1990-03-15', 'Male',   'Pakistani', 'House 5, Street 3, Rawalpindi', '0300-1234567', 'Imprisoned'),
('Sara',   'Khan',   '1985-07-22', 'Female', 'Pakistani', 'Flat 12, G-10, Islamabad',      '0311-9876543', 'Released'),
('Bilal',  'Ahmed',  '1995-11-01', 'Male',   'Pakistani', 'Village Gujar Khan, Rawalpindi','0321-4567890', 'Wanted'),
('Usman',  'Tariq',  '1988-05-30', 'Male',   'Pakistani', 'Model Town, Lahore',            '0333-6543210', 'Imprisoned'),
('Fatima', 'Malik',  '1993-09-18', 'Female', 'Pakistani', 'Saddar, Karachi',               '0345-3210987', 'Wanted');

INSERT INTO Crime (crime_type, description, date_occurred, location, severity) VALUES
('Robbery',      'Armed robbery at a bank in Rawalpindi',         '2023-01-10', 'Rawalpindi', 'Major'),
('Murder',       'Premeditated murder in a residential area',     '2023-03-22', 'Lahore',     'Felony'),
('Fraud',        'Online banking fraud targeting multiple people', '2023-06-15', 'Islamabad',  'Major'),
('Drug Dealing', 'Large quantity of drugs seized near border',    '2022-11-05', 'Peshawar',   'Felony'),
('Car Theft',    'Stolen vehicles sold under fake documents',     '2023-08-20', 'Karachi',    'Minor');

INSERT INTO Criminal_Crime VALUES
(1,1,'Main Accused','2023-01-12'),
(3,1,'Accomplice',  '2023-01-14'),
(4,2,'Main Accused','2023-03-25'),
(2,3,'Main Accused','2023-06-18'),
(5,3,'Accomplice',  '2023-06-20'),
(1,4,'Suspect',     '2022-11-10'),
(4,5,'Main Accused','2023-08-22'),
(3,5,'Accomplice',  '2023-08-23');

INSERT INTO Case_Record (crime_id, case_status, open_date, close_date, description) VALUES
(1,'Closed',              '2023-01-15','2023-06-30','Bank robbery case - suspects caught'),
(2,'Under Investigation', '2023-03-23', NULL,       'Murder investigation ongoing'),
(3,'Open',                '2023-06-20', NULL,       'Online fraud case'),
(4,'Closed',              '2022-11-08','2023-02-14','Drug dealing case - convicted'),
(5,'Open',                '2023-08-25', NULL,       'Car theft ring investigation');

INSERT INTO Officer_Case VALUES
(1,1,'2023-01-15','Lead Investigator'),
(2,1,'2023-01-16','Assistant'),
(3,2,'2023-03-24','Lead Investigator'),
(1,3,'2023-06-21','Lead Investigator'),
(2,3,'2023-06-22','Assistant'),
(5,4,'2022-11-09','Lead Investigator'),
(4,5,'2023-08-26','Lead Investigator'),
(3,5,'2023-08-27','Assistant');

INSERT INTO Victim (crime_id, first_name, last_name, age, gender, contact_number, statement) VALUES
(1,'Rashid','Mehmood',45,'Male',  '0300-9991111','Two armed men entered the bank and threatened everyone'),
(2,'Sana',  'Raza',   30,'Female','0311-8882222','Witnessed the attack from the window upstairs'),
(3,'Imran', 'Sheikh', 38,'Male',  '0321-7773333','Received fake investment links via email'),
(3,'Asma',  'Javed',  27,'Female','0333-6664444','Lost savings through fake banking application'),
(5,'Naeem', 'Butt',   50,'Male',  '0345-5555555','Car was stolen from outside my house at night');

INSERT INTO Court_Hearing (case_id, hearing_date, judge_name, verdict, court_name, next_hearing_date) VALUES
(1,'2023-04-10','Justice Anwar Kamal','Guilty',  'Sessions Court Rawalpindi', NULL),
(2,'2023-05-20','Justice Saima Noor', 'Pending', 'High Court Lahore',         '2024-02-15'),
(3,'2023-09-05','Justice Khalid Rauf','Pending', 'Federal Court Islamabad',   '2024-03-10'),
(4,'2023-01-18','Justice Amjad Ali',  'Guilty',  'Sessions Court Peshawar',   NULL),
(5,'2023-11-12','Justice Rubina Shah','Pending', 'Sessions Court Karachi',    '2024-04-20');

INSERT INTO Imprisonment (criminal_id, prison_id, cell_number, start_date, end_date, sentence_years) VALUES
(1,1,'C-101','2023-07-01','2033-07-01',10),
(4,2,'B-205','2023-04-01','2043-04-01',20),
(2,3,'A-310','2020-03-01','2023-03-01', 3),
(1,4,'D-412','2019-01-01','2022-01-01', 3),
(4,5,'E-515','2018-06-01','2021-06-01', 3);
