<?php
namespace App\Controllers;

use App\Models\AnalysisResult;
use Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'page_title' => 'Home',
            'summary'    => (new AnalysisResult())->publicSummary(),
        ]);
    }
}
