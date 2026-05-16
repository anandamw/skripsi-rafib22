# IMPORT.md — Rencana Import Data Excel ke Database
## File: `EOQ_ROP_v8_FINAL.xlsx` → MySQL/MariaDB
## Studi Kasus: PT. JJ Top Cosmindo Sidoarjo

---

## RINGKASAN KEPUTUSAN IMPORT

| Sheet | Diimport? | Alasan |
|-------|:---------:|--------|
| `Data Bulanan` | ✅ YA | Sumber data mentah utama — pemakaian aktual per bulan |
| `Data Historis 5 Tahun` | ✅ SEBAGIAN | Hanya kolom input manual (S, H, LT, Stok Aktual, Kategori) |
| `Evaluasi TIC` | ❌ TIDAK | Semua angka adalah hasil kalkulasi, sistem yang hitung sendiri |

---

## STRUKTUR EXCEL (Pemetaan Teknis)

### Sheet 1: `Data Bulanan`
- **Shape:** 100 baris data × 135 kolom
- **Baris header:** Row 2 (No, Kode, Nama, Satuan, TAHUN...), Row 3 (bulan), Row 4 (sub-label Pemakaian/d_harian)
- **Baris data:** Row 5 s/d Row 104 (100 bahan baku BB001–BB100)

**Peta kolom per tahun:**

| Tahun | Kolom Pemakaian Jan–Des | Kolom σ_d |
|-------|------------------------|-----------|
| 2021 | col[4,6,8,10,12,14,16,18,20,22,24,26] | col[29] |
| 2022 | col[30,32,34,36,38,40,42,44,46,48,50,52] | col[55] |
| 2023 | col[56,58,60,62,64,66,68,70,72,74,76,78] | col[81] |
| 2024 | col[82,84,86,88,90,92,94,96,98,100,102,104] | col[107] |
| 2025 | col[108,110,112,114,116,118,120,122,124,126,128,130] | col[133] |

> Untuk setiap bulan: kolom genap = `pemakaian`, kolom ganjil berikutnya = `d_harian`

**Jumlah hari per bulan (konsisten semua tahun):**

| Jan | Feb | Mar | Apr | Mei | Jun | Jul | Agu | Sep | Okt | Nov | Des |
|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|
| 31 | 28 | 31 | 30 | 31 | 30 | 31 | 31 | 30 | 31 | 30 | 31 |

---

### Sheet 2: `Data Historis 5 Tahun`
- **Shape:** 500 baris data × 22 kolom (100 bahan × 5 tahun)
- **Baris header:** Row 5 (header aktual)
- **Baris data:** Row 6 s/d Row 505

**Peta kolom lengkap:**

| col | Nama Kolom | Import? | Keterangan |
|-----|-----------|:-------:|------------|
| 0 | No | ❌ | Nomor urut, tidak perlu |
| 1 | Tahun | ✅ | 2021–2025 |
| 2 | Kategori | ✅ | "Lokal" / "Impor" — ke tabel `bahan_baku` |
| 3 | Kode | ✅ | BB001–BB100 — foreign key |
| 4 | Nama Bahan Baku | ❌ | Sudah ada di `bahan_baku` |
| 5 | Satuan | ❌ | Sudah ada di `bahan_baku` |
| 6 | Stok Aktual | ✅ | Data historis stok tiap tahun |
| 7 | LT Lead Time (hari) | ✅ | Tetap sama tiap tahun per bahan |
| 8 | D (Permintaan Tahunan) | ❌ | Dihitung sistem dari sum pemakaian |
| 9 | S (Biaya Pemesanan) | ✅ | **Berbeda tiap tahun** — wajib import |
| 10 | H (Biaya Simpan) | ✅ | **Berbeda tiap tahun** — wajib import |
| 11 | EOQ | ❌ | Dihitung sistem: √(2DS/H) |
| 12 | d (Permintaan Harian) | ❌ | Dihitung sistem dari avg d_harian |
| 13 | σ_d | ❌ | Sudah ada di Data Bulanan |
| 14 | σ_DL | ❌ | Dihitung sistem: σ_d × √LT |
| 15 | SS | ❌ | Dihitung sistem: 1.65 × σ_DL |
| 16 | ROP | ❌ | Dihitung sistem: (d × LT) + SS |
| 17 | Selisih | ❌ | Dihitung sistem: Stok − ROP |
| 18 | STATUS | ❌ | Dihitung sistem |
| 19 | CV | ❌ | Dihitung sistem: σ_d / d |
| 20 | Slope Trend | ❌ | Dihitung sistem |
| 21 | R² Trend | ❌ | Dihitung sistem |

---

## TABEL DATABASE TUJUAN IMPORT

