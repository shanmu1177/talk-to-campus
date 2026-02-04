-- chatbot_db.sql
-- Talk To Campus (rule-based chatbot) schema + sample data
-- Compatible with MySQL 5.7 / PHP 5.6 environment

SET FOREIGN_KEY_CHECKS = 0;

-- DROP existing (safe for repeated imports)
DROP TABLE IF EXISTS frequent_asks;
DROP TABLE IF EXISTS unanswered;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS responses;
DROP TABLE IF EXISTS system_info;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- users (admin accounts)
-- =========================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- stored as MD5 in this sample (legacy)
  `full_name` VARCHAR(200) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_idx` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- sample admin user:
-- username: admin
-- password: admin123  -> md5('admin123') = 0192023a7bbd73250516f069df18b500
INSERT INTO `users` (`username`, `password`, `full_name`) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 'Campus Administrator');

-- =========================
-- responses (actual bot replies; reply may contain HTML)
-- =========================
CREATE TABLE `responses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `reply` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- sample responses
INSERT INTO `responses` (`title`, `reply`) VALUES
('Campus Info - Location & Facilities',
 '<strong>Campus Location & Facilities</strong><br>
 <ul>
  <li>📍 <b>Address:</b> Gobi Arts & Science College, Karattadipalayam(Po),Gobichettipalayam(Tk)-638453,Erode(Dt),Tamilnadu.</li>
  <li>🏛️ <b>Facilities:</b> Library, Labs, Auditorium, Sports Complex</li>
  <li>⏰ <b>Timings:</b> 9:00 AM – 5:00 PM (Mon–Fri)</li>
  <li>🚌 <b>Transport:</b> College buses available from major stops</li>
  <li>🍽️ <b>Cafeteria:</b> Veg & Non-veg options; open 8:30 AM – 4:30 PM</li>
 </ul>'
),
('Academics - Courses & Faculty',
 '<strong>Academics</strong><br>
 <ul>
  <li>📚 <b>Courses:</b> B.Sc., B.Com., B.A., BCA, MBA (check department pages for syllabus)</li>
  <li>👨‍🏫 <b>Faculty:</b> View faculty list on the Academics page or contact the office</li>
  <li>📅 <b>Exam Schedule:</b> Published on noticeboard and website before exams</li>
  <li>📖 <b>Library:</b> Open 9:30 AM – 5:00 PM, digital catalog available</li>
 </ul>'
),
('Events - Upcoming & Announcements',
 '<strong>Events & Announcements</strong><br>
 <ul>
  <li>🎉 Upcoming: Annual College Fest (dates will be announced)</li>
  <li>🏆 Sports: Inter-college sports week every February</li>
  <li>💼 Workshops: Regular guest lectures & workshops </li>
  <li>📢 Recent: Keep an eye on the noticeboard for urgent announcements</li>
 </ul>'
),
('FAQs - Admission & Fees',
 '<strong>Frequently Asked Questions</strong><br>
 <ul>
  <li>❓ <b>Admission:</b> Forms available on the college website during admission period</li>
  <li>💰 <b>Fees:</b> Fee structure varies by course — contact the accounts office</li>
  <li>🎓 <b>Scholarships:</b> Merit & need-based scholarships available; see scholarship cell</li>
  <li>📜 <b>Documents:</b> SSLC/PUC marksheet, ID proof, passport photo, transfer certificate</li>
 </ul>'
);

-- =========================
-- questions (user variants mapped to responses)
-- =========================
CREATE TABLE `questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `response_id` INT UNSIGNED NOT NULL,
  `question` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `response_idx` (`response_id`),
  CONSTRAINT `questions_resp_fk` FOREIGN KEY (`response_id`) REFERENCES `responses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- sample question variants mapping
INSERT INTO `questions` (`response_id`, `question`) VALUES
(1, 'campus location'),
(1, 'where is the campus'),
(1, 'campus facilities'),
(1, 'college timings'),
(1, 'transportation to college'),
(2, 'courses offered'),
(2, 'faculty details'),
(2, 'exam schedule'),
(2, 'library timings'),
(3, 'upcoming events'),
(3, 'college fest'),
(3, 'workshops and seminars'),
(4, 'admission process'),
(4, 'fee structure'),
(4, 'scholarship information'),
(4, 'document requirements');

-- =========================
-- unanswered (queries bot couldn't match)
-- =========================
CREATE TABLE `unanswered` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `query` VARCHAR(1000) NOT NULL,
  `cnt` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `query_idx` (`query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- (no sample rows)

-- =========================
-- frequent_asks (optional ordering / pinned FAQs)
-- =========================
CREATE TABLE `frequent_asks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(500) NOT NULL,
  `response_id` INT UNSIGNED NOT NULL,
  `ordering` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fa_resp_idx` (`response_id`),
  CONSTRAINT `fa_resp_fk` FOREIGN KEY (`response_id`) REFERENCES `responses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- sample frequent asks (optional)
INSERT INTO `frequent_asks` (`question`, `response_id`, `ordering`) VALUES
('How to apply for admission?', 4, 1),
('What courses are available?', 2, 2),
('Where is the campus located?', 1, 3);

-- =========================
-- system_info (site-wide messages and assets)
-- =========================
CREATE TABLE `system_info` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_title` VARCHAR(255) DEFAULT 'Talk To Campus',
  `intro_msg` TEXT DEFAULT NULL,
  `no_result_msg` TEXT DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default system info
INSERT INTO `system_info` (`site_title`, `intro_msg`, `no_result_msg`, `logo_path`) VALUES
('Talk To Campus', 'Hello! I\\'m Talk To Campus Bot. How can I assist you today?', 'I couldn\\'t find an answer for that. 
The admin will check and reply soon.', NULL);

-- =========================
-- Simple view (optional) — list common QA pairs
-- =========================
DROP VIEW IF EXISTS view_questions_responses;
CREATE VIEW view_questions_responses AS
SELECT q.id AS question_id, q.question, r.id AS response_id, r.title, r.reply
FROM questions q JOIN responses r ON r.id = q.response_id;

-- End of file
