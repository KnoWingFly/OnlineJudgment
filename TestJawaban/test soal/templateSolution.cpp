#include <stdio.h>
#include <stdlib.h>
#include <string.h>

// Problem-specific constants and types
#define MAX_N 100  // Maximum input size

// Problem-specific functions
void solve() {
    // TODO: Implement solution logic
    // Example:
    // int n;
    // scanf("%d", &n);
    // int result = process(n);
    // printf("%d\n", result);
}

int main() {
    // Setup input and output files
    freopen("in", "r", stdin);
    freopen("out", "w", stdout);

    // Read number of test cases
    int T;
    scanf("%d", &T);

    // Process each test case
    char marker[100];
    for(int t = 1; t <= T; t++) {
        // Read and echo case marker
        scanf(" %[^\n]", marker);
        printf("%s\n", marker);

        // Solve this test case
        solve();
    }

    return 0;
}