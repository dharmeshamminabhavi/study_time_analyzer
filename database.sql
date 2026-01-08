CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_name VARCHAR(100),
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

CREATE TABLE study_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT,
    start_time DATETIME,
    end_time DATETIME,
    duration_minutes INT,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
);
