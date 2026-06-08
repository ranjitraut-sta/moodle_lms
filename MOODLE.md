⚠️ 2. Moodle default मा के हुन्छ?

👉 Important truth:

❌ Moodle does NOT track “re-enroll count” by default in simple form

तर data scattered हुन्छ:


# tyo user tyo course ma kati choti enroll vayo vanera chai matter garxa ki nai ??
USE moodle;

CREATE TABLE mdl_course_attempts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    userid BIGINT NOT NULL,
    courseid BIGINT NOT NULL,
    attempt_no INT NOT NULL DEFAULT 1,
    timestart BIGINT NOT NULL,
    timeend BIGINT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active'
);


# yo chai maile banako cutsom table upgrade garna lai cmd bata chalaune code 
- cd C:\xampp\htdocs\moodle
- php admin\cli\upgrade.php


# chnage gareko kura 

# jaba user le tokeko time vanda badi course complete gareko xaina vani 


# email setup garne 
- host : mail.amdsoft.com.np:587
- smtp user name :test@amdsoft.com.np
- ani pass :Test@99999#


# Random Question Bata Kun Bata Kati Wata Aaune vanne kura
- category choose garisakepaxi pencil jasto bata kati wata dekhaune vanera tyaa setup garna milxa 


# Moodle ma module lai grouping garne concept


