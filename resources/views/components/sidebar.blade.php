<div class="sidebar d-lg-block">
    <div class="sidebar-header">
        <h5 class="mb-0 text-white fw-bold">JJ TOP COSMINDO</h5>
        <small class="text-white-50">SI Persediaan EOQ/ROP</small>
    </div>

    <div class="nav flex-column p-3">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large me-2"></i> Dashboard
        </a>

        <!-- MENU UNTUK SEMUA ROLE -->
        <div class="menu-label">MASTER DATA</div>
        <a href="{{ route('bahan-baku.index') }}" class="nav-link {{ request()->routeIs('bahan-baku.*') ? 'active' : '' }}"><i class="fas fa-boxes me-2"></i> Bahan Baku</a>


        <!-- MENU BERDASARKAN ROLE -->
        <div class="menu-label">PROSES INVENTORI</div>
        
        @if(auth()->user()->isProduksi())
            <a href="{{ route('pemakaian.index') }}" class="nav-link {{ request()->routeIs('pemakaian.*') ? 'active' : '' }}"><i class="fas fa-edit me-2"></i> Input Pemakaian</a>
        @endif


        @if(auth()->user()->isGudang() || auth()->user()->isManajer())
            <a href="{{ route('stok.index') }}" class="nav-link {{ request()->routeIs('stok.index') ? 'active' : '' }}"><i class="fas fa-exchange-alt me-2"></i> Manajemen Stok</a>
            <a href="{{ route('stok.riwayat') }}" class="nav-link {{ request()->routeIs('stok.riwayat') ? 'active' : '' }}"><i class="fas fa-history me-2"></i> Riwayat Transaksi</a>
        @endif


        @if(auth()->user()->isGudang())
            <a href="{{ route('eoq.index') }}" class="nav-link {{ request()->routeIs('eoq.*') ? 'active' : '' }}"><i class="fas fa-calculator me-2"></i> Kalkulasi EOQ & ROP</a>
        @endif

        @if(auth()->user()->isManajer() || auth()->user()->isGudang())
            <a href="{{ route('tic.index') }}" class="nav-link {{ request()->routeIs('tic.*') ? 'active' : '' }}"><i class="fas fa-chart-line me-2"></i> Evaluasi TIC</a>
        @endif

        @if(auth()->user()->isPurchasing() || auth()->user()->isManajer())
            <div class="menu-label">TRANSAKSI</div>
            <a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="fas fa-shopping-cart me-2"></i> Purchase Order</a>
        @endif


        <div class="menu-label">LAPORAN</div>
        <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}"><i class="fas fa-file-pdf me-2"></i> Pusat Laporan & Ekspor</a>
        

        @if(auth()->user()->isManajer())
            <div class="menu-label">ADMINISTRATOR</div>
            <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}"><i class="fas fa-users-cog me-2"></i> Manajemen User</a>
        @endif

    </div>
</div>