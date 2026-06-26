-- =============================================================
--  Zanzibar Safari VILLA — DEPLOYMENT SCHEMA
-- =============================================================

CREATE TABLE IF NOT EXISTS bookings (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100) NOT NULL,
  email        VARCHAR(100) NOT NULL,
  phone        VARCHAR(20),
  tour_name    VARCHAR(150),
  travel_date  DATE,
  num_people   INT DEFAULT 1,
  message      TEXT,
  status       ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subscribers (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(100) UNIQUE NOT NULL,
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin') DEFAULT 'admin',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(100) UNIQUE NOT NULL,
  phone          VARCHAR(20),
  total_bookings INT DEFAULT 0,
  first_seen     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_booking   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tours (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(150) NOT NULL,
  category     VARCHAR(100),
  price        VARCHAR(50), 
  duration     VARCHAR(50), 
  image_url    VARCHAR(255),
  description  TEXT,
  active       BOOLEAN DEFAULT 1,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Admin (Admin@Zanzibar2026)
INSERT IGNORE INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@zanzibar.com', '$2y$12$CMKZ3vaCwVElA12WqfvA1OE8vgOKxLJ3shs0C7SmjqdXq5D3mka9e', 'admin');

-- INITIAL TOURS
INSERT IGNORE INTO tours (title, category, price, duration, image_url, description) VALUES
('Amani Journey', 'Signature', 'From $45', 'Full Day', 'pictures/ZANZIBAR PICS/Salaam/welcome-so-much-let-me.jpg', 'Experience the heart of Zanzibar culture.'),
('Beach Journey', 'Signature', 'From $55', 'Full Day', 'pictures/ZANZIBAR PICS/Nakupenda beach/nakupenda sandbank.jpeg', 'Crystal clear waters and white sands.'),
('Stone Town', 'Culture', 'From $35', 'Half Day', 'pictures/Things-to-do-in-Stone-Town-Zanzibar.webp', 'Historical tour of the ancient Stone Town.'),
('Spice Journey', 'Culture', 'From $30', 'Half Day', 'pictures/ZANZIBAR PICS/Spice/Spice-tours-in-Zanzibar.jpg', 'Discover the aromas of the spice islands.'),
('Rock Journey', 'Dining', 'On Request', 'Flexible', 'pictures/ZANZIBAR PICS/the rock/the rock.jpg', 'Dine at the world-famous Rock Restaurant.'),
('Safari Journey (Mikumi)', 'Safari', 'From $450', '2 Days', 'pictures/ZANZIBAR PICS/mikumi/2-days-mikumi-safari-zanzibar.jpg', 'Flight safari to Mikumi National Park.'),
('Dolphin Journey (Mnemba)', 'Water', 'From $65', 'Full Day', 'pictures/ZANZIBAR PICS/mnemba/mnemba dolphin tour.jpeg', 'Snorkel at Mnemba Atoll with dolphins.'),
('Blue Journey (Safari Blue)', 'Water', 'From $70', 'Full Day', 'pictures/ZANZIBAR PICS/safari blue/safariblue.jpg', 'The original Zanzibar dhow safari.'),
('Sunset Journey', 'Experience', 'From $40', '3 Hours', 'pictures/ZANZIBAR PICS/cruise/sunset-dhow-cruise6.jpg', 'Romantic sunset dhow cruise.'),
('Jozani Journey', 'Nature', 'From $45', 'Half Day', 'pictures/ZANZIBAR PICS/jozani/jozani forest.jpg', 'Red colobus monkeys and mangrove walk.');
