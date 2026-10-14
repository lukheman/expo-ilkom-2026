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
    // Keranjang belanja (session): [product_id => qty], checkout dikelompokkan per stan.
    private function cartGroups()
    {
        $cart = $_SESSION['cart'] ?? [];
        if (! $cart) {
            return [[], 0];
        }
        $ids = array_map('intval', array_keys($cart));
        $byId = [];
        foreach (Product::whereIn('id', $ids)->where('active', true)->get() as $p) {
            $byId[$p->id] = $p;
        }
        $storeIds = [];
        foreach ($byId as $p) {
            $storeIds[] = $p->store_id;
        }
        $storeMap = [];
        if ($storeIds) {
            foreach (Store::whereIn('id', array_unique($storeIds))->get() as $s) {
                $storeMap[$s->id] = $s;
            }
        }
        $groups = [];
        $grand = 0;
        foreach ($cart as $pid => $qty) {
            $pid = (int) $pid;
            $p = $byId[$pid] ?? null;
            if (! $p) {
                continue;
            }
            $qty = max(1, min(10, (int) $qty));
            $p->qty = $qty;
            $p->subtotal = (float) $p->price * $qty;
            $p->store_name = $storeMap[$p->store_id]->name ?? 'Stan '.$p->store_id;
            $sid = $p->store_id;
            if (! isset($groups[$sid])) {
                $groups[$sid] = ['store_id' => $sid, 'store' => $storeMap[$sid] ?? null, 'items' => [], 'subtotal' => 0];
            }
            $groups[$sid]['items'][] = $p;
            $groups[$sid]['subtotal'] += $p->subtotal;
            $grand += $p->subtotal;
        }

        return [array_values($groups), $grand];
    }

    public function cart()
    {
        [$groups,$grand] = $this->cartGroups();
        $this->view('home/cart', ['title' => 'Keranjang', 'groups' => $groups, 'grand' => $grand]);
    }

    public function cartAdd()
    {
        $pid = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, min(10, (int) ($_POST['qty'] ?? 1)));
        $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $p = Product::find($pid);
        if (! $p || ! $p->active) {
            if ($ajax) {
                $this->cartJson(false, 'Produk tidak tersedia.');

                return;
            }
            $this->toast('Produk tidak tersedia.', 'danger');
            header('Location: /');

            return;
        }
        $cart = $_SESSION['cart'] ?? [];
        $new = min(10, ($cart[$pid] ?? 0) + $qty);
        if ($new > $p->stock) {
            if ($ajax) {
                $this->cartJson(false, 'Stok tidak cukup. Sisa: '.$p->stock.'.');

                return;
            }
            $this->toast('Stok tidak cukup. Sisa: '.$p->stock.'.', 'danger');
            header('Location: /home/store/'.$p->store_id);

            return;
        }
        $cart[$pid] = $new;
        $_SESSION['cart'] = $cart;
        if ($ajax) {
            $this->cartJson(true, $p->name.' ditambahkan ke keranjang.', $pid, $new);

            return;
        }
        $this->toast($p->name.' ditambahkan ke keranjang.');
        header('Location: /home/store/'.$p->store_id);
    }

    private function cartJson($ok, $message, $pid = 0, $qty = 0)
    {
        [$count,$total] = $this->cartSummary();
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok, 'message' => $message, 'count' => $count, 'total' => $total, 'pid' => $pid, 'qty' => $qty]);
    }

    private function cartSummary()
    {
        $cart = $_SESSION['cart'] ?? [];
        $count = array_sum($cart);
        $total = 0;
        if ($cart) {
            $ids = array_map('intval', array_keys($cart));
            foreach (Product::whereIn('id', $ids)->where('active', true)->get() as $p) {
                $total += (float) $p->price * max(1, min(10, (int) ($cart[$p->id] ?? 0)));
            }
        }

        return [$count, $total];
    }

    public function cartUpdate()
    {
        $pid = (int) ($_POST['product_id'] ?? 0);
        $qty = (int) ($_POST['qty'] ?? 1);
        $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $cart = $_SESSION['cart'] ?? [];
        $p = $pid ? Product::find($pid) : null;
        $msg = '';
        if ($qty <= 0 || ! $p || ! $p->active || $p->stock < 1) {
            unset($cart[$pid]);
            $msg = 'Item dihapus dari keranjang.';
            if (! $ajax) {
                $this->toast($msg);
            }
        } else {
            $new = min($qty, 10, $p->stock);
            $cart[$pid] = $new;
            if ($new < $qty) {
                $msg = 'Stok tersisa '.$p->stock.'.';
            }
        }
        $_SESSION['cart'] = $cart;
        if ($ajax) {
            $this->cartJson(true, $msg, $pid, $cart[$pid] ?? 0);

            return;
        }
        header('Location: /home/cart');
    }

    public function cartRemove($id)
    {
        $cart = $_SESSION['cart'] ?? [];
        unset($cart[(int) $id]);
        $_SESSION['cart'] = $cart;
        $this->toast('Item dihapus dari keranjang.');
        header('Location: /home/cart');
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
            $approvedIds = [];
            foreach ($stores as $s) {
                $approvedIds[] = $s->id;
            }
            $products = $products->filter(fn ($p) => in_array($p->store_id, $approvedIds))->values();
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
        $old = ['name' => '', 'phone' => '', 'payment' => 'QRIS', 'note' => ''];
        $scoped = (int) ($_GET['store'] ?? $_POST['store_id'] ?? 0);
        [$allGroups,$allGrand] = $this->cartGroups();
        $groups = $allGroups;
        $grand = $allGrand;
        if ($scoped > 0) {
            $filtered = array_values(array_filter($allGroups, fn ($g) => $g['store_id'] === $scoped));
            if ($filtered) {
                $groups = $filtered;
                $grand = $filtered[0]['subtotal'];
            }
        } else {
            $scoped = 0;
        }
        // Konfirmasi pesanan oleh pembeli (tanpa login), satu order per stan.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'confirm') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $payment = $_POST['payment'] ?? '';
            $note = trim($_POST['note'] ?? '');
            $old = ['name' => $name, 'phone' => $phone, 'payment' => $payment, 'note' => $note];
            if (! $groups) {
                $error = 'Keranjang kosong. Pilih menu dulu.';
            } elseif ($name === '' || mb_strlen($name) > 100) {
                $error = 'Nama pemesan wajib diisi (maks 100 karakter).';
            } elseif ($phone !== '' && ! preg_match('/^[+0-9][0-9 \-]{7,17}$/', $phone)) {
                $error = 'Nomor WA tidak valid. Contoh: 081234567890.';
            } elseif (! in_array($payment, ['QRIS', 'Tunai di stan'], true)) {
                $error = 'Pilih metode pembayaran: QRIS atau Tunai di stan.';
            } else {
                foreach ($groups as $g) {
                    foreach ($g['items'] as $item) {
                        $fresh = Product::find($item->id);
                        if (! $fresh || ! $fresh->active || $item->qty > $fresh->stock) {
                            $error = 'Stok "'.$item->name.'" tidak cukup. Sisa: '.($fresh ? $fresh->stock : 0).'.';
                            break 2;
                        }
                    }
                }
            }
            if (! $error) {
                $group = 'GB-'.date('YmdHis').'-'.strtoupper(bin2hex(random_bytes(2)));
                foreach ($groups as $g) {
                    $store = $g['store'];
                    $sid = $store ? $store->id : $g['items'][0]->store_id;
                    $booth = ($store && $store->booth_number) ? $store->booth_number : 'S'.$sid;
                    $order = Order::create([
                        'user_id' => $_SESSION['user'] ?? null,
                        'store_id' => $sid,
                        'group_code' => $group,
                        'pickup_code' => null,
                        'customer_name' => $name,
                        'buyer_phone' => $phone !== '' ? $phone : null,
                        'address' => 'Ambil di stan',
                        'fulfillment' => 'pickup',
                        'payment_method' => $payment,
                        'payment_status' => 'Belum bayar',
                        'total' => $g['subtotal'],
                        'status' => 'Menunggu',
                        'note' => $note !== '' ? $note : null,
                    ]);
                    foreach ($g['items'] as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->id,
                            'quantity' => $item->qty,
                            'price' => (float) $item->price,
                            'variant' => null,
                        ]);
                        Product::where('id', $item->id)->decrement('stock', $item->qty);
                    }
                    $pickup = $booth.'-'.str_pad($order->id, 3, '0', STR_PAD_LEFT);
                    $order->update(['pickup_code' => $pickup]);
                }
                $cart = $_SESSION['cart'] ?? [];
                foreach ($groups as $g) {
                    foreach ($g['items'] as $item) {
                        unset($cart[$item->id]);
                    }
                }
                $_SESSION['cart'] = $cart;
                header('Location: /home/success/'.$group);

                return;
            }
        }
        $this->view('home/checkout', ['title' => 'Checkout Expo', 'groups' => $groups, 'grand' => $grand, 'scoped' => $scoped, 'error' => $error, 'old' => $old]);
    }

    public function store($id)
    {
        $s = Store::where('id', (int) $id)->where('approved', true)->first();
        if (! $s) {
            header('Location: /');

            return;
        }
        $catMap = [];
        foreach (Category::all() as $c) {
            $catMap[$c->id] = $c->name;
        }
        $products = Product::where('store_id', $s->id)->where('active', true)->orderBy('id', 'desc')->get();
        foreach ($products as $p) {
            $p->store_name = $s->name;
            $p->category_name = $catMap[$p->category_id] ?? '';
        }
        $this->view('home/store', ['title' => $s->name, 'store' => $s, 'products' => $products, 'cart' => $_SESSION['cart'] ?? []]);
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
        $grand = 0;
        foreach ($orders as $o) {
            $grand += (float) $o->total;
        }
        $this->view('home/success', ['title' => 'Pesanan Berhasil', 'group' => $group, 'orders' => $orders, 'grand' => $grand]);
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
