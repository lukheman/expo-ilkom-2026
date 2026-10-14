<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

use App\Models\Category;
use App\Models\User;
use Core\Database;

require_once __DIR__.'/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__.'/..')->load();
require_once __DIR__.'/../core/Database.php';
Database::init();

User::firstOrCreate(['username' => 'admin'], ['name' => 'Admin Expo', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'admin']);

$defaultImages = [
    'Makanan Berat' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=200&q=60&auto=format&fit=crop',
    'Camilan' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=200&q=60&auto=format&fit=crop',
    'Minuman' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=200&q=60&auto=format&fit=crop',
];

foreach ($defaultImages as $n => $img) {
    $c = Category::firstOrCreate(['name' => $n], ['slug' => strtolower(str_replace(' ', '-', $n)), 'image' => $img]);
    if (! $c->image) {
        $c->update(['image' => $img]);
    }
}
echo "Seed selesai: akun admin + 3 kategori\n";
