# 📦 Inventory Management System

A clean, open-source inventory system built with **PHP 8+ / MySQL / Vanilla JS** — no framework required.
Designed for IT students and PHP freelancers who want a real, production-ready reference project.

> ⭐ If this project helped you, please consider giving it a star!

---

## 🖼️ Screenshots

| Dashboard | Products | Stock Movement |
|---|---|---|
| Stat cards + 14-day chart | Search, filter, pagination | Log IN / OUT / Adjustment |

---

## ✨ Features

| Feature | Details |
|---|---|
| **Product CRUD** | SKU, name, description, category, price, stock quantity |
| **Category Management** | Add / edit / delete (with product-count guard) |
| **Stock Movements** | Log every IN / OUT / Adjustment with note & user |
| **Low Stock Alerts** | Sidebar badge + dashboard banner when below threshold |
| **Dashboard + Charts** | 14-day movement line chart, inventory value by category (Chart.js 4) |
| **Role-based Access** | Admin (full CRUD) vs Staff (view + stock movements only) |
| **Search + Filter + Pagination** | On products and stock movement pages |
| **CSV Export** | Products, movements, low-stock report, inventory valuation |
| **User Management** | Create users, reset passwords, activate / deactivate accounts |
| **CSRF Protection** | All POST forms use token verification |
| **Soft Delete** | Products are deactivated, not erased — movement history stays intact |

---

## 🔑 Demo Credentials

| Role | Username | Password | Access |
|---|---|---|---|
| **Admin** | `admin` | `demo1234` | Full access — all menus including Users |
| **Staff** | `staff1` | `demo1234` | View data + record stock movements only |

---

## 🚀 Quick Start (XAMPP)

**Requirements:** XAMPP (Apache + MySQL + PHP 8+)

```bash
# 1. Clone or download into htdocs
git clone https://github.com/Natanon95/inventory-system-management-CRUD.git
# Place the folder at: C:\xampp\htdocs\inventory-system\

# 2. Import the database
# Open phpMyAdmin → Import → select sql/schema.sql → Import
# Then import sql/seed.sql (demo data)

# 3. (Optional) Edit DB credentials
# config/database.php → change DB_USER / DB_PASS if needed

# 4. Open your browser
http://localhost/inventory-system/
```

---

## ⚙️ Configuration

### `config/database.php`

```php
define('DB_HOST', 'localhost');            // MySQL host
define('DB_NAME', 'inventory_db');         // Must match schema.sql
define('DB_USER', 'root');                 // XAMPP default
define('DB_PASS', '');                     // XAMPP default (no password)
define('BASE_URL', 'http://localhost/inventory-system'); // Change when deploying
```

> When deploying to a live server, update `BASE_URL` and all `DB_*` constants accordingly.

---

## 🗄️ Database Schema

```
users
  id, username (UNIQUE), password (bcrypt), full_name, email (UNIQUE),
  role (admin|staff), is_active, created_at

categories
  id, name (UNIQUE), description, created_at

products
  id, sku (UNIQUE), name, description, category_id (FK),
  price (DECIMAL 12,2), stock_qty, low_stock_threshold,
  is_active, created_at, updated_at

stock_movements
  id, product_id (FK), user_id (FK),
  type (in|out|adjustment), quantity, note, created_at
```

### Seed data included

| Data | Count |
|---|---|
| Users | 2 (admin + staff) |
| Categories | 5 (Electronics, Office Supplies, Furniture, Food & Beverage, Clothing) |
| Products | 17 items (mix of normal and low-stock) |
| Stock Movements | 16 records (last 30 days) |

---

## 📁 Project Structure

```
inventory-system/
├── config/
│   └── database.php       → DB constants + BASE_URL + APP_NAME
├── core/
│   ├── Database.php       → PDO singleton
│   ├── Auth.php           → login / logout / check / requireAdmin / isAdmin
│   └── helpers.php        → e(), money(), redirect(), flash(), csrf*, paginate(), timeAgo()
├── modules/
│   ├── dashboard/         → stat cards + Chart.js graphs
│   ├── products/          → index, add, edit, delete
│   ├── categories/        → index (inline modal), save, delete
│   ├── stock/             → movement log + IN/OUT/ADJ form
│   ├── reports/           → summary page + CSV export
│   └── users/             → index, save, toggle active
├── assets/
│   ├── css/app.css        → Custom CSS (~400 lines, no Bootstrap)
│   └── js/app.js          → Vanilla JS (sidebar, modals, search debounce)
├── includes/
│   ├── header.php         → Opens HTML, loads CSS/fonts, shows flash messages
│   ├── sidebar.php        → Navigation + low-stock badge (5-min session cache)
│   ├── footer.php         → Closes HTML, loads app.js
│   └── 403.php            → Access Denied page
├── sql/
│   ├── schema.sql         → CREATE DATABASE + 4 tables
│   └── seed.sql           → Demo users, categories, products, movements
├── bootstrap.php          → session_start + require all core files
├── login.php              → Login page (CSRF protected)
├── logout.php             → Destroys session + redirects
└── index.php              → Redirects to dashboard
```

