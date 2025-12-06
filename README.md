
# Jadwal Azmania

Project Jadwal Azmania adalah aplikasi manajemen jadwal berbasis Laravel.

## Instalasi

### 1. Clone repository

git clone [https://github.com/username/jadwal-azmania.git](https://github.com/username/jadwal-azmania.git)
cd jadwal-azmania


atau download sebagai ZIP lalu ekstrak.


### 2. Install dependencies
```

composer install

```

---

### 3. Setup environment
Copy file `.env.example` menjadi `.env`:
```

cp .env.example .env

```
Kemudian atur konfigurasi database pada file `.env`.

---

### 4. Generate application key
```

php artisan key:generate

```

---

### 5. Migrasi database + seeder
```

php artisan migrate --seed

```
Digunakan untuk melakukan migrasi database dan seed data awal user login.

---

### 6. Storage link
```

php artisan storage:link

```

Digunakan agar bisa melakukan upload file import Excel

---

## Login Default
```

Email   : superadmin@azmania_id
Password: password

```

---

## Perintah Tambahan

### Reset password user

Jika lupa password akun bisa menggunakan perintah berikut untuk mereset password

```

php artisan reset-password {username} {password}

```

---
