 CTST — Campus Tech Support Ticket System

> A dual-platform technical support management system for IIE Rosebank College, Johannesburg.
> Built as part of Work Integrated Learning 3B (XISD6329/w) — Task 1.

  Project Overview

The Campus Tech Support Ticket System (CTST) solves a real problem at IIE Rosebank College — students and lecturers have no formal way to report technical issues on campus. Issues are currently reported informally via email or in person, with no tracking, no accountability, and no visibility for IT staff.

CTST provides:
- A web dashboard for IT support staff to manage, assign, and resolve tickets
- An Android mobile app for students and lecturers to log and track their tickets

 Links

| Item | Link |
|  Live website | [ paste your InfinityFree URL here ] |
|  Figma prototype | [ paste your Figma share link here ] |
|  GitHub repository | [ paste your GitHub repo URL here ] |

Lecturer: Rolivhuwa Thabo
Module: XISD6329/w — Work Integrated Learning 3B
Institution: IIE Rosebank College, Johannesburg

  Tools and Technologies
 Website
- PHP 8 — server-side scripting
- MySQL — database
- HTML5 / CSS3 — structure and styling
- JavaScript — theme switcher, chart animations
- jsPDF — PDF export on the reports page
- InfinityFree — free PHP and MySQL hosting

Mobile App
- Kotlin — Android programming language
- Android Studio — IDE
- SQLiteOpenHelper — local database
- Retrofit — HTTP requests to the PHP API
- Material Components — UI library

 Development Tools
- Visual Studio Code — code editor
- XAMPP — local PHP and MySQL server
- phpMyAdmin — database management
- Figma — sitemaps and wireframes
- GitHub — version control

  Folder Structure
CTST/
├── index.php                IT staff login page
├── dashboard.php            Main dashboard with ticket counts
├── tickets.php              All tickets with filter controls
├── ticket_detail.php        Ticket detail, status update, comments
├── reports.php              Reports page with charts and PDF export
├── manage_staff.php         Staff account management (admin only)
├── logout.php               Session destroy and redirect
├── nav.php                  Shared navigation bar and sidebar
├── debug.php                Password hash debugger (delete after use)
├── ctst_db.sql              Full database schema and seed data
├── README.md                This file
│
├── includes/
│   ├── DBConn.php           Database connection configuration
│   ├── auth.php             Session guard for protected pages
│   └── appData.php          Shared ticket counts and helper functions
│
├── api/
│   ├── config.php           API database connection
│   ├── login.php            Mobile app login endpoint
│   ├── register.php         Mobile app register endpoint
│   ├── submit_ticket.php    Mobile app submit ticket endpoint
│   └── my_tickets.php       Mobile app get tickets endpoint
│
└── assets/
    ├── style.css            Global stylesheet
    └── theme.js             Theme switcher (Light, Dark, Midnight, High Contrast)

 How to Run the Website Locally

 Requirements
- XAMPP (includes Apache + MySQL + PHP)
- A browser (Chrome, Edge, Firefox)
 Steps

1. Start XAMPP
Open XAMPP Control Panel and start Apache and MySQL

2. Copy files
Place the `CTST` folder inside:
```
C:/xampp/htdocs/CTST

3. Set up the database
- Open your browser and go to `http://localhost/phpmyadmin`
- Click the SQL tab
- Open `ctst_db.sql`, copy all the content, paste it into the SQL tab
- Click Go

4. Open the website
Go to:

http://localhost/CTST


5. Log in with the test account

Email:    j.mokoena@rosebankcollege.co.za
Password: password123

  How to Access the Live Website

The website is deployed on InfinityFree:


http://ctst-rosebank.infinityfreeapp.com/index.php

Log in with the same test credentials above.

  Mobile App Setup

The Android app is built in Android Studio using Kotlin.

1. Open Android Studio
2. Open the `CTST_App` folder as a project
3. Wait for Gradle to sync
4. Update `BASE_URL` in `ApiService.kt` to point to your live InfinityFree URL
5. Run on an Android emulator or physical device (Android 7.0+)

 Database

The database is MySQL with 5 tables:

| Table | Purpose |
|---|---|
| `tblStaff` | IT support staff accounts |
| `tblUsers` | Student and lecturer accounts |
| `tblTickets` | All support tickets |
| `tblComments` | Staff comments and activity on tickets |
| `tblNotifications` | Notifications sent to users |

The full schema and seed data is in `ctst_db.sql`.

Default staff password for all seed accounts: `password123`



 Testing

| Test type | How it was done |
|---|---|
| HTML validation | W3C Validator (validator.w3.org) |
| Navigation testing | Every link clicked and confirmed working |
| Login testing | Correct and incorrect credentials tested |
| Responsive testing | Checked at desktop and tablet widths |
| Database testing | CRUD operations tested via phpMyAdmin and live site |
| API testing | Endpoints tested via browser and Postman |

 Current Prototype Status

Website —Fully built and deployed
- All 6 pages working
- Database connected
- Login, ticket management, reports, and staff management functional
- Theme switcher working (Light, Dark, Midnight, High Contrast)
- PDF export working on reports page

Mobile App — In progress (Task 2)
- Login, Register, Dashboard, Log Ticket activities built
- SQLite local database implemented
- Retrofit API connection set up
- Remaining: Notifications, Profile, full Firebase integration



 Known Limitations

- The mobile app is not yet connected to the live API (Task 2)
- Push notifications require Firebase Cloud Messaging (Task 2)
- The website is hosted on InfinityFree free tier — may have occasional downtime
- `debug.php` should be deleted before final deployment

 References

- Google Fonts — Inter and JetBrains Mono typefaces
- jsPDF (2025) — client-side PDF generation
- Retrofit by Square (2025) — Android HTTP client
- Material Components for Android — UI library
- XAMPP by Apache Friends — local development environment
- InfinityFree — free PHP and MySQL hosting
- XISD5319 project documents — updated in this report


CTST — IIE Rosebank College | XISD6329/w Work Integrated Learning 3B | 2026
