<?php
namespace App\Controllers;

use App\Models\AnalysisResult;
use Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user    = $this->requireAuth();
        $results = new AnalysisResult();

        $this->view('dashboard/index', [
            'page_title' => 'Dashboard',
            'user'       => $user,
            'stats'      => $results->statsForUser((int) $user['id']),
            'recent'     => $results->forUser((int) $user['id'], 5),
        ]);
    }
}
