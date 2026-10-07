<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Core\Controller;

class SellerController extends Controller
{
    private function user()
    {
        return isset($_SESSION['user']) ? User::find($_SESSION['user']) : null;
    }

    private function seller()
    {
        $u = $this->user();
        if (! $u || $u->role !== 'seller') {
            header('Location: /auth/login');

            return [null, null];
        }
        if ($u->status !== 'active') {
            $this->view('seller/waiting', ['title' => 'Menunggu Persetujuan', 'status' => $u->status]);

            return [null, null];
        }

        return [$u, Store::where('user_id', $u->id)->first()];
    }

    private function sellerView($tpl, $menu, $store, $data = [])
    {
        $data['menu'] = $menu;
        $data['chrome'] = 'seller';
        $data['panel'] = 'Kelola Stan';
        $data['store'] = $store;
        $data['activeOrders'] = $store ? Order::where('store_id', $store->id)->whereIn('status', ['Menunggu', 'Diproses'])->count() : 0;
        $this->view($tpl, $data);
    }

    public function index()
    {
        [$u,$s] = $this->seller();
        if (! $u) {
            return;
        }
        $orders = $s ? Order::where('store_id', $s->id)->orderBy('id', 'desc')->limit(20)->get() : [];
        $today = date('Y-m-d');
        $this->sellerView('seller/dashboard', 'dashboard', $s, [
            'title' => 'Dashboard Penjual',
            'orders' => $orders,
            'revenueToday' => $s ? (float) Order::where('store_id', $s->id)->whereDate('created_at', $today)->where('status', '!=', 'Dibatalkan')->sum('total') : 0,
            'revenueTotal' => $s ? (float) Order::where('store_id', $s->id)->where('status', '!=', 'Dibatalkan')->sum('total') : 0,
            'doneCount' => $s ? Order::where('store_id', $s->id)->where('status', 'Selesai')->count() : 0,
            'productCount' => $s ? Product::where('store_id', $s->id)->where('active', true)->count() : 0,
        ]);
    }

    public function orders()
    {
        [$u,$s] = $this->seller();
        if (! $u) {
            return;
        }
        $status = $_GET['status'] ?? '';
        $query = Order::where('store_id', $s->id)->orderBy('id', 'desc')->limit(200);
        if (in_array($status, ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'], true)) {
            $query->where('status', $status);
        }
        $this->sellerView('seller/orders', 'orders', $s, ['title' => 'Pesanan Masuk', 'orders' => $query->get(), 'status' => $status]);
    }

    private function ownOrder($id)
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            return null;
        }
        $s = Store::where('user_id', $u->id)->first();
        if (! $s) {
            return null;
        }

