#include <stdio.h>


int main(){
    freopen("file.in","r",stdin);
    freopen("file.out","w",stdout);
    int a;
    scanf("%d",&a);
    for(int i = 0; i < a;i++){
        char sampah;
        scanf("%c",&sampah);
        char aa,bb;
        scanf("%c + %c",&aa,&bb);
        int sum = aa-'A'+1 + bb-'A'+1;
        if(sum%26 == 0) printf("Case #%d: Z\n",i+1);
        else printf("Case #%d: %c\n",i+1,sum%26+'A'-1);
    }

    return 0;
}
