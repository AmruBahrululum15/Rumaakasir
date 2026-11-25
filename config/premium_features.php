<?php

// Implementation readiness is controlled by code, never by an admin checkbox alone.
$core = [
    'advanced_dashboard' => ['Advanced Dashboard', 'Analitik', true],
    'advanced_reports' => ['Advanced Reports', 'Analitik', true],
    'export_excel' => ['Export Excel', 'Analitik', true],
    'export_pdf' => ['Export PDF', 'Analitik', true],
    'export_csv' => ['Export CSV Penjualan', 'Analitik', true],
    'custom_branding' => ['Custom Branding', 'Branding', true],
    'customer_loyalty' => ['Loyalty & Poin Customer', 'Customer', true],
    'customer_membership' => ['Membership Customer', 'Customer', false],
    'promotions' => ['Promo & Diskon', 'Customer', true],
    'scheduled_promotions' => ['Promo Terjadwal', 'Customer', true],
    'vouchers' => ['Voucher', 'Customer', true],
    'low_stock_alert' => ['Notifikasi Minimum Stok', 'Manajemen', false],
    'smart_stock_alert' => ['Smart Stock Recommendation', 'Manajemen', false],
    'audit_log' => ['Audit Aktivitas Toko', 'Manajemen', false],
    'approval_transactions' => ['Approval Transaksi', 'Manajemen', true],
    'custom_roles' => ['Role Kustom Toko', 'Manajemen', false],
    'email_receipt' => ['Email Receipt', 'Customer', false],
    'whatsapp_receipt' => ['WhatsApp Receipt', 'Customer', false],
    'bulk_import' => ['Import Data Massal', 'Manajemen', false],
    'bulk_export' => ['Export Data Massal', 'Manajemen', false],
    'backup_tools' => ['Backup Data Terstruktur', 'Manajemen', false],
    'api_access' => ['API Access', 'Manajemen', false],
    'multi_outlet' => ['Multi Outlet', 'Manajemen', true],
    'inter_outlet_stock_transfer' => ['Transfer Stok Antar Outlet', 'Manajemen', true],
    'advanced_notifications' => ['Notifikasi Lanjutan', 'Manajemen', false],
    'ai_business_assistant' => ['AI Business Assistant', 'Analitik', true],
];
$features = [];
foreach ($core as $key => [$label, $group, $implemented]) {
    $features[$key] = compact('label', 'group', 'implemented') + ['types' => null, 'status' => $implemented ? 'available' : 'disabled'];
}
foreach (['advanced_dashboard'=>'premium.dashboard','advanced_reports'=>'premium.reports','export_csv'=>'premium.csv'] as $key=>$route) $features[$key]['route'] = $route;
foreach (['scheduled_promotions','vouchers'] as $key) $features[$key]['inherits']='promotions';
$features['ai_business_assistant']['package_flag'] = 'ai_agent';
$features['ai_business_assistant']['provider'] = 'services.gemini.api_key';
foreach (['export_excel','export_pdf','custom_branding'] as $key) $features[$key]['package_flag'] = $key;
$specific = [
    'retail,minimarket,fashion,bengkel' => [
        'barcode'=>'Barcode', 'quick_scan'=>'Quick Scan', 'stock_opname'=>'Stock Opname',
        'supplier_analytics'=>'Supplier Analytics', 'purchase_recommendation'=>'Rekomendasi Pembelian',
    ],
    'cafe,restaurant,kedai' => [
        'qr_order'=>'QR Order', 'dine_in'=>'Dine In', 'takeaway'=>'Take Away', 'menu_modifiers'=>'Menu Modifier',
        'toppings'=>'Topping / Add-on', 'order_notes'=>'Catatan Pesanan Lanjutan', 'kitchen_status'=>'Status Dapur',
        'kitchen_display'=>'Kitchen Display', 'split_bill'=>'Split Bill', 'merge_table'=>'Gabung Meja',
        'table_reservations'=>'Reservasi Meja', 'menu_analytics'=>'Analitik Menu',
    ],
    'fashion' => ['product_variants'=>'Varian Produk', 'size'=>'Ukuran', 'color'=>'Warna', 'variant_sku'=>'SKU Varian'],
    'laundry' => ['laundry_services'=>'Layanan Laundry', 'laundry_weight'=>'Layanan Kiloan', 'laundry_units'=>'Layanan Satuan',
        'laundry_status'=>'Status Cucian', 'laundry_tracking'=>'Tracking Laundry', 'laundry_estimation'=>'Estimasi Selesai',
        'laundry_pickup'=>'Pickup Laundry', 'laundry_delivery'=>'Delivery Laundry'],
    'bengkel' => ['vehicles'=>'Kendaraan / Nomor Polisi', 'mechanics'=>'Mekanik', 'sparepart_usage'=>'Sparepart per Service',
        'service_history'=>'Riwayat Service', 'service_reminders'=>'Reminder Service'],
    'salon,barbershop' => ['booking'=>'Booking', 'staff_schedule'=>'Jadwal Staff', 'staff'=>'Staff / Terapis / Barber',
        'staff_services'=>'Layanan per Staff', 'staff_commissions'=>'Komisi Staff', 'booking_reminders'=>'Reminder Booking'],
    'bengkel,jasa' => ['service_orders'=>'Service Order', 'job_status'=>'Status Pekerjaan', 'invoice'=>'Invoice Service'],
    'jasa' => ['deadlines'=>'Deadline Pekerjaan', 'job_notes'=>'Catatan Pekerjaan', 'attachments'=>'Lampiran Pekerjaan', 'email_invoice'=>'Email Invoice'],
    'salon,barbershop,jasa' => ['customer_history'=>'Riwayat Layanan Customer'],
];
foreach ($specific as $types => $items) foreach ($items as $key => $label) {
    $features[$key] = ['label'=>$label, 'group'=>'Operasional', 'types'=>explode(',', $types), 'implemented'=>false, 'status'=>'disabled'];
}
foreach (['qr_order','dine_in','takeaway'] as $key) {
    $features[$key]['implemented'] = true;
    $features[$key]['status'] = 'available';
    $features[$key]['package_flag'] = 'qr_order';
}
foreach (['dine_in','takeaway'] as $key) $features[$key]['inherits'] = 'qr_order';
foreach (['menu_analytics'=>'advanced_dashboard','supplier_analytics'=>'advanced_reports'] as $key=>$parent) {
    $features[$key]['implemented'] = true;
    $features[$key]['status'] = 'available';
    $features[$key]['inherits'] = $parent;
    $features[$key]['group'] = 'Analitik';
}
foreach (['laundry_services','laundry_weight','laundry_units','laundry_status','laundry_tracking','laundry_estimation','laundry_pickup'] as $key) {
    $features[$key]['implemented']=true; $features[$key]['status']='available';
    if($key!=='laundry_services') $features[$key]['inherits']='laundry_services';
}
$features['laundry_pickup']['label']='Pengambilan Laundry';
$features['booking']['implemented']=true; $features['booking']['status']='available'; $features['booking']['types']=['salon','barbershop','jasa'];
$features['inter_outlet_stock_transfer']['inherits']='multi_outlet';
foreach ([
    'customer_loyalty'=>['Loyalty Customer','loyalty.index','loyalty.*','customers.view',[],'fa-star'],
    'promotions'=>['Promo & Voucher','promotions.index','promotions.*','settings.edit',['Atasan','Admin'],'fa-tags'],
    'approval_transactions'=>['Approval Refund / Void','sale-approvals.index','sale-approvals.*','sales.create',['Atasan','Admin','Kasir'],'fa-check-circle'],
    'laundry_services'=>['Laundry','laundry.index','laundry.*','sales.create',['Atasan','Admin','Kasir'],'fa-tshirt'],
    'booking'=>['Reservasi','bookings.index','bookings.*','sales.create',['Atasan','Admin','Kasir'],'fa-calendar'],
    'multi_outlet'=>['Multi Outlet','outlets.index','outlets.*','settings.edit',['Atasan'],'fa-store'],
] as $key=>[$label,$route,$active,$permission,$roles,$icon]) $features[$key]['navigation']=compact('label','route','active','permission','roles','icon');
foreach (['customer_portal'=>'Customer Portal','customer_chat'=>'Chat Customer','customer_booking'=>'Booking Customer','customer_vouchers'=>'Voucher Customer'] as $key=>$label) {
    $features[$key]=['label'=>$label,'group'=>'Customer','types'=>null,'implemented'=>true,'status'=>'available'];
}
$features['customer_booking']['types']=null;
$features['booking']['types']=null;
$features['customer_portal']['navigation']=['label'=>'Order Customer','route'=>'portal-orders.index','active'=>'portal-orders.*','permission'=>'sales.view','roles'=>[],'icon'=>'fa-shopping-bag'];
$features['customer_chat']['navigation']=['label'=>'Chat Customer','route'=>'customer-chat.index','active'=>'customer-chat.*','permission'=>'customer_chat.view','roles'=>[],'icon'=>'fa-comments'];
// Notes in the existing POS remain available as before; extended notes/analytics
// are not rebranded as a new paid feature without a dedicated implementation.
return ['features'=>$features, 'aliases'=>['ai_agent'=>'ai_business_assistant'], 'states'=>['available','disabled']];
