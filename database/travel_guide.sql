-- ============================================================
-- База данных: travel_guide
-- Интернет-магазин / каталог туров "Travel Guide"
-- СУБД: MariaDB 10.8 / MySQL 8 (phpMyAdmin, Open Server Panel)
-- Кодировка: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS travel_guide
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE travel_guide;

-- ------------------------------------------------------------
-- Пользователи сайта (администратор / клиент)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  last_name     VARCHAR(50)  NOT NULL,               -- фамилия
  first_name    VARCHAR(50)  NOT NULL,               -- имя
  middle_name   VARCHAR(50)  DEFAULT NULL,           -- отчество
  login         VARCHAR(50)  NOT NULL,               -- логин
  email         VARCHAR(100) NOT NULL,               -- e-mail
  password_hash VARCHAR(255) NOT NULL,               -- пароль (хэш)
  role          ENUM('admin','client') NOT NULL DEFAULT 'client',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_login (login),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Категории туров (страны / типы отдыха)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(100) NOT NULL,
  description TEXT,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Туры (товары каталога)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tours (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id  INT UNSIGNED NOT NULL,
  title        VARCHAR(150) NOT NULL,               -- название тура
  country      VARCHAR(80)  NOT NULL,               -- страна
  resort       VARCHAR(120) DEFAULT NULL,           -- курорт / город
  description  TEXT,                                -- описание
  price        DECIMAL(10,2) NOT NULL DEFAULT 0,    -- цена, руб.
  days         SMALLINT UNSIGNED NOT NULL DEFAULT 7,-- длительность, дней
  rating       DECIMAL(2,1) NOT NULL DEFAULT 4.5,   -- рейтинг 0..5
  image        VARCHAR(255) DEFAULT NULL,           -- файл изображения
  is_popular   TINYINT(1) NOT NULL DEFAULT 0,       -- популярный тур
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tours_category (category_id),
  CONSTRAINT fk_tours_category FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Корзины клиентов
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_carts_user (user_id),
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Позиции корзины
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cart_id    INT UNSIGNED NOT NULL,
  tour_id    INT UNSIGNED NOT NULL,
  quantity   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cart_tour (cart_id, tour_id),
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id)
    REFERENCES carts (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cart_items_tour FOREIGN KEY (tour_id)
    REFERENCES tours (id)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Заказы
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT UNSIGNED NOT NULL,
  total_price  DECIMAL(10,2) NOT NULL DEFAULT 0,
  status       ENUM('new','confirmed','paid','cancelled') NOT NULL DEFAULT 'new',
  comment      TEXT,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_orders_user (user_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Состав заказа
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id     INT UNSIGNED NOT NULL,
  tour_id      INT UNSIGNED NOT NULL,
  quantity     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  price        DECIMAL(10,2) NOT NULL DEFAULT 0,    -- цена на момент покупки
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
    REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_tour FOREIGN KEY (tour_id)
    REFERENCES tours (id)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Начальные данные
-- ============================================================

-- Категории
INSERT INTO categories (name, slug, description) VALUES
  ('Пляжный отдых',      'beach',      'Туры на морские курорты.'),
  ('Экскурсионные туры', 'excursion',  'Поездки по городам и достопримечательностям.'),
  ('Горные туры',        'mountains',  'Походы и отдых в горах.'),
  ('Круизы',             'cruise',     'Морские круизы на лайнерах.'),
  ('Лечебные туры',      'health',     'Санатории и оздоровительные программы.');

-- Туры
INSERT INTO tours (category_id, title, country, resort, description, price, days, rating, image, is_popular) VALUES
  (1, 'Тур в Турцию — Анталия',      'Турция',    'Анталия',    'Отель 5* с собственным пляжем, всё включено. Рядом — старый город Калеичи.', 78900.00, 7, 4.8, 'turkey-antalya.jpg', 1),
  (1, 'Тур в Египет — Хургада',      'Египет',    'Хургада',    'Дайвинг на коралловых рифах, отель с «всё включено».', 54900.00, 7, 3.9, 'egypt-hurghada.jpg', 1),
  (1, 'Тур в Таиланд — Пхукет',      'Таиланд',   'Пхукет',     'Десять дней на острове: пляжи, острова Пхи-Пхи, экскурсии по джунглям и тайский массаж.', 112340.00, 10, 4.7, 'thailand-phuket.jpg', 1),
  (2, 'Экскурсионный тур по Италии', 'Италия',    'Рим — Флоренция', 'Рим, Ватикан, Флоренция, Уффици, Венеция. Гранд-тур по классическому маршруту.', 98000.00, 8, 4.9, 'italy-rome.jpg', 1),
  (2, 'Тур во Францию — Париж',      'Франция',   'Париж',      'Эйфелева башня, Лувр, Версаль, вечерний круиз по Сене. Размещение в 9-м округе.', 87520.00, 7, 4.2, 'france-paris.jpg', 0),
  (2, 'Тур по Чехии — Прага',        'Чехия',     'Прага',      'Пять дней в Праге с выездом в Крумлов.', 46900.00, 5, 4.5, 'czech-prague.jpg', 0),
  (3, 'Горный тур в Швейцарию',      'Швейцария', 'Интерлакен', 'Альпы: Юнгфрау, ледник Алеч, канатные дороги. Пешие маршруты для подготовленных.', 133870.00, 8, 4.9, 'switzerland-alps.jpg', 0),
  (3, 'Тур на Эльбрус',              'Россия',    'Приэльбрусье', 'Восхождение с гидом, ночёвки в приютах, акклиматизационные выходы.', 62000.00, 9, 4.1, 'russia-elbrus.jpg', 0),
  (4, 'Круиз по Средиземному морю',  'Италия',    'Круиз из Чивитавеккьи', 'Лайнер из Рима: Барселона, Марсель, Пальма-де-Мальорка.', 154600.00, 8, 4.6, 'med-cruise.jpg', 1),
  (4, 'Круиз по Балтике',            'Россия',    'Санкт-Петербург', 'Из Петербурга в Таллин, Стокгольм и Хельсинки.', 78000.00, 5, 3.6, 'baltic-cruise.jpg', 0),
  (5, 'Санаторий в Кисловодске',     'Россия',    'Кисловодск', 'Нарзанные ванны, терренкур, санаторий у Курортного бульвара.', 38500.00, 10, 4.4, 'kislovodsk-sanatorium.jpg', 0),
  (5, 'Тур в Карловы Вары',          'Чехия',     'Карловы Вары', 'Минеральные источники и колоннады. Девять дней с лечебными процедурами и экскурсией в Прагу на выходных.', 57180.00, 9, 4.0, 'karlovy-vary.jpg', 0);

-- администратор: admin / admin123
INSERT INTO users (last_name, first_name, middle_name, login, email, password_hash, role) VALUES
  ('Сидоров', 'Иван', 'Петрович', 'admin', 'admin@travel-guide.local',
   '$2y$10$NMPDiiUz2s8EVF8itHqK1.XwbeEBTJn0etnz4mpmFlMvobgoowGU.', 'admin');
