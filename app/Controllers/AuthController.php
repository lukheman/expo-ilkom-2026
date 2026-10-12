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
                header('Location: '.($u->role === 'admin' ? '/admin' : ($u->role === 'seller' ? '/seller' : '/')));

                return;
            } $error = 'Email atau password salah.';
        } $this->view('auth/login', ['title' => 'Masuk', 'error' => $error ?? null]);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Expo: pendaftaran hanya untuk penjual. Pembeli memesan tanpa akun.
            User::create(['name' => $_POST['name'], 'email' => $_POST['email'], 'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), 'role' => 'seller', 'status' => 'pending']);
            $this->toast('Pendaftaran berhasil. Menunggu persetujuan admin.');
            header('Location: /login');

            return;
        }$this->view('auth/register', ['title' => 'Daftar Penjual']);
    }

    public function logout()
    {
        session_destroy();
        session_start();
        $_SESSION['toast'] = ['message' => 'Anda berhasil keluar.', 'type' => 'success'];
        header('Location: /');
    }
}
