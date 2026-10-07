# Framework NORION

**NORION** adalah Framework MVC PHP yang ringan dan modern, dirancang untuk kecepatan dan kesederhanaan. Framework ini dilengkapi dengan alat-alat canggih untuk mempercepat pengembangan aplikasi Anda.

## 🚀 Fitur

- **Arsitektur MVC**: Pemisahan logika yang bersih (Model-View-Controller).
- **Twig Template Engine**: Templating yang cepat, aman, dan fleksibel.
- **Eloquent ORM**: Implementasi ActiveRecord yang kuat dari Laravel untuk interaksi database.
- **Sistem Migrasi**: Manajemen skema database yang sederhana dan kompatibel dengan Eloquent.
- **Integrasi Bootstrap 5**: Komponen UI responsif yang siap digunakan.
- **Routing Tanpa Konfigurasi**: Routing otomatis berdasarkan nama controller dan method (`/controller/method/param`).

## 📦 Instalasi

1. **Clone repositori**:
   ```bash
   git clone <url-repositori> norion
   cd norion
   ```

2. **Install Dependensi**:
   ```bash
   composer install
   ```

3. **Konfigurasi Database**:
   Edit file `config/config.php` dengan kredensial database Anda:
   ```php
   return [
       'host' => '127.0.0.1',
       'dbname' => 'nama_database_anda',
       'user' => 'root',
       'pass' => '',
   ];
   ```

4. **Setup Database**:
   Jalankan skrip pembatu untuk membuat database jika belum ada:
   ```bash
   php setup_db.php
   ```

5. **Jalankan Migrasi**:
   ```bash
   php public/migrate.php
   ```

6. **Jalankan Aplikasi**:
   ```bash
   php -S localhost:8000 -t public
   ```
   Buka `http://localhost:8000` di browser Anda.

## 📁 Struktur Direktori

- `app/`: Kode aplikasi Anda.
    - `Controllers/`: Menangani logika dan mengembalikan tampilan (views).
    - `Models/`: Model Eloquent (Tabel Database).
    - `Views/`: Template Twig.
- `config/`: File konfigurasi.
- `core/`: File inti framework (Jangan diubah kecuali Anda tahu apa yang Anda lakukan).
- `database/migrations/`: Definisi skema database.
- `public/`: Titik masuk web (`index.php`) dan aset.

## 🛠 Penggunaan

### Controller
Buat controller baru di `app/Controllers/`. Nama class harus diakhiri dengan `Controller`.

```php
namespace App\Controllers;
use Core\Controller;

class BlogController extends Controller
{
    public function index()
    {
        $this->view('blog/index', ['title' => 'Blog Saya']);
    }
}
```

### Routing
Route mengikuti pola: `http://localhost:8000/NamaController/NamaMethod/Parameter`.
- `/user` -> `UserController::index()`
- `/user/create` -> `UserController::create()`
- `/blog/show/1` -> `BlogController::show(1)`

### Model
Buat model Eloquent di `app/Models/`.

```php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content'];
}
```

### View
Buat template Twig di `app/Views/`. Anda bisa mewarisi (extend) layout utama.

```twig
{% extends "layout.twig" %}

{% block content %}
    <h1>{{ title }}</h1>
{% endblock %}
```

### Migrasi
Buat file migrasi di `database/migrations/`. 

```php
use Illuminate\Database\Capsule\Manager as Capsule;

class _002_create_posts
{
    public function up()
    {
        Capsule::schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }
}
```

## 📜 Lisensi
NORION adalah perangkat lunak open-source di bawah lisensi MIT.
