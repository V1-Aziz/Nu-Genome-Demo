<?php
namespace App\Controllers;

use Core\Controller;

/** Static content pages. */
class PageController extends Controller
{
    public function howItWorks(): void
    {
        $this->view('pages/how-it-works', ['page_title' => 'How It Works']);
    }
}