---

## 🔒 Role Permissions

| Action | Admin | Staff |
|---|---|---|
| View Dashboard / Products / Stock | ✅ | ✅ |
| Record Stock Movement | ✅ | ✅ |
| Add / Edit / Delete Product | ✅ | ❌ |
| Manage Categories | ✅ | ❌ |
| Export CSV | ✅ | ✅ |
| Manage Users | ✅ | ❌ |

---

## 🛡️ Security

- All output escaped via `htmlspecialchars()` through the `e()` helper
- Passwords hashed with `password_hash(PASSWORD_BCRYPT)`
- CSRF token on every POST form
- All queries use PDO Prepared Statements — zero string concatenation in SQL
- Product delete is **soft-delete** (`is_active = 0`) to preserve movement history
- Every admin-only page guarded by `Auth::requireAdmin()`
- Session ID regenerated on every successful login

---

## 📦 CSV Exports

| File | Contents |
|---|---|
| `products_*.csv` | All active products with price and stock quantity |
| `stock_movements_*.csv` | Full movement history (IN / OUT / Adjustment) |
| `low_stock_*.csv` | Products currently below their low-stock threshold |
| `inventory_value_*.csv` | Inventory value ranked highest to lowest |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+ (PDO, sessions, bcrypt) |
| Database | MySQL / MariaDB |
| Frontend | Vanilla JS ES6+, custom CSS |
| Charts | Chart.js 4 (CDN) |
| Icons | Font Awesome 6 (CDN) |
| Font | Inter (Google Fonts CDN) |

No Composer. No npm. No framework. Just drop it in htdocs and run.

---

## 📈 Roadmap

- [ ] Barcode scanner support (QuaggaJS)
- [ ] Email notifications for low stock
- [ ] PDF report generation
- [ ] Multi-warehouse support
- [ ] REST API endpoints

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

1. Fork the repo
2. Create your branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

---

## 📄 License

MIT — free to use, modify, and distribute.

---

---

# 📦 ระบบจัดการสินค้าคงคลัง (ภาษาไทย)

ระบบจัดการสินค้าคงคลังที่เขียนด้วย **PHP 8+ / MySQL / Vanilla JS** — ไม่ต้องใช้ Framework ใดๆ
เหมาะสำหรับนักศึกษา IT และ PHP Freelancer ที่ต้องการโปรเจคตัวอย่างระดับ Production

---

## 🔑 Demo Credentials

> ใช้บัญชีด้านล่างเพื่อทดสอบระบบทันทีโดยไม่ต้องตั้งค่าใดเพิ่มเติม

| Role | Username | Password | สิทธิ์ |
|---|---|---|---|
| **Admin** | `admin` | `demo1234` | เข้าถึงได้ทุกเมนู รวมถึง Users และ ลบ/แก้ไขสินค้า |
| **Staff** | `staff1` | `demo1234` | ดูข้อมูล + บันทึก Stock Movement เท่านั้น |

> **หมายเหตุ:** Password ทั้งหมดถูก hash ด้วย `bcrypt` ใน column `users.password`
> หากต้องการเปลี่ยน password ให้ใช้หน้า **Admin → Users → Reset Password**

---

## ✨ ฟีเจอร์หลัก

| Feature | Details |
|---|---|
| **Product CRUD** | SKU, name, description, category, price, stock qty |
| **Category Management** | Add / edit / delete (with product-count guard) |
| **Stock Movements** | Log every IN / OUT / Adjustment with note & user |
| **Low Stock Alerts** | Sidebar badge + dashboard banner when below threshold |
| **Dashboard + Charts** | 14-day movement chart, inventory value by category (Chart.js) |
| **Role-based Access** | Admin (full CRUD) vs Staff (view + stock movements) |
| **Search + Filter + Pagination** | On products and movements pages |
| **Export CSV** | Products, movements, low-stock report, inventory valuation |
| **User Management** | Create users, reset passwords, activate/deactivate |
| **CSRF Protection** | All POST forms use token verification |
| **Demo Mode** | Pre-seeded data — no setup needed to explore |

---

## 🚀 Quick Start (XAMPP)

```bash
# 1. วางโฟลเดอร์ใน htdocs
C:\xampp\htdocs\inventory-system\

# 2. Import ฐานข้อมูล
# เปิด phpMyAdmin → Import → เลือก sql/schema.sql → Import
# จากนั้น Import sql/seed.sql เพิ่มอีกครั้ง (สำหรับข้อมูลตัวอย่าง)

# 3. (ถ้าจำเป็น) แก้ไข DB credentials
# config/database.php → เปลี่ยน DB_USER / DB_PASS ให้ตรงกับ XAMPP ของคุณ

# 4. เปิดเบราว์เซอร์
http://localhost/inventory-system/
```

---

## ⚙️ Configuration (สิ่งสำคัญที่ควรรู้)

### `config/database.php`

