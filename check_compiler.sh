#!/bin/bash

echo "Checking compilation environment..."

# Check GCC
echo -e "\nChecking GCC..."
if which gcc > /dev/null; then
    gcc --version
    echo "GCC location: $(which gcc)"
else
    echo "GCC not found!"
fi

# Check G++
echo -e "\nChecking G++..."
if which g++ > /dev/null; then
    g++ --version
    echo "G++ location: $(which g++)"
else
    echo "G++ not found!"
fi

# Check Java
echo -e "\nChecking Java..."
if which java > /dev/null; then
    java -version
    echo "Java location: $(which java)"
else
    echo "Java not found!"
fi

# Check javac
echo -e "\nChecking Java Compiler..."
if which javac > /dev/null; then
    javac -version
    echo "Javac location: $(which javac)"
else
    echo "Javac not found!"
fi

# Check Python
echo -e "\nChecking Python..."
if which python > /dev/null; then
    python --version
    echo "Python location: $(which python)"
else
    echo "Python not found!"
fi

# Check Go
echo -e "\nChecking Go..."
if which go > /dev/null; then
    go version
    echo "Go location: $(which go)"
    # Check GOPATH
    echo "GOPATH: $GOPATH"
    # Test Go compilation
    echo -e "\nTesting basic Go compilation..."
    echo 'package main\n\nfunc main() {}' > test.go
    go build test.go 2>&1
    if [ $? -eq 0 ]; then
        echo "Basic Go compilation successful"
    else
        echo "Basic Go compilation failed"
    fi
    rm -f test.go test
else
    echo "Go not found!"
fi

# Check permissions
echo -e "\nChecking permissions..."
echo "Current user: $(whoami)"
echo "User groups: $(groups)"

# Test compilation
echo -e "\nTesting basic compilation..."
echo 'int main() { return 0; }' > test.cpp
g++ test.cpp -o test 2>&1
if [ $? -eq 0 ]; then
    echo "Basic C++ compilation successful"
else
    echo "Basic C++ compilation failed"
fi
rm -f test.cpp test

# Check library availability
echo -e "\nChecking math library..."
echo '#include <math.h>\nint main() { sqrt(2.0); return 0; }' > test.cpp
g++ -lm test.cpp -o test 2>&1
if [ $? -eq 0 ]; then
    echo "Math library linking successful"
else
    echo "Math library linking failed"
fi
rm -f test.cpp test