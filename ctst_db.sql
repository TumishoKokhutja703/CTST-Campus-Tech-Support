-- ============================================================
--  CTST — Campus Tech Support Ticket System
--  IIE Rosebank College
--  Database: ctst_db


CREATE DATABASE IF NOT EXISTS ctst_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ctst_db;

-- ── Staff (IT Support) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblStaff (
    staff_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','senior','technician','inactive') NOT NULL DEFAULT 'technician',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Users (Students & Lecturers) ────────────────────────────
CREATE TABLE IF NOT EXISTS tblUsers (
    user_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('Student','Lecturer') DEFAULT 'Student',
    department    VARCHAR(100),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tickets ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblTickets (
    ticket_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_ref    VARCHAR(20)  NOT NULL UNIQUE,
    user_id       INT UNSIGNED NULL,
    assigned_to   INT UNSIGNED NULL,
    category      ENUM('Hardware','Software','Network','Other') NOT NULL,
    device_type   VARCHAR(60)  DEFAULT NULL,
    priority      ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
    status        ENUM('Open','In Progress','Resolved','Closed') NOT NULL DEFAULT 'Open',
    location      VARCHAR(120) DEFAULT NULL,
    description   TEXT NOT NULL,
    resolution    TEXT DEFAULT NULL,
    date_opened   DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_resolved DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id)     REFERENCES tblUsers(user_id)  ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES tblStaff(staff_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Comments / Activity ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblComments (
    comment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    staff_id    INT UNSIGNED NOT NULL,
    comment     TEXT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tblTickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id)  REFERENCES tblStaff(staff_id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Notifications ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tblNotifications (
    notif_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    ticket_id   INT UNSIGNED DEFAULT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES tblUsers(user_id)    ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tblTickets(ticket_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  SEED DATA — Staff accounts
--  Password for ALL accounts: password123
--  Hash generated via: password_hash('password123', PASSWORD_DEFAULT)
-- ============================================================
INSERT INTO tblStaff (full_name, email, password_hash, role) VALUES
('J. Mokoena',  'j.mokoena@rosebankcollege.co.za',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('S. Pillay',   's.pillay@rosebankcollege.co.za',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'senior'),
('T. Nkosi',    't.nkosi@rosebankcollege.co.za',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician'),
('L. van Wyk',  'l.vanwyk@rosebankcollege.co.za',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician');