### Tabel 1: `bahan_baku`
Diisi dari **Sheet 1 (Data Bulanan)** + **Sheet 2 kolom Kategori & LT**

```sql
CREATE TABLE bahan_baku (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10) UNIQUE NOT NULL,   -- BB001 s/d BB100
    nama        VARCHAR(100) NOT NULL,          -- Phenoxyethanol, dst.
    satuan      VARCHAR(10) NOT NULL,           -- kg / L
    kategori    ENUM('Lokal','Impor') NOT NULL,
    lead_time   INT NOT NULL,                  -- dalam hari, tetap per bahan
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Sumber kolom:**
- `kode` ← Sheet 1 col[1] / Sheet 2 col[3]
- `nama` ← Sheet 1 col[2] / Sheet 2 col[4]
- `satuan` ← Sheet 1 col[3] / Sheet 2 col[5]
- `kategori` ← Sheet 2 col[2]
- `lead_time` ← Sheet 2 col[7] (ambil dari tahun manapun, nilainya sama)

**Jumlah baris:** 100 baris (BB001–BB100)

---

### Tabel 2: `pemakaian_bulanan`
Diisi dari **Sheet 1 (Data Bulanan)** — ini tabel terbesar

```sql
CREATE TABLE pemakaian_bulanan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    bahan_baku_id   INT NOT NULL,
    tahun           YEAR NOT NULL,
    bulan           TINYINT NOT NULL,          -- 1 (Jan) s/d 12 (Des)
    jumlah_hari     TINYINT NOT NULL,          -- 28/30/31
    pemakaian       DECIMAL(12,2) NOT NULL,    -- unit/bulan
    d_harian        DECIMAL(12,6) NOT NULL,    -- unit/hari
    FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id),
    UNIQUE KEY uq_bahan_tahun_bulan (bahan_baku_id, tahun, bulan)
);
```

**Sumber kolom:**
- `bahan_baku_id` ← lookup dari kode (Sheet 1 col[1])
- `tahun` ← 2021/2022/2023/2024/2025
- `bulan` ← 1–12 (Jan–Des)
- `jumlah_hari` ← dari label sub-header (Jan=31, Feb=28, dst.)
- `pemakaian` ← Sheet 1, kolom genap per bulan per tahun
- `d_harian` ← Sheet 1, kolom ganjil per bulan per tahun

**Jumlah baris:** 100 bahan × 5 tahun × 12 bulan = **6.000 baris**

---

### Tabel 3: `parameter_bahan`
Diisi dari **Sheet 2 col[9] dan col[10]** — S dan H berbeda tiap tahun

```sql
CREATE TABLE parameter_bahan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    bahan_baku_id   INT NOT NULL,
    tahun           YEAR NOT NULL,
    biaya_pesan     BIGINT NOT NULL,           -- S dalam Rupiah/order
    biaya_simpan    BIGINT NOT NULL,           -- H dalam Rupiah/unit/tahun
    FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id),
    UNIQUE KEY uq_bahan_tahun (bahan_baku_id, tahun)
);
```

**Sumber kolom:**
- `bahan_baku_id` ← lookup dari kode (Sheet 2 col[3])
- `tahun` ← Sheet 2 col[1]
- `biaya_pesan` ← Sheet 2 col[9] (S)
- `biaya_simpan` ← Sheet 2 col[10] (H)

**Jumlah baris:** 100 bahan × 5 tahun = **500 baris**

> ⚠️ **Penting:** S dan H memang **berbeda tiap tahun** untuk bahan yang sama.
> Contoh BB001 Phenoxyethanol:
> - 2021: S=2,168,498 | H=87,265
> - 2022: S=2,246,093 | H=88,264
> - 2023: S=2,073,806 | H=84,691
> - 2024: S=2,187,510 | H=76,011
> - 2025: S=2,141,357 | H=84,902

---

### Tabel 4: `stok_historis`
Diisi dari **Sheet 2 col[6]** — stok aktual tiap akhir tahun

```sql
CREATE TABLE stok_historis (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    bahan_baku_id   INT NOT NULL,
    tahun           YEAR NOT NULL,
    stok_aktual     INT NOT NULL DEFAULT 0,
    FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id),
    UNIQUE KEY uq_bahan_tahun (bahan_baku_id, tahun)
);
```

**Sumber kolom:**
- `bahan_baku_id` ← lookup dari kode (Sheet 2 col[3])
- `tahun` ← Sheet 2 col[1]
- `stok_aktual` ← Sheet 2 col[6]

**Jumlah baris:** 100 bahan × 5 tahun = **500 baris**

---

## URUTAN IMPORT (WAJIB BERURUTAN)

```
STEP 1 → Import tabel bahan_baku       (100 baris)
            ↓ (harus selesai dulu, jadi foreign key)
