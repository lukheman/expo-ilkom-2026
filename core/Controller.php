<?php

namespace Core;

class Controller
{
    public function view($view, $data = [])
    {
        View::render($view, $data);
    }

    public function model($model)
    {
        require_once __DIR__ . '/../app/Models/' . $model . '.php';
        $class = "App\\Models\\$model";
        return new $class;
    }
}
