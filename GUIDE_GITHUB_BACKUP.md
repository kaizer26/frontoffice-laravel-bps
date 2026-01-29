# Panduan Backup Project ke GitHub 🚀

Ikuti langkah-langkah di bawah ini untuk mengupload project **Laravel Front Office** Anda ke GitHub agar aman sebelum melakukan update script.

## 1. Persiapan .gitignore
Pastikan file sensitif dan file sampah tidak ikut terupload. Buka file `.gitignore` dan pastikan baris berikut sudah ada (atau tambahkan jika belum):

```text
/node_modules
/vendor
.env
database/database.sqlite
storage/*.key
```

## 2. Inisialisasi Git Lokal
Buka terminal (Powershell atau CMD) di folder project Anda, lalu jalankan perintah:

```bash
# Inisialisasi git
git init

# Tambahkan semua file
git add .

# Buat commit pertama
git commit -m "Initial commit - Backup sebelum update"
```

## 3. Buat Repository di GitHub
1. Login ke [github.com](https://github.com).
2. Klik tombol **New** (atau ikon **+** di pojok kanan atas > **New repository**).
3. Isi **Repository name**: `frontoffice-laravel-bps` (atau nama lain).
4. Pilih **Public** atau **Private** (disarankan Private jika ada data demo).
5. Klik **Create repository**.
6. Jangan centang "Initialize this repository with a README" karena kita sudah punya file lokal.

## 4. Hubungkan dan Push ke GitHub
Setelah repository dibuat, GitHub akan menampilkan beberapa perintah. Salin perintah pada bagian **"…or push an existing repository from the command line"**:

```bash
# Ganti URL di bawah dengan URL repo Anda
git remote add origin https://github.com/USERNAME/NAMA-REPO.git
git branch -M main
git push -u origin main
```

---

### Tips Penting:
- **Token GitHub**: Jika dimintai password saat push, gunakan **Personal Access Token (PAT)** karena GitHub sudah tidak mendukung password biasa via terminal.
- **Database**: File `database.sqlite` secara default diabaikan agar data pengunjung tidak terupload ke publik. Jika Anda ingin membackup data tersebut juga, hapus baris `database/database.sqlite` dari `.gitignore` (Hanya disarankan jika repo bersifat **Private**).

---
*Dibuat oleh Antigravity untuk membantu proses backup Anda.*
