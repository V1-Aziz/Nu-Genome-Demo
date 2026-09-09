#!/bin/bash
# Start the ML Model API for Genetic Variant Classification
# This script starts the Flask API on port 8000

cd "$(dirname "$0")"

echo "Starting ML Model API..."
echo "The API will be available at: http://localhost:8000"
echo ""
echo "To stop the server, press Ctrl+C"
echo ""

python3 api.py
