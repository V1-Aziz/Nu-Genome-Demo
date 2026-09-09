<?php
namespace App\Controllers;

use App\Models\AnalysisResult;
use Core\Controller;

class ReportController extends Controller
{
    /** Chart-backed report for a single analysis, scoped to its owner. */
    public function show(string $id): void
    {
        $user = $this->requireAuth();

        $analysis = (new AnalysisResult())->findForUser((int) $id, (int) $user['id']);

        // Same response whether the row is missing or belongs to someone else,
        // so ids cannot be probed for existence.
        if ($analysis === null) {
            $this->flash('error', 'That analysis could not be found.');
            $this->redirect('/results');
        }

        $this->view('report/show', [
            'page_title' => 'Analysis Report #' . (int) $id,
            'analysis'   => $analysis,
        ]);
    }
}
