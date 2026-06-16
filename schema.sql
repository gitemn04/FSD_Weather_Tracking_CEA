CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  location VARCHAR(100) NOT NULL,
  date_of_observation DATE NOT NULL,
  time_of_observation TIME NOT NULL,
  bird_species VARCHAR(100) NOT NULL,
  primary_activity VARCHAR(100) NOT NULL,
  duration_minutes INT NOT NULL,
  comments TEXT NOT NULL,
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_comments ON posts(comments);