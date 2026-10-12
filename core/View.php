<?php

namespace Core;

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
        echo self::$twig->render($view.'.twig', $data);
    }
}
