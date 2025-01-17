#!/bin/bash

# Save as test_problem.sh and make executable
echo "Testing problem submission..."

# Create test directory
TEST_DIR="test_submission"
mkdir -p $TEST_DIR

# Copy your solution to test directory
cp solution.cpp $TEST_DIR/

# Verify solution content
echo "Verifying solution content..."
cat $TEST_DIR/solution.cpp

# Test compilation
echo "Testing compilation..."
g++ -Wall -O2 $TEST_DIR/solution.cpp -o $TEST_DIR/a.out
if [ $? -eq 0 ]; then
    echo "Compilation successful"
else
    echo "Compilation failed"
    exit 1
fi

# Test with sample input
echo "Testing with sample input..."
cat problems/1/in | $TEST_DIR/a.out > $TEST_DIR/myout
if [ $? -eq 0 ]; then
    echo "Execution successful"
else
    echo "Execution failed"
    exit 1
fi

# Compare output
echo "Comparing output..."
diff -w $TEST_DIR/myout problems/1/out
if [ $? -eq 0 ]; then
    echo "Output matches expected result"
else
    echo "Output differs from expected result"
    diff -w $TEST_DIR/myout problems/1/out
fi

# Clean up
rm -rf $TEST_DIR
