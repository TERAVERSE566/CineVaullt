-- Database Schema for CineVault
CREATE DATABASE IF NOT EXISTS cinevault_db;
USE cinevault_db;
-- Users Table
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Content Table (Movies and Series)
CREATE TABLE IF NOT EXISTS content (
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(255) NOT NULL,
release_year VARCHAR(10) NOT NULL,
duration VARCHAR(50),
rating VARCHAR(10),
genre VARCHAR(100),
description TEXT,
poster_url VARCHAR(255),
content_type ENUM('movie', 'series') DEFAULT 'movie',
category VARCHAR(50) DEFAULT 'Trending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Watchlist Table
CREATE TABLE IF NOT EXISTS watchlist (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
content_id INT NOT NULL,
added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
UNIQUE KEY unique_watchlist (user_id, content_id)
);
-- Seed Data (Example data to test with)
INSERT INTO content (title, release_year, duration, rating, genre, description, poster_url,
content_type, category) VALUES
('The Shawshank Redemption', '1994', '2h 22m', '9.3', 'Drama', 'Framed for murder, banker
Andy Dufresne begins a new life at Shawshank prison. His integrity and hope earn the
respect of fellow inmates over decades.',
'https://image.tmdb.org/t/p/w300/lyQBXzOQSuE59IsHyhrp0qIiPAz.jpg', 'movie', 'top10'),

('Breaking Bad', '2008', '5 Seasons', '9.5', 'Crime · Drama · Thriller', 'A chemistry teacher
diagnosed with inoperable cancer turns to manufacturing methamphetamine.',
'https://image.tmdb.org/t/p/w300/ggFHVNu6YYI5L9pCfOacjizRGt.jpg', 'series', 'top10'),
('War', '2019', '2h 34m', '7.0', 'Action · Thriller', 'India''s most wanted terrorist must be
stopped.', 'https://image.tmdb.org/t/p/w300/6CoRTJTmijhBLJTUNoVSUNxZMEI.jpg',
'movie', 'action'),
('3 Idiots', '2009', '2h 50m', '8.4', 'Comedy · Drama', 'Two friends search for their long lost
companion while recalling their college days.', 'https://m.media-
amazon.com/images/I/61NSZeiNF3L._AC_UF894,1000_QL80_.jpg', 'movie', 'bollywood'),
('The Matrix', '1999', '2h 16m', '8.7', 'Action · Sci-Fi', 'A computer hacker learns from
mysterious rebels about the true nature of his reality.',
'https://image.tmdb.org/t/p/w342/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg', 'movie',
'hollywood'),
('Stranger Things', '2016', '4 Seasons', '8.7', 'Sci-Fi · Horror · Drama', 'When a young boy
vanishes, a small town uncovers a mystery involving secret experiments.',
'https://image.tmdb.org/t/p/original/uOOtwVbSr4QDjAGIifLDwpb2Pdl.jpg', 'series',
'trending');
