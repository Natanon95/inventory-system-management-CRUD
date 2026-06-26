USE `inventory_db`;

-- ----------------------------
-- Demo Users
-- password for all: demo1234  (bcrypt)
-- ----------------------------
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('admin',  '$2y$12$AoWlqiBvIEGRFndI8u1tOOoyQQ89eQQjjdzmnurx9ZulRt.Oz89Au', 'Admin User',   'admin@demo.local', 'admin'),
('staff1', '$2y$12$AoWlqiBvIEGRFndI8u1tOOoyQQ89eQQjjdzmnurx9ZulRt.Oz89Au', 'Staff Member', 'staff@demo.local', 'staff');

-- ----------------------------
-- Categories
-- ----------------------------
INSERT INTO `categories` (`name`, `description`) VALUES
('Electronics',  'Electronic devices and accessories'),
('Office Supplies', 'Stationery and office equipment'),
('Furniture',    'Office and home furniture'),
('Food & Beverage', 'Consumable food products'),
('Clothing',     'Apparel and fashion items');

-- ----------------------------
-- Products
-- ----------------------------
INSERT INTO `products` (`sku`, `name`, `description`, `category_id`, `price`, `stock_qty`, `low_stock_threshold`) VALUES
('ELEC-001', 'Wireless Mouse',         'Ergonomic Bluetooth mouse',              1, 499.00,   45, 10),
('ELEC-002', 'Mechanical Keyboard',    'TKL RGB mechanical keyboard',            1, 1290.00,  20, 8),
('ELEC-003', 'USB-C Hub 7-in-1',       'Multi-port USB-C adapter',               1, 750.00,   5,  10),
('ELEC-004', 'Webcam 1080p',           'Full HD USB webcam',                     1, 890.00,   12, 8),
('ELEC-005', 'Laptop Stand',           'Adjustable aluminium laptop stand',      1, 620.00,   30, 10),
('OFF-001',  'A4 Paper Ream',          '80gsm copy paper 500 sheets',           2, 120.00,   200, 50),
('OFF-002',  'Ballpoint Pen Box',      'Blue ink, box of 50',                   2, 85.00,    80,  20),
('OFF-003',  'Sticky Notes Pack',      'Assorted colours, 4 pads',              2, 65.00,    7,   15),
('OFF-004',  'Stapler Heavy Duty',     '100-sheet capacity',                    2, 380.00,   15,  5),
('FURN-001', 'Office Chair',           'Ergonomic mesh chair',                  3, 3500.00,  8,   3),
('FURN-002', 'Standing Desk',          'Height-adjustable sit-stand desk',      3, 8900.00,  4,   2),
('FURN-003', 'Bookshelf 5-Tier',       'Steel frame bookshelf',                 3, 1200.00,  6,   3),
('FOOD-001', 'Instant Coffee 200g',    'Rich roast arabica blend',              4, 220.00,   60,  20),
('FOOD-002', 'Green Tea Box 50pcs',    'Jasmine green tea bags',                4, 150.00,   3,   15),
('CLO-001',  'Company T-Shirt S',      'Cotton company uniform — size S',       5, 350.00,   25,  10),
('CLO-002',  'Company T-Shirt M',      'Cotton company uniform — size M',       5, 350.00,   18,  10),
('CLO-003',  'Company T-Shirt L',      'Cotton company uniform — size L',       5, 350.00,   2,   10);

-- ----------------------------
-- Stock Movements (seed history)
-- ----------------------------
INSERT INTO `stock_movements` (`product_id`, `user_id`, `type`, `quantity`, `note`, `created_at`) VALUES
(1,  1, 'in',         50, 'Initial stock',                     DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2,  1, 'in',         25, 'Initial stock',                     DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3,  1, 'in',         10, 'Initial stock',                     DATE_SUB(NOW(), INTERVAL 30 DAY)),
(4,  1, 'in',         15, 'Initial stock',                     DATE_SUB(NOW(), INTERVAL 28 DAY)),
(5,  1, 'in',         35, 'Initial stock',                     DATE_SUB(NOW(), INTERVAL 28 DAY)),
(1,  2, 'out',         5, 'Sale order #1001',                  DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2,  2, 'out',         3, 'Sale order #1002',                  DATE_SUB(NOW(), INTERVAL 18 DAY)),
(6,  1, 'in',        250, 'Bulk purchase',                     DATE_SUB(NOW(), INTERVAL 15 DAY)),
(6,  2, 'out',        50, 'Department request',                DATE_SUB(NOW(), INTERVAL 10 DAY)),
(10, 1, 'in',         10, 'Furniture restock',                 DATE_SUB(NOW(), INTERVAL 12 DAY)),
(10, 2, 'out',         2, 'New employee setup',                DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3,  1, 'adjustment', -5, 'Damaged goods write-off',           DATE_SUB(NOW(), INTERVAL 7 DAY)),
(13, 1, 'in',         80, 'Monthly pantry restock',            DATE_SUB(NOW(), INTERVAL 3 DAY)),
(13, 2, 'out',        20, 'Staff consumption',                 DATE_SUB(NOW(), INTERVAL 1 DAY)),
(14, 1, 'in',         20, 'Monthly pantry restock',            DATE_SUB(NOW(), INTERVAL 3 DAY)),
(14, 2, 'out',        17, 'Staff consumption',                 DATE_SUB(NOW(), INTERVAL 2 DAY));
