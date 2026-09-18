<div class="stock-monitor">

    {{-- SUMMARY --}}

    <div class="stock-summary">

        <div class="stock-summary-item">

            <div class="stock-summary-icon available">

                <i class="fas fa-check"></i>

            </div>

            <div>

                <strong>
                    {{ $availableStock }}
                </strong>

                <span>
                    Stok Aman
                </span>

            </div>

        </div>


        <div class="stock-summary-item">

            <div class="stock-summary-icon low">

                <i class="fas fa-exclamation"></i>

            </div>

            <div>

                <strong>
                    {{ $lowStock }}
                </strong>

                <span>
                    Stok Menipis
                </span>

            </div>

        </div>


        <div class="stock-summary-item">

            <div class="stock-summary-icon empty">

                <i class="fas fa-times"></i>

            </div>

            <div>

                <strong>
                    {{ $outOfStock }}
                </strong>

                <span>
                    Stok Habis
                </span>

            </div>

        </div>

    </div>


    {{-- LIST --}}

    <div class="stock-list">

        @forelse($lowStockProducts as $stockProduct)

            <div class="stock-product">

                <div class="stock-product-icon">

                    <i class="fas fa-box"></i>

                </div>


                <div class="stock-product-info">

                    <strong>

                        {{ $stockProduct->name }}

                    </strong>

                    <span>

                        {{ $stockProduct->category->name ?? 'Tanpa kategori' }}

                    </span>

                </div>


                <div class="stock-product-number
                    @if($stockProduct->stock <= 0)
                        danger
                    @elseif($stockProduct->stock <= 5)
                        warning
                    @else
                        success
                    @endif
                ">

                    {{ $stockProduct->stock }}

                    <small>
                        stok
                    </small>

                </div>

            </div>

        @empty

            <div class="empty-state">

                <i class="fas fa-box-open"></i>

                <p>
                    Belum ada data produk.
                </p>

            </div>

        @endforelse

    </div>

</div>