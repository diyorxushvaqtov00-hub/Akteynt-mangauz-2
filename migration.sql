-- ============================================================
-- Akteynt-MangaUz: Admin Panel Migration
-- Ishlatish: MySQL terminalida yoki phpMyAdmin orqali bajaring
-- ============================================================

-- 1. Mangas jadvaliga is_featured ustunini qo'shish
ALTER TABLE mangas
  ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = Featured (Saralangan), 0 = Oddiy';

-- 2. Tezlashtirish uchun index
CREATE INDEX IF NOT EXISTS idx_is_featured ON mangas (is_featured);

-- 3. Admins jadvali (agar mavjud bo'lmasa)
CREATE TABLE IF NOT EXISTS admins (
  id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  username   VARCHAR(60)     NOT NULL UNIQUE,
  password   VARCHAR(255)    NOT NULL COMMENT 'password_hash() bilan saqlanadi',
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Demo admin foydalanuvchi (parol: admin123)
-- MUHIM: Ishlab chiqishga o'tishdan oldin parolni o'zgartiring!
INSERT IGNORE INTO admins (username, password)
VALUES (
  'admin',
  '$2y$12$YourHashHere'   -- php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
);

-- 5. Mangas jadvalining namunaviy tuzilishi (agar mavjud bo'lmasa)
CREATE TABLE IF NOT EXISTS mangas (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(120)    NOT NULL UNIQUE,
  title       VARCHAR(255)    NOT NULL,
  alt_title   VARCHAR(255)    DEFAULT NULL,
  cover       VARCHAR(500)    DEFAULT NULL,
  synopsis    TEXT            DEFAULT NULL,
  status      ENUM('Ongoing','Completed','Hiatus') NOT NULL DEFAULT 'Ongoing',
  author      VARCHAR(150)    DEFAULT NULL,
  artist      VARCHAR(150)    DEFAULT NULL,
  genres      JSON            DEFAULT NULL,
  rating      DECIMAL(3,1)    NOT NULL DEFAULT 0.0,
  views       INT UNSIGNED    NOT NULL DEFAULT 0,
  year        SMALLINT        DEFAULT NULL,
  is_featured TINYINT(1)      NOT NULL DEFAULT 0,
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_is_featured (is_featured),
  INDEX idx_rating (rating DESC),
  INDEX idx_views (views DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
