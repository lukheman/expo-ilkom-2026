<?php

namespace App\Controllers;

use App\Models\User;
use Core\Controller;

class AuthController extends Controller
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $u = User::where('email', $_POST['email'])->first();
            if ($u && password_verify($_POST['password'], $u->password)) {
                $_SESSION['user'] = $u->id;
                header('Location: /');

                return;
            } $error = 'Email atau password salah.';
        } $this->view('auth/login', ['title' => 'Masuk', 'error' => $error ?? null]);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            User::create(['name' => $_POST['name'], 'email' => $_POST['email'], 'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), 'role' => $_POST['role'], 'status' => $_POST['role'] === 'seller' ? 'pending' : 'active']);
            header('Location: /auth/login');

            return;
        }$this->view('auth/register', ['title' => 'Daftar']);
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
    }
}
