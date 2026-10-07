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
        if (! $u || $u->role !== 'admin' || $u->status !== 'active') {
            header('Location: /auth/login');

            return null;
        }

        return $u;
    }

    private function adminView($tpl, $menu, $data = [])
    {
        $data['menu'] = $menu;
        $data['chrome'] = 'admin';
        $data['panel'] = 'Admin Expo';
        $data['pending'] = User::where('role', 'seller')->where('status', 'pending')->count();
        $this->view($tpl, $data);
    }

    public function index()
    {
        if (! $this->admin()) {
            return;
        }
        $this->adminView('admin/dashboard', 'dashboard', [
            'title' => 'Admin Expo',
            'sellers' => User::where('role', 'seller')->count(),
            'pending' => User::where('role', 'seller')->where('status', 'pending')->count(),
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

    public function approve($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::where('id', (int) $id)->where('role', 'seller')->first();
        if ($u) {
            $u->update(['status' => 'active']);
            Store::where('user_id', $u->id)->update(['approved' => true]);
        }
        header('Location: /admin/sellers');
    }

    public function reject($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::where('id', (int) $id)->where('role', 'seller')->first();
        if ($u) {
            $u->update(['status' => 'rejected']);
            Store::where('user_id', $u->id)->update(['approved' => false]);
        }
        header('Location: /admin/sellers');
    }

    public function storeSeller()
    {
        if (! $this->admin()) {
            return;
        }
        $name = substr(trim($_POST['name'] ?? ''), 0, 100);
        $email = substr(trim($_POST['email'] ?? ''), 0, 100);
        $password = $_POST['password'] ?? '';
        $storeName = substr(trim($_POST['store_name'] ?? ''), 0, 100);
        if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '' && ! User::where('email', $email)->exists()) {
            $u = User::create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'seller',
                'status' => 'active',
            ]);
            Store::create([
                'user_id' => $u->id,
                'name' => $storeName !== '' ? $storeName : 'Stan '.$name,
                'approved' => true,
            ]);
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
            header('Location: /admin/sellers');

            return;
        }
        $name = substr(trim($_POST['name'] ?? $u->name), 0, 100);
        $email = substr(trim($_POST['email'] ?? $u->email), 0, 100);
        $status = in_array($_POST['status'] ?? $u->status, ['pending', 'active', 'rejected', 'nonactive'], true) ? $_POST['status'] : $u->status;
        $data = ['name' => $name !== '' ? $name : $u->name, 'status' => $status];
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $exists = User::where('email', $email)->where('id', '!=', $u->id)->exists();
            if (! $exists) {
                $data['email'] = $email;
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
            'approved' => $status === 'active',
        ];
        if ($store) {
            $store->update($storeData);
        } else {
            $storeData['user_id'] = $u->id;
            Store::create($storeData);
        }
        header('Location: /admin/sellers');
    }

    public function toggleSeller($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::where('id', (int) $id)->where('role', 'seller')->first();
        if ($u) {
            $new = $u->status === 'active' ? 'nonactive' : 'active';
            $u->update(['status' => $new]);
            Store::where('user_id', $u->id)->update(['approved' => $new === 'active']);
        }
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

    public function toggleUser($id)
    {
        if (! $this->admin()) {
            return;
        }
        $u = User::find((int) $id);
        if ($u && $u->role !== 'admin') {
            $u->update(['status' => $u->status === 'active' ? 'nonactive' : 'active']);
        }
        header('Location: /admin/users');
    }

    public function storeUser()
    {
        if (! $this->admin()) {
            return;
        }
        $name = substr(trim($_POST['name'] ?? ''), 0, 100);
        $email = substr(trim($_POST['email'] ?? ''), 0, 100);
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? 'buyer', ['admin', 'seller', 'buyer'], true) ? $_POST['role'] : 'buyer';
        $status = in_array($_POST['status'] ?? 'active', ['active', 'nonactive', 'pending'], true) ? $_POST['status'] : 'active';
        if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '' && ! User::where('email', $email)->exists()) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => $status,
            ]);
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
            Category::firstOrCreate(['name' => $name], ['slug' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name))]);
            header('Location: /admin/categories');

            return;
        }
        $this->adminView('admin/categories', 'categories', ['title' => 'Kelola Kategori', 'categories' => Category::orderBy('id')->get()]);
    }

    public function delCategory($id)
    {
        if (! $this->admin()) {
            return;
        }
        if (! Product::where('category_id', (int) $id)->exists()) {
            Category::where('id', (int) $id)->delete();
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
