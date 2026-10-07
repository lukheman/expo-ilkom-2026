<?php

namespace Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View
{
    private static $twig;

    private static function init()
    {
        if (!self::$twig) {
            $loader = new FilesystemLoader(__DIR__ . '/../app/Views');

            self::$twig = new Environment($loader, [
                'cache' => false
            ]);
        }
    }

    public static function render($view, $data = [])
    {
        self::init();
        echo self::$twig->render($view . '.twig', $data);
    }
}
