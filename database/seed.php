<?php

require_once __DIR__.'/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__.'/..')->load();
require_once __DIR__.'/../core/Database.php';
\Core\Database::init();
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Core\Database;

$cat = [];
foreach (['Makanan Berat', 'Camilan', 'Minuman'] as $n) {
    $cat[$n] = Category::firstOrCreate(['name' => $n], ['slug' => strtolower(str_replace(' ', '-', $n))]);
}
$data = [['Dapur Nusantara', 'Masakan rumahan hangat dengan resep warisan keluarga.', 'Nasi Campur Bali', 'Makanan Berat', 28000, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800'], ['Kedai Rasa', 'Camilan gurih, manis, dan selalu fresh setiap hari.', 'Pisang Goreng Keju', 'Camilan', 18000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=800'], ['Segar Sari', 'Minuman segar dibuat dari bahan pilihan terbaik.', 'Es Kopi Gula Aren', 'Minuman', 22000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800'], ['Dapur Nusantara', 'Masakan rumahan hangat dengan resep warisan keluarga.', 'Ayam Bakar Madu', 'Makanan Berat', 32000, 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=800'], ['Kedai Rasa', 'Camilan gurih, manis, dan selalu fresh setiap hari.', 'Dimsum Ayam', 'Camilan', 25000, 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=800'], ['Segar Sari', 'Minuman segar dibuat dari bahan pilihan terbaik.', 'Jus Alpukat', 'Minuman', 20000, 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=800'], ['Dapur Nusantara', 'Masakan rumahan hangat dengan resep warisan keluarga.', 'Soto Ayam', 'Makanan Berat', 24000, 'https://images.unsplash.com/photo-1547592180-85f173990554?w=800'], ['Kedai Rasa', 'Camilan gurih, manis, dan selalu fresh setiap hari.', 'Donat Gula', 'Camilan', 15000, 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=800'], ['Segar Sari', 'Minuman segar dibuat dari bahan pilihan terbaik.', 'Matcha Latte', 'Minuman', 26000, 'https://images.unsplash.com/photo-1515823662972-da6a2e4d3002?w=800'], ['Dapur Nusantara', 'Masakan rumahan hangat dengan resep warisan keluarga.', 'Nasi Goreng Spesial', 'Makanan Berat', 27000, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=800']];
$stores = [];
$booths = ['Dapur Nusantara' => 'A1', 'Kedai Rasa' => 'A2', 'Segar Sari' => 'A3'];
foreach (['Dapur Nusantara', 'Kedai Rasa', 'Segar Sari'] as $i => $name) {
    $u = User::firstOrCreate(['email' => 'demo'.($i + 1).'@rasa.test'], ['name' => 'Pemilik '.$name, 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'seller', 'status' => 'active']);
    $stores[$name] = Store::firstOrCreate(['name' => $name], ['user_id' => $u->id, 'description' => $data[$i][1], 'address' => 'Jakarta Selatan', 'opening_hours' => '08:00 - 21:00', 'approved' => true, 'booth_number' => $booths[$name]]);
    if (empty($stores[$name]->booth_number)) {
        $stores[$name]->update(['booth_number' => $booths[$name]]);
    }
}
foreach ($data as $p) {
    Product::firstOrCreate(['name' => $p[2]], ['store_id' => $stores[$p[0]]->id, 'category_id' => $cat[$p[3]]->id, 'description' => 'Dibuat segar dengan bahan pilihan.', 'price' => $p[4], 'stock' => 25, 'image' => $p[5], 'active' => true]);
} echo "Seed selesai: 3 toko, 10 produk\n";
