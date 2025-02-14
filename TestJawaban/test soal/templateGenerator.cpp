#include <stdio.h>
#include <stdlib.h>
#include <time.h>

// Problem-specific parameters
#define MAX_N 100        // Maximum size of input
#define NUM_CASES 20     // Number of test cases

// Problem-specific functions
void generateTestCase() {
    // TODO: Implement test case generation logic
    // Example:
    // int n = 1 + rand() % MAX_N;
    // printf("%d\n", n);
    // for(int i = 0; i < n; i++) {
    //     printf("%d ", rand() % 100);
    // }
    // printf("\n");
}

void generateExpectedOutput() {
    // TODO: Implement expected output generation
    // Example:
    // fprintf(stderr, "Expected output for this case\n");
}

int main() {
    // Setup input and output files
    freopen("in", "w", stdout);   // Test input goes to stdout
    freopen("out", "w", stderr);  // Expected output goes to stderr
    srand(time(NULL));           // Initialize random seed

    // Write number of test cases
    printf("%d\n", NUM_CASES);

    // Generate each test case
    for(int t = 1; t <= NUM_CASES; t++) {
        // Print case marker
        printf("=== Case %d ===\n", t);
        fprintf(stderr, "=== Case %d ===\n", t);

        // Generate test case input
        generateTestCase();

        // Generate expected output
        generateExpectedOutput();
    }

    return 0;
}