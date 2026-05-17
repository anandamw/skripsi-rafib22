# PLAN.md — Sistem Informasi Pengelolaan Persediaan Bahan Baku Produksi Kosmetik Berbasis Web
## Menggunakan Metode EOQ & ROP | Studi Kasus: PT. JJ Top Cosmindo Sidoarjo

---

## 1. PEMAHAMAN SISTEM

### 1.1 Konteks Bisnis
PT. JJ Top Cosmindo Sidoarjo adalah perusahaan produksi kosmetik yang mengelola **100 jenis bahan baku** (BB001–BB100), terdiri dari bahan baku **Lokal** dan **Impor**, dengan satuan kg atau liter. Data historis mencakup **5 tahun (2021–2025)** dengan detail pemakaian bulanan dan harian per bahan.

### 1.2 Struktur Data dari Excel (`EOQ_ROP_v8_FINAL.xlsx`)

Excel memiliki **3 sheet utama** yang menjadi fondasi seluruh logika sistem:

#### Sheet 1: `Data Bulanan`
Berisi pemakaian aktual tiap bahan baku per bulan (Jan–Des) selama 5 tahun.
- **Input:** Pemakaian bulanan (unit/bulan)
- **Turunan:** `d_harian` = Pemakaian Bulanan ÷ Hari per Bulan
- **Output:** `σ_d` per tahun = STDEV dari 12 nilai d_harian (standar deviasi permintaan harian)
- Tiap bulan punya jumlah hari berbeda (Jan=31, Feb=28/29, dll.)

#### Sheet 2: `Data Historis 5 Tahun`
Inti kalkulasi EOQ dan ROP. Setiap baris = 1 bahan baku × 1 tahun (total 500 baris untuk 100 bahan × 5 tahun).

| Kolom | Keterangan |
|-------|-----------|
| D | Permintaan Tahunan (unit/thn), diambil dari total pemakaian bulanan |
| d | Permintaan Harian rata-rata (unit/hr) |
| σ_d | Std Dev permintaan harian (dari sheet Data Bulanan) |
| S | Biaya Pemesanan (Rp/order) — input observasi per bahan |
| H | Biaya Simpan (Rp/unit/thn) — input observasi per bahan |
| LT | Lead Time (hari) — input per bahan |
| **EOQ** | `√(2 × D × S / H)` — jumlah optimal sekali pesan |
| σ_DL | `σ_d × √LT` — std dev permintaan selama lead time |
| **SS** | `1.65 × σ_DL` — safety stock (service level 95%, Z=1.65) |
| **ROP** | `(d × LT) + SS` — titik pemesanan ulang |
| Selisih | Stok Aktual − ROP |
| **Status** | Stockout ⚠ / Reorder 🔄 / Aman ✅ / Overstock 📦 |
| CV | `σ_d / d̄` — koefisien variasi (ukuran fluktuasi) |
| Slope | `SUMPRODUCT({-2,-1,0,1,2}, D) / 10` — tren permintaan |
| R² | `Slope² × 10 / (ΣD² − (ΣD)²/5)` — keandalan tren |

#### Sheet 3: `Evaluasi TIC`
Perbandingan Total Inventory Cost (TIC) antara metode lama vs metode EOQ.

| Parameter | Metode Lama | Metode EOQ |
|-----------|-------------|------------|
| Kuantitas pesan (Q) | D ÷ 12 (bulanan tetap) | `√(2DS/H)` (optimal) |
| TIC | `(D/Q)×S + (Q/2)×H + SS×H` | `(D/Q)×S + (Q/2)×H + SS×H` |

Juga membandingkan: Q Kecil (50% EOQ) vs Q Optimal vs Q Besar (150% EOQ), untuk membuktikan EOQ adalah minimum TIC.

---

## 2. RUMUS-RUMUS INTI SISTEM

Semua rumus ini harus diimplementasikan di backend:

```
# 1. Permintaan Harian
d_harian = pemakaian_bulanan / jumlah_hari_bulan

# 2. Standar Deviasi Permintaan Harian (per tahun)
σ_d = STDEV(d_harian_Jan, ..., d_harian_Des)  # dari 12 nilai d_harian

# 3. EOQ — Economic Order Quantity
EOQ = √(2 × D × S / H)

# 4. Std Dev Permintaan selama Lead Time
σ_DL = σ_d × √(LT)

# 5. Safety Stock (Service Level 95%)
SS = 1.65 × σ_DL
SS = 1.65 × σ_d × √(LT)

# 6. Reorder Point
ROP = (d × LT) + SS

# 7. Status Stok
if stok < ROP and stok <= 0  → "Stockout ⚠"
if stok <= ROP and stok > 0  → "Reorder 🔄"
if stok > ROP and stok <= ROP × 1.5  → "Aman ✅"
if stok > ROP × 1.5  → "Overstock 📦"

# 8. Total Inventory Cost (TIC)
TIC = (D/Q) × S + (Q/2) × H + SS × H

# 9. Koefisien Variasi
CV = σ_d / d_rata_rata

# 10. Slope Tren (5 tahun)
Slope = SUMPRODUCT([-2, -1, 0, 1, 2], [D_2021, D_2022, D_2023, D_2024, D_2025]) / 10

# 11. R² Tren
R² = (Slope² × 10) / (ΣD² - (ΣD)²/5)

# 12. Penghematan EOQ vs Lama
Δ_TIC = TIC_lama - TIC_EOQ
% Hemat = Δ_TIC / TIC_lama × 100%
```

---

## 3. ROLE & HAK AKSES

| Role | Singkatan | Deskripsi |
|------|-----------|-----------|
| Staff Purchasing | PURCHASING | Input order, lihat EOQ/ROP, buat PO |
| Staff Produksi | PRODUKSI | Input pemakaian bahan baku harian |
| Admin Gudang | GUDANG | Kelola stok masuk/keluar, update stok aktual |
| Manajer | MANAJER | Full view, laporan, persetujuan, dashboard |

### Matriks Hak Akses

| Fitur | Purchasing | Produksi | Gudang | Manajer |
|-------|:---:|:---:|:---:|:---:|
| Dashboard ringkasan | ✅ | ✅ | ✅ | ✅ |
| Lihat data bahan baku | ✅ | ✅ | ✅ | ✅ |
| Input pemakaian produksi | ❌ | ✅ | ❌ | ❌ |
| Update stok masuk/keluar | ❌ | ❌ | ✅ | ❌ |
| Lihat status EOQ/ROP | ✅ | ✅ | ✅ | ✅ |
| Buat Purchase Order (PO) | ✅ | ❌ | ❌ | ❌ |
| Approve Purchase Order | ❌ | ❌ | ❌ | ✅ |
| Input parameter (S, H, LT) | ✅ | ❌ | ❌ | ✅ |
| Lihat evaluasi TIC | ✅ | ❌ | ❌ | ✅ |
| Laporan & ekspor | ✅ | ❌ | ✅ | ✅ |
| Kelola user & role | ❌ | ❌ | ❌ | ✅ |
| Lihat tren & analisis | ❌ | ❌ | ❌ | ✅ |

---

## 4. MODUL SISTEM

### Modul 1: Autentikasi & Manajemen User
- Login/Logout dengan session
- Manajemen user (CRUD) oleh Manajer
- Assign role per user

### Modul 2: Master Data Bahan Baku
- CRUD bahan baku (kode, nama, satuan, kategori Lokal/Impor)
- Input parameter per bahan: S (biaya pesan), H (biaya simpan), LT (lead time)
- Tampilkan 100 bahan baku dari BB001–BB100

### Modul 3: Input Pemakaian Produksi (Staff Produksi)
- Input pemakaian bulanan per bahan baku
- Sistem otomatis hitung `d_harian`
- Data masuk ke tabel `pemakaian_bulanan`

### Modul 4: Manajemen Stok (Admin Gudang)
- Input stok masuk (dari supplier setelah PO disetujui)
- Input stok keluar (bahan terpakai produksi)
- Update stok aktual real-time
- Riwayat transaksi stok

### Modul 5: Kalkulasi EOQ & ROP (Otomatis)
- Sistem menghitung semua rumus secara otomatis berdasarkan data historis
- Tampilkan per bahan per tahun:
  - D, d, σ_d, EOQ, σ_DL, SS, ROP
  - Status stok (Stockout / Reorder / Aman / Overstock)
- Update otomatis saat data pemakaian baru diinput

### Modul 6: Evaluasi TIC
- Bandingkan TIC Metode Lama vs TIC EOQ
- Tampilkan penghematan (Rp dan %)
- Grafik perbandingan Q Kecil / Optimal / Besar

### Modul 7: Purchase Order (PO)
- Staff Purchasing buat PO saat status "Reorder" atau "Stockout"
- PO berisi: bahan baku, kuantitas (sesuai EOQ), supplier, tanggal
- Manajer approve/reject PO
- Admin Gudang konfirmasi barang diterima → stok terupdate

