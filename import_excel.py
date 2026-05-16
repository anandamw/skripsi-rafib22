import pandas as pd
import mysql.connector
import math

# ─── Konfigurasi Database ───────────────────────────────────────────────
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='eoq_rop_jjtop'
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
    INSERT IGNORE INTO bahan_bakus (kode, nama, satuan, kategori, lead_time)
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
cursor.execute("SELECT id, kode FROM bahan_bakus")
kode_to_id = {row[1]: row[0] for row in cursor.fetchall()}

# ─── STEP 2: Import parameter_bahan ─────────────────────────────────────
print("STEP 2: Importing parameter_bahan...")

sql = """
    INSERT IGNORE INTO parameter_bahan (bahan_baku_id, tahun, biaya_pesan, biaya_simpan)
    VALUES (%s, %s, %s, %s)
"""
count_parameter = 0
for _, row in data_historis.iterrows():
    kode  = str(row[3]).strip()
    tahun = int(row[1])
    s     = int(float(row[9]))   # Biaya Pemesanan
    h     = int(float(row[10]))  # Biaya Simpan
    bid   = kode_to_id.get(kode)
    if bid:
        cursor.execute(sql, (bid, tahun, s, h))
        count_parameter += 1

conn.commit()
print(f"  ✅ {count_parameter} parameter diimport")

# ─── STEP 3: Import stok_historis ────────────────────────────────────────
print("STEP 3: Importing stok_historis...")

sql = """
    INSERT IGNORE INTO stok_historis (bahan_baku_id, tahun, stok_aktual)
    VALUES (%s, %s, %s)
"""
count_stok = 0
for _, row in data_historis.iterrows():
    kode  = str(row[3]).strip()
    tahun = int(row[1])
    stok  = int(float(row[6]))
    bid   = kode_to_id.get(kode)
    if bid:
        cursor.execute(sql, (bid, tahun, stok))
        count_stok += 1

conn.commit()
print(f"  ✅ {count_stok} record stok historis diimport")

# ─── STEP 4: Import pemakaian_bulanan ────────────────────────────────────
print("STEP 4: Importing pemakaian_bulanan...")

sql = """
    INSERT IGNORE INTO pemakaian_bulanans
        (bahan_baku_id, tahun, bulan, jumlah_hari, pemakaian, d_harian)
    VALUES (%s, %s, %s, %s, %s, %s)
"""
count_pemakaian = 0
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
            count_pemakaian += 1

conn.commit()
print(f"  ✅ {count_pemakaian} record pemakaian bulanan diimport")

# ─── Selesai ─────────────────────────────────────────────────────────────
cursor.close()
conn.close()

# Hitung total seluruh data yang berhasil diimport
total_rows = (
    len(kode_to_id) +  # bahan_baku
    count_parameter +
    count_stok +
    count_pemakaian
)

print("\n🎉 Import selesai!")
print(f"   bahan_baku       : {len(kode_to_id):,} baris")
print(f"   parameter_bahan  : {count_parameter:,} baris")
print(f"   stok_historis    : {count_stok:,} baris")
print(f"   pemakaian_bulanan: {count_pemakaian:,} baris")
print(f"   TOTAL            : {total_rows:,} baris")