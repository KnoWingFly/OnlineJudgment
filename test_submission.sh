#!/bin/bash

# Save as test_submission.sh and run with: bash test_submission.sh

echo "Testing submission process..."

# Create test C++ file
cat > test_submission.cpp << 'EOL'
#include <iostream>
using namespace std;

int main() {
    int n;
    cin >> n;
    cout << n << endl;
    return 0;
}
EOL

# Test direct compilation
echo "Testing direct compilation..."
g++ test_submission.cpp -o test_submission
if [ $? -eq 0 ]; then
    echo "Direct compilation successful"
else
    echo "Direct compilation failed"
    exit 1
fi

# Test onj script directly
echo "Testing onj script..."
./onj ./test_submission.cpp 1
RESULT=$?
echo "onj exit code: $RESULT"

# Check permissions
echo -e "\nChecking critical permissions..."
ls -l onj
ls -ld problems/
ls -l problems/1/
echo -e "\nChecking write permissions in upload directory..."
UPLOAD_DIR="./codes"  # Adjust this to match your $CODEDIR
if [ -d "$UPLOAD_DIR" ]; then
    ls -ld "$UPLOAD_DIR"
    echo "Upload directory exists"
else
    echo "Upload directory missing!"
fi

# Clean up
rm -f test_submission.cpp test_submission

# Display recent logs
echo -e "\nRecent judge.log entries (if exists):"
if [ -f judge.log ]; then
    tail -n 20 judge.log
else
    echo "No judge.log file found"
fi