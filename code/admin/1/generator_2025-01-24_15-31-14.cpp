#include <stdio.h>
#include <stdlib.h>
#include <time.h>
int main(){
    int n;
    // freopen("in","w",stdout);
    srand(time(NULL));
    printf("%d\n",20);
    for(int i = 0; i < 20;i++){
        int a,b,c;
        a = 1+rand()%10000;
        b = 1+rand()%10000;
        c = 1+rand()%2000;
        printf("%d %d %d\n",a,b,c);
        for(int j = 0; j < a;j++){
            int d,e;
            d = 1+rand()%100;
            e = 1+rand()%10000;
            printf("%d %d\n",d,e);
        }
    }
    return 0;
}
