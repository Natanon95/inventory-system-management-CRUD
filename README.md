# 📦 Inventory Management System

A clean, open-source inventory system built with **PHP 8+ / MySQL / Vanilla JS** — no framework required.  
Designed for IT students and PHP freelancers who want a real, production-ready reference project.

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

## ✨ Features

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

## 📁 Folder Structure

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
