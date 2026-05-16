# REFACTOR.md — Panduan Refactor Sistem Laravel
## Tujuan: Mendukung Kalkulasi EOQ/ROP Historis Per Tahun (2021–2025)
## Studi Kasus: PT. JJ Top Cosmindo Sidoarjo

---

## MENGAPA PERLU DIREFACTOR

Sistem saat ini menyimpan S (Biaya Pesan) dan H (Biaya Simpan) langsung di tabel
`bahan_baku` sebagai nilai konstan. Padahal data aktual membuktikan nilai S dan H
**berbeda tiap tahun** untuk bahan yang sama.

Akibatnya, kalkulasi EOQ/ROP historis 2021–2025 tidak bisa direproduksi dengan benar.

**Sebelum refactor:**
```
bahan_baku (id, kode, nama, satuan, kategori, lead_time, biaya_pesan, biaya_simpan, ...)
```

**Setelah refactor:**
```
bahan_baku      (id, kode, nama, satuan, kategori, lead_time)       ← S & H dihapus
parameter_bahan (id, bahan_baku_id, tahun, biaya_pesan, biaya_simpan) ← tabel baru
stok_historis   (id, bahan_baku_id, tahun, stok_aktual)              ← tabel baru
```

---

## PETA PERUBAHAN LENGKAP

| Komponen | Perubahan |
|----------|-----------|
| Migration `bahan_baku` | Hapus kolom `biaya_pesan`, `biaya_simpan` |
| Migration baru `parameter_bahan` | Buat tabel baru |
| Migration baru `stok_historis` | Buat tabel baru |
| Model `BahanBaku` | Update relasi, hapus fillable S & H |
| Model baru `ParameterBahan` | Buat model baru |
| Model baru `StokHistoris` | Buat model baru |
| Seeder `BahanBakuSeeder` | Hapus kolom S & H dari data |
| Seeder baru `ParameterBahanSeeder` | Data S & H per tahun |
| Seeder baru `StokHistorisSeeder` | Data stok per tahun |
| `KalkulasiController` | Ambil S & H dari `parameter_bahan` |
| `EvaluasiTicController` | Ambil S & H dari `parameter_bahan` |
| View kalkulasi EOQ/ROP | Tambah filter tahun |
| View evaluasi TIC | Sesuaikan sumber data |
| Script import Python | Sudah ada di IMPORT.md |

---

## STEP 1 — MIGRATION: Hapus S & H dari `bahan_baku`

Buat migration baru (jangan edit migration lama):

```bash
php artisan make:migration remove_biaya_columns_from_bahan_baku_table
```

```php
// database/migrations/xxxx_remove_biaya_columns_from_bahan_baku_table.php

public function up(): void
{
    Schema::table('bahan_baku', function (Blueprint $table) {
        $table->dropColumn(['biaya_pesan', 'biaya_simpan']);
    });
}

public function down(): void
{
    Schema::table('bahan_baku', function (Blueprint $table) {
        $table->bigInteger('biaya_pesan')->nullable();
        $table->bigInteger('biaya_simpan')->nullable();
    });
}
```

---

## STEP 2 — MIGRATION: Buat tabel `parameter_bahan`

```bash
php artisan make:migration create_parameter_bahan_table
```

```php
// database/migrations/xxxx_create_parameter_bahan_table.php

public function up(): void
{
    Schema::create('parameter_bahan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bahan_baku_id')
              ->constrained('bahan_baku')
              ->onDelete('cascade');
        $table->year('tahun');
        $table->bigInteger('biaya_pesan');   // S — Rp/order
        $table->bigInteger('biaya_simpan');  // H — Rp/unit/tahun
        $table->timestamps();

        $table->unique(['bahan_baku_id', 'tahun'], 'uq_parameter_bahan_tahun');
    });
}

public function down(): void
{
    Schema::dropIfExists('parameter_bahan');
}
```

---

## STEP 3 — MIGRATION: Buat tabel `stok_historis`

