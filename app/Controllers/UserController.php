<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

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
        if (isset($_POST['name']) && isset($_POST['email'])) {
            User::create([
                'name' => $_POST['name'],
                'email' => $_POST['email']
            ]);
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
        if ($user && isset($_POST['name']) && isset($_POST['email'])) {
            $user->update([
                'name' => $_POST['name'],
                'email' => $_POST['email']
            ]);
        }
        header('Location: /user');
    }

    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        header('Location: /user');
    }
}
