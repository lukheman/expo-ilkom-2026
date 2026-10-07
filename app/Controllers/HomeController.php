<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Core\Controller;
use Illuminate\Database\Capsule\Manager;

class HomeController extends Controller
{
    private function cartCount()
    {
        $c = 0;
        foreach ($_SESSION['cart'] ?? [] as $v) {
            $c += is_array($v) ? ($v['qty'] ?? 0) : $v;
        }

        return $c;
    }

    private function cartDetailed()
    {
        $cart = [];
        $total = 0;
        foreach ($_SESSION['cart'] ?? [] as $key => $v) {
            if (is_array($v)) {
                $id = $v['id'];
                $qty = $v['qty'];
                $unit = $v['unit_price'];
                $size = $v['size'] ?? 'Reguler';
                $ice = $v['ice'] ?? 'Es Normal';
            } else {
                $id = $key;
                $qty = $v;
                $size = 'Reguler';
                $ice = 'Es Normal';
                $unit = null;
            }
            $p = Product::find($id);
            if (! $p) {
                continue;
            }
            if ($unit === null) {
                $unit = (float) $p->price;
            }
            $p->qty = $qty;
            $p->unit_price = $unit;
            $p->size_opt = $size;
            $p->ice_opt = $ice;
            $p->subtotal = $unit * $qty;
            $total += $p->subtotal;
            $cart[] = $p;
        }

        return [$cart, $total];
    }

    public function index()
    {
        $q = $_GET['q'] ?? '';
        $cat = $_GET['category'] ?? '';
        $filter = $_GET['filter'] ?? '';
        try {
            $query = Product::where('active', true)
                ->when($q, function ($x) use ($q) {
                    $x->where('name', 'like', '%'.$q.'%');
                })
                ->when($cat, function ($x) use ($cat) {
                    $x->where('category_id', $cat);
                });
            if ($filter === 'promo') {
                $query->where('price', '<', 25000);
            }
            if ($filter === 'hemat20') {
                $query->where('price', '<=', 20000);
            }
            if ($filter === 'termurah') {
                $query->orderBy('price', 'asc');
            } elseif ($filter === 'rating') {
                $query->orderBy('price', 'asc');
            } else {
                $query->orderBy('id', 'desc');
            }
            $products = $query->get();
            $stores = Store::where('approved', true)->get();
            $categories = Category::all();
            $storeMap = [];
            foreach ($stores as $s) {
                $storeMap[$s->id] = $s->name;
            }
            $catMap = [];
            foreach ($categories as $c) {
                $catMap[$c->id] = $c->name;
            }
            $catImages = [];
            foreach ($categories as $c) {
                $first = Product::where('category_id', $c->id)->where('active', true)->first();
                $catImages[$c->id] = $first && $first->image ? $first->image : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&q=60&auto=format&fit=crop';
            }
            foreach ($products as $p) {
                $p->store_name = $storeMap[$p->store_id] ?? 'Toko';
                $p->category_name = $catMap[$p->category_id] ?? '';
                $p->sold = 60 + (($p->id * 137) % 800);
                $r = 43 + ($p->id % 7);
                $p->rating = number_format($r / 10, 1);
                $p->reviews = 20 + (($p->id * 53) % 500);
                $m1 = 15 + ($p->id % 20);
                $p->time = $m1.'-'.($m1 + 10).' mnt';
                $p->original = (int) (ceil(((float) $p->price * 1.45) / 500) * 500);
            }
            if ($filter === 'laris') {
                $products = $products->filter(fn ($p) => $p->sold > 60)->values();
            }
            if ($filter === 'rating') {
                $products = $products->filter(fn ($p) => $p->rating >= 4.5)->values();
            }
            if ($filter === 'populer') {
                $products = $products->sortByDesc('sold')->values();
            }
        } catch (\Throwable $e) {
            $products = [];
            $stores = [];
            $categories = [];
            $catImages = [];
            $db_error = 'Database belum siap. Jalankan migrasi.';
        }
        $this->view('home/index', [
            'title' => 'RasaRaya HEMAT',
            'products' => $products, 'stores' => $stores, 'categories' => $categories,
            'cat_images' => $catImages ?? [],
            'q' => $q, 'cat' => $cat, 'filter' => $filter,
            'db_error' => $db_error ?? null,
            'cart_count' => $this->cartCount(),
        ]);
    }

    public function add($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pid = (int) ($_POST['product_id'] ?? $id ?? 0);
            $size = $_POST['size'] ?? 'Reguler';
            $ice = $_POST['ice'] ?? 'Es Normal';
            $qty = max(1, min(10, (int) ($_POST['qty'] ?? 1)));
            $p = Product::find($pid);
            if (! $p) {
                header('Location: /');

                return;
            }
            $extra = ($size === 'Besar') ? 5000 : 0;
            $unit = (float) $p->price + $extra;
            $key = $pid.'_'.$size.'_'.$ice;
            if (! isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$key]) && is_array($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['qty'] += $qty;
            } elseif (isset($_SESSION['cart'][$pid]) && ! is_array($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$key] = ['id' => $pid, 'qty' => $qty, 'unit_price' => $unit, 'size' => $size, 'ice' => $ice];
            } else {
                $_SESSION['cart'][$key] = ['id' => $pid, 'qty' => $qty, 'unit_price' => $unit, 'size' => $size, 'ice' => $ice];
            }
            header('Location: /home/cart');

            return;
        }
        $id = (int) $id;
        if ($id) {
            $p = Product::find($id);
            if ($p) {
                $key = $id.'_Reguler_Es Normal';
                if (! isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                if (isset($_SESSION['cart'][$key]) && is_array($_SESSION['cart'][$key])) {
                    $_SESSION['cart'][$key]['qty'] += 1;
                } else {
                    $_SESSION['cart'][$key] = ['id' => $id, 'qty' => 1, 'unit_price' => (float) $p->price, 'size' => 'Reguler', 'ice' => 'Es Normal'];
                }
            }
        }
        header('Location: /?added=1');
    }

    public function cart()
    {
        [$cart,$total] = $this->cartDetailed();
        $this->view('home/cart', ['title' => 'Keranjang', 'cart' => $cart, 'total' => $total, 'cart_count' => $this->cartCount()]);
    }

    public function checkout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$cart,$total] = $this->cartDetailed();
            if ($total) {
                Manager::table('orders')->insert(['customer_name' => $_POST['name'], 'address' => $_POST['address'], 'payment_method' => $_POST['payment'], 'total' => $total, 'note' => $_POST['note'] ?? '', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
                $_SESSION['cart'] = [];
                header('Location: /?success=1');

                return;
            }
        } $this->view('home/checkout', ['title' => 'Checkout', 'total' => $_GET['total'] ?? 0, 'cart_count' => $this->cartCount()]);
    }
}
