CREATE DATABASE IF NOT EXISTS dentaflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dentaflow;

CREATE TABLE IF NOT EXISTS patient_statuses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(40) NOT NULL,
  label VARCHAR(80) NOT NULL,
  badge_color VARCHAR(20) NOT NULL DEFAULT 'gray',
  PRIMARY KEY (id), UNIQUE KEY uq_status_code (code)
) ENGINE=InnoDB;

INSERT INTO patient_statuses (id, code, label, badge_color) VALUES
(1,'new_appointment','Nouveau rendez-vous','blue'),
(2,'treatment','En traitement','amber')
ON DUPLICATE KEY UPDATE label=VALUES(label), badge_color=VALUES(badge_color);

CREATE TABLE IF NOT EXISTS patients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  insurance VARCHAR(120) NULL,
  status_id INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_patients_name(full_name), KEY idx_patients_status(status_id),
  CONSTRAINT fk_patients_status FOREIGN KEY(status_id) REFERENCES patient_statuses(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id INT UNSIGNED NOT NULL,
  appointment_date DATETIME NOT NULL,
  reason VARCHAR(255) NULL,
  status ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_appt_date(appointment_date), KEY idx_appt_patient(patient_id),
  CONSTRAINT fk_appt_patient FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teeth_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id INT UNSIGNED NOT NULL,
  tooth_number TINYINT UNSIGNED NOT NULL,
  state ENUM('healthy','needs_intervention','in_progress','treated') NOT NULL DEFAULT 'healthy',
  treatment VARCHAR(255) NULL,
  notes TEXT NULL,
  record_date DATE NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_patient_tooth(patient_id,tooth_number),
  CONSTRAINT fk_teeth_patient FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS patient_notes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY idx_notes_patient(patient_id),
  CONSTRAINT fk_notes_patient FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attachments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(100) NOT NULL,
  file_type VARCHAR(10) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_stored_name(stored_name), KEY idx_att_patient(patient_id),
  CONSTRAINT fk_att_patient FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;