```bash
php artisan make:migration create_stok_historis_table
```

```php
// database/migrations/xxxx_create_stok_historis_table.php

public function up(): void
{
    Schema::create('stok_historis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bahan_baku_id')
              ->constrained('bahan_baku')
              ->onDelete('cascade');
        $table->year('tahun');
        $table->integer('stok_aktual')->default(0);
        $table->timestamps();

        $table->unique(['bahan_baku_id', 'tahun'], 'uq_stok_historis_tahun');
    });
}

public function down(): void
{
    Schema::dropIfExists('stok_historis');
}
```

Jalankan semua migration:

```bash
php artisan migrate
```

---

## STEP 4 — MODEL: Update `BahanBaku`

```php
// app/Models/BahanBaku.php

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'kategori',
        'lead_time',
        // HAPUS: 'biaya_pesan', 'biaya_simpan'
    ];

    // ── Relasi baru ──────────────────────────────────────────────
    public function parameterBahan()
    {
        return $this->hasMany(ParameterBahan::class, 'bahan_baku_id');
    }

    public function stokHistoris()
    {
        return $this->hasMany(StokHistoris::class, 'bahan_baku_id');
    }

    // Parameter untuk tahun tertentu
    public function parameterTahun(int $tahun)
    {
        return $this->parameterBahan()->where('tahun', $tahun)->first();
    }

    // Stok historis tahun tertentu
    public function stokTahun(int $tahun)
    {
        return $this->stokHistoris()->where('tahun', $tahun)->first();
    }

    // Relasi lama yang tetap dipertahankan
    public function pemakaianBulanan()
    {
        return $this->hasMany(PemakaianBulanan::class, 'bahan_baku_id');
    }

    public function stok()
    {
        return $this->hasOne(Stok::class, 'bahan_baku_id');
    }
}
```

---

## STEP 5 — MODEL: Buat `ParameterBahan`

```bash
php artisan make:model ParameterBahan
```

```php
// app/Models/ParameterBahan.php

class ParameterBahan extends Model
{
    protected $table = 'parameter_bahan';

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'biaya_pesan',
        'biaya_simpan',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'biaya_pesan'  => 'integer',
        'biaya_simpan' => 'integer',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
```

---

## STEP 6 — MODEL: Buat `StokHistoris`

```bash
php artisan make:model StokHistoris
```

```php
// app/Models/StokHistoris.php

class StokHistoris extends Model
{
    protected $table = 'stok_historis';

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'stok_aktual',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'stok_aktual'  => 'integer',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
```

---

## STEP 7 — SEEDER: Update `BahanBakuSeeder`

Hapus kolom `biaya_pesan` dan `biaya_simpan` dari data seeder:

```php
// database/seeders/BahanBakuSeeder.php

BahanBaku::insert([
    // SEBELUM (hapus biaya_pesan & biaya_simpan):
    // ['kode'=>'BB001','nama'=>'Phenoxyethanol','satuan'=>'kg',
    //  'kategori'=>'Lokal','lead_time'=>6,
    //  'biaya_pesan'=>2141357,'biaya_simpan'=>84902],

    // SESUDAH:
    ['kode'=>'BB001','nama'=>'Phenoxyethanol',  'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>6],
    ['kode'=>'BB002','nama'=>'Glycerin',         'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>41],
    ['kode'=>'BB003','nama'=>'Cetyl Alcohol',    'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>9],
    ['kode'=>'BB004','nama'=>'Stearic Acid',     'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>7],
    ['kode'=>'BB005','nama'=>'Titanium Dioxide', 'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>11],
    // ... dst untuk 100 bahan baku
]);
```

---

## STEP 8 — SEEDER: Buat `ParameterBahanSeeder`

```bash
php artisan make:seeder ParameterBahanSeeder
```

