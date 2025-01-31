#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int main() {
    freopen("in", "w", stdout);
    srand(time(NULL));

    int c = 20; // Number of test cases
    printf("%d\n", c);

    for (int i = 0; i < c; i++) {
        int n = 2 + rand() % 100; // Array size (minimum 2 to ensure valid two-sum)
        int target = rand() % 200 - 100; // Target value between -100 and 100

        printf("%d %d\n", n, target);

        for (int j = 0; j < n; j++) {
            int num = rand() % 201 - 100; // Array values between -100 and 100
            printf("%d ", num);
        }

        printf("\n");
    }

    return 0;
}