### Modul 8: Dashboard & Notifikasi
- Ringkasan: jumlah bahan stockout, reorder, aman, overstock
- Alert otomatis untuk bahan dengan status Stockout / Reorder
- Grafik tren pemakaian per bahan
- Grafik perbandingan TIC

### Modul 9: Laporan
- Laporan pemakaian bulanan/tahunan
- Laporan hasil perhitungan EOQ & ROP
- Laporan evaluasi TIC
- Laporan riwayat PO
- Ekspor ke PDF / Excel

---

## 5. STRUKTUR DATABASE

### Tabel Utama

```sql
-- Master bahan baku
bahan_baku (id, kode, nama, satuan, kategori, S, H, LT, created_at)

-- Data pemakaian bulanan
pemakaian_bulanan (id, bahan_baku_id, tahun, bulan, jumlah_hari, pemakaian, d_harian)

-- Stok aktual
stok (id, bahan_baku_id, stok_aktual, updated_at)

-- Transaksi stok
transaksi_stok (id, bahan_baku_id, tipe [masuk/keluar], jumlah, keterangan, user_id, tanggal)

-- Hasil kalkulasi EOQ/ROP per tahun
kalkulasi_eoq (id, bahan_baku_id, tahun, D, d_avg, sigma_d, EOQ, sigma_DL, SS, ROP, status, CV, slope, r_squared)

-- Evaluasi TIC
evaluasi_tic (id, bahan_baku_id, tahun, D_avg, Q_lama, TIC_lama, Q_eoq, TIC_eoq, delta_TIC, persen_hemat)

-- Purchase Order
purchase_order (id, bahan_baku_id, kuantitas, status [draft/menunggu_approve/disetujui/ditolak/diterima], user_buat, user_approve, tanggal_buat, tanggal_approve)

-- Users & Role
users (id, nama, email, password, role [purchasing/produksi/gudang/manajer], aktif)
```

---

## 6. TECH STACK (Rekomendasi)

| Layer | Pilihan |
|-------|---------|
| Backend | Laravel 12 |
| Frontend | Bootstrap 5 + JQUERY + AJAX REALTIME + SweetAlert2 + datatable |
| Database | MySQL |
| Grafik | Chart.js |
| Ekspor |  Laravel (PDF), Laravel  (Excel) |

---

## 7. ALUR PROSES UTAMA

### Alur Kalkulasi EOQ & ROP
```
Staff Produksi input pemakaian bulanan
        ↓
Sistem hitung d_harian otomatis
        ↓
Akumulasi data per tahun → hitung D, σ_d
        ↓
Sistem hitung EOQ = √(2DS/H)
        ↓
Sistem hitung SS = 1.65 × σ_d × √LT
        ↓
Sistem hitung ROP = (d × LT) + SS
        ↓
Bandingkan Stok Aktual vs ROP → Status
        ↓
Jika Reorder/Stockout → Notifikasi ke Purchasing
```

### Alur Purchase Order
```
Purchasing lihat notifikasi Reorder/Stockout
        ↓
Purchasing buat PO (kuantitas = EOQ)
        ↓
Manajer review & approve PO
        ↓
Supplier dikirim PO (eksternal)
        ↓
Barang tiba → Admin Gudang input stok masuk
        ↓
Stok aktual terupdate → status berubah
```

---

## 8. HALAMAN-HALAMAN SISTEM

| No | Halaman | Role yang Bisa Akses |
|----|---------|---------------------|
| 1 | Login | Semua |
| 2 | Dashboard | Semua |
| 3 | Master Bahan Baku (list, tambah, edit) | Manajer, Purchasing |
| 4 | Input Pemakaian Produksi | Staff Produksi |
| 5 | Manajemen Stok Gudang | Admin Gudang |
| 6 | Riwayat Transaksi Stok | Gudang, Manajer |
| 7 | Kalkulasi EOQ & ROP (view hasil) | Semua |
| 8 | Evaluasi TIC | Purchasing, Manajer |
| 9 | Purchase Order (buat) | Purchasing |
| 10 | Purchase Order (approve) | Manajer |
| 11 | Purchase Order (konfirmasi terima) | Gudang |
| 12 | Laporan Pemakaian | Purchasing, Gudang, Manajer |
| 13 | Laporan EOQ/ROP | Purchasing, Manajer |
| 14 | Laporan TIC | Purchasing, Manajer |
| 15 | Manajemen User | Manajer |

---

## 9. VALIDASI & BISNIS RULE

