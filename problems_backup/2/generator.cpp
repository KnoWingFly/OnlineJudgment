#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <vector>
#include <algorithm>
using namespace std;

// Function to generate test case and its solution
void generateTestCase(int caseNum, FILE* inFile, FILE* outFile) {
    int n = 2 + rand() % 99; // Array size (2 to 100)
    int target = rand() % 201 - 100; // Target (-100 to 100)
    
    fprintf(inFile, "Case #%d:\n", caseNum);
    fprintf(inFile, "%d %d\n", n, target);
    
    vector<int> numbers;
    // Generate array values
    for(int i = 0; i < n; i++) {
        numbers.push_back(rand() % 201 - 100); // (-100 to 100)
    }
    
    // Print numbers to input file
    for(int i = 0; i < n; i++) {
        fprintf(inFile, "%d", numbers[i]);
        if(i < n-1) fprintf(inFile, " ");
    }
    fprintf(inFile, "\n");
    
    // Find solution (first pair that sums to target)
    bool found = false;
    for(int i = 0; i < n && !found; i++) {
        for(int j = i+1; j < n; j++) {
            if(numbers[i] + numbers[j] == target) {
                fprintf(outFile, "Case #%d: %d %d\n", caseNum, i+1, j+1);
                found = true;
                break;
            }
        }
    }
    
    if(!found) {
        fprintf(outFile, "Case #%d: IMPOSSIBLE\n", caseNum);
    }
}

int main() {
    srand(time(NULL));
    
    FILE* inFile = fopen("in", "w");
    FILE* outFile = fopen("out", "w");
    
    if(!inFile || !outFile) {
        printf("Error opening files!\n");
        return 1;
    }
    
    int numCases = 20;
    fprintf(inFile, "%d\n", numCases);
    
    for(int i = 1; i <= numCases; i++) {
        generateTestCase(i, inFile, outFile);
    }
    
    fclose(inFile);
    fclose(outFile);
    return 0;
}