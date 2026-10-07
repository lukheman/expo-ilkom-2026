<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // For demonstration, let's try to fetch users.
        // If the table doesn't exist yet, this might error, but that's expected until migration runs.
        try {
            $users = User::all();
        } catch (\Exception $e) {
            $users = [];
        }

        $this->view('home/index', [
            'title' => 'Home Page',
            'users' => $users
        ]);
    }
}
