<?php

$catalog = ['products', 'categories', 'inventory', 'reports'];
$retail = [...$catalog, 'suppliers', 'purchases', 'barcode'];
$food = [...$catalog, 'suppliers', 'purchases', 'tables', 'qr_order', 'dine_in', 'takeaway'];

return [
    'core' => ['dashboard', 'pos', 'customers', 'settings', 'profile', 'subscriptions', 'users', 'notifications', 'ai_agent'],
    'types' => [
        'retail' => ['label' => 'Retail', 'icon' => 'fa-store', 'modules' => $retail],
        'minimarket' => ['label' => 'Minimarket', 'icon' => 'fa-shopping-basket', 'modules' => $retail],
        'cafe' => ['label' => 'Cafe', 'icon' => 'fa-coffee', 'modules' => $food],
        'restaurant' => ['label' => 'Restoran', 'icon' => 'fa-utensils', 'modules' => $food],
        'kedai' => ['label' => 'Kedai', 'icon' => 'fa-mug-hot', 'modules' => $food],
        'fashion' => ['label' => 'Fashion', 'icon' => 'fa-tshirt', 'modules' => [...$retail, 'product_variants']],
        'laundry' => ['label' => 'Laundry', 'icon' => 'fa-soap', 'modules' => [...$catalog, 'services', 'laundry_services', 'laundry_weight', 'laundry_status', 'laundry_pickup']],
        'bengkel' => ['label' => 'Bengkel', 'icon' => 'fa-tools', 'modules' => [...$retail, 'vehicles', 'mechanics', 'service_orders']],
        'salon' => ['label' => 'Salon', 'icon' => 'fa-cut', 'modules' => [...$catalog, 'services', 'staff', 'booking']],
        'barbershop' => ['label' => 'Barbershop', 'icon' => 'fa-cut', 'modules' => [...$catalog, 'services', 'staff', 'booking']],
        'jasa' => ['label' => 'Jasa', 'icon' => 'fa-briefcase', 'modules' => [...$catalog, 'services', 'invoice']],
        'lainnya' => ['label' => 'Lainnya', 'icon' => 'fa-shapes', 'modules' => $catalog],
    ],
    // Earlier applications retain their stored values; only new writes are normalized.
    'aliases' => ['elektronik' => 'retail', 'apotek' => 'retail', 'kafe' => 'cafe', 'restoran' => 'restaurant'],
    'legacy' => array_unique([...$retail, ...$food]),
    'planned' => ['barcode', 'product_variants', 'services', 'laundry_services', 'laundry_weight', 'laundry_status', 'laundry_pickup', 'vehicles', 'mechanics', 'service_orders', 'staff', 'booking', 'invoice'],
    'labels' => [
        'dashboard' => 'Dashboard', 'pos' => 'Penjualan / Transaksi', 'customers' => 'Pelanggan', 'settings' => 'Pengaturan',
        'profile' => 'Profil', 'subscriptions' => 'Paket & Langganan', 'users' => 'Manajemen Pengguna', 'notifications' => 'Notifikasi', 'ai_agent' => 'AI Agent',
        'products' => 'Produk / Menu', 'categories' => 'Kategori', 'suppliers' => 'Supplier', 'purchases' => 'Pembelian', 'inventory' => 'Stok (pada Produk)',
        'tables' => 'Meja', 'qr_order' => 'QR Order', 'dine_in' => 'Dine In (QR)', 'takeaway' => 'Take Away (QR)', 'reports' => 'Laporan',
        'barcode' => 'Barcode', 'product_variants' => 'Varian / Ukuran / Warna', 'services' => 'Layanan', 'laundry_services' => 'Transaksi Laundry',
        'laundry_weight' => 'Berat / Kg', 'laundry_status' => 'Status Cucian', 'laundry_pickup' => 'Pengambilan Laundry',
        'vehicles' => 'Kendaraan', 'mechanics' => 'Mekanik', 'service_orders' => 'Service Order', 'booking' => 'Booking', 'staff' => 'Staff', 'invoice' => 'Invoice Jasa',
    ],
    'premium_only' => ['qr_order', 'dine_in', 'takeaway'],
    'feature_labels' => ['export_excel' => 'Export Excel', 'export_pdf' => 'Export PDF', 'custom_branding' => 'Custom Branding', 'ai_agent' => 'AI Agent', 'qr_order' => 'QR Order'],
    'package_modules' => ['export_excel' => 'reports', 'export_pdf' => 'reports', 'custom_branding' => 'settings', 'ai_agent' => 'ai_agent', 'qr_order' => 'qr_order'],
    'module_packages' => ['qr_order' => 'qr_order', 'dine_in' => 'qr_order', 'takeaway' => 'qr_order', 'ai_agent' => 'ai_agent'],
    'routes' => ['products.*' => 'products', 'categories.*' => 'categories', 'suppliers.*' => 'suppliers', 'purchases.*' => 'purchases',
        'tables.*' => 'tables', 'reports.*' => 'reports', 'sales.*' => 'pos', 'customers.*' => 'customers'],
];