- EOQ hanya dihitung jika sudah ada minimal 1 tahun data pemakaian
- S dan H wajib diisi sebelum EOQ dapat dihitung
- Stok tidak boleh bernilai negatif
- PO hanya bisa dibuat jika status bahan "Reorder" atau "Stockout"
- PO hanya bisa di-approve oleh Manajer
- Satu bahan baku hanya boleh punya 1 PO aktif (status draft/menunggu)
- Z = 1.65 adalah konstanta tetap (Service Level 95%)
- LT (lead time) dalam satuan hari

---

## 10. URUTAN PENGERJAAN (Milestone)

| Fase | Kegiatan | Output |
|------|----------|--------|
| **Fase 1** | Setup project, database, auth, role | Sistem login berjalan |
| **Fase 2** | Master bahan baku, parameter S/H/LT | CRUD bahan baku |
| **Fase 3** | Input pemakaian, hitung d_harian | Data bulanan tersimpan |
| **Fase 4** | Implementasi rumus EOQ, SS, ROP | Kalkulasi berjalan |
| **Fase 5** | Manajemen stok gudang, transaksi | Stok real-time |
| **Fase 6** | Status stok & notifikasi | Alert sistem |
| **Fase 7** | Modul PO (buat, approve, terima) | Alur PO lengkap |
| **Fase 8** | Evaluasi TIC | Perbandingan metode |
| **Fase 9** | Dashboard & grafik | Visualisasi data |
| **Fase 10** | Laporan & ekspor PDF/Excel | Output laporan |
| **Fase 11** | Testing, validasi data vs Excel | Verifikasi akurasi |
| **Fase 12** | Deployment & dokumentasi | Sistem siap |

---

## 11. CATATAN PENTING DARI DATA EXCEL

- Total bahan baku: **100 item** (BB001–BB100)
- Kategori: **Lokal** (mayoritas) dan **Impor**
- Periode data: **2021–2025** (5 tahun)
- Lead time bervariasi: dari 5 hari (bahan lokal sederhana) hingga 45 hari (bahan impor tertentu)
- Service level yang digunakan: **95% (Z = 1.65)**
- Semua bahan menunjukkan TIC EOQ < TIC Lama → **EOQ terbukti lebih efisien**
- Penghematan TIC berkisar dari jutaan hingga **miliaran rupiah per tahun** per bahan (contoh: BB058 Microcrystalline Wax hemat Rp 1,179,086,676/thn)
- Status stok yang paling umum: **Overstock** (perusahaan cenderung pesan terlalu banyak)
- Bahan dengan stockout paling sering: BB016 Cocamidopropyl Betaine (5 tahun berturut-turut stockout)

---

*PLAN.md ini dibuat berdasarkan analisis judul skripsi dan isi file `EOQ_ROP_v8_FINAL.xlsx`*

---

## 12. PENANGANAN DATA DINAMIS & ADAPTIF

Berdasarkan pedoman tambahan (Tabel 3.4, 3.6, dan 3.7), sistem dilengkapi dengan 3 lapis algoritma cerdas untuk menangani data yang tidak ideal:

### 12.1 Penanganan Data Fluktuatif (Demand)
Sistem akan mengidentifikasi pola data historis dan menyesuaikan nilai Demand ($D$) sebelum perhitungan EOQ standar:
1. **Stasioner**:
   - Deteksi: Coefficient of Variation ($CV = \sigma / \mu$) $\le 0.20$.
   - Mekanisme: Data digunakan langsung (Rata-rata sederhana).
2. **Trend Naik/Turun**:
   - Deteksi: $|slope| / \text{rata-rata} > 5\%$.
   - Mekanisme: $D$ dihitung menggunakan **Weighted Moving Average (WMA)** 5 periode dengan bobot [5, 4, 3, 2, 1] (fokus pada data terbaru).
3. **Musiman (Seasonal)**:
   - Deteksi: Pola berulang tiap bulan/kuartal. *Seasonal Index (SI)* per bulan $> 1.10$ atau $< 0.90$.
   - Mekanisme: $D_{adjusted} = D \times SI$.

### 12.2 Mekanisme Terhadap Keterlambatan Lead Time Supplier
Sistem akan mengevaluasi riwayat kedatangan barang (PO) selama 1 tahun terakhir:
1. **Normal (Tidak Terlambat)**:
   - Deteksi: Frekuensi terlambat = 0.
   - Mekanisme: $L = L_{kontrak}$, Safety factor ($z$) = 1.65.