```php
// database/seeders/ParameterBahanSeeder.php
// Data S dan H per bahan per tahun — diambil dari Excel sheet "Data Historis 5 Tahun"

use App\Models\ParameterBahan;
use App\Models\BahanBaku;

class ParameterBahanSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [kode, tahun, biaya_pesan (S), biaya_simpan (H)]
        $data = [
            // BB001 - Phenoxyethanol
            ['BB001', 2021, 2168498,  87265],
            ['BB001', 2022, 2246093,  88264],
            ['BB001', 2023, 2073806,  84691],
            ['BB001', 2024, 2187510,  76011],
            ['BB001', 2025, 2141357,  84902],

            // BB002 - Glycerin
            ['BB002', 2021, 10459221, 103961],
            ['BB002', 2022, 10897733, 103809],
            ['BB002', 2023, 10242342, 124568],
            ['BB002', 2024, 10188565, 116830],
            ['BB002', 2025, 11280718, 117450],

            // BB003 - Cetyl Alcohol
            ['BB003', 2021,  732974, 417580],
            ['BB003', 2022,  760393, 402234],
            ['BB003', 2023,  703207, 390435],
            ['BB003', 2024,  710824, 443142],
            ['BB003', 2025,  753745, 450334],

            // BB004 - Stearic Acid
            ['BB004', 2021, 2367078, 157936],
            ['BB004', 2022, 2351416, 158162],
            ['BB004', 2023, 1956089, 156198],
            ['BB004', 2024, 2321241, 172692],
            ['BB004', 2025, 2352427, 163758],

            // BB005 - Titanium Dioxide
            ['BB005', 2021, 1421350, 198494],
            ['BB005', 2022, 1321594, 188023],
            ['BB005', 2023, 1364040, 197313],
            ['BB005', 2024, 1404580, 193105],
            ['BB005', 2025, 1442234, 200121],

            // BB006 - Sodium Lauryl Sulfate
            ['BB006', 2021, 2021087, 281391],
            ['BB006', 2022, 1932789, 299039],
            ['BB006', 2023, 1981160, 327575],
            ['BB006', 2024, 1881500, 288276],
            ['BB006', 2025, 1902503, 313993],

            // BB007 - Propylene Glycol
            ['BB007', 2021, 7335190, 218719],
            ['BB007', 2022, 7169624, 218599],
            ['BB007', 2023, 8167110, 235846],
            ['BB007', 2024, 8040961, 237595],
            ['BB007', 2025, 7642331, 233568],

            // BB008 - Carbomer 940
            ['BB008', 2021,  852402, 147934],
            ['BB008', 2022,  794896, 143692],
            ['BB008', 2023,  750715, 134390],
            ['BB008', 2024,  729890, 139923],
            ['BB008', 2025,  799643, 142565],

            // BB009 - Kaolin Clay
            ['BB009', 2021,  537137, 371408],
            ['BB009', 2022,  636619, 393306],
            ['BB009', 2023,  595125, 347955],
            ['BB009', 2024,  604578, 403541],
            ['BB009', 2025,  641761, 394193],

            // BB010 - Talc Powder
            ['BB010', 2021,  361831, 489661],
            ['BB010', 2022,  402435, 491251],
            ['BB010', 2023,  398182, 425432],
            ['BB010', 2024,  382618, 426511],
            ['BB010', 2025,  395099, 478272],

            // Untuk BB011–BB100: gunakan script Python dari IMPORT.md
            // yang otomatis mengisi semua 500 baris dari Excel
            // Seeder ini hanya contoh untuk development/testing
        ];

        // Buat mapping kode → id
        $kodeToId = BahanBaku::pluck('id', 'kode')->toArray();

        $rows = [];
        foreach ($data as [$kode, $tahun, $s, $h]) {
            if (!isset($kodeToId[$kode])) continue;
            $rows[] = [
                'bahan_baku_id' => $kodeToId[$kode],
                'tahun'         => $tahun,
                'biaya_pesan'   => $s,
                'biaya_simpan'  => $h,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        ParameterBahan::insert($rows);
    }
}
```

