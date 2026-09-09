#!/usr/bin/env python3
"""
ML Model API for Genetic Variant Classification
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
import json
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Load the trained model and data
MODEL_PATH = os.path.join(os.path.dirname(__file__), 'Model', 'Model.csv')

def load_model_and_data():
    """Load data and train the model"""
    # Load data
    df = pd.read_csv(MODEL_PATH)
    
    # Prepare features and labels
    X = df[['Allele_Freq', 'CADD_Score', 'GERP_Score']].copy()
    
    # Encode consequence type
    consequence_mapping = {'Missense': 0, 'Nonsense': 1, 'Synonymous': 2, 'Frameshift': 3}
    X['Consequence_Encoded'] = df['Consequence'].map(consequence_mapping)
    
    # Reorder columns to match training
    X = X[['Allele_Freq', 'CADD_Score', 'GERP_Score', 'Consequence_Encoded']]
    
    y = df['Is_Dangerous']
    
    # Train model
    clf = RandomForestClassifier(n_estimators=100, random_state=42)
    clf.fit(X, y)
    
    return clf, consequence_mapping

# Load model at startup
clf, consequence_mapping = load_model_and_data()

@app.route('/api/predict', methods=['POST'])
def predict():
    """
    Predict if a genetic variant is dangerous
    
    Expected JSON:
    {
        "allele_freq": float,
        "cadd_score": float,
        "gerp_score": float,
        "consequence": string
    }
    """
    try:
        data = request.get_json()
        
        # Validate input
        required_fields = ['allele_freq', 'cadd_score', 'gerp_score', 'consequence']
        if not all(field in data for field in required_fields):
            return jsonify({'error': 'Missing required fields'}), 400
        
        # Extract and validate values
        allele_freq = float(data['allele_freq'])
        cadd_score = float(data['cadd_score'])
        gerp_score = float(data['gerp_score'])
        consequence = data['consequence']
        
        # Validate ranges
        if not (0 <= allele_freq <= 1):
            return jsonify({'error': 'Allele frequency must be between 0 and 1'}), 400
        if not (0 <= cadd_score <= 40):
            return jsonify({'error': 'CADD score must be between 0 and 40'}), 400
        if not (-2 <= gerp_score <= 6):
            return jsonify({'error': 'GERP score must be between -2 and 6'}), 400
        if consequence not in consequence_mapping:
            return jsonify({'error': f'Consequence must be one of {list(consequence_mapping.keys())}'}), 400
        
        # Prepare input for model
        consequence_encoded = consequence_mapping[consequence]
        X_input = pd.DataFrame({
            'Allele_Freq': [allele_freq],
            'CADD_Score': [cadd_score],
            'GERP_Score': [gerp_score],
            'Consequence_Encoded': [consequence_encoded]
        })
        
        # Make prediction
        prediction = clf.predict(X_input)[0]
        probability = clf.predict_proba(X_input)[0]
        
        return jsonify({
            'success': True,
            'prediction': int(prediction),
            'is_dangerous': bool(prediction),
            'probability_benign': float(probability[0]),
            'probability_dangerous': float(probability[1]),
            'risk_level': 'HIGH' if prediction == 1 else 'LOW'
        })
    
    except ValueError as e:
        return jsonify({'error': f'Invalid input: {str(e)}'}), 400
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({'status': 'ok'})

if __name__ == '__main__':
    app.run(debug=True, port=5001, host='0.0.0.0')
