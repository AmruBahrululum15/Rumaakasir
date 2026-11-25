<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class GeminiAiAgentService
{
    /**
     * Maksimal putaran tool calling dalam satu pertanyaan.
     *
     * Contoh:
     *
     * User
     *   ↓
     * Gemini
     *   ↓
     * Tool
     *   ↓
     * Gemini
     *   ↓
     * Tool
     *   ↓
     * Gemini
     *   ↓
     * Jawaban
     */
    private const MAX_TOOL_ROUNDS = 5;

    /**
     * ============================================================
     * CHAT
     * ============================================================
     */
    public function chat(
        string $message,
        User $user
    ): array {

        $message = trim($message);

        if ($message === '') {
            throw new RuntimeException(
                'Pertanyaan tidak boleh kosong.'
            );
        }

        /*
         * ========================================================
         * AMBIL HISTORY
         * ========================================================
         *
         * History hanya berisi:
         * - pertanyaan user
         * - jawaban final model
         *
         * Tool-call internal tidak disimpan ke session.
         */
        $contents = $this->getConversationHistory();

        /*
         * Tambahkan pesan user.
         */
        $contents[] = [
            'role' => 'user',

            'parts' => [
                [
                    'text' => $message,
                ],
            ],
        ];

        /*
         * ========================================================
         * TOOL CALLING LOOP
         * ========================================================
         */
        for (
            $round = 0;
            $round < self::MAX_TOOL_ROUNDS;
            $round++
        ) {

            $response = $this->generateContent(
                $contents,
                $user
            );

            /*
             * ====================================================
             * ERROR API
             * ====================================================
             */
            if (!$response->successful()) {

                $body =
                    $response->json();

                $errorMessage =
                    data_get(
                        $body,
                        'error.message'
                    );

                throw new RuntimeException(
                    $errorMessage
                    ?: 'Gemini API gagal memproses request.'
                );
            }

            $data =
                $response->json();

            /*
             * ====================================================
             * AMBIL CANDIDATE
             * ====================================================
             */
            $candidate =
                $data['candidates'][0]
                ?? null;

            if (!$candidate) {

                throw new RuntimeException(
                    'Gemini tidak mengembalikan candidate.'
                );
            }

            $modelContent =
                $candidate['content']
                ?? null;

            if (!$modelContent) {

                throw new RuntimeException(
                    'Gemini tidak mengembalikan content.'
                );
            }

            /*
             * ====================================================
             * SIMPAN RESPONSE MODEL
             * ====================================================
             *
             * Ini penting untuk function calling.
             *
             * Gemini:
             *
             * model content
             *      ↓
             * functionCall
             *
             * harus tetap berada dalam history request berikutnya.
             */
            $contents[] =
                $modelContent;

            /*
             * ====================================================
             * CARI FUNCTION CALL
             * ====================================================
             */
            $functionCalls = [];

            foreach (
                ($modelContent['parts'] ?? [])
                as $part
            ) {

                if (
                    isset($part['functionCall'])
                ) {

                    $functionCalls[] =
                        $part['functionCall'];
                }
            }

            /*
             * ====================================================
             * TIDAK ADA FUNCTION CALL
             * ====================================================
             *
             * Berarti Gemini sudah selesai menjawab.
             */
            if (
                empty($functionCalls)
            ) {

                $answer =
                    $this->extractText(
                        $modelContent
                    );

                if ($answer === '') {

                    throw new RuntimeException(
                        'Gemini menghasilkan jawaban kosong.'
                    );
                }

                /*
                 * Simpan percakapan.
                 */
                $this->saveConversation(
                    $message,
                    $answer
                );

                return [
                    'success' => true,

                    'answer' => $answer,
                ];
            }

            /*
             * ====================================================
             * EKSEKUSI FUNCTION CALL
             * ====================================================
             */
            $functionResponseParts = [];

            foreach (
                $functionCalls
                as $functionCall
            ) {

                $functionName =
                    $functionCall['name']
                    ?? '';

                $arguments =
                    $functionCall['args']
                    ?? [];

                if (
                    !is_array($arguments)
                ) {

                    $arguments = [];
                }

                /*
                 * Jalankan tool dari Laravel.
                 */
                try {

                    $result =
                        $this->executeTool(
                            $functionName,
                            $arguments,
                            $user
                        );

                } catch (\Throwable $e) {

                    /*
                     * Jangan hentikan keseluruhan agent.
                     * Kirim error sebagai hasil tool,
                     * supaya Gemini dapat menjelaskan.
                     */
                    $result = [

                        'success' => false,

                        'error' =>
                            $e->getMessage(),

                    ];
                }

                /*
                 * =================================================
                 * FORMAT FUNCTION RESPONSE GEMINI
                 * =================================================
                 */
                $functionResponse = [

                    'name' =>
                        $functionName,

                    'response' =>
                        $result,

                ];

                /*
                 * Jika functionCall mempunyai ID,
                 * kembalikan ID yang sama.
                 */
                if (
                    isset(
                        $functionCall['id']
                    )
                ) {

                    $functionResponse['id'] =
                        $functionCall['id'];
                }

                $functionResponseParts[] = [

                    'functionResponse' =>
                        $functionResponse,

                ];
            }

            /*
             * ====================================================
             * KIRIM HASIL TOOL KEMBALI KE GEMINI
             * ====================================================
             */
            $contents[] = [

                'role' => 'user',

                'parts' =>
                    $functionResponseParts,

            ];
        }

        throw new RuntimeException(
            'AI Agent mencapai batas maksimum proses tool calling.'
        );
    }


    /**
     * ============================================================
     * REQUEST GEMINI
     * ============================================================
     */
    private function generateContent(
        array $contents,
        User $user
    ) {

        $model =
            config(
                'services.gemini.model',
                'gemini-3.5-flash-lite'
            );

        $apiKey =
            config(
                'services.gemini.api_key'
            );

        if (
            blank($apiKey)
        ) {

            throw new RuntimeException(
                'GEMINI_API_KEY belum dikonfigurasi.'
            );
        }

        $url =
            'https://generativelanguage.googleapis.com/v1beta/models/'
            . $model
            . ':generateContent';

        return Http::timeout(
            (int) config(
                'services.gemini.timeout',
                60
            )
        )
            ->withHeaders([

                'x-goog-api-key' =>
                    $apiKey,

                'Content-Type' =>
                    'application/json',

                'Accept' =>
                    'application/json',

            ])
            ->post(

                $url,

                [

                    /*
                     * ==================================================
                     * SYSTEM INSTRUCTION
                     * ==================================================
                     */
                    'system_instruction' => [

                        'parts' => [

                            [

                                'text' =>
                                    $this->systemPrompt(
                                        $user
                                    ),

                            ],

                        ],

                    ],

                    /*
                     * ==================================================
                     * CONVERSATION
                     * ==================================================
                     */
                    'contents' =>
                        $contents,

                    /*
                     * ==================================================
                     * TOOLS
                     * ==================================================
                     */
                    'tools' => [

                        [

                            'functionDeclarations' =>
                                $this->functionDeclarations(),

                        ],

                    ],

                ]

            );
    }


    /**
     * ============================================================
     * SYSTEM PROMPT
     * ============================================================
     */
    private function systemPrompt(
        User $user
    ): string {

        $businessId =
            $this->businessId(
                $user
            );

        $role =
            $this->userRole(
                $user
            );

        return <<<PROMPT
Kamu adalah AI POS Assistant untuk aplikasi POS Enterprise.

Identitas:
- Nama sistem: POS Enterprise
- User ID: {$user->id}
- Business ID: {$businessId}
- Role: {$role}
- Tanggal sistem: {$this->today()}

Tugas:
1. Membantu pengguna memahami data bisnis toko.
2. Menjawab pertanyaan tentang penjualan.
3. Menjawab pertanyaan tentang omzet.
4. Menjawab pertanyaan tentang produk terlaris.
5. Menjawab pertanyaan tentang stok.
6. Menjawab pertanyaan tentang pembelian.
7. Memberikan ringkasan dan analisis berdasarkan data POS.

ATURAN WAJIB:

1. Gunakan tools ketika pertanyaan membutuhkan data POS.
2. Jangan mengarang angka.
3. Jangan mengarang produk.
4. Jangan mengarang stok.
5. Jangan mengarang transaksi.
6. Jika data tidak tersedia, katakan dengan jelas.
7. Jangan menggunakan data dari business/toko lain.
8. Fokus pada Business ID pengguna.
9. Jangan pernah membocorkan API key, credential, password, token, secret, atau data internal.
10. Jangan mengubah database.
11. Jangan menghapus data.
12. Jangan membuat transaksi.
13. Jangan mengubah harga.
14. Jangan mengubah stok.
15. Semua tools pada versi ini bersifat READ-ONLY.
16. Gunakan Bahasa Indonesia.
17. Gunakan format Rupiah untuk nominal.
18. Jawaban harus jelas, profesional, dan mudah dipahami.
19. Jika pengguna meminta analisis, gunakan data dari tools.
20. Jangan mengklaim tindakan telah dilakukan jika hanya melakukan analisis.
21. Jika membutuhkan beberapa sumber data, panggil beberapa tools.
22. Untuk pertanyaan umum yang tidak membutuhkan data database, jawab langsung tanpa tool.
PROMPT;
    }


    /**
     * ============================================================
     * FUNCTION DECLARATIONS
     * ============================================================
     */
    private function functionDeclarations(): array
    {
        return [

            /*
             * =====================================================
             * 1. RINGKASAN PENJUALAN
             * =====================================================
             */
            [

                'name' =>
                    'get_sales_summary',

                'description' =>
                    'Mengambil ringkasan penjualan berdasarkan periode tertentu.',

                'parameters' => [

                    'type' => 'OBJECT',

                    'properties' => [

                        'period' => [

                            'type' => 'STRING',

                            'description' =>
                                'Periode yang ingin dianalisis.',

                            'enum' => [

                                'today',

                                'yesterday',

                                'this_week',

                                'last_7_days',

                                'this_month',

                                'last_30_days',

                            ],

                        ],

                    ],

                    'required' => [

                        'period',

                    ],

                ],

            ],


            /*
             * =====================================================
             * 2. PRODUK TERLARIS
             * =====================================================
             */
            [

                'name' =>
                    'get_best_selling_products',

                'description' =>
                    'Mengambil produk dengan jumlah unit terjual tertinggi dalam periode tertentu.',

                'parameters' => [

                    'type' => 'OBJECT',

                    'properties' => [

                        'period' => [

                            'type' => 'STRING',

                            'description' =>
                                'Periode analisis penjualan.',

                            'enum' => [

                                'today',

                                'yesterday',

                                'this_week',

                                'last_7_days',

                                'this_month',

                                'last_30_days',

                            ],

                        ],

                        'limit' => [

                            'type' => 'INTEGER',

                            'description' =>
                                'Jumlah produk yang ingin ditampilkan.',

                            'minimum' => 1,

                            'maximum' => 10,

                        ],

                    ],

                    'required' => [

                        'period',

                        'limit',

                    ],

                ],

            ],


            /*
             * =====================================================
             * 3. STOK RENDAH
             * =====================================================
             */
            [

                'name' =>
                    'get_low_stock_products',

                'description' =>
                    'Mengambil daftar produk aktif yang stoknya berada pada atau di bawah threshold.',

                'parameters' => [

                    'type' => 'OBJECT',

                    'properties' => [

                        'threshold' => [

                            'type' => 'INTEGER',

                            'description' =>
                                'Batas maksimum stok yang dianggap rendah.',

                            'minimum' => 1,

                            'maximum' => 100,

                        ],

                    ],

                    'required' => [

                        'threshold',

                    ],

                ],

            ],


            /*
             * =====================================================
             * 4. RINGKASAN STOK
             * =====================================================
             */
            [

                'name' =>
                    'get_stock_summary',

                'description' =>
                    'Mengambil jumlah produk tersedia, stok rendah, stok habis, dan total unit stok.',

                'parameters' => [

                    'type' => 'OBJECT',

                    'properties' => [

                        'threshold' => [

                            'type' => 'INTEGER',

                            'description' =>
                                'Batas stok rendah.',

                            'minimum' => 1,

                            'maximum' => 100,

                        ],

                    ],

                    'required' => [

                        'threshold',

                    ],

                ],

            ],


            /*
             * =====================================================
             * 5. RINGKASAN PEMBELIAN
             * =====================================================
             */
            [

                'name' =>
                    'get_purchase_summary',

                'description' =>
                    'Mengambil jumlah transaksi pembelian dan total nilai pembelian berdasarkan periode.',

                'parameters' => [

                    'type' => 'OBJECT',

                    'properties' => [

                        'period' => [

                            'type' => 'STRING',

                            'description' =>
                                'Periode analisis pembelian.',

                            'enum' => [

                                'today',

                                'yesterday',

                                'this_week',

                                'last_7_days',

                                'this_month',

                                'last_30_days',

                            ],

                        ],

                    ],

                    'required' => [

                        'period',

                    ],

                ],

            ],

        ];
    }


    /**
     * ============================================================
     * EXECUTE TOOL
     * ============================================================
     */
    private function executeTool(
        string $name,
        array $arguments,
        User $user
    ): array {

        return match ($name) {

            'get_sales_summary' =>
                $this->getSalesSummary(
                    $arguments['period']
                        ?? 'today',
                    $user
                ),

            'get_best_selling_products' =>
                $this->getBestSellingProducts(
                    $arguments['period']
                        ?? 'today',
                    (int) (
                        $arguments['limit']
                            ?? 5
                    ),
                    $user
                ),

            'get_low_stock_products' =>
                $this->getLowStockProducts(
                    (int) (
                        $arguments['threshold']
                            ?? 5
                    ),
                    $user
                ),

            'get_stock_summary' =>
                $this->getStockSummary(
                    (int) (
                        $arguments['threshold']
                            ?? 5
                    ),
                    $user
                ),

            'get_purchase_summary' =>
                $this->getPurchaseSummary(
                    $arguments['period']
                        ?? 'today',
                    $user
                ),

            default => [

                'success' => false,

                'error' =>
                    "Tool '{$name}' tidak tersedia.",

            ],

        };
    }


    /**
     * ============================================================
     * SALES SUMMARY
     * ============================================================
     */
    private function getSalesSummary(
        string $period,
        User $user
    ): array {

        [$start, $end] =
            $this->resolvePeriod(
                $period
            );

        $query =
            Sale::query();

        /*
         * Tenant scope.
         */
        $query =
            $this->applyTenantModelScope(
                $query,
                'sales',
                $user
            );

        $sales =
            $query
                ->whereBetween(
                    'sale_date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                )
                ->get([
                    'id',
                    'total',
                ]);

        /*
         * Hitung total item.
         */
        $productsSold = 0;

        if (
            $sales->isNotEmpty()
        ) {

            $productsSold =
                (int) SaleDetail::query()
                    ->whereIn(
                        'sale_id',
                        $sales->pluck('id')
                    )
                    ->sum(
                        'quantity'
                    );
        }

        $revenue =
            (float) $sales->sum(
                'total'
            );

        $transactions =
            $sales->count();

        $averageTransaction =
            $transactions > 0
                ? $revenue / $transactions
                : 0;

        return [

            'success' => true,

            'period' =>
                $period,

            'date_from' =>
                $start->toDateString(),

            'date_to' =>
                $end->toDateString(),

            'transactions' =>
                $transactions,

            'revenue' =>
                $revenue,

            'products_sold' =>
                $productsSold,

            'average_transaction' =>
                round(
                    $averageTransaction,
                    2
                ),

        ];
    }


    /**
     * ============================================================
     * BEST SELLING PRODUCTS
     * ============================================================
     */
    private function getBestSellingProducts(
        string $period,
        int $limit,
        User $user
    ): array {

        [$start, $end] =
            $this->resolvePeriod(
                $period
            );

        $limit =
            max(
                1,
                min(
                    $limit,
                    10
                )
            );

        $query =
            SaleDetail::query()
                ->join(
                    'sales',
                    'sales.id',
                    '=',
                    'sale_details.sale_id'
                )
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'sale_details.product_id'
                )
                ->whereBetween(
                    'sales.sale_date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                );

        /*
         * Scope sales by business if available.
         */
        if (
            Schema::hasColumn(
                'sales',
                'business_id'
            )
        ) {

            $query->where(
                'sales.business_id',
                $this->businessId(
                    $user
                )
            );

        } elseif (
            $this->businessId($user) !== 1
        ) {

            throw new RuntimeException(
                'Tenant isolation tabel sales belum tersedia.'
            );
        }

        /*
         * Scope products by business if available.
         */
        if (
            Schema::hasColumn(
                'products',
                'business_id'
            )
        ) {

            $query->where(
                'products.business_id',
                $this->businessId(
                    $user
                )
            );

        } elseif (
            $this->businessId($user) !== 1
        ) {

            throw new RuntimeException(
                'Tenant isolation tabel products belum tersedia.'
            );
        }

        $products =
            $query
                ->select(
                    'products.id',
                    'products.name'
                )
                ->selectRaw(
                    'SUM(sale_details.quantity) AS total_sold'
                )
                ->selectRaw(
                    'SUM(sale_details.subtotal) AS total_revenue'
                )
                ->groupBy(
                    'products.id',
                    'products.name'
                )
                ->orderByDesc(
                    'total_sold'
                )
                ->limit(
                    $limit
                )
                ->get();

        return [

            'success' => true,

            'period' =>
                $period,

            'date_from' =>
                $start->toDateString(),

            'date_to' =>
                $end->toDateString(),

            'products' =>
                $products
                    ->map(
                        function ($product) {

                            return [

                                'id' =>
                                    $product->id,

                                'name' =>
                                    $product->name,

                                'total_sold' =>
                                    (int)
                                    $product->total_sold,

                                'total_revenue' =>
                                    (float)
                                    $product->total_revenue,

                            ];
                        }
                    )
                    ->values()
                    ->all(),

        ];
    }


    /**
     * ============================================================
     * LOW STOCK PRODUCTS
     * ============================================================
     */
    private function getLowStockProducts(
        int $threshold,
        User $user
    ): array {

        $threshold =
            max(
                1,
                min(
                    $threshold,
                    100
                )
            );

        $query =
            Product::query()
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'stock',
                    '>',
                    0
                )
                ->where(
                    'stock',
                    '<=',
                    $threshold
                )
                ->orderBy(
                    'stock'
                );

        $query =
            $this->applyTenantModelScope(
                $query,
                'products',
                $user
            );

        $products =
            $query
                ->limit(50)
                ->get([
                    'id',
                    'name',
                    'stock',
                    'price',
                ]);

        return [

            'success' => true,

            'threshold' =>
                $threshold,

            'count' =>
                $products->count(),

            'products' =>
                $products
                    ->map(
                        function ($product) {

                            return [

                                'id' =>
                                    $product->id,

                                'name' =>
                                    $product->name,

                                'stock' =>
                                    (int)
                                    $product->stock,

                                'price' =>
                                    (float)
                                    $product->price,

                            ];
                        }
                    )
                    ->values()
                    ->all(),

        ];
    }


    /**
     * ============================================================
     * STOCK SUMMARY
     * ============================================================
     */
    private function getStockSummary(
        int $threshold,
        User $user
    ): array {

        $threshold =
            max(
                1,
                min(
                    $threshold,
                    100
                )
            );

        $query =
            Product::query()
                ->where(
                    'is_active',
                    true
                );

        $query =
            $this->applyTenantModelScope(
                $query,
                'products',
                $user
            );

        $products =
            $query->get([
                'id',
                'stock',
            ]);

        $totalProducts =
            $products->count();

        $outOfStock =
            $products
                ->where(
                    'stock',
                    0
                )
                ->count();

        $lowStock =
            $products
                ->filter(
                    function ($product)
                        use ($threshold) {

                        return
                            $product->stock > 0
                            &&
                            $product->stock <= $threshold;
                    }
                )
                ->count();

        $available =
            $products
                ->filter(
                    function ($product)
                        use ($threshold) {

                        return
                            $product->stock >
                            $threshold;
                    }
                )
                ->count();

        $totalUnits =
            (int) $products->sum(
                'stock'
            );

        return [

            'success' =>
                true,

            'threshold' =>
                $threshold,

            'total_products' =>
                $totalProducts,

            'available_products' =>
                $available,

            'low_stock_products' =>
                $lowStock,

            'out_of_stock_products' =>
                $outOfStock,

            'total_units' =>
                $totalUnits,

        ];
    }


    /**
     * ============================================================
     * PURCHASE SUMMARY
     * ============================================================
     */
    private function getPurchaseSummary(
        string $period,
        User $user
    ): array {

        if (
            !Schema::hasTable(
                'purchases'
            )
        ) {

            return [

                'success' =>
                    false,

                'error' =>
                    'Tabel purchases tidak tersedia.',

            ];
        }

        if (
            !Schema::hasColumn(
                'purchases',
                'purchase_date'
            )
        ) {

            return [

                'success' =>
                    false,

                'error' =>
                    'Kolom purchase_date tidak tersedia.',

            ];
        }

        if (
            !Schema::hasColumn(
                'purchases',
                'total'
            )
        ) {

            return [

                'success' =>
                    false,

                'error' =>
                    'Kolom total pada purchases tidak tersedia.',

            ];
        }

        [$start, $end] =
            $this->resolvePeriod(
                $period
            );

        $query =
            DB::table(
                'purchases'
            )->when(app(\App\Tenancy\OutletContext::class)->id(),fn($q,$id)=>$q->where('purchases.outlet_id',$id));

        /*
         * Tenant scope jika tabel sudah
         * memiliki business_id.
         */
        if (
            Schema::hasColumn(
                'purchases',
                'business_id'
            )
        ) {

            $query->where(
                'purchases.business_id',
                $this->businessId(
                    $user
                )
            );

        } elseif (
            $this->businessId($user) !== 1
        ) {

            throw new RuntimeException(
                'Tenant isolation tabel purchases belum tersedia.'
            );
        }

        $purchases =
            $query
                ->whereBetween(
                    'purchase_date',
                    [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]
                )
                ->get([
                    'id',
                    'total',
                ]);

        return [

            'success' =>
                true,

            'period' =>
                $period,

            'date_from' =>
                $start->toDateString(),

            'date_to' =>
                $end->toDateString(),

            'transactions' =>
                $purchases->count(),

            'total_purchase' =>
                (float)
                $purchases->sum(
                    'total'
                ),

        ];
    }


    /**
     * ============================================================
     * PERIOD RESOLVER
     * ============================================================
     */
    private function resolvePeriod(
        string $period
    ): array {

        $today =
            Carbon::today();

        return match (
            $period
        ) {

            'today' => [

                $today->copy(),

                $today->copy(),

            ],

            'yesterday' => [

                $today
                    ->copy()
                    ->subDay(),

                $today
                    ->copy()
                    ->subDay(),

            ],

            'this_week' => [

                $today
                    ->copy()
                    ->startOfWeek(),

                $today->copy(),

            ],

            'last_7_days' => [

                $today
                    ->copy()
                    ->subDays(6),

                $today->copy(),

            ],

            'this_month' => [

                $today
                    ->copy()
                    ->startOfMonth(),

                $today->copy(),

            ],

            'last_30_days' => [

                $today
                    ->copy()
                    ->subDays(29),

                $today->copy(),

            ],

            default => [

                $today->copy(),

                $today->copy(),

            ],

        };
    }


    /**
     * ============================================================
     * TENANT SCOPE ELOQUENT
     * ============================================================
     */
    private function applyTenantModelScope(
        $query,
        string $table,
        User $user
    ) {

        $businessId =
            $this->businessId(
                $user
            );

        /*
         * Kalau tabel sudah punya business_id,
         * selalu gunakan scope.
         */
        if (
            Schema::hasColumn(
                $table,
                'business_id'
            )
        ) {

            return $query->where(
                "{$table}.business_id",
                $businessId
            );
        }

        /*
         * Kondisi project saat ini:
         * Toko Default = business_id 1.
         *
         * Jangan membaca data unscoped untuk tenant
         * lain karena bisa menyebabkan cross-tenant access.
         */
        if (
            $businessId !== 1
        ) {

            throw new RuntimeException(
                "Tenant isolation tabel {$table} belum tersedia."
            );
        }

        return $query;
    }


    /**
     * ============================================================
     * BUSINESS ID
     * ============================================================
     */
    private function businessId(
        User $user
    ): int {

        return (int) (
            app(\App\Tenancy\TenantContext::class)->id() ?? $user->business_id ?? 1
        );
    }


    /**
     * ============================================================
     * USER ROLE
     * ============================================================
     */
    private function userRole(
        User $user
    ): string {

        try {

            return $user
                ->getRoleNames()
                ->implode(', ');

        } catch (
            \Throwable
        ) {

            return 'User';
        }
    }


    /**
     * ============================================================
     * SYSTEM DATE
     * ============================================================
     */
    private function today(): string
    {
        return Carbon::now()
            ->format(
                'd-m-Y H:i:s'
            );
    }


    /**
     * ============================================================
     * EXTRACT TEXT
     * ============================================================
     */
    private function extractText(
        array $content
    ): string {

        $text = '';

        foreach (
            ($content['parts'] ?? [])
            as $part
        ) {

            if (
                isset(
                    $part['text']
                )
            ) {

                $text .=
                    $part['text']
                    . "\n";
            }
        }

        return trim($text);
    }


    /**
     * ============================================================
     * SESSION HISTORY
     * ============================================================
     */
    private function getConversationHistory(): array
    {
        $history =
            session(
                'ai_conversation_history',
                []
            );

        return is_array(
            $history
        )
            ? $history
            : [];
    }


    /**
     * ============================================================
     * SAVE CONVERSATION
     * ============================================================
     */
    private function saveConversation(
        string $userMessage,
        string $assistantMessage
    ): void {

        $history =
            $this->getConversationHistory();

        $history[] = [

            'role' =>
                'user',

            'parts' => [

                [
                    'text' =>
                        $userMessage,
                ],

            ],

        ];

        $history[] = [

            'role' =>
                'model',

            'parts' => [

                [
                    'text' =>
                        $assistantMessage,
                ],

            ],

        ];

        /*
         * Simpan maksimal 20 message entries.
         */
        if (
            count($history) > 20
        ) {

            $history =
                array_slice(
                    $history,
                    -20
                );
        }

        session([
            'ai_conversation_history' =>
                $history,
        ]);
    }


    /**
     * ============================================================
     * RESET
     * ============================================================
     */
    public function reset(): void
    {
        session()->forget(
            'ai_conversation_history'
        );
    }
}
