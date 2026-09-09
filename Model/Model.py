import pandas as pd
import numpy as np

# Set seed for reproducibility
np.random.seed(42)

# Generate 100 variants
n = 100
variant_ids = [f"rs{np.random.randint(100000, 999999)}" for _ in range(n)]

# Feature 1: Allele Frequency (Lower frequency = higher risk)
# Dangerous variants are usually very rare (0 to 0.001)
allele_freq = np.random.uniform(0, 0.05, n)

# Feature 2: CADD Score (Higher = more likely to be pathogenic)
cadd_scores = np.random.uniform(0, 40, n)

# Feature 3: GERP Score (Conservation: higher = more conserved/essential)
gerp_scores = np.random.uniform(-2, 6, n)

# Feature 4: Consequence Type (Categorical)
consequences = np.random.choice(['Missense', 'Nonsense', 'Synonymous', 'Frameshift'], n)

# Logic to generate the label 'Is_Dangerous'
# If CADD > 20 and Frequency is low, it's likely Pathogenic (1)
is_dangerous = []
for i in range(n):
    score = 0
    if cadd_scores[i] > 20: score += 1
    if allele_freq[i] < 0.005: score += 1
    if gerp_scores[i] > 3: score += 1
    if consequences[i] in ['Nonsense', 'Frameshift']: score += 2
    
    # Label as 1 (Dangerous) if score is high, else 0 (Benign)
    is_dangerous.append(1 if score >= 3 else 0)

# Create DataFrame
df = pd.DataFrame({
    'Variant_ID': variant_ids,
    'Allele_Freq': allele_freq,
    'CADD_Score': cadd_scores,
    'GERP_Score': gerp_scores,
    'Consequence': consequences,
    'Is_Dangerous': is_dangerous
})

# Display the first 10 rows
print(df.head(10))

# Save to CSV
df.to_csv('Model.csv', index=False)