---

## STEP 9 — SEEDER: Buat `StokHistorisSeeder`

```bash
php artisan make:seeder StokHistorisSeeder
```

```php
// database/seeders/StokHistorisSeeder.php

use App\Models\StokHistoris;
use App\Models\BahanBaku;

class StokHistorisSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [kode, tahun, stok_aktual]
        // Diambil dari Excel sheet "Data Historis 5 Tahun" kolom "Stok Aktual"
        $data = [
            ['BB001', 2021,     0], ['BB001', 2022,   611], ['BB001', 2023,  5530],
            ['BB001', 2024,     0], ['BB001', 2025,  4121],

            ['BB002', 2021, 15322], ['BB002', 2022,  6496], ['BB002', 2023, 16278],
            ['BB002', 2024, 23217], ['BB002', 2025, 12645],

            ['BB003', 2021,  2265], ['BB003', 2022,  1928], ['BB003', 2023,  1837],
            ['BB003', 2024,     0], ['BB003', 2025,  1365],

            ['BB004', 2021,     0], ['BB004', 2022,  3034], ['BB004', 2023,  1488],
            ['BB004', 2024,     0], ['BB004', 2025,   518],

            ['BB005', 2021,  2953], ['BB005', 2022,  2881], ['BB005', 2023,   465],
            ['BB005', 2024,  1130], ['BB005', 2025,  3097],

            ['BB006', 2021,   131], ['BB006', 2022,     0], ['BB006', 2023,     0],
            ['BB006', 2024,  1299], ['BB006', 2025,   540],

            ['BB007', 2021,     0], ['BB007', 2022,  7175], ['BB007', 2023,  7360],
            ['BB007', 2024,  4147], ['BB007', 2025, 10845],

            ['BB008', 2021,     0], ['BB008', 2022,     0], ['BB008', 2023,  6679],
            ['BB008', 2024,  4599], ['BB008', 2025,  4757],

            ['BB009', 2021,  2352], ['BB009', 2022,   987], ['BB009', 2023,  1466],
            ['BB009', 2024,  1824], ['BB009', 2025,  1269],

            ['BB010', 2021,  1331], ['BB010', 2022,     0], ['BB010', 2023,  2084],
            ['BB010', 2024,  4343], ['BB010', 2025,  2382],

            // Untuk BB011–BB100: gunakan script Python dari IMPORT.md
        ];

        $kodeToId = BahanBaku::pluck('id', 'kode')->toArray();

        $rows = [];
        foreach ($data as [$kode, $tahun, $stok]) {
            if (!isset($kodeToId[$kode])) continue;
            $rows[] = [
                'bahan_baku_id' => $kodeToId[$kode],
                'tahun'         => $tahun,
                'stok_aktual'   => $stok,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        StokHistoris::insert($rows);
    }
}
```

---

## STEP 10 — CONTROLLER: Update `KalkulasiController`

Ini adalah perubahan **terpenting**. Logika kalkulasi harus mengambil S dan H dari
`parameter_bahan` berdasarkan tahun yang dipilih, bukan dari `bahan_baku`.

