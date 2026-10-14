<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Core\Controller;

class AdminController extends Controller
{
    private function admin()
    {
        $u = isset($_SESSION['user']) ? User::find($_SESSION['user']) : null;
        if (! $u || $u->role !== 'admin') {
            header('Location: /login');

            return null;
        }

        return $u;
    }

    private function adminView($tpl, $menu, $data = [])
    {
        $data['menu'] = $menu;
        $data['chrome'] = 'admin';
        $data['panel'] = 'Admin Expo';
        $this->view($tpl, $data);
    }

    private function usernameOk($username)
    {
        return $username !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $username);
    }

    public function index()
    {
        if (! $this->admin()) {
            return;
        }
        $this->adminView('admin/dashboard', 'dashboard', [
            'title' => 'Admin Expo',
            'sellers' => User::where('role', 'seller')->count(),
            'stores' => Store::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'revenue' => (float) Order::where('status', '!=', 'Dibatalkan')->sum('total'),
        ]);
    }

    public function sellers()
    {
        if (! $this->admin()) {
            return;
        }
        $rows = User::where('role', 'seller')->orderBy('id')->get();
        foreach ($rows as $u) {
            $store = Store::where('user_id', $u->id)->first();
            $u->store = $store;
            $u->store_name = $store->name ?? null;
            $u->booth_number = $store->booth_number ?? null;
            $u->product_count = $store ? Product::where('store_id', $store->id)->count() : 0;
        }
        $this->adminView('admin/sellers', 'sellers', ['title' => 'Kelola Penjual', 'sellers' => $rows]);
    }

    public function storeSeller()
    {
        if (! $this->admin()) {
            return;
        }
        $name = substr(trim($_POST['name'] ?? ''), 0, 100);
        $username = substr(trim($_POST['username'] ?? ''), 0, 50);
        $password = $_POST['password'] ?? '';
        $storeName = substr(trim($_POST['store_name'] ?? ''), 0, 100);
        if ($name !== '' && $this->usernameOk($username) && $password !== '' && ! User::where('username', $username)->exists()) {
            $u = User::create([
                'name' => $name,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'seller',
            ]);
            Store::create([
                'user_id' => $u->id,
                'name' => $storeName !== '' ? $storeName : 'Stan '.$name,
                'approved' => true,
            ]);
            $this->toast('Penjual berhasil ditambahkan.');
        } else {
            $this->toast('Gagal menambahkan. Username sudah terdaftar atau data tidak valid.', 'danger');
        }
        header('Location: /admin/sellers');
    }

    public function updateSeller($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::where('id', (int) $id)->where('role', 'seller')->first();
        if (! $u) {
            $this->toast('Penjual tidak ditemukan.', 'danger');
            header('Location: /admin/sellers');

            return;
        }
        $name = substr(trim($_POST['name'] ?? $u->name), 0, 100);
        $username = substr(trim($_POST['username'] ?? $u->username), 0, 50);
        $data = ['name' => $name !== '' ? $name : $u->name];
        if ($this->usernameOk($username)) {
            $exists = User::where('username', $username)->where('id', '!=', $u->id)->exists();
            if (! $exists) {
                $data['username'] = $username;
            }
        }
        if (trim($_POST['password'] ?? '') !== '') {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        $u->update($data);

        $store = Store::where('user_id', $u->id)->first();
        $storeData = [
            'name' => substr(trim($_POST['store_name'] ?? ($store->name ?? 'Stan '.$data['name'])), 0, 100),
            'booth_number' => substr(trim($_POST['booth_number'] ?? ''), 0, 10) ?: null,
            'approved' => true,
        ];
        if ($store) {
            $store->update($storeData);
        } else {
            $storeData['user_id'] = $u->id;
            Store::create($storeData);
        }
        $this->toast('Penjual berhasil diubah.');
        header('Location: /admin/sellers');
    }

    public function delSeller($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::where('id', (int) $id)->where('role', 'seller')->first();
        if ($u) {
            $store = Store::where('user_id', $u->id)->first();
            if ($store) {
                foreach (Product::where('store_id', $store->id)->get() as $p) {
                    if ($p->image && str_starts_with($p->image, '/uploads/')) {
                        @unlink(__DIR__.'/../../public'.$p->image);
                    }
                    $p->delete();
                }
                $store->delete();
            }
            $u->delete();
            $this->toast('Penjual beserta toko & produknya berhasil dihapus.');
        }
        header('Location: /admin/sellers');
    }

    public function users()
    {
        if (! $this->admin()) {
            return;
        }
        $this->adminView('admin/users', 'users', ['title' => 'Kelola Pengguna', 'users' => User::orderBy('id')->get()]);
    }

    public function storeUser()
    {
        if (! $this->admin()) {
            return;
        }
        $name = substr(trim($_POST['name'] ?? ''), 0, 100);
        $username = substr(trim($_POST['username'] ?? ''), 0, 50);
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? 'seller', ['admin', 'seller'], true) ? $_POST['role'] : 'seller';
        if ($name !== '' && $this->usernameOk($username) && $password !== '' && ! User::where('username', $username)->exists()) {
            User::create([
                'name' => $name,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
            ]);
            $this->toast('Pengguna berhasil ditambahkan.');
        } else {
            $this->toast('Gagal menambahkan. Username sudah terdaftar atau data tidak valid.', 'danger');
        }
        header('Location: /admin/users');
    }

    public function categories()
    {
        if (! $this->admin()) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim($_POST['name'] ?? '') !== '') {
            $name = substr(trim($_POST['name']), 0, 50);
            $image = substr(trim($_POST['image'] ?? ''), 0, 255) ?: null;
            Category::firstOrCreate(['name' => $name], ['slug' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)), 'image' => $image]);
            $this->toast('Kategori berhasil ditambahkan.');
            header('Location: /admin/categories');

            return;
        }
        $this->adminView('admin/categories', 'categories', ['title' => 'Kelola Kategori', 'categories' => Category::orderBy('id')->get()]);
    }

    public function updateCategory($id)
    {
        if (! $this->admin()) {
            return;
        }
        $c = Category::find((int) $id);
        if ($c) {
            $name = substr(trim($_POST['name'] ?? $c->name), 0, 50);
            $data = ['image' => substr(trim($_POST['image'] ?? ''), 0, 255) ?: null];
            if ($name !== '') {
                $data['name'] = $name;
                $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            }
            $c->update($data);
            $this->toast('Kategori berhasil diubah.');
        }
        header('Location: /admin/categories');
    }

    public function delCategory($id)
    {
        if (! $this->admin()) {
            return;
        }
        if (! Product::where('category_id', (int) $id)->exists()) {
            Category::where('id', (int) $id)->delete();
            $this->toast('Kategori berhasil dihapus.');
        } else {
            $this->toast('Kategori masih dipakai produk, tidak bisa dihapus.', 'danger');
        }
        header('Location: /admin/categories');
    }

    public function products()
    {
        if (! $this->admin()) {
            return;
        }
        $rows = Product::orderBy('id', 'desc')->limit(200)->get();
        $stores = Store::orderBy('name')->get();
        $storeMap = [];
        foreach ($stores as $s) {
            $storeMap[$s->id] = $s->name;
        }
        foreach ($rows as $p) {
            $p->store_name = $storeMap[$p->store_id] ?? 'Stan '.$p->store_id;
        }
        $this->adminView('admin/products', 'products', [
            'title' => 'Moderasi Produk',
            'products' => $rows,
            'stores' => $stores,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function storeProduct()
    {
        if (! $this->admin()) {
            return;
        }
        $storeId = (int) ($_POST['store_id'] ?? 0);
        $name = substr(trim($_POST['name'] ?? ''), 0, 100);
        if ($storeId > 0 && $name !== '' && Store::where('id', $storeId)->exists()) {
            Product::create([
                'store_id' => $storeId,
                'category_id' => $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null,
                'name' => $name,
                'description' => substr(trim($_POST['description'] ?? ''), 0, 500),
                'price' => max(0, (int) ($_POST['price'] ?? 0)),
                'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
                'image' => substr(trim($_POST['image'] ?? ''), 0, 255) ?: null,
                'active' => true,
            ]);
            $this->toast('Produk berhasil ditambahkan.');
        } else {
            $this->toast('Gagal menambahkan. Stan atau nama produk tidak valid.', 'danger');
        }
        header('Location: /admin/products');
    }

    public function toggleProduct($id)
    {
        if (! $this->admin()) {
            return;
        }
        $p = Product::find((int) $id);
        if ($p) {
            $p->update(['active' => ! $p->active]);
            if (($_GET['format'] ?? '') === 'json') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'active' => (bool) $p->active, 'label' => $p->active ? 'Aktif' : 'Nonaktif']);

                return;
            }
            $this->toast($p->active ? 'Produk berhasil diaktifkan.' : 'Produk berhasil dinonaktifkan.');
        }
        header('Location: /admin/products');
    }

    public function orders()
    {
        if (! $this->admin()) {
            return;
        }
        $status = $_GET['status'] ?? '';
        $query = Order::orderBy('id', 'desc')->limit(200);
        if (in_array($status, ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'], true)) {
            $query->where('status', $status);
        }
        $rows = $query->get();
        $storeMap = [];
        foreach (Store::all() as $s) {
            $storeMap[$s->id] = $s->name;
        }
        foreach ($rows as $o) {
            $o->store_name = $storeMap[$o->store_id] ?? 'Stan '.$o->store_id;
        }
        $this->adminView('admin/orders', 'orders', ['title' => 'Semua Pesanan', 'orders' => $rows, 'status' => $status]);
    }
}
