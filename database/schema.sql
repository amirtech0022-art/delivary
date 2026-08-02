CREATE DATABASE IF NOT EXISTS amir_technology CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE amir_technology;

CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  image_url TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  embed_url TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  price VARCHAR(100) NOT NULL DEFAULT '',
  features TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visit_date DATE NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_visit_date (visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (title, description) VALUES
('سیستەمی کاشێر و فرۆشتن', 'کاشێر، ڕاپۆرتە ڕۆژانە و کۆنترۆڵی مەخزەن.'),
('سیستەمی ژمێریاری و ERP', 'بەڕێوەبردنی مەخزەن و فاکتۆرەکان لە یەک جۆرە.'),
('گەیاندنی تەرازووی زیرەک', 'پەیوەندیی تەرازو و هەڵبژاردنی داتایەکی ڕەنگی.');

INSERT INTO projects (title, description, category, image_url) VALUES
('POS بۆ کۆشکی خۆر', 'سیستەمی فرۆشتن و کەشێر بۆ کەسایەتییە ناوخۆییەکان.', 'pos', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="80" y="80" width="1040" height="640" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="140" y="160" width="280" height="160" rx="24" fill="%23d9eeff"/%3E%3Crect x="460" y="160" width="600" height="360" rx="24" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Crect x="140" y="360" width="280" height="240" rx="24" fill="%23d9eeff"/%3E%3Ctext x="600" y="405" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eپڕۆژەی POS%3C/text%3E%3Ctext x="600" y="455" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E'),
('ERP بۆ کۆمپانیای کەرەسەیی', 'بەڕێوەبردنی مەخزەن و فاکتۆرەکان لە یەک سیستەم.', 'erp', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="100" y="90" width="1000" height="620" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="150" y="140" width="260" height="180" rx="24" fill="%23d9eeff"/%3E%3Crect x="450" y="140" width="600" height="360" rx="24" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Crect x="150" y="360" width="260" height="220" rx="24" fill="%23d9eeff"/%3E%3Ctext x="600" y="395" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eپڕۆژەی ERP%3C/text%3E%3Ctext x="600" y="445" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای ڕوونکەرەوەی وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E'),
('ماڵپەڕی کەشتیارەکان', 'ماڵپەڕی سادە و ڕەنگاوڕەنگ بۆ کۆمپانیا و بازاڕ.', 'web', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3E%3Crect width="1200" height="800" fill="%23f7fbff"/%3E%3Crect x="140" y="120" width="920" height="560" rx="32" fill="%23ffffff" stroke="%230044ff" stroke-opacity="0.16"/%3E%3Crect x="190" y="170" width="820" height="80" rx="18" fill="%23d9eeff"/%3E%3Crect x="190" y="280" width="250" height="240" rx="18" fill="%23d9eeff"/%3E%3Crect x="470" y="280" width="540" height="240" rx="18" fill="%23f7fbff" stroke="%2300a2ff" stroke-opacity="0.25"/%3E%3Ctext x="600" y="405" text-anchor="middle" fill="%230a1b33" font-size="38" font-family="Arial" font-weight="700"%3Eماڵپەڕ%3C/text%3E%3Ctext x="600" y="455" text-anchor="middle" fill="%235f728f" font-size="24" font-family="Arial"%3Eئەم وێنەیەیە جێگای وێنەی ڕاستەقینە%3C/text%3E%3C/svg%3E');

INSERT INTO videos (title, description, embed_url) VALUES
('پێشانگای POS', 'پیشاندانی سەرەکییەکانی کەشێر و فرۆشتن.', 'https://www.youtube.com/embed/ScMzIvxBSi4?rel=0'),
('پێشانگای ERP', 'چۆنیەتییەکی گەیشتن بە زانیاری و فۆرمی کار.', 'https://www.youtube.com/embed/aqz-KE-bpKQ?rel=0'),
('پێشانگای ئەپ', 'پێشانگای ئەپ و ماڵپەڕی بیزنەس.', 'https://www.youtube.com/embed/2Vv-BfVoq4g?rel=0');
