<?php

namespace App\Controllers;

use App\Models\User;
use Core\Controller;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $this->view('users/index', ['users' => $users]);
    }

    public function create()
    {
        $this->view('users/create');
    }

    public function store()
    {
        if (isset($_POST['name']) && isset($_POST['username'])) {
            User::create([
                'name' => $_POST['name'],
                'username' => $_POST['username'],
            ]);
            $this->toast('User berhasil ditambahkan.');
        }
        header('Location: /user');
    }

    public function edit($id)
    {
        $user = User::find($id);
        if ($user) {
            $this->view('users/edit', ['user' => $user]);
        } else {
            header('Location: /user');
        }
    }

    public function update($id)
    {
        $user = User::find($id);
        if ($user && isset($_POST['name']) && isset($_POST['username'])) {
            $user->update([
                'name' => $_POST['name'],
                'username' => $_POST['username'],
            ]);
            $this->toast('User berhasil diubah.');
        }
        header('Location: /user');
    }

    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            $this->toast('User berhasil dihapus.');
        }
        header('Location: /user');
    }
}
