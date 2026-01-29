# Panduan Akses Aplikasi di Jaringan Kantor (LAN) 🌐

Aplikasi ini telah dikonfigurasi agar dapat diakses oleh komputer atau laptop lain yang terhubung dalam satu jaringan WiFi atau kabel LAN yang sama.

---

## 🚀 Cara Menjalankan Server
Anda tidak perlu menggunakan terminal. Cukup gunakan file yang telah disediakan:
1. Cari file bernama **`jalankan_server.bat`** di folder utama aplikasi.
2. Klik dua kali pada file tersebut.
3. Jendela hitam akan muncul dan otomatis mendeteksi alamat IP komputer Anda.
4. **Alamat Akses:** Ketik alamat yang tertera (contoh: `http://192.168.1.15:8001`) di browser komputer lain.

---

## 🛠️ Langkah Jika Tidak Bisa Diakses (Troubleshooting)

Jika komputer lain gagal mengakses (Loading terus atau Error), lakukan langkah-langkah berikut secara berurutan:

### 1. Ubah Profil Jaringan ke "Private"
Windows sering memblokir akses jika jaringan diset sebagai "Public".
* Klik ikon WiFi/LAN di pojok kanan bawah.
* Klik **Properties** pada jaringan yang tersambung.
* Pilih **Private** pada bagian Network Profile.
* *Lakukan ini di komputer Server dan komputer Klien.*

### 2. Izinkan PHP melalui Windows Firewall
* Buka Start Menu, ketik: **Allow an app through Windows Firewall**.
* Klik **Change Settings**.
* Cari **"php"** dalam daftar.
* Pastikan kolom **Private** dan **Public** keduanya dicentang (ceklist).
* Klik OK.

### 3. Buka Port 8001 (Langkah Lanjutan)
Jika masih buntu, buka port secara manual:
1. Cari **Windows Defender Firewall with Advanced Security**.
2. Klik **Inbound Rules** (kiri) > **New Rule...** (kanan).
3. Pilih **Port** > Next.
4. Pilih **TCP** dan isi **Specific local ports**: `8001` > Next.
5. Pilih **Allow the connection** > Next.
6. Centang Domain, Private, dan Public > Next.
7. Nama: `Laravel Front Office` > Finish.

### 4. Cek Antivirus
Jika Anda menggunakan antivirus seperti **ESET, Kaspersky, atau Avast**, nonaktifkan fitur Firewall antivirus tersebut sementara untuk memastikan apakah mereka yang memblokir.

---

## 📌 Tips Tambahan
* **IP Statis:** Disarankan meminta bagian IT kantor untuk menetapkan "Static IP" pada komputer server agar alamat akses tidak berubah-ubah setiap kali komputer dinyalakan ulang.
* **Satu Jalur:** Pastikan komputer server dan klien terhubung ke router/SSID WiFi yang sama.

---
*Dibuat otomatis oleh Antigravity untuk Front Office BPS.*
