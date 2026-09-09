<div class="container">
    <div class="hero">
        <h2><i class="fas fa-road"></i> How It Works</h2>
        <p>Understanding genomic analysis in five steps</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="color:var(--primary);"><i class="fas fa-user-circle"></i> 1. Create Account</h3>
            <p>Sign up with your email. Your password is stored as a bcrypt hash and your results are private to your account.</p>
        </div>
        <div class="card">
            <h3 style="color:var(--secondary);"><i class="fas fa-dna"></i> 2. Enter Parameters</h3>
            <p>Provide the variant's allele frequency, CADD score and predicted consequence type.</p>
        </div>
        <div class="card">
            <h3 style="color:var(--accent);"><i class="fas fa-microchip"></i> 3. Run Analysis</h3>
            <p>The platform derives a rarity classification, a pathogenic score and a risk level from those inputs.</p>
        </div>
        <div class="card">
            <h3 style="color:var(--success);"><i class="fas fa-chart-bar"></i> 4. View Results</h3>
            <p>Each analysis produces a visual report with charts and an interpretation guide.</p>
        </div>
        <div class="card">
            <h3 style="color:var(--warning);"><i class="fas fa-history"></i> 5. Track History</h3>
            <p>Return to your history at any time to compare variants you have analysed.</p>
        </div>
    </div>

    <div class="card card--flat">
        <h2><i class="fas fa-info-circle"></i> How the Scoring Works</h2>
        <div class="grid grid-2" style="margin-top:1rem;">
            <div>
                <p><strong>Pathogenic score</strong> is the CADD score expressed as a percentage of the 0&ndash;60 scale.</p>
                <p style="margin-top:.75rem;"><strong>Risk level</strong> is banded from the CADD score: above 30 is High, 15&ndash;30 is Medium, below 15 is Low.</p>
                <p style="margin-top:.75rem;"><strong>Rarity</strong> comes from allele frequency: below 1% is Very Rare, 1&ndash;5% is Rare, above 5% is Common.</p>
            </div>
            <div>
                <div class="alert alert-info">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        These are threshold heuristics for research and teaching. They are not a
                        clinical variant classification and must not be used for diagnosis.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
