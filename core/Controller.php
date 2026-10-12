<?php

namespace Core;

class Controller
{
    public function view($view, $data = [])
    {
        View::render($view, $data);
    }

    protected function toast($message, $type = 'success')
    {
        $_SESSION['toast'] = [
            'message' => $message,
            'type' => in_array($type, ['success', 'danger'], true) ? $type : 'success',
        ];
    }

    public function model($model)
    {
        require_once __DIR__.'/../app/Models/'.$model.'.php';
        $class = "App\\Models\\$model";

        return new $class;
    }
}