STEP 2 → Import tabel parameter_bahan  (500 baris)
STEP 3 → Import tabel stok_historis    (500 baris)
STEP 4 → Import tabel pemakaian_bulanan (6.000 baris)
```

> Tabel `bahan_baku` harus diisi **pertama** karena tabel lain mereferensikannya lewat `bahan_baku_id`.

---

## SCRIPT PYTHON IMPORT

Simpan sebagai `import_excel.py` dan jalankan sekali saat setup awal.

```python
import pandas as pd
import mysql.connector
import math

# ─── Konfigurasi Database ───────────────────────────────────────────────
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='your_password',
    database='db_cosmindo'
)
cursor = conn.cursor()

# ─── Load Excel ──────────────────────────────────────────────────────────
FILE = 'EOQ_ROP_v8_FINAL.xlsx'
df_bulanan  = pd.read_excel(FILE, sheet_name='Data Bulanan',        header=None)
df_historis = pd.read_excel(FILE, sheet_name='Data Historis 5 Tahun', header=None)

# Ambil baris data saja (skip header rows)
data_bulanan  = df_bulanan.iloc[5:105].reset_index(drop=True)   # 100 baris
data_historis = df_historis.iloc[6:506].reset_index(drop=True)  # 500 baris

# ─── Peta kolom Data Bulanan ─────────────────────────────────────────────
TAHUN_COLS = {
    2021: [4,6,8,10,12,14,16,18,20,22,24,26],
    2022: [30,32,34,36,38,40,42,44,46,48,50,52],
    2023: [56,58,60,62,64,66,68,70,72,74,76,78],
    2024: [82,84,86,88,90,92,94,96,98,100,102,104],
    2025: [108,110,112,114,116,118,120,122,124,126,128,130],
}
HARI_BULAN = {
    1:31, 2:28, 3:31, 4:30, 5:31, 6:30,
    7:31, 8:31, 9:30, 10:31, 11:30, 12:31
}

# ─── STEP 1: Import bahan_baku ───────────────────────────────────────────
print("STEP 1: Importing bahan_baku...")

# Ambil LT dan Kategori dari Data Historis (1 bahan cukup ambil 1 row, misal tahun 2025)
historis_2025 = data_historis[data_historis.iloc[:, 1] == 2025].copy()
lt_map       = dict(zip(historis_2025.iloc[:, 3], historis_2025.iloc[:, 7]))   # kode → LT
kategori_map = dict(zip(historis_2025.iloc[:, 3], historis_2025.iloc[:, 2]))   # kode → Kategori

sql = """
    INSERT IGNORE INTO bahan_baku (kode, nama, satuan, kategori, lead_time)
    VALUES (%s, %s, %s, %s, %s)
"""
for _, row in data_bulanan.iterrows():
    kode   = str(row[1]).strip()
    nama   = str(row[2]).strip()
    satuan = str(row[3]).strip()
    lt     = int(lt_map.get(kode, 0))
    kat    = str(kategori_map.get(kode, 'Lokal'))
    cursor.execute(sql, (kode, nama, satuan, kat, lt))

conn.commit()
print(f"  ✅ {cursor.rowcount} bahan baku diimport")

# Buat mapping kode → id
cursor.execute("SELECT id, kode FROM bahan_baku")
kode_to_id = {row[1]: row[0] for row in cursor.fetchall()}

# ─── STEP 2: Import parameter_bahan ─────────────────────────────────────
print("STEP 2: Importing parameter_bahan...")

sql = """
    INSERT IGNORE INTO parameter_bahan (bahan_baku_id, tahun, biaya_pesan, biaya_simpan)
    VALUES (%s, %s, %s, %s)
"""
count = 0
for _, row in data_historis.iterrows():
    kode  = str(row[3]).strip()
    tahun = int(row[1])
    s     = int(float(row[9]))   # Biaya Pemesanan
    h     = int(float(row[10]))  # Biaya Simpan
    bid   = kode_to_id.get(kode)
    if bid:
        cursor.execute(sql, (bid, tahun, s, h))
        count += 1

conn.commit()
print(f"  ✅ {count} parameter diimport")

# ─── STEP 3: Import stok_historis ────────────────────────────────────────
print("STEP 3: Importing stok_historis...")

sql = """
    INSERT IGNORE INTO stok_historis (bahan_baku_id, tahun, stok_aktual)
    VALUES (%s, %s, %s)
"""
count = 0
for _, row in data_historis.iterrows():
    kode  = str(row[3]).strip()
    tahun = int(row[1])
    stok  = int(float(row[6]))
    bid   = kode_to_id.get(kode)
    if bid:
        cursor.execute(sql, (bid, tahun, stok))
        count += 1

