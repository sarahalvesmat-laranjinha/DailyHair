CREATE DATABASE IF NOT EXISTS daily_hair CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE daily_hair;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firebase_uid VARCHAR(128) NULL UNIQUE,
  hair_type ENUM('Liso','Ondulado','Cacheado','Crespo') NOT NULL DEFAULT 'Liso',
  curl_pattern VARCHAR(20) DEFAULT '',
  porosity ENUM('Baixa','Média','Alta') NOT NULL DEFAULT 'Média',
  thickness ENUM('Fina','Média','Grossa') NOT NULL DEFAULT 'Média',
  needs VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  category VARCHAR(60) NOT NULL,
  type VARCHAR(60) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  image VARCHAR (60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

INSERT INTO products (name,brand,category,description,image) VALUES

-- TRUSS
('Shampoo Ultra Hydration Plus','TRUSS','Hidratação','Shampoo para hidratação e limpeza dos fios.','truss-shampoo2.png'),
('Shampoo Nutri Infusion','TRUSS','Nutrição','Shampoo nutritivo para cabelos ressecados.','truss-shampoo.png'),

-- SALON LINE
('Meu Liso Shampoo','Salon Line','Hidratação','Shampoo para limpeza e cuidado dos cabelos lisos.','meuliso-shampoo.png'),
('Meu Liso Máscara','Salon Line','Hidratação','Máscara para hidratação e maciez dos fios.','meuliso-mascara.png'),

-- TOCTUS
('Perfect Poo','Toctus','Hidratação','Produto para limpeza e cuidado dos cabelos.','toctus.png'),
('Eskove','Toctus','Hidratação','Produto para auxiliar no cuidado e finalização dos fios.','toctusskove.png'),

-- LOLA COSMETICS
('Morte Súbita Máscara','Lola Cosmetics','Hidratação','Máscara de tratamento intenso para cabelos ressecados.','mortesubitalola.png'),
('Meu Cacho Minha Vida Máscara','Lola Cosmetics','Nutrição','Creme para definição e cuidado dos cachos.','meuscaixoslola.png'),

-- JUBA
('Shampoo Juba','Juba','Nutrição','Shampoo para limpeza suave dos cabelos cacheados.','juba-shampoo.png'),
('Condicionador Juba','Juba','Hidratação','Condicionador para maciez e desembaraço dos fios.','juba-condicionador.png'),

-- DOVE
('Shampoo Bond Intense Repair','Dove','Reconstrução','Shampoo para cabelos danificados e enfraquecidos.','bond-repere-dove.png'),
('Condicionador Bond Intense Repair','Dove','Reconstrução','Condicionador para fortalecimento e maciez dos fios.','bond-repair-condicionador.png'),

-- SEDA
('Shampoo Mega Crescimento','Seda','Hidratação','Shampoo para limpeza e cuidado durante a rotina capilar.','seda-shampoo.png'),
('Condicionador Mega Crescimento','Seda','Hidratação','Condicionador para maciez e desembaraço.','seda-condicionador.png'),

-- WELLA
('Shampoo Ultimate Color','Wella','Hidratação','Shampoo para cabelos coloridos e proteção da cor.','shampoo-wella.png'),
('Condicionador Ultimate Color','Wella','Hidratação','Condicionador para cabelos coloridos.','wella-condicionador.png'),

-- SKALA
('Mais Cachos Creme de Tratamento','Skala','Hidratação','Creme para tratamento e definição dos cachos.','skala-mycachos.png'),
('Babosa Creme de Tratamento Máscara','Skala','Hidratação','Tratamento hidratante com foco em maciez dos fios.','skala-babosa.png'),

-- L'ORÉAL PARIS
('Elseve Hidra Hialurônico Shampoo','L’Oréal Paris','Hidratação','Shampoo para hidratação e limpeza dos fios.','loreal-shampoo.png'),
('Elseve Hidra Hialurônico Máscara','L’Oréal Paris','Hidratação','Creme para hidratação e maciez dos cabelos.','loreal-mascara.png'),

-- APICE
('Kit Cachos Máscara','Apice','Nutrição','Kit para cuidado,nutrição e definição dos cabelos cacheados.','apice-kit.png'),
('Kit Onduladas Nutri Waves Máscara','Apice','Nutrição','Kit nutritivo para cabelos ondulados.','apice-kit-onduladas.png'),

-- SIÀGE
('Shampoo Ultra Bond Reset','Siàge','Reconstrução','Shampoo para limpeza e fortalecimento dos fios.','siage-ultra-bond.png'),
('Máscara Ultra Bond Reset Máscara','Siàge','Reconstrução','Máscara para recuperação e fortalecimento dos cabelos.','siage-mascara-ultrabond.png'),

-- OX
('Shampoo OX Reconstrução','OX','Reconstrução','Shampoo para cabelos danificados e enfraquecidos.','ox-shampoo-restaura.png'),
('Condicionador OX Reconstrução','OX','Reconstrução','Condicionador para fortalecimento e maciez.','ox-condicionador-restaura.png'),

-- PANTENE
('Shampoo Hidro-Cauterização','Pantene','Hidratação','Shampoo para hidratação e cuidado dos fios.','pantene-shampoo.png'),
('Condicionador Hidro-Cauterização','Pantene','Hidratação','Condicionador para maciez e hidratação.','pantene-condicionador.png'),

-- TIO NACHO
('Shampoo Engrossador','Tío Nacho','Reconstrução','Shampoo para cuidado e fortalecimento dos cabelos.','tio-nacho-shampoo.png'),
('Shampoo Antiqueda','Tío Nacho','Reconstrução','Shampoo para cuidado do couro cabeludo e dos fios.','tio-nacho-anti-queda.png'),

-- ARVENSIS
('Shampoo Cachos Naturais','Arvensis','Hidratação','Shampoo para limpeza,hidratação e cuidado dos cabelos cacheados.','arvensis-shampoo.png'),
('Condicionador Cachos Naturais','Arvensis','Hidratação','Condicionador para hidratação e desembaraço dos cachos.','arvensis-condicionador.png'),

-- WIDE
('Shampoo Wide','Wide','Hidratação','Shampoo para limpeza e cuidado diário dos cabelos.','juba-shampoo-rosa.png'),
('Condicionador Wide','Wide','Hidratação','Condicionador para maciez e desembaraço dos fios.','juba-condicionador-rosa.png'),

-- MUSK
('Shampoo Musk','Musk','Hidratação','Shampoo para limpeza e cuidado dos cabelos.','musk-shampoo.png'),
('Condicionador Musk','Musk','Hidratação','Condicionador para maciez e desembaraço dos fios.','musk-condicionador.png')

ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Usuário de demonstração. A senha abaixo é: ........
INSERT INTO users (name,email,password,hair_type,curl_pattern,porosity,thickness,needs) VALUES
('-----------','***********.com','$2y$10$3d6lYf6fH1oG9W7x9gk2QeXQv4kHfYw4f0M3YQmVYVYfM4e9Z7a6K','Cacheado','3A','Alta','Média','frizz, ressecamento, definição')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Avaliações iniciais para demonstrar o filtro por perfis.
INSERT INTO reviews (product_id,user_id,rating,comment)
SELECT p.id,u.id,5,'Meu cabelo ficou macio e com bastante definição. Gostei muito para dias de ressecamento.' FROM products p JOIN users u ON u.email='**********.com' WHERE p.name='Máscara Hidratação Intensa' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.product_id=p.id AND r.user_id=u.id);
INSERT INTO reviews (product_id,user_id,rating,comment)
SELECT p.id,u.id,4,'Nutriu bem e ajudou no frizz. Usei pouca quantidade para não pesar.' FROM products p JOIN users u ON u.email='*********.com' WHERE p.name='Máscara Nutrição de Óleos' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.product_id=p.id AND r.user_id=u.id);