        return Order::where('id', $id)->where('store_id', $s->id)->first();
    }

    public function status($id, $to)
    {
        $allowed = ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
        $to = urldecode($to);
        $o = $this->ownOrder($id);
        if ($o && in_array($to, $allowed, true)) {
            $o->update(['status' => $to]);
        }
        header('Location: /seller/orders');
    }

    public function paid($id)
    {
        $o = $this->ownOrder($id);
        if ($o) {
            $o->update(['payment_status' => 'Lunas']);
        }
        header('Location: /seller/orders');
    }

    public function products()
    {
        [$u,$s] = $this->seller();
        if (! $u) {
            return;
        }
        $uploadMsg = ['besar' => 'Foto terlalu besar (maks 2MB).', 'tipe' => 'Format foto harus JPG, PNG, WebP, atau GIF.', 'gagal' => 'Upload foto gagal, coba lagi.'][$_GET['upload'] ?? ''] ?? null;
        $this->sellerView('seller/products', 'products', $s, [
            'title' => 'Kelola Produk',
            'products' => $s ? Product::where('store_id', $s->id)->orderBy('id', 'desc')->get() : [],
            'categories' => Category::all(),
            'uploadMsg' => $uploadMsg,
        ]);
    }

    private function uploadImage($file, &$error)
    {
        $error = null;
        if (! $file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'gagal';

            return null;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            $error = 'besar';

            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = $finfo->file($file['tmp_name']);
        if (! isset($map[$mime]) || ! @getimagesize($file['tmp_name'])) {
            $error = 'tipe';

            return null;
        }
        $dir = __DIR__.'/../../public/uploads';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(12)).'.'.$map[$mime];
        if (! move_uploaded_file($file['tmp_name'], $dir.'/'.$name)) {
            $error = 'gagal';

            return null;
        }

        return '/uploads/'.$name;
    }

    private function removeLocalImage($path)
    {
        if ($path && str_starts_with($path, '/uploads/')) {
            @unlink(__DIR__.'/../../public'.$path);
        }
    }

    public function save()
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            header('Location: /seller');

            return;
        }
        $s = Store::firstOrCreate(['user_id' => $u->id], ['name' => trim($_POST['store'] ?? 'Stan '.$u->name), 'approved' => true]);
        if (isset($_POST['booth']) && $_POST['booth'] !== '') {
            $s->update(['booth_number' => substr(trim($_POST['booth']), 0, 10)]);
        }
        $image = $this->uploadImage($_FILES['photo'] ?? null, $uploadError);
        if ($uploadError) {
            header('Location: /seller/products?upload='.$uploadError);

            return;
        }
        Product::create([
            'store_id' => $s->id,
            'category_id' => $_POST['category'] ?: null,
            'name' => substr(trim($_POST['name'] ?? ''), 0, 100),
            'description' => substr(trim($_POST['description'] ?? ''), 0, 500),
            'price' => max(0, (int) ($_POST['price'] ?? 0)),
            'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
            'image' => $image ?? (substr(trim($_POST['image'] ?? ''), 0, 255) ?: null),
            'active' => true,
        ]);
        header('Location: /seller/products');
    }

    public function edit($id)
    {
        [$u,$s] = $this->seller();
        if (! $u) {
            return;
        }
        $p = Product::where('id', (int) $id)->where('store_id', $s->id)->first();
        if (! $p) {
            header('Location: /seller/products');

            return;
        }
        $this->sellerView('seller/product_form', 'products', $s, ['title' => 'Ubah Produk', 'p' => $p, 'categories' => Category::all()]);
    }

    public function update($id)
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            header('Location: /seller');

            return;
        }
        $s = Store::where('user_id', $u->id)->first();
        $p = Product::where('id', (int) $id)->where('store_id', $s->id)->first();
        if ($p) {
            $image = $this->uploadImage($_FILES['photo'] ?? null, $uploadError);
            if ($uploadError) {
                header('Location: /seller/products?upload='.$uploadError);

                return;
            }
            $url = substr(trim($_POST['image'] ?? ''), 0, 255);
            $newImage = $image ?? ($url !== '' ? $url : $p->image);
            if ($image && $newImage !== $p->image) {
                $this->removeLocalImage($p->image);
            }
            $p->update([
                'category_id' => $_POST['category'] ?: null,
                'name' => substr(trim($_POST['name'] ?? $p->name), 0, 100),
                'description' => substr(trim($_POST['description'] ?? ''), 0, 500),
                'price' => max(0, (int) ($_POST['price'] ?? $p->price)),
                'stock' => max(0, (int) ($_POST['stock'] ?? $p->stock)),
                'image' => $newImage,
            ]);
        }
        header('Location: /seller/products');
    }

    public function toggle($id)
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            header('Location: /seller');

            return;
        }
        $s = Store::where('user_id', $u->id)->first();
        $p = Product::where('id', (int) $id)->where('store_id', $s->id)->first();
        if ($p) {
            $p->update(['active' => ! $p->active]);
        }
        header('Location: /seller/products');
    }

    public function delete($id)
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            header('Location: /seller');

            return;
        }
        $s = Store::where('user_id', $u->id)->first();
        $p = Product::where('id', (int) $id)->where('store_id', $s->id)->first();
        if ($p) {
            $this->removeLocalImage($p->image);
            $p->delete();
        }
        header('Location: /seller/products');
    }

    public function store()
    {
        [$u,$s] = $this->seller();
        if (! $u) {
            return;
        }
        $this->sellerView('seller/store', 'store', $s, ['title' => 'Profil Toko']);
    }

    public function updateStore()
    {
        [$u,$s] = [$this->user(), null];
        if (! $u || $u->role !== 'seller' || $u->status !== 'active') {
            header('Location: /seller');

            return;
        }
        $s = Store::firstOrCreate(['user_id' => $u->id], ['name' => 'Stan '.$u->name, 'approved' => true]);
        $s->update([
            'name' => substr(trim($_POST['name'] ?? $s->name), 0, 100),
            'description' => substr(trim($_POST['description'] ?? ''), 0, 500),
            'booth_number' => substr(trim($_POST['booth'] ?? ''), 0, 10) ?: null,
            'address' => substr(trim($_POST['address'] ?? ''), 0, 255) ?: null,
            'opening_hours' => substr(trim($_POST['hours'] ?? ''), 0, 50) ?: null,
        ]);
        header('Location: /seller/store');
    }
}
