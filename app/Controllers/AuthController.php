<?php

namespace App\Controllers;

use App\Models\User;
use Core\Controller;

class AuthController extends Controller
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $u = User::where('username', trim($_POST['username'] ?? ''))->first();
            if ($u && password_verify($_POST['password'], $u->password)) {
                $_SESSION['user'] = $u->id;
                header('Location: '.($u->role === 'admin' ? '/admin' : ($u->role === 'seller' ? '/seller' : '/')));

                return;
            } $error = 'Username atau password salah.';
        } $this->view('auth/login', ['title' => 'Masuk', 'error' => $error ?? null]);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Expo: pendaftaran hanya untuk penjual. Pembeli memesan tanpa akun.
            $username = substr(trim($_POST['username'] ?? ''), 0, 50);
            $phone = substr(trim($_POST['phone'] ?? ''), 0, 20);
            if ($username !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $username) && ($phone === '' || preg_match('/^[+0-9][0-9 \-]{7,17}$/', $phone)) && ! User::where('username', $username)->exists()) {
                User::create(['name' => $_POST['name'], 'username' => $username, 'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), 'role' => 'seller', 'phone' => $phone !== '' ? $phone : null]);
                $this->toast('Pendaftaran berhasil. Silakan masuk.');
            } else {
                $this->toast('Gagal mendaftar. Username sudah dipakai / tidak valid, atau no. WA tidak valid.', 'danger');
            }
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
