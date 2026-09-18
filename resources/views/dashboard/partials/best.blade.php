<div class="best-products">

    @forelse($bestProducts as $index => $productItem)

        <div class="best-product-item">

            <div class="best-rank">

                {{ $index + 1 }}

            </div>


            <div class="best-product-info">

                <strong>

                    {{ $productItem->name }}

                </strong>

                <span>

                    {{ number_format(
                        $productItem->total_quantity,
                        0,
                        ',',
                        '.'
                    ) }}
                    terjual

                </span>

            </div>


            <div class="best-product-total">

                Rp {{ number_format(
                    $productItem->total_sales,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>

    @empty

        <div class="empty-state">

            <i class="fas fa-chart-bar"></i>

            <p>
                Belum ada data penjualan.
            </p>

        </div>

    @endforelse

</div>