```php
// app/Http/Controllers/KalkulasiController.php

use App\Models\BahanBaku;
use App\Models\ParameterBahan;
use App\Models\PemakaianBulanan;
use App\Models\StokHistoris;

class KalkulasiController extends Controller
{
    // Daftar tahun yang tersedia
    const TAHUN_TERSEDIA = [2021, 2022, 2023, 2024, 2025];
    const Z = 1.65; // Service Level 95%

    public function index(Request $request)
    {
        // Ambil filter tahun dari request, default ke tahun terbaru
        $tahun = (int) $request->get('tahun', 2025);

        $bahan_baku = BahanBaku::with([
            'pemakaianBulanan' => fn($q) => $q->where('tahun', $tahun),
        ])->get();

        // Ambil semua parameter S & H untuk tahun yang dipilih
        $parameters = ParameterBahan::where('tahun', $tahun)
            ->pluck(null, 'bahan_baku_id')  // key by bahan_baku_id
            ->toArray();

        // Ambil semua stok historis untuk tahun yang dipilih
        $stokHistoris = StokHistoris::where('tahun', $tahun)
            ->pluck('stok_aktual', 'bahan_baku_id')
            ->toArray();

        $hasil = [];

        foreach ($bahan_baku as $bb) {
            $pemakaian = $bb->pemakaianBulanan;

            // Skip jika tidak ada data pemakaian untuk tahun ini
            if ($pemakaian->isEmpty()) continue;

            // Ambil parameter S dan H tahun ini
            $param = $parameters[$bb->id] ?? null;
            if (!$param) continue;

            $S  = $param['biaya_pesan'];
            $H  = $param['biaya_simpan'];
            $LT = $bb->lead_time;

            // ── Kalkulasi dari data pemakaian ──────────────────────
            $D       = $pemakaian->sum('pemakaian');             // Total tahunan
            $d_avg   = $pemakaian->avg('d_harian');              // Rata-rata d harian
            $sigma_d = $this->hitungSigmaD($pemakaian);         // STDEV 12 d_harian

            // ── EOQ ────────────────────────────────────────────────
            $EOQ = ($H > 0) ? sqrt((2 * $D * $S) / $H) : 0;

            // ── Safety Stock ───────────────────────────────────────
            $sigma_DL = $sigma_d * sqrt($LT);
            $SS       = self::Z * $sigma_DL;

            // ── ROP ────────────────────────────────────────────────
            $ROP = ($d_avg * $LT) + $SS;

            // ── Stok & Status ──────────────────────────────────────
            $stok    = $stokHistoris[$bb->id] ?? 0;
            $selisih = $stok - $ROP;
            $status  = $this->tentukanStatus($stok, $ROP);

            // ── Analisis Trend & CV ────────────────────────────────
            $CV = ($d_avg > 0) ? $sigma_d / $d_avg : 0;

            // ── TIC ────────────────────────────────────────────────
            $TIC_EOQ = ($EOQ > 0)
                ? ($D / $EOQ) * $S + ($EOQ / 2) * $H + $SS * $H
                : 0;

            $hasil[] = [
                'bahan_baku' => $bb,
                'tahun'      => $tahun,
                'D'          => round($D, 2),
                'd_avg'      => round($d_avg, 4),
                'sigma_d'    => round($sigma_d, 4),
                'S'          => $S,
                'H'          => $H,
                'LT'         => $LT,
                'EOQ'        => round($EOQ, 0),
                'sigma_DL'   => round($sigma_DL, 4),
                'SS'         => round($SS, 0),
                'ROP'        => round($ROP, 0),
                'stok'       => $stok,
                'selisih'    => round($selisih, 0),
                'status'     => $status,
                'CV'         => round($CV, 4),
                'TIC_EOQ'    => round($TIC_EOQ, 0),
            ];
        }

        return view('kalkulasi.index', [
            'hasil'          => $hasil,
            'tahun_dipilih'  => $tahun,
            'tahun_tersedia' => self::TAHUN_TERSEDIA,
        ]);
    }

    // ── Helper: Hitung STDEV dari 12 nilai d_harian ───────────────────
    private function hitungSigmaD($pemakaian): float
    {
        $values = $pemakaian->pluck('d_harian')->toArray();
        $n = count($values);
        if ($n < 2) return 0;

        $mean = array_sum($values) / $n;
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / ($n - 1);
        return sqrt($variance);
    }

    // ── Helper: Tentukan Status Stok ──────────────────────────────────
    private function tentukanStatus(float $stok, float $ROP): string
    {
        if ($stok <= 0)               return 'Stockout';
        if ($stok <= $ROP)            return 'Reorder';
        if ($stok <= $ROP * 1.5)     return 'Aman';
        return 'Overstock';
    }

    // ── Detail kalkulasi satu bahan, semua tahun ─────────────────────
    public function show(BahanBaku $bahanBaku)
    {
        $hasilPerTahun = [];

        foreach (self::TAHUN_TERSEDIA as $tahun) {
            $pemakaian = $bahanBaku->pemakaianBulanan()
                ->where('tahun', $tahun)->get();

            $param = ParameterBahan::where('bahan_baku_id', $bahanBaku->id)
                ->where('tahun', $tahun)->first();

            $stokRow = StokHistoris::where('bahan_baku_id', $bahanBaku->id)
                ->where('tahun', $tahun)->first();

            if ($pemakaian->isEmpty() || !$param) continue;

            $S  = $param->biaya_pesan;
            $H  = $param->biaya_simpan;
            $LT = $bahanBaku->lead_time;
            $D  = $pemakaian->sum('pemakaian');
            $d  = $pemakaian->avg('d_harian');
            $sd = $this->hitungSigmaD($pemakaian);
            $EOQ     = ($H > 0) ? sqrt((2 * $D * $S) / $H) : 0;
            $sdl     = $sd * sqrt($LT);
            $SS      = self::Z * $sdl;
            $ROP     = ($d * $LT) + $SS;
            $stok    = $stokRow ? $stokRow->stok_aktual : 0;
            $TIC_EOQ = ($EOQ > 0) ? ($D/$EOQ)*$S + ($EOQ/2)*$H + $SS*$H : 0;
            $Q_lama  = $D / 12;
            $TIC_lama = ($Q_lama > 0) ? ($D/$Q_lama)*$S + ($Q_lama/2)*$H + $SS*$H : 0;

            $hasilPerTahun[$tahun] = [
                'tahun'    => $tahun,
                'D'        => round($D, 2),
                'd'        => round($d, 4),
                'sigma_d'  => round($sd, 4),
                'S'        => $S,
                'H'        => $H,
                'LT'       => $LT,
                'EOQ'      => round($EOQ, 0),
                'sigma_DL' => round($sdl, 4),
                'SS'       => round($SS, 0),
                'ROP'      => round($ROP, 0),
                'stok'     => $stok,
                'selisih'  => round($stok - $ROP, 0),
                'status'   => $this->tentukanStatus($stok, $ROP),
                'CV'       => ($d > 0) ? round($sd / $d, 4) : 0,
                'TIC_EOQ'  => round($TIC_EOQ, 0),
                'TIC_lama' => round($TIC_lama, 0),
                'hemat'    => round($TIC_lama - $TIC_EOQ, 0),
            ];
        }

        return view('kalkulasi.show', [
            'bahan_baku'    => $bahanBaku,
            'hasilPerTahun' => $hasilPerTahun,
        ]);
    }
}
```

