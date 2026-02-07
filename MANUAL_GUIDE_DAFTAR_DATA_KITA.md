# Panduan Manual: Fitur "Daftar Data Kita"

Fitur ini dirancang untuk memudahkan pengelolaan tabel data dinamis dengan antarmuka yang familiar (seperti Excel) dan kemampuan penggabungan data multi-periode secara otomatis.

---

## 1. Membuat Template Tabel (Data Registry)
Langkah pertama sebelum menginput data adalah membuat template struktur tabelnya.

1.  Buka menu **[Daftar Data]** -> **[Tambah Tabel Baru]**.
2.  Isi metadata (Judul, Deskripsi, Satuan, dll).
3.  **Pilih Tipe Periode**: Tentukan apakah data ini bersifat Tahunan, Bulanan, Triwulanan, atau Semesteran.
4.  **Atur Layout**: 
    *   **Vertical**: Data periode baru akan ditambahkan ke bawah (baris baru).
    *   **Horizontal**: Data periode baru akan ditambahkan ke samping (kolom baru).
5.  **Desain Struktur Tabel (Handsontable)**:
    *   Desain header sesuai keinginan (bisa klik kanan untuk *Merge Cells*).
    *   **Penting**: Sisakan baris kosong di bagian bawah untuk data. Kolom pertama biasanya digunakan sebagai label (misal: Nama Kecamatan).
6.  Klik **[Simpan Tabel]**.

---

## 2. Menginput Data (Data Entry)

Setelah template jadi, Anda bisa mulai mengisi data per periode.

### A. Entri Periode Tunggal
1.  Klik **[Manage]** pada tabel yang diinginkan.
2.  Klik **[Tambah Data Periode]**.
3.  Isi nama periode (Contoh: `2024`, `2024-01`, `2024-Q1`).
4.  Ketik atau Paste data dari Excel langsung ke tabel.
5.  Simpan.

### B. Entri Bulk (Banyak Periode Sekaligus) - SMART IMPORT
Jika Anda punya data Excel yang sudah berisi banyak baris (misal 12 bulan sekaligus), Anda tidak perlu menginputnya satu-satu.

1.  Pada kolom **Periode**, masukkan rentang menggunakan pemisah **`--`** (Double Hyphen).
    *   Tahunan: `2010-2035` (Pemisah `-` tunggal khusus untuk tahun)
    *   Bulanan: `2024-01--2024-12`
    *   Triwulanan: `2024-Q1--2024-Q4`
    *   Semesteran: `2024-S1--2024-S2`
2.  **Siapkan Data Excel**: Pastikan di file Excel Anda, **Kolom Pertama** berisi label periode yang sama persis dengan range yang Anda tulis (misal baris 1: 2024-01, baris 2: 2024-02, dst).
3.  **Paste ke Tabel**: Paste seluruh data tersebut.
4.  **Simpan**: Sistem akan otomatis memecah setiap baris menjadi entri periode yang terpisah secara ajaib!

---

## 3. Melihat dan Menggabungkan Data (Data Viewer)

Ini adalah fitur terkuat untuk melihat tren data.

1.  Klik **[Viewer]** pada tabel.
2.  **Filter Periode**: Anda bisa mencentang beberapa periode sekaligus.
3.  **Smart Merging**:
    *   Jika template bertipe **Vertical**, sistem akan menumpuk baris data ke bawah.
    *   Jika template bertipe **Horizontal**, sistem akan menambahkan kolom ke kanan dan memberi label periode di atasnya secara otomatis.
4.  **Export**: Klik **[Export Excel]** untuk mengunduh hasil penggabungan data tersebut.

---

## Tips & Troubleshooting
*   **Thousand Separator**: Sistem otomatis mengenali format angka Indonesia (`1.234,56`) atau Internasional (`1,234.56`) sesuai pengaturan template.
*   **Format Periode**: Selalu gunakan format standar (YYYY-MM, YYYY-QX, YYYY-SX) agar fitur Sorting dan Smart Import berjalan lancar.
*   **Merge Cells**: Fitur merge cells pada viewer sangat bergantung pada struktur template awal. Pastikan template awal sudah memiliki struktur header yang benar.

---
*Dibuat oleh: Antigravity AI Systems*
