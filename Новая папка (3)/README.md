# Akteynt MangaUz — Admin Panel

PHP + MySQL asosidagi manga sayt admin paneli.

## 📁 Fayl strukturasi

```
akteynt-php-admin/
├── index.php                  ← Bosh sahifa (Featured bloki bilan)
├── migration.sql              ← DB o'zgarishlari (bir marta ishlatiladi)
│
├── includes/
│   ├── config.php             ← DB sozlamalari, konstantlar
│   ├── db.php                 ← PDO ulanish (Singleton)
│   ├── auth.php               ← Sessiya, login, CSRF
│   └── functions.php          ← Manga CRUD funksiyalari
│
└── admin/
    ├── login.php              ← Admin kirish sahifasi
    ├── logout.php             ← Chiqish (POST only)
    ├── dashboard.php          ← Manga ro'yxati + tugmalar
    ├── edit.php               ← Yaratish / Tahrirlash formasi
    └── admin_actions.php      ← AJAX endpoint (Delete, Pin/Unpin)
```

## 🚀 O'rnatish

### 1. SQL migratsiyasi
```sql
-- phpMyAdmin yoki terminal orqali:
source migration.sql;
```

### 2. Admin parolini sozlash
Terminal yoki PHP orqali hash yarating:
```bash
php -r "echo password_hash('YourSecurePassword', PASSWORD_BCRYPT, ['cost'=>12]);"
```
Keyin `migration.sql` dagi hash ni yangilang va admins jadvaliga insert qiling.

### 3. `includes/config.php` ni tahrirlang
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'akteynt_manga');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 4. Apache/Nginx serverga yuklang
Loyiha papkasini `htdocs/` yoki `www/` ichiga joylashtiring.

## 🔒 Xavfsizlik xususiyatlari

| Xavf | Himoya |
|------|--------|
| SQL Injection | PDO Prepared Statements |
| XSS | `htmlspecialchars()` barcha chiqishlarda |
| CSRF | Token har formada tekshiriladi |
| Session Fixation | `session_regenerate_id(true)` login da |
| Brute Force | `sleep(1)` noto'g'ri loginlarda |
| Clickjacking | Admin bo'lmaganda tugmalar ko'rinmaydi |

## ⚙️ Admin Panel ishlash tartibi

1. `/admin/login.php` ga kiring
2. Username/parol kiriting
3. Dashboard da manga ro'yxatini ko'ring
4. **✏️ Tahrirlash** → Edit sahifasi
5. **🗑 O'chirish** → JavaScript `confirm()` + AJAX delete
6. **⭐ Pin** → is_featured = 1 (Bosh sahifada yuqorida chiqadi)
7. **Unpin** → is_featured = 0

## 🏠 Bosh sahifa (index.php)

- `is_featured = 1` bo'lgan mangalar **"⭐ Saralangan"** blokida **yuqorida** chiqadi
- Admin kirgan bo'lsa, har bir karta ustiga sichqoncha olib borganida Edit/Delete/Pin tugmalari ko'rinadi
- Admin bo'lmagan foydalanuvchilar bu tugmalarni ko'rmaydi
