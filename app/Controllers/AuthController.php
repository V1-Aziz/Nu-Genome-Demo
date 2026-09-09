<?php
namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->requireGuest();
        $this->view('auth/login', ['page_title' => 'Login']);
    }

    public function login(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $email    = $this->input('email');
        $password = $this->raw('password');

        if ($email === '' || $password === '') {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('/login');
        }

        $user = (new User())->attempt($email, $password);

        if ($user === null) {
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        Auth::login($user);

        $intended = $_SESSION['intended_url'] ?? '/dashboard';
        unset($_SESSION['intended_url']);

        // Only ever redirect within this application.
        $this->redirect($this->safePath($intended, '/dashboard'));
    }

    public function showRegister(): void
    {
        $this->requireGuest();
        $this->view('auth/register', [
            'page_title' => 'Create Account',
            'old'        => $this->pullOld(),
        ]);
    }

    public function register(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $username = $this->input('username');
        $email    = $this->input('email');
        $password = $this->raw('password');
        $confirm  = $this->raw('password_confirm');

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[A-Za-z0-9_.\- ]{3,100}$/', $username)) {
            $errors[] = 'Username must be 3-100 characters (letters, numbers, spaces, . _ -).';
        }

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        $model = new User();

        if (!$errors && $model->exists($email, $username)) {
            $errors[] = 'That email or username is already registered.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            $_SESSION['old'] = ['username' => $username, 'email' => $email];
            $this->redirect('/register');
        }

        $id = $model->create($username, $email, $password);

        Auth::login($model->find($id));
        $this->flash('success', 'Welcome to GenomePlatform, ' . $username . '.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }

}
