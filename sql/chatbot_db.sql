SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS view_questions_responses;

DROP TABLE IF EXISTS frequent_asks;
DROP TABLE IF EXISTS unanswered;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS responses;
DROP TABLE IF EXISTS system_info;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;


-- ================= USERS =================
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(200) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY username_idx (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO users (username, password, full_name) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 'Campus Administrator');


-- ================= RESPONSES =================
CREATE TABLE responses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  reply TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO responses (title, reply) VALUES
('Campus Info - Location & Facilities',
 '<strong>Campus Location & Facilities</strong><br>
 <ul>
 <li><b>Address:</b> Gobi Arts & Science College, Gobichettipalayam.</li>
 <li><b>Facilities:</b> Library, Labs, Auditorium, Sports Complex</li>
 <li><b>Timings:</b> 9:00 AM – 5:00 PM (Mon–Fri)</li>
 </ul>');


-- ================= QUESTIONS =================
CREATE TABLE questions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  response_id INT UNSIGNED NOT NULL,
  question VARCHAR(500) NOT NULL,
  PRIMARY KEY (id),
  KEY response_idx (response_id),
  CONSTRAINT questions_fk FOREIGN KEY (response_id)
  REFERENCES responses(id)
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO questions (response_id, question) VALUES
(1, 'campus location'),
(1, 'where is the campus'),
(1, 'college timings');


-- ================= UNANSWERED =================
CREATE TABLE unanswered (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  query VARCHAR(1000) NOT NULL,
  cnt INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY query_idx (query(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ================= FREQUENT ASKS =================
CREATE TABLE frequent_asks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question VARCHAR(500) NOT NULL,
  response_id INT UNSIGNED NOT NULL,
  ordering INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY fa_resp_idx (response_id),
  CONSTRAINT fa_fk FOREIGN KEY (response_id)
  REFERENCES responses(id)
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ================= SYSTEM INFO =================
CREATE TABLE system_info (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  site_title VARCHAR(255) DEFAULT 'Talk To Campus',
  intro_msg TEXT,
  no_result_msg TEXT,
  logo_path VARCHAR(255) DEFAULT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO system_info (site_title, intro_msg, no_result_msg, logo_path)
VALUES (
'Talk To Campus',
'Hello! I am Talk To Campus Bot. How can I assist you today?',
'I could not find an answer for that. The admin will check and reply soon.',
NULL
);


-- ================= VIEW =================
CREATE VIEW view_questions_responses AS
SELECT q.id AS question_id, q.question,
       r.id AS response_id, r.title, r.reply
FROM questions q
JOIN responses r ON r.id = q.response_id;