-- ═══════════════════════════════════════════
-- CTST Database Schema
-- IIE Rosebank College — Campus Tech Support
-- Run this in phpMyAdmin or MySQL CLI
-- ═══════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS ctst_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ctst_db;

-- ── Staff accounts ──────────────────────────
CREATE TABLE IF NOT EXISTS tblStaff (
    staff_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,  -- use password_hash() in PHP
    role          ENUM('technician','senior','admin') DEFAULT 'technician',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample staff (password = "password123" hashed with PASSWORD_DEFAULT)
INSERT INTO tblStaff (full_name, email, password_hash, role) VALUES
('J. Mokoena',  'j.mokoena@rosebankcollege.co.za',  '$2y$12$XmQz5O1v6c8Ql3K9pRtNuOjY7sH4wA2bE6dF0gI1kJ3lM5nP7qR9', 'senior'),
('S. Pillay',   's.pillay@rosebankcollege.co.za',   '$2y$12$XmQz5O1v6c8Ql3K9pRtNuOjY7sH4wA2bE6dF0gI1kJ3lM5nP7qR9', 'technician'),
('T. Nkosi',    't.nkosi@rosebankcollege.co.za',    '$2y$12$XmQz5O1v6c8Ql3K9pRtNuOjY7sH4wA2bE6dF0gI1kJ3lM5nP7qR9', 'technician'),
('L. van Wyk',  'l.vanwyk@rosebankcollege.co.za',   '$2y$12$XmQz5O1v6c8Ql3K9pRtNuOjY7sH4wA2bE6dF0gI1kJ3lM5nP7qR9', 'technician');

-- ── Tickets ─────────────────────────────────
CREATE TABLE IF NOT EXISTS tblTickets (
    ticket_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_ref   VARCHAR(20) NOT NULL UNIQUE,    -- e.g. TKT-042
    submitted_by VARCHAR(150) NOT NULL,           -- name / email of reporter
    assigned_to  INT UNSIGNED NULL,
    category     ENUM('Network','Hardware','Software','Other') NOT NULL,
    priority     ENUM('Low','Medium','High') DEFAULT 'Medium',
    status       ENUM('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
    title        VARCHAR(255) NOT NULL,
    description  TEXT NOT NULL,
    date_opened  DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_closed  DATETIME NULL,
    FOREIGN KEY (assigned_to) REFERENCES tblStaff(staff_id) ON DELETE SET NULL
);

-- Sample tickets
INSERT INTO tblTickets (ticket_ref, submitted_by, assigned_to, category, priority, status, title, description, date_opened) VALUES
('TKT-042', 'Nomsa Bhengu', 1, 'Network',  'High',   'In Progress', 'Wi-Fi drops in Block C lab',            'Students and lecturers in Block C (Room C-07) are experiencing intermittent Wi-Fi disconnections every 15–20 minutes.', '2025-05-19 08:42:00'),
('TKT-041', 'K. Dlamini',   2, 'Hardware', 'Medium', 'Open',        'Projector no HDMI in Room 204',          'Projector does not display via HDMI. VGA works fine.',                                                           '2025-05-19 10:05:00'),
('TKT-040', 'Reception',    3, 'Software', 'High',   'Open',        'Student portal password reset loop',     'Multiple students stuck in password-reset loop after registering in the last 48 hours.',                        '2025-05-18 14:30:00'),
('TKT-039', 'Admin Office', 4, 'Hardware', 'Low',    'Resolved',    'Printer queue stuck on 2nd floor',       'HP LaserJet print queue was blocked, preventing new jobs.',                                                      '2025-05-17 09:10:00');

-- ── Comments / Activity log ──────────────────
CREATE TABLE IF NOT EXISTS tblComments (
    comment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    staff_id    INT UNSIGNED NOT NULL,
    comment     TEXT NOT NULL,
    posted_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tblTickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id)  REFERENCES tblStaff(staff_id)   ON DELETE CASCADE
);

INSERT INTO tblComments (ticket_id, staff_id, comment, posted_at) VALUES
(1, 1, 'Investigated the Cisco AP in C-07. DHCP lease renewal is timing out due to a misconfigured scope added during maintenance. Rolling back now.', '2025-05-19 09:15:00'),
(1, 3, 'Scope rolled back. No drops in last 25 minutes. Monitoring until the 12h00 exam session.',                                                     '2025-05-19 09:51:00'),
(4, 4, 'Print spooler cleared and service restarted. Queue is flowing normally. Marking resolved.',                                                     '2025-05-18 11:20:00');
