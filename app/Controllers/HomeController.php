<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Core\Controller;

class HomeController extends Controller
{
    // Expo: tanpa keranjang. Satu aksi beli = satu item langsung ke checkout.
    private function buyItem()
    {
        $b = $_SESSION['buy'] ?? null;
        if (! $b) {
            return [null, null, 0];
        }
        $p = Product::find($b['id'] ?? 0);
        if (! $p || ! $p->active) {
            unset($_SESSION['buy']);

            return [null, null, 0];
        }
        $p->qty = $b['qty'];
        $p->unit_price = $b['unit_price'];
        $p->size_opt = $b['size'];
        $p->ice_opt = $b['ice'];
        $p->subtotal = $b['unit_price'] * $b['qty'];
        $store = Store::find($p->store_id);

        return [$p, $store, $p->subtotal];
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
            }
        } catch (\Throwable $e) {
            $products = [];
            $stores = [];
            $categories = [];
            $catImages = [];
            $db_error = 'Database belum siap. Jalankan migrasi.';
        }
        $this->view('home/index', [
            'title' => 'KewirausahaanDigital FTI',
            'products' => $products, 'stores' => $stores, 'categories' => $categories,
            'cat_images' => $catImages ?? [],
            'q' => $q, 'cat' => $cat, 'filter' => $filter,
            'habis' => isset($_GET['habis']),
            'db_error' => $db_error ?? null,
        ]);
    }

    public function checkout()
    {
        $error = null;
        // Langkah 1: dari tombol "Beli Langsung" di katalog.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && ($_POST['step'] ?? '') !== 'confirm') {
            $pid = (int) $_POST['product_id'];
            $p = Product::find($pid);
            $qty = max(1, min(10, (int) ($_POST['qty'] ?? 1)));
            if (! $p || ! $p->active || $qty > $p->stock) {
                header('Location: /?habis=1');

                return;
            }
            $_SESSION['buy'] = [
                'id' => $pid,
                'qty' => $qty,
                'unit_price' => (float) $p->price,
                'size' => '',
                'ice' => '',
            ];
            header('Location: /home/checkout');

            return;
        }
        // Langkah 2: konfirmasi pesanan oleh pembeli (tanpa login).
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'confirm') {
            [$item,$store,$total] = $this->buyItem();
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $payment = $_POST['payment'] ?? '';
            $note = trim($_POST['note'] ?? '');
            if (! $item || ! $store) {
                $error = 'Item pembelian tidak valid. Pilih menu lagi.';
            } elseif ($name === '' || mb_strlen($name) > 100) {
                $error = 'Nama pemesan wajib diisi (maks 100 karakter).';
            } elseif ($phone !== '' && ! preg_match('/^[+0-9][0-9 \-]{7,17}$/', $phone)) {
                $error = 'Nomor WA tidak valid. Contoh: 081234567890.';
            } elseif (! in_array($payment, ['QRIS', 'Tunai di stan'], true)) {
                $error = 'Pilih metode pembayaran: QRIS atau Tunai di stan.';
            } else {
                $fresh = Product::find($item->id);
                if (! $fresh || ! $fresh->active || $item->qty > $fresh->stock) {
                    $error = 'Stok "'.$item->name.'" tidak cukup. Sisa: '.($fresh ? $fresh->stock : 0).'.';
                    unset($_SESSION['buy']);
                    [$item,$store,$total] = [null, null, 0];
                }
            }
            if (! $error) {
                $sid = $store->id;
                $booth = $store->booth_number ?: 'S'.$sid;
                $today = Order::where('store_id', $sid)->whereDate('created_at', date('Y-m-d'))->count() + 1;
                $pickup = $booth.'-'.str_pad($today, 3, '0', STR_PAD_LEFT);
                $order = Order::create([
                    'user_id' => $_SESSION['user'] ?? null,
                    'store_id' => $sid,
                    'group_code' => $pickup,
                    'pickup_code' => $pickup,
                    'customer_name' => $name,
                    'buyer_phone' => $phone !== '' ? $phone : null,
                    'address' => 'Ambil di stan',
                    'fulfillment' => 'pickup',
                    'payment_method' => $payment,
                    'payment_status' => 'Belum bayar',
                    'total' => $total,
                    'status' => 'Menunggu',
                    'note' => $note !== '' ? $note : null,
                ]);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->id,
                    'quantity' => $item->qty,
                    'price' => $item->unit_price,
                    'variant' => null,
                ]);
                Product::where('id', $item->id)->decrement('stock', $item->qty);
                unset($_SESSION['buy']);
                header('Location: /home/success/'.$pickup);

                return;
            }
            $this->view('home/checkout', ['title' => 'Checkout Expo', 'store' => $store, 'items' => $item ? [$item] : [], 'total' => $total, 'error' => $error, 'old' => ['name' => $name ?? '', 'phone' => $phone ?? '', 'payment' => $payment ?? 'QRIS', 'note' => $note ?? '']]);

            return;
        }
        [$item,$store,$total] = $this->buyItem();
        $this->view('home/checkout', ['title' => 'Checkout Expo', 'store' => $store, 'items' => $item ? [$item] : [], 'total' => $total, 'error' => $error, 'old' => ['name' => '', 'phone' => '', 'payment' => 'QRIS', 'note' => '']]);
    }

    public function success($group = null)
    {
        $orders = [];
        if ($group) {
            $orders = Order::where('group_code', $group)->orderBy('id')->get();
            $storeIds = [];
            foreach ($orders as $o) {
                $storeIds[] = $o->store_id;
            }
            $storeMap = [];
            if ($storeIds) {
                foreach (Store::whereIn('id', array_unique($storeIds))->get() as $s) {
                    $storeMap[$s->id] = $s;
                }
            }
            foreach ($orders as $o) {
                $o->store_name = $storeMap[$o->store_id]->name ?? 'Stan '.$o->store_id;
                $o->booth = ($storeMap[$o->store_id]->booth_number ?? null) ?: 'S'.$o->store_id;
                $o->qris_image = $storeMap[$o->store_id]->qris_image ?? null;
            }
        }
        $this->view('home/success', ['title' => 'Pesanan Berhasil', 'group' => $group, 'orders' => $orders]);
    }

    public function track()
    {
        $code = trim($_GET['kode'] ?? '');
        $orders = [];
        $searched = $code !== '';
        if ($searched) {
            $orders = Order::where('group_code', $code)->orWhere('pickup_code', $code)->orderBy('id')->get();
            $storeIds = [];
            foreach ($orders as $o) {
                $storeIds[] = $o->store_id;
            }
            $storeMap = [];
            if ($storeIds) {
                foreach (Store::whereIn('id', array_unique($storeIds))->get() as $s) {
                    $storeMap[$s->id] = $s;
                }
            }
            foreach ($orders as $o) {
                $o->store_name = $storeMap[$o->store_id]->name ?? 'Stan '.$o->store_id;
                $o->booth = ($storeMap[$o->store_id]->booth_number ?? null) ?: 'S'.$o->store_id;
            }
        }
        $this->view('home/track', ['title' => 'Lacak Pesanan', 'kode' => $code, 'searched' => $searched, 'orders' => $orders]);
    }
}