```php
define('DB_HOST', 'localhost');   // host ของ MySQL
define('DB_NAME', 'inventory_db'); // ชื่อ database (ต้องตรงกับ schema.sql)
define('DB_USER', 'root');        // default ของ XAMPP
define('DB_PASS', '');            // default ของ XAMPP ไม่มี password
define('BASE_URL', 'http://localhost/inventory-system'); // แก้ถ้า deploy บน server จริง
```

> ถ้า deploy บน hosting จริง ให้เปลี่ยน `BASE_URL` และ `DB_*` ให้ตรงกับ server นั้น ๆ

---

## 🗄️ Database Schema

```
users
  id, username (UNIQUE), password (bcrypt), full_name, email (UNIQUE),
  role (admin|staff), is_active, created_at

categories
  id, name (UNIQUE), description, created_at

products
  id, sku (UNIQUE), name, description, category_id (FK),
  price (DECIMAL 12,2), stock_qty, low_stock_threshold,
  is_active, created_at, updated_at

stock_movements
  id, product_id (FK), user_id (FK),
  type (in|out|adjustment), quantity, note, created_at
```

### ข้อมูล Seed ที่มาพร้อมระบบ

| ข้อมูล | จำนวน |
|---|---|
| Users | 2 (admin + staff) |
| Categories | 5 (Electronics, Office Supplies, Furniture, Food & Beverage, Clothing) |
| Products | 17 รายการ (มีทั้งสต็อกปกติและ Low Stock) |
| Stock Movements | 16 รายการ (ย้อนหลัง 30 วัน) |

---

## 📁 โครงสร้างโปรเจค

```
inventory-system/
├── config/
│   └── database.php       → DB constants + BASE_URL + APP_NAME
├── core/
│   ├── Database.php       → PDO singleton
│   ├── Auth.php           → login / logout / check / requireAdmin / isAdmin
│   └── helpers.php        → e(), money(), redirect(), flash(), csrf*, paginate(), timeAgo()
├── modules/
│   ├── dashboard/         → index.php (stat cards + Chart.js)
│   ├── products/          → index.php, add.php, edit.php, delete.php
│   ├── categories/        → index.php (inline modal), save.php, delete.php
│   ├── stock/             → index.php (log), add.php (IN/OUT/ADJ form)
│   ├── reports/           → index.php (summary), export.php (CSV)
│   └── users/             → index.php, save.php, toggle.php
├── assets/
│   ├── css/app.css        → Custom CSS ทั้งหมด (~400 บรรทัด, ไม่ใช้ Bootstrap)
│   └── js/app.js          → Vanilla JS (sidebar toggle, modals, search debounce)
├── includes/
│   ├── header.php         → เปิด HTML, โหลด CSS/font, include sidebar, แสดง flash
│   ├── sidebar.php        → nav links + low-stock badge (cache 5 นาที)
│   ├── footer.php         → ปิด HTML, โหลด app.js
│   └── 403.php            → หน้า Access Denied
├── sql/
│   ├── schema.sql         → CREATE DATABASE + 4 tables
│   └── seed.sql           → ข้อมูลตัวอย่างสำหรับ demo
├── bootstrap.php          → session_start + require core ทั้งหมด
├── login.php              → หน้า login (CSRF protected)
├── logout.php             → ทำลาย session + redirect
└── index.php              → redirect → dashboard
```

---

## 🔒 Role Permissions

| Action | Admin | Staff |
|---|---|---|
| ดู Dashboard / Products / Stock | ✅ | ✅ |
| บันทึก Stock Movement | ✅ | ✅ |
| เพิ่ม / แก้ไข / ลบ Product | ✅ | ❌ |
| จัดการ Category | ✅ | ❌ |
| Export CSV | ✅ | ✅ |
| จัดการ Users | ✅ | ❌ |

---

## 🛡️ Security Notes

- Output ทุกจุดผ่าน `htmlspecialchars()` (ฟังก์ชัน `e()`)
- Password ใช้ `password_hash(PASSWORD_BCRYPT)`
- ทุก POST form มี CSRF token
- SQL ใช้ PDO Prepared Statements ทั้งหมด — ไม่มี string concatenation ใน query
- ลบ Product เป็น **soft-delete** (`is_active = 0`) เพื่อรักษา movement history
- Role check ทุก admin-only page ด้วย `Auth::requireAdmin()`
- Session regenerate ID ทุกครั้งที่ login สำเร็จ

---

## 📦 CSV Export ที่มีให้

| ไฟล์ | เนื้อหา |
|---|---|
| `products_*.csv` | รายชื่อสินค้าทั้งหมด พร้อมราคาและจำนวนคงเหลือ |
| `stock_movements_*.csv` | ประวัติการรับ-จ่ายสินค้าทั้งหมด |
| `low_stock_*.csv` | เฉพาะสินค้าที่ต่ำกว่า threshold |
| `inventory_value_*.csv` | มูลค่าสินค้าคงคลังเรียงจากมากไปน้อย |

---

## 📈 Roadmap (open for contributions)

- [ ] Barcode scanner support (QuaggaJS)
- [ ] Email notifications for low stock
- [ ] PDF report generation
- [ ] Multi-warehouse support
- [ ] REST API endpoints

---

## 📄 License

MIT — free to use, modify, and distribute.
