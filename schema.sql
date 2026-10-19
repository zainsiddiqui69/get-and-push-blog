CREATE DATABASE IF NOT EXISTS getandpush CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE getandpush;

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(190) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  cat VARCHAR(60) NOT NULL,
  author VARCHAR(10) NOT NULL DEFAULT 'a1',
  post_date DATE NOT NULL,
  read_min TINYINT UNSIGNED NOT NULL DEFAULT 5,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  excerpt TEXT,
  body MEDIUMTEXT,
  seo_title VARCHAR(255),
  meta_desc VARCHAR(320),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_status_date (status, post_date)
);
CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO posts (slug,title,cat,author,post_date,read_min,featured,status,excerpt,body,seo_title,meta_desc) VALUES
('the-90-day-plan-every-growing-team-needs','The 90-Day Plan Every Growing Team Needs','Strategy','a1','2026-10-12',7,1,'published','A simple quarterly framework to turn big goals into weekly momentum.','Most strategies fail in the gap between the annual plan and Monday morning. A 90-day plan closes that gap.\n\nStart with one measurable result per quarter, then work backwards into monthly checkpoints.','The 90-Day Plan Every Growing Team Needs | Get & Push','A simple quarterly framework to turn big goals into weekly momentum.'),
('get-push-launches-its-new-partner-programme','Get & Push Launches Its New Partner Programme','Company News','a2','2026-10-09',4,1,'published','Agencies and consultants can now join our partner network.','Today we are opening the Get & Push Partner Programme to agencies, freelancers and consultants.\n\nApplications are open now.','Get & Push Launches Its New Partner Programme','Agencies and consultants can now join our partner network.'),
('brand-storytelling-that-actually-converts','Brand Storytelling That Actually Converts','Marketing','a2','2026-10-05',6,0,'published','Stop listing features. Start telling the story customers repeat.','People remember how a product changed their day. Build every campaign around one customer transformation.','Brand Storytelling That Actually Converts','Stop listing features. Start telling the story customers repeat.');
