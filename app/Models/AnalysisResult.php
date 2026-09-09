<?php
namespace App\Models;

use Core\Database;
use Core\Model;

/**
 * A stored variant analysis, plus the scoring rules that derive one.
 *
 * The thresholds below are the same ones the original analysis.php applied
 * inline; they live here so the controller and the views never re-implement them.
 */
class AnalysisResult extends Model
{
    protected string $table = 'analysis_results';

    public const CONSEQUENCES = [
        'Missense Variant',
        'Nonsense Variant',
        'Frameshift',
        'Synonymous',
        'Splice Site',
    ];

    public const MAX_CADD = 60.0;

    /**
     * Derive rarity, pathogenic score and risk level from the raw inputs.
     */
    public static function score(float $alleleFreq, float $caddScore): array
    {
        if ($alleleFreq < 0.01) {
            $rarity = 'Very Rare';
        } elseif ($alleleFreq < 0.05) {
            $rarity = 'Rare';
        } else {
            $rarity = 'Common';
        }

        if ($caddScore > 30) {
            $risk = 'High';
        } elseif ($caddScore > 15) {
            $risk = 'Medium';
        } else {
            $risk = 'Low';
        }

        return [
            'rarity'           => $rarity,
            'pathogenic_score' => min(100.0, ($caddScore / self::MAX_CADD) * 100),
            'risk_level'       => $risk,
        ];
    }

    public function create(int $userId, array $data): int
    {
        return Database::insert(
            'INSERT INTO analysis_results
                (user_id, allele_frequency, cadd_score, consequence_type, rarity, pathogenic_score, risk_level, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $data['allele_frequency'],
                $data['cadd_score'],
                $data['consequence_type'],
                $data['rarity'],
                $data['pathogenic_score'],
                $data['risk_level'],
                $data['notes'],
            ]
        );
    }

    /**
     * Fetch one result scoped to its owner.
     *
     * The user_id predicate is the fix for the old visual-report.php, which
     * looked up by id alone and so exposed every user's results.
     */
    public function findForUser(int $id, int $userId): ?array
    {
        return Database::selectOne(
            'SELECT * FROM analysis_results WHERE id = ? AND user_id = ? LIMIT 1',
            [$id, $userId]
        );
    }

    public function forUser(int $userId, ?int $limit = null): array
    {
        $sql = 'SELECT * FROM analysis_results WHERE user_id = ? ORDER BY created_at DESC, id DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;   // cast, never interpolated from input
        }

        return Database::select($sql, [$userId]);
    }

    public function statsForUser(int $userId): array
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS total,
                    AVG(pathogenic_score) AS avg_pathogenic,
                    SUM(risk_level = "High") AS high_risk
             FROM analysis_results WHERE user_id = ?',
            [$userId]
        );

        return [
            'total'          => (int) ($row['total'] ?? 0),
            'avg_pathogenic' => $row['avg_pathogenic'] !== null ? (float) $row['avg_pathogenic'] : null,
            'high_risk'      => (int) ($row['high_risk'] ?? 0),
        ];
    }

    /**
     * Aggregate counts for the public home page.
     *
     * Deliberately returns no usernames and no row ids: the old index.php
     * listed both, which let anonymous visitors enumerate other people's reports.
     */
    public function publicSummary(): array
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS analyses, COUNT(DISTINCT user_id) AS researchers FROM analysis_results'
        );

        return [
            'analyses'    => (int) ($row['analyses'] ?? 0),
            'researchers' => (int) ($row['researchers'] ?? 0),
        ];
    }
}