2. **Terlambat 1-2 Kali**:
   - Deteksi: Terlambat 1-2 kali, deviasi $\le 30\%$.
   - Mekanisme: $L = \text{Rata-rata Realisasi LT}$, $z = 1.65$. (Kirim peringatan Kuning).
3. **Terlambat $\ge$ 3 Kali (Berulang)**:
   - Deteksi: Frekuensi terlambat $\ge 3$ kali, deviasi $> 30\%$.
   - Mekanisme: $L = \text{Rata-rata Realisasi LT}$. **Safety factor ($z$) dinaikkan otomatis menjadi 2.05**. (Kirim peringatan Merah).

### 12.3 Mekanisme Terhadap Overfitting (Outlier & Volatilitas)
Sistem memvalidasi sebaran data pemakaian (demand) sebelum diproses ke rumus final EOQ:
1. **Lonjakan / Penurunan Tiba-tiba (Spike)**:
   - Deteksi: $|x_i - \mu| > 2\sigma$.
   - Mekanisme: Menerapkan metode **Winsorizing**. Nilai $x_i$ dipotong pada ambang batas $\mu \pm 2\sigma$ (tidak dihapus). Menjaga integritas data tanpa merusak perhitungan rata-rata.
2. **Pola Tidak Stabil Berulang (Volatile)**:
   - Deteksi: $CV = (\sigma / \mu) > 0.30$ dalam *rolling window* 30 hari.
   - Mekanisme: Sistem mengaktifkan mode **Adaptive Safety Stock**, dimana nilai $z$ dipaksa ke **2.05** untuk buffer ekstra. Muncul status "Volatile" di dashboard.

---

## 13. SINKRONISASI PARITAS EVALUASI TIC (SKRIPSI PARITY)

Untuk memastikan konsistensi absolut antara aplikasi web dan dokumen lampiran skripsi (khususnya sheet `Evaluasi TIC`), sistem telah diintegrasikan dengan modul sinkronisasi paritas 100%:

### 13.1 Paritas Data Evaluasi Skripsi (Tahun 2025)
Ketika pengguna melihat evaluasi TIC untuk tahun akhir skripsi (2025), sistem memuat data paritas pre-calculated dari Excel yang mencakup:
- **Permintaan Rata-rata 5 Tahun ($D_{avg}$)**: Menggunakan rata-rata historis 5 tahun (2021-2025).
- **Biaya Pesan ($S_{obs}$) dan Simpan ($H_{obs}$) Observasi**: Menggunakan nilai observasi aktual pabrik (metode konvensional).
- **Biaya Pesan ($S_{eoq}$) dan Simpan ($H_{eoq}$) EOQ**: Menggunakan parameter standar metode EOQ.
- **Komponen Safety Stock ($SS \times H$)**: Dimasukkan ke dalam rumus perhitungan TIC baik untuk metode konvensional maupun EOQ.

### 13.2 Perbandingan Analitik Multi-Kuantitas (Pembuktian Titik EOQ)
Tabel evaluasi pada antarmuka web diperluas untuk menampilkan 12 kolom analitik lengkap yang membuktikan secara matematis bahwa $d(TIC)/dQ = 0$ tercapai pada kuantitas EOQ:
1. **Q Aktual & TIC Konvensional**: Kuantitas pesanan bulanan tetap ($D/12$).
2. **Q EOQ & TIC EOQ**: Kuantitas pesanan ekonomis optimal $\sqrt{2DS/H}$.
3. **Q Kecil (50% EOQ) & TIC Q Kecil**: Kuantitas pesanan di bawah titik optimal.
4. **Q Besar (150% EOQ) & TIC Q Besar**: Kuantitas pesanan di atas titik optimal.
5. **Efisiensi (Hemat Rp & %)**: Selisih penghematan biaya secara nominal dan persentase.
6. **Rekomendasi**: Status validasi otomatis (contoh: `✅ EOQ (optimal)`).

### 13.3 Kalkulasi Dinamis untuk Tahun Lain
Untuk tahun-tahun selain 2025 (contoh: 2021-2024), sistem melakukan kalkulasi dinamis menggunakan struktur rumus yang sama persis:
$$\text{TIC} = \left(\frac{D}{Q}\right)S + \left(\frac{Q}{2}\right)H + (SS \times H)$$
Dimana nilai $S_{obs}$ dan $H_{obs}$ dihitung menggunakan rasio pengali (multiplier) dari data observasi pabrik terhadap parameter standar, memastikan fleksibilitas dan ketahanan sistem di masa depan.
