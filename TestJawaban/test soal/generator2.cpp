#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int main() {
    freopen("in", "w", stdout);
    freopen("out", "w", stderr);  // Use stderr for expected output
    srand(time(NULL));

    int numCases = 20;
    printf("%d\n", numCases);  // First line is number of test cases

    for(int t = 1; t <= numCases; t++) {
        // Print clear case boundary marker
        printf("=== Case %d ===\n", t);
        fprintf(stderr, "=== Case %d ===\n", t);

        // Generate test case
        int n1 = 1 + rand() % 5;
        int n2 = 1 + rand() % 5;

        printf("%d %d\n", n1, n2);

        // Generate first number
        for(int i = 0; i < n1; i++) {
            printf("%d", rand() % 10);
            if(i < n1-1) printf(" ");
        }
        printf("\n");

        // Generate second number
        for(int i = 0; i < n2; i++) {
            printf("%d", rand() % 10);
            if(i < n2-1) printf(" ");
        }
        printf("\n");

        // Generate expected output for this case
        // First collect digits into an array
        int result[20] = {0};  // Assuming max 20 digits
        int carry = 0;
        int len = 0;
        
        // Simulate linked list addition
        for(int i = 0; i < n1 || i < n2 || carry; i++) {
            int sum = carry;
            if(i < n1) sum += rand() % 10;
            if(i < n2) sum += rand() % 10;
            
            result[len++] = sum % 10;
            carry = sum / 10;
        }

        // Print result
        for(int i = len-1; i >= 0; i--) {
            fprintf(stderr, "%d", result[i]);
            if(i > 0) fprintf(stderr, " ");
        }
        fprintf(stderr, "\n");
    }

    return 0;
}