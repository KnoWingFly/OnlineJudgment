#include <stdio.h>
#include <stdlib.h>
#include <time.h>
int main(){
    int n;
    freopen("in","w",stdout);
    srand(time(NULL));
    printf("%d\n",100);
    for(int i = 0; i < 100;i++){
        int a,b,c;
        a = 'A'+rand()%25;
        c = 'A'+rand()%25;
        printf("%c + %c\n",a,c);
    }
    return 0;
}
