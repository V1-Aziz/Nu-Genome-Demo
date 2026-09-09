<?php
namespace App\Controllers;

use Core\Controller;

class ErrorController extends Controller
{
    public function notFound(string $path = ''): void
    {
        $this->view('errors/404', [
            'page_title' => 'Page Not Found',
            'path'       => $path,
        ]);
    }

    public function serverError(string $message = ''): void
    {
        $this->view('errors/500', [
            'page_title' => 'Something Went Wrong',
            'message'    => $message,
        ]);
    }
}
