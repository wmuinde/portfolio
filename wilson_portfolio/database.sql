-- Create database
CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Create projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    technologies VARCHAR(500),
    demo_url VARCHAR(255),
    github_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create about table
CREATE TABLE IF NOT EXISTS about (
    id INT PRIMARY KEY DEFAULT 1,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(200),
    bio TEXT,
    email VARCHAR(100),
    phone VARCHAR(20),
    location VARCHAR(100),
    profile_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    level INT DEFAULT 50,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create contact_messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO about (id, name, title, bio, email, phone, location) VALUES
(1, 'John Doe', 'Full Stack Developer', 'Passionate web developer with 5+ years of experience in creating dynamic and responsive websites. I love working with modern technologies and solving complex problems.', 'john.doe@email.com', '+1 (555) 123-4567', 'New York, USA');

INSERT INTO skills (name, level, category) VALUES
('PHP', 90, 'Backend'),
('MySQL', 85, 'Database'),
('JavaScript', 88, 'Frontend'),
('HTML/CSS', 95, 'Frontend'),
('React', 75, 'Frontend'),
('Node.js', 70, 'Backend'),
('Git', 85, 'Tools'),
('Bootstrap', 90, 'Frontend');

INSERT INTO projects (title, description, technologies, demo_url, github_url) VALUES
('E-commerce Website', 'A full-featured e-commerce platform with user authentication, shopping cart, and payment integration.', 'PHP, MySQL, JavaScript, Bootstrap', '#', '#'),
('Task Management App', 'A collaborative task management application with real-time updates and team collaboration features.', 'React, Node.js, MongoDB', '#', '#'),
('Portfolio Website', 'A responsive portfolio website showcasing my work and skills with an admin panel for content management.', 'PHP, MySQL, HTML/CSS, JavaScript', '#', '#');