conn.commit()
print(f"  ✅ {count} record stok historis diimport")

# ─── STEP 4: Import pemakaian_bulanan ────────────────────────────────────
print("STEP 4: Importing pemakaian_bulanan...")

sql = """
    INSERT IGNORE INTO pemakaian_bulanan
        (bahan_baku_id, tahun, bulan, jumlah_hari, pemakaian, d_harian)
    VALUES (%s, %s, %s, %s, %s, %s)
"""
count = 0
for _, row in data_bulanan.iterrows():
    kode = str(row[1]).strip()
    bid  = kode_to_id.get(kode)
    if not bid:
        continue
    for tahun, cols in TAHUN_COLS.items():
        for bulan_idx, col_pemakaian in enumerate(cols):
            bulan     = bulan_idx + 1
            hari      = HARI_BULAN[bulan]
            pemakaian = float(row[col_pemakaian])
            d_harian  = float(row[col_pemakaian + 1])
            cursor.execute(sql, (bid, tahun, bulan, hari, pemakaian, d_harian))
            count += 1

conn.commit()
print(f"  ✅ {count} record pemakaian bulanan diimport")

# ─── Selesai ─────────────────────────────────────────────────────────────
cursor.close()
conn.close()
print("\n🎉 Import selesai!")
print("   bahan_baku      : 100 baris")
print("   parameter_bahan : 500 baris")
print("   stok_historis   : 500 baris")
print("   pemakaian_bulanan: 6.000 baris")
print("   TOTAL           : 7.100 baris")
```

---

## VERIFIKASI SETELAH IMPORT

Jalankan query berikut untuk memastikan data benar:

```sql
-- 1. Cek jumlah bahan baku
SELECT COUNT(*) AS total_bahan FROM bahan_baku;
-- Expected: 100

-- 2. Cek distribusi kategori
SELECT kategori, COUNT(*) AS jumlah FROM bahan_baku GROUP BY kategori;
-- Expected: Lokal ~74, Impor ~26

-- 3. Cek parameter per tahun
SELECT tahun, COUNT(*) AS jumlah FROM parameter_bahan GROUP BY tahun ORDER BY tahun;
-- Expected: 100 per tahun, 5 tahun

-- 4. Cek pemakaian bulanan
SELECT tahun, COUNT(*) AS jumlah FROM pemakaian_bulanan GROUP BY tahun ORDER BY tahun;
-- Expected: 1200 per tahun (100 bahan × 12 bulan)

-- 5. Verifikasi contoh BB001 2021 Januari
SELECT bb.kode, pm.tahun, pm.bulan, pm.pemakaian, pm.d_harian
FROM pemakaian_bulanan pm
JOIN bahan_baku bb ON bb.id = pm.bahan_baku_id
WHERE bb.kode = 'BB001' AND pm.tahun = 2021 AND pm.bulan = 1;
-- Expected: pemakaian=3725, d_harian=120.161290...

-- 6. Verifikasi S dan H BB001 2025
SELECT pb.tahun, pb.biaya_pesan, pb.biaya_simpan
FROM parameter_bahan pb
JOIN bahan_baku bb ON bb.id = pb.bahan_baku_id
WHERE bb.kode = 'BB001' ORDER BY pb.tahun;
-- Expected: 2025 → S=2,141,357 | H=84,902

-- 7. Verifikasi stok historis BB002
SELECT sh.tahun, sh.stok_aktual
FROM stok_historis sh
JOIN bahan_baku bb ON bb.id = sh.bahan_baku_id
WHERE bb.kode = 'BB002' ORDER BY sh.tahun;
-- Expected: 2021=15322, 2022=6496, 2023=16278, 2024=23217, 2025=12645
```

---

## CATATAN PENTING

| # | Catatan |
|---|---------|
| 1 | Import ini hanya dilakukan **SEKALI** saat setup awal sistem |
| 2 | Data setelah 2025 akan diinput manual oleh Staff Produksi lewat form sistem |
| 3 | `d_harian` di pemakaian_bulanan **bisa dihitung ulang** oleh sistem (pemakaian ÷ jumlah_hari), tapi diimport agar sesuai presisi Excel |
| 4 | `stok_aktual` saat ini (live) disimpan di tabel `stok` yang terpisah, bukan `stok_historis` |
| 5 | Feb selalu 28 hari di Excel ini (tidak ada perlakuan tahun kabisat 2024) |
| 6 | LT diambil dari data 2025 karena nilainya konsisten sama tiap tahun per bahan |
| 7 | Semua nilai S dan H sudah dalam satuan Rupiah (tidak perlu konversi) |

---

*IMPORT.md ini dibuat berdasarkan analisis teknis mendalam terhadap struktur file `EOQ_ROP_v8_FINAL.xlsx`*
