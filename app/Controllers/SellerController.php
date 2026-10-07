<?php

namespace App\Controllers;

use App\Models\Category;
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

    public function index()
    {
        $u = $this->user();
        if (! $u || $u->role !== 'seller') {
            header('Location: /auth/login');

            return;
        }$s = Store::where('user_id', $u->id)->first();
        $orders = $s ? \App\Models\Order::where('store_id', $s->id)->orderBy('id', 'desc')->limit(100)->get() : [];
        $this->view('seller/index', ['title' => 'Dashboard Penjual', 'store' => $s, 'products' => $s ? Product::where('store_id', $s->id)->get() : [], 'categories' => Category::all(), 'orders' => $orders]);
    }

    private function ownOrder($id)
    {
        $u = $this->user();
        if (! $u || $u->role !== 'seller') {
            return null;
        }
        $s = Store::where('user_id', $u->id)->first();
        if (! $s) {
            return null;
        }

        return \App\Models\Order::where('id', $id)->where('store_id', $s->id)->first();
    }

    public function status($id, $to)
    {
        $allowed = ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
        $to = urldecode($to);
        $o = $this->ownOrder($id);
        if ($o && in_array($to, $allowed, true)) {
            $o->update(['status' => $to]);
        }
        header('Location: /seller#pesanan');
    }

    public function paid($id)
    {
        $o = $this->ownOrder($id);
        if ($o) {
            $o->update(['payment_status' => 'Lunas']);
        }
        header('Location: /seller#pesanan');
    }

    public function save()
    {
        $u = $this->user();
        $s = Store::firstOrCreate(['user_id' => $u->id], ['name' => $_POST['store'], 'approved' => true]);
        if (isset($_POST['booth']) && $_POST['booth'] !== '') {
            $s->update(['booth_number' => substr(trim($_POST['booth']), 0, 10)]);
        }
        Product::create(['store_id' => $s->id, 'category_id' => $_POST['category'], 'name' => $_POST['name'], 'description' => $_POST['description'], 'price' => $_POST['price'], 'stock' => $_POST['stock'], 'image' => $_POST['image'], 'active' => true]);
        header('Location: /seller');
    }

    public function delete($id)
    {
        $u = $this->user();
        $s = Store::where('user_id', $u->id)->first();
        Product::where('id', $id)->where('store_id', $s->id)->delete();
        header('Location: /seller');
    }
}