---

## STEP 11 — VIEW: Tambah Filter Tahun di Halaman Kalkulasi

```blade
{{-- resources/views/kalkulasi/index.blade.php --}}
{{-- Tambahkan form filter tahun di bagian atas tabel --}}

<form method="GET" action="{{ route('kalkulasi.index') }}" class="mb-3">
    <div class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 fw-semibold">Tampilkan Tahun:</label>
        <select name="tahun" class="form-select w-auto" onchange="this.form.submit()">
            @foreach($tahun_tersedia as $t)
                <option value="{{ $t }}" {{ $tahun_dipilih == $t ? 'selected' : '' }}>
                    {{ $t }}
                </option>
            @endforeach
        </select>
    </div>
</form>

{{-- Tabel hasil kalkulasi --}}
<table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
        <tr>
            <th>Kode</th>
            <th>Nama Bahan</th>
            <th>D (unit/thn)</th>
            <th>d (unit/hr)</th>
            <th>σ_d</th>
            <th>S (Rp)</th>
            <th>H (Rp)</th>
            <th>LT (hr)</th>
            <th>EOQ</th>
            <th>SS</th>
            <th>ROP</th>
            <th>Stok</th>
            <th>Selisih</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($hasil as $row)
        <tr>
            <td>{{ $row['bahan_baku']->kode }}</td>
            <td>{{ $row['bahan_baku']->nama }}</td>
            <td>{{ number_format($row['D'], 0, ',', '.') }}</td>
            <td>{{ number_format($row['d_avg'], 2, ',', '.') }}</td>
            <td>{{ number_format($row['sigma_d'], 2, ',', '.') }}</td>
            <td>{{ number_format($row['S'], 0, ',', '.') }}</td>
            <td>{{ number_format($row['H'], 0, ',', '.') }}</td>
            <td>{{ $row['LT'] }}</td>
            <td><strong>{{ number_format($row['EOQ'], 0, ',', '.') }}</strong></td>
            <td>{{ number_format($row['SS'], 0, ',', '.') }}</td>
            <td><strong>{{ number_format($row['ROP'], 0, ',', '.') }}</strong></td>
            <td>{{ number_format($row['stok'], 0, ',', '.') }}</td>
            <td class="{{ $row['selisih'] < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($row['selisih'], 0, ',', '.') }}
            </td>
            <td>
                @php
                    $badge = match($row['status']) {
                        'Stockout'  => 'danger',
                        'Reorder'   => 'warning',
                        'Aman'      => 'success',
                        'Overstock' => 'info',
                        default     => 'secondary',
                    };
                    $icon = match($row['status']) {
                        'Stockout'  => '⚠',
                        'Reorder'   => '🔄',
                        'Aman'      => '✅',
                        'Overstock' => '📦',
                        default     => '',
                    };
                @endphp
                <span class="badge bg-{{ $badge }}">{{ $icon }} {{ $row['status'] }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## STEP 12 — VIEW: Detail Per Bahan (Semua Tahun)

```blade
{{-- resources/views/kalkulasi/show.blade.php --}}
{{-- Tabel perbandingan EOQ/ROP bahan ini dari 2021-2025 --}}

