<?php

namespace Core;

use App\Models\Product;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private static $twig;

    private static function init()
    {
        if (! self::$twig) {
            $loader = new FilesystemLoader(__DIR__.'/../app/Views');

            self::$twig = new Environment($loader, [
                'cache' => false,
            ]);
        }
    }

    public static function render($view, $data = [])
    {
        self::init();
        $data['toast'] = $_SESSION['toast'] ?? null;
        unset($_SESSION['toast']);
        $data['cartCount'] = array_sum($_SESSION['cart'] ?? []);
        $data['cartTotal'] = 0;
        if (! empty($_SESSION['cart'])) {
            try {
                $ids = array_map('intval', array_keys($_SESSION['cart']));
                foreach (Product::whereIn('id', $ids)->where('active', true)->get() as $p) {
                    $data['cartTotal'] += (float) $p->price * max(1, min(10, (int) ($_SESSION['cart'][$p->id] ?? 0)));
                }
            } catch (\Throwable $e) {
                $data['cartTotal'] = 0;
            }
        }
        echo self::$twig->render($view.'.twig', $data);
    }
}
