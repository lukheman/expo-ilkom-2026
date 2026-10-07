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
        $this->view('seller/index', ['title' => 'Dashboard Penjual', 'store' => $s, 'products' => $s ? Product::where('store_id', $s->id)->get() : [], 'categories' => Category::all()]);
    }

    public function save()
    {
        $u = $this->user();
        $s = Store::firstOrCreate(['user_id' => $u->id], ['name' => $_POST['store'], 'approved' => true]);
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
