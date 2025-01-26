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
        a = 2+rand()%(999/(i+1));
        c = a+rand()%((200000-a-1)/(i+1));
        printf("%d %d\n",a,c);
        for(int j = 0; j < c;j++){
            int d,e,f;
            d = 1+rand()%1000;
            e = 1+rand()%1000;
            f = 1+rand()%1000000;
            printf("%d %d %d\n",d,e,f);
        }
    }
    return 0;
}