<h5>{{ $bahan_baku->kode }} — {{ $bahan_baku->nama }}</h5>

<table class="table table-bordered table-sm">
    <thead class="table-dark">
        <tr>
            <th>Tahun</th>
            <th>D</th>
            <th>d</th>
            <th>σ_d</th>
            <th>S (Rp)</th>
            <th>H (Rp)</th>
            <th>EOQ</th>
            <th>SS</th>
            <th>ROP</th>
            <th>Stok</th>
            <th>Status</th>
            <th>TIC EOQ</th>
            <th>TIC Lama</th>
            <th>Hemat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($hasilPerTahun as $row)
        <tr>
            <td><strong>{{ $row['tahun'] }}</strong></td>
            <td>{{ number_format($row['D'], 0, ',', '.') }}</td>
            <td>{{ number_format($row['d'], 2, ',', '.') }}</td>
            <td>{{ number_format($row['sigma_d'], 2, ',', '.') }}</td>
            <td>{{ number_format($row['S'], 0, ',', '.') }}</td>
            <td>{{ number_format($row['H'], 0, ',', '.') }}</td>
            <td><strong>{{ number_format($row['EOQ'], 0, ',', '.') }}</strong></td>
            <td>{{ number_format($row['SS'], 0, ',', '.') }}</td>
            <td><strong>{{ number_format($row['ROP'], 0, ',', '.') }}</strong></td>
            <td>{{ number_format($row['stok'], 0, ',', '.') }}</td>
            <td>
                @php $badge = match($row['status']) {
                    'Stockout'=>'danger','Reorder'=>'warning',
                    'Aman'=>'success','Overstock'=>'info',default=>'secondary'};
                @endphp
                <span class="badge bg-{{ $badge }}">{{ $row['status'] }}</span>
            </td>
            <td>{{ number_format($row['TIC_EOQ'], 0, ',', '.') }}</td>
            <td>{{ number_format($row['TIC_lama'], 0, ',', '.') }}</td>
            <td class="text-success fw-bold">
                {{ number_format($row['hemat'], 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## STEP 13 — UPDATE `DatabaseSeeder`

```php
// database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call([
        BahanBakuSeeder::class,       // 1. Master bahan baku (tanpa S & H)
        ParameterBahanSeeder::class,  // 2. S & H per tahun   ← BARU
        StokHistorisSeeder::class,    // 3. Stok historis     ← BARU
        PemakaianBulananSeeder::class, // 4. Data pemakaian
        StokSeeder::class,            // 5. Stok berjalan saat ini
        UserSeeder::class,            // 6. User & role
    ]);
}
```

---

## STEP 14 — JALANKAN ULANG

```bash
# Reset dan jalankan ulang semua migration + seeder
php artisan migrate:fresh --seed

# Verifikasi tabel baru terbentuk
php artisan tinker
>>> \App\Models\ParameterBahan::count()   // Expected: 500 (atau sesuai seeder)
>>> \App\Models\StokHistoris::count()     // Expected: 500
>>> \App\Models\PemakaianBulanan::count() // Expected: 6000
```

---

## STEP 15 — IMPORT DATA PENUH DARI EXCEL (Opsional tapi Direkomendasikan)

Setelah seeder berjalan untuk development, jalankan script Python dari `IMPORT.md`
untuk mengisi **semua 100 bahan baku × 5 tahun** data aktual dari Excel:

```bash
# Pastikan sudah install library
pip install pandas mysql-connector-python openpyxl

# Jalankan script import
python import_excel.py
```

Script ini akan mengisi:
- `bahan_baku` — 100 baris
- `parameter_bahan` — 500 baris (S & H tiap tahun)
- `stok_historis` — 500 baris
- `pemakaian_bulanan` — 6.000 baris

---

## RINGKASAN URUTAN EKSEKUSI

```
STEP  1  →  Migration: hapus biaya_pesan & biaya_simpan dari bahan_baku
STEP  2  →  Migration: buat tabel parameter_bahan
STEP  3  →  Migration: buat tabel stok_historis
            php artisan migrate
STEP  4  →  Update Model BahanBaku (hapus fillable, tambah relasi)
STEP  5  →  Buat Model ParameterBahan
STEP  6  →  Buat Model StokHistoris
STEP  7  →  Update BahanBakuSeeder (hapus kolom S & H)
STEP  8  →  Buat ParameterBahanSeeder
STEP  9  →  Buat StokHistorisSeeder
STEP 10  →  Update KalkulasiController (ambil S & H dari parameter_bahan)
STEP 11  →  Update view kalkulasi/index.blade.php (tambah filter tahun)
STEP 12  →  Update view kalkulasi/show.blade.php (tabel per tahun)
STEP 13  →  Update DatabaseSeeder
STEP 14  →  php artisan migrate:fresh --seed
STEP 15  →  Jalankan import_excel.py untuk data penuh
```

---

## CHECKLIST VERIFIKASI AKHIR

- [ ] `php artisan migrate` berjalan tanpa error
- [ ] `php artisan db:seed` berjalan tanpa error
- [ ] Halaman `/kalkulasi` tampil dengan dropdown filter tahun
- [ ] Ganti tahun di dropdown → angka EOQ/ROP berubah
- [ ] EOQ BB001 tahun 2021 ≈ **1.373** (sesuai Excel)
- [ ] EOQ BB001 tahun 2025 ≈ **1.265** (sesuai Excel)
- [ ] ROP BB001 tahun 2021 ≈ **704** (sesuai Excel)
- [ ] Status BB001 tahun 2021 = **Stockout** (stok=0, ROP=704)
- [ ] Halaman detail bahan menampilkan tabel 5 tahun
- [ ] Kolom Hemat (TIC Lama − TIC EOQ) bernilai positif semua

---

*REFACTOR.md — dibuat berdasarkan analisis struktur Excel dan kebutuhan kalkulasi historis*
