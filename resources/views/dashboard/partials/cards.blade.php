<div class="row dashboard-stat-row">

    {{-- TOTAL PRODUK --}}
    <div class="col-xl-3 col-md-6 mb-3">

        <div class="stat-card stat-blue">

            <div class="stat-content">

                <span class="stat-label">
                    Total Produk
                </span>

                <h3 class="stat-value">
                    {{ number_format($product, 0, ',', '.') }}
                </h3>

                <span class="stat-description">
                    Produk terdaftar
                </span>

            </div>

            <div class="stat-icon">

                <i class="fas fa-box"></i>

            </div>

        </div>

    </div>


    {{-- KATEGORI --}}
    <div class="col-xl-3 col-md-6 mb-3">

        <div class="stat-card stat-purple">

            <div class="stat-content">

                <span class="stat-label">
                    Kategori
                </span>

                <h3 class="stat-value">
                    {{ number_format($category, 0, ',', '.') }}
                </h3>

                <span class="stat-description">
                    Kategori produk
                </span>

            </div>

            <div class="stat-icon">

                <i class="fas fa-tags"></i>

            </div>

        </div>

    </div>


    {{-- SUPPLIER --}}
    <div class="col-xl-3 col-md-6 mb-3">

        <div class="stat-card stat-orange">

            <div class="stat-content">

                <span class="stat-label">
                    Supplier
                </span>

                <h3 class="stat-value">
                    {{ number_format($supplier, 0, ',', '.') }}
                </h3>

                <span class="stat-description">
                    Supplier terdaftar
                </span>

            </div>

            <div class="stat-icon">

                <i class="fas fa-truck"></i>

            </div>

        </div>

    </div>


    {{-- TRANSAKSI --}}
    <div class="col-xl-3 col-md-6 mb-3">

        <div class="stat-card stat-green">

            <div class="stat-content">

                <span class="stat-label">
                    Transaksi
                </span>

                <h3 class="stat-value">
                    {{ number_format($sales, 0, ',', '.') }}
                </h3>

                <span class="stat-description">
                    Total transaksi
                </span>

            </div>

            <div class="stat-icon">

                <i class="fas fa-receipt"></i>

            </div>

        </div>

    </div>

</div>


{{-- SALES SUMMARY --}}

<div class="row">

    <div class="col-xl-4 col-md-6 mb-3">

        <div class="mini-stat-card">

            <div class="mini-stat-icon blue">

                <i class="fas fa-wallet"></i>

            </div>

            <div>

                <span>
                    Penjualan Hari Ini
                </span>

                <strong>
                    Rp {{ number_format($todaySales, 0, ',', '.') }}
                </strong>

            </div>

        </div>

    </div>


    <div class="col-xl-4 col-md-6 mb-3">

        <div class="mini-stat-card">

            <div class="mini-stat-icon green">

                <i class="fas fa-chart-line"></i>

            </div>

            <div>

                <span>
                    Penjualan Bulan Ini
                </span>

                <strong>
                    Rp {{ number_format($monthlySales, 0, ',', '.') }}
                </strong>

            </div>

        </div>

    </div>


    <div class="col-xl-4 col-md-6 mb-3">

        <div class="mini-stat-card">

            <div class="mini-stat-icon orange">

                <i class="fas fa-boxes"></i>

            </div>

            <div>

                <span>
                    Total Stok
                </span>

                <strong>
                    {{ number_format($totalStock, 0, ',', '.') }}
                    item
                </strong>

            </div>

        </div>

    </div>

</div>