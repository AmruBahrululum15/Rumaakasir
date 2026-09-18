<div class="chart-container">

    <canvas id="salesChart"></canvas>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const canvas =
            document.getElementById('salesChart');

        if (!canvas) {
            return;
        }


        const ctx =
            canvas.getContext('2d');


        const gradient =
            ctx.createLinearGradient(
                0,
                0,
                0,
                320
            );


        gradient.addColorStop(
            0,
            'rgba(37, 99, 235, 0.25)'
        );


        gradient.addColorStop(
            1,
            'rgba(37, 99, 235, 0.01)'
        );


        new Chart(
            ctx,
            {

                type: 'line',

                data: {

                    labels:
                        @json($chartLabels),

                    datasets: [

                        {

                            label:
                                'Penjualan',

                            data:
                                @json($chartSales),

                            borderColor:
                                '#2563eb',

                            backgroundColor:
                                gradient,

                            fill:
                                true,

                            tension:
                                0.4,

                            borderWidth:
                                3,

                            pointRadius:
                                4,

                            pointHoverRadius:
                                6,

                        }

                    ]

                },


                options: {

                    responsive:
                        true,

                    maintainAspectRatio:
                        false,

                    interaction: {

                        intersect:
                            false,

                        mode:
                            'index'

                    },


                    plugins: {

                        legend: {

                            display:
                                false

                        },


                        tooltip: {

                            callbacks: {

                                label:
                                    function(context) {

                                        return ' Rp ' +
                                            new Intl.NumberFormat(
                                                'id-ID'
                                            ).format(
                                                context.raw
                                            );

                                    }

                            }

                        }

                    },


                    scales: {

                        y: {

                            beginAtZero:
                                true,

                            grid: {

                                color:
                                    '#f1f5f9'

                            },

                            ticks: {

                                callback:
                                    function(value) {

                                        if (
                                            value >=
                                            1000000
                                        ) {

                                            return 'Rp ' +
                                                (
                                                    value /
                                                    1000000
                                                ) +
                                                'jt';

                                        }


                                        if (
                                            value >=
                                            1000
                                        ) {

                                            return 'Rp ' +
                                                (
                                                    value /
                                                    1000
                                                ) +
                                                'rb';

                                        }


                                        return 'Rp ' +
                                            value;

                                    }

                            }

                        },


                        x: {

                            grid: {

                                display:
                                    false

                            }

                        }

                    }

                }

            }
        );

    }

);

</script>