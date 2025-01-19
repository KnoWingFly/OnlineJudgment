#include<bits/stdc++.h>

using namespace std;

int main() {
    srand(time(NULL));
    int N = 1000;
    freopen("in","w",stdout);
    printf("100\n");
    for (int i = 1; i <= 98; i++) {
        int n = (rand() % N) + 1;
        int k = (rand() % n) + 1;
        printf("%d %d\n",n,k);
    }
    printf("1000 500\n");
    printf("999 356\n");
    return 0;
}
