-- CRMS Phase 2 Schema Migration
USE criminal_record_db;

-- Add case_id to Court_Hearing
ALTER TABLE Court_Hearing ADD COLUMN IF NOT EXISTS case_id INT NULL;
ALTER TABLE Court_Hearing ADD CONSTRAINT fk_hearing_case FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE SET NULL;

-- Junction: case_officers
CREATE TABLE IF NOT EXISTS case_officers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  officer_id INT NOT NULL,
  role VARCHAR(60) DEFAULT 'Investigator',
  assigned_date DATE,
  UNIQUE KEY uq_co(case_id,officer_id),
  FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE,
  FOREIGN KEY (officer_id) REFERENCES Officer(officer_id) ON DELETE CASCADE
);

-- Junction: case_criminals
CREATE TABLE IF NOT EXISTS case_criminals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  criminal_id INT NOT NULL,
  role VARCHAR(60) DEFAULT 'Suspect',
  UNIQUE KEY uq_cc(case_id,criminal_id),
  FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE,
  FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE
);

-- Junction: case_victims
CREATE TABLE IF NOT EXISTS case_victims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  victim_id INT NOT NULL,
  UNIQUE KEY uq_cv(case_id,victim_id),
  FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE,
  FOREIGN KEY (victim_id) REFERENCES Victim(victim_id) ON DELETE CASCADE
);

-- Junction: case_suspects
CREATE TABLE IF NOT EXISTS case_suspects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  suspect_id INT NOT NULL,
  UNIQUE KEY uq_cs(case_id,suspect_id),
  FOREIGN KEY (case_id) REFERENCES Case_Record(case_id) ON DELETE CASCADE,
  FOREIGN KEY (suspect_id) REFERENCES Suspect(suspect_id) ON DELETE CASCADE
);

-- Junction: hearing_criminals
CREATE TABLE IF NOT EXISTS hearing_criminals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hearing_id INT NOT NULL,
  criminal_id INT NOT NULL,
  UNIQUE KEY uq_hc(hearing_id,criminal_id),
  FOREIGN KEY (hearing_id) REFERENCES Court_Hearing(hearing_id) ON DELETE CASCADE,
  FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE
);

-- Junction: hearing_officers
CREATE TABLE IF NOT EXISTS hearing_officers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hearing_id INT NOT NULL,
  officer_id INT NOT NULL,
  UNIQUE KEY uq_ho(hearing_id,officer_id),
  FOREIGN KEY (hearing_id) REFERENCES Court_Hearing(hearing_id) ON DELETE CASCADE,
  FOREIGN KEY (officer_id) REFERENCES Officer(officer_id) ON DELETE CASCADE
);

-- Migrate existing Officer_Case data to case_officers
INSERT IGNORE INTO case_officers(case_id,officer_id,role,assigned_date)
SELECT case_id,officer_id,COALESCE(role,'Investigator'),assigned_date FROM Officer_Case;

-- Migrate existing Criminal_Crime -> case_criminals via Case_Record
INSERT IGNORE INTO case_criminals(case_id,criminal_id,role)
SELECT cr.case_id, cc.criminal_id, COALESCE(cc.role,'Suspect')
FROM Criminal_Crime cc JOIN Case_Record cr ON cc.crime_id=cr.crime_id;

-- Add title field to Case_Record
ALTER TABLE Case_Record ADD COLUMN IF NOT EXISTS title VARCHAR(120) NULL;
ALTER TABLE Case_Record ADD COLUMN IF NOT EXISTS crime_category VARCHAR(60) NULL;
ALTER TABLE Case_Record ADD COLUMN IF NOT EXISTS lead_officer_id INT NULL;
ALTER TABLE Case_Record ADD CONSTRAINT fk_cr_lo FOREIGN KEY (lead_officer_id) REFERENCES Officer(officer_id) ON DELETE SET NULL;
