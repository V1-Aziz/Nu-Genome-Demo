<?php
namespace App\Controllers;

use App\Models\AnalysisResult;
use Core\Controller;

class AnalysisController extends Controller
{
    /** The analysis form. */
    public function create(): void
    {
        $this->requireAuth();

        $this->view('analysis/create', [
            'page_title'   => 'New Analysis',
            'consequences' => AnalysisResult::CONSEQUENCES,
            'old'          => $this->pullOld(),
        ]);
    }

    /** Validate, score and persist a submitted analysis. */
    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();

        $alleleFreq  = $this->numeric('allele_freq');
        $caddScore   = $this->numeric('cadd_score');
        $consequence = $this->input('consequence');
        $notes       = $this->input('notes');

        $errors = [];

        if ($alleleFreq === null || $alleleFreq < 0 || $alleleFreq > 1) {
            $errors[] = 'Allele frequency must be a number between 0 and 1.';
        }

        if ($caddScore === null || $caddScore < 0 || $caddScore > AnalysisResult::MAX_CADD) {
            $errors[] = 'CADD score must be a number between 0 and ' . (int) AnalysisResult::MAX_CADD . '.';
        }

        if (!in_array($consequence, AnalysisResult::CONSEQUENCES, true)) {
            $errors[] = 'Please select a valid consequence type.';
        }

        if (mb_strlen($notes) > 2000) {
            $errors[] = 'Notes must be 2000 characters or fewer.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            $_SESSION['old'] = [
                'allele_freq' => $_POST['allele_freq'] ?? '',
                'cadd_score'  => $_POST['cadd_score'] ?? '',
                'consequence' => $consequence,
                'notes'       => $notes,
            ];
            $this->redirect('/analysis');
        }

        $scores = AnalysisResult::score($alleleFreq, $caddScore);

        $id = (new AnalysisResult())->create((int) $user['id'], [
            'allele_frequency'  => $alleleFreq,
            'cadd_score'        => $caddScore,
            'consequence_type'  => $consequence,
            'notes'             => $notes,
        ] + $scores);

        $this->flash('success', 'Analysis complete.');
        $this->redirect('/report/' . $id);
    }

    /** The signed-in user's full history. */
    public function history(): void
    {
        $user = $this->requireAuth();

        $this->view('analysis/history', [
            'page_title' => 'Analysis History',
            'analyses'   => (new AnalysisResult())->forUser((int) $user['id']),
        ]);
    }

    /** A POST value parsed as a float, or null when it is not numeric. */
    private function numeric(string $key): ?float
    {
        $value = $this->input($key);
        return is_numeric($value) ? (float) $value : null;
    }
}
