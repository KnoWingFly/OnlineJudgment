#include <stdio.h>
#include <string.h>
struct cons{
    int m,t;
};

cons ko[10010];

int K[10010][10010];
int ans[10010][10010];
int n,l,j,c;
int max(int a,int b){
    return a > b ? a : b;
}
int solve(int W, int n)
{
    int i, w;

  // memset(K,0,sizeof(K));
 //  memset(ans,0,sizeof(K));
   for (i = 0; i <= n; i++)
   {
       for (w = 0; w <= W; w++)
       {
           if (i==0 || w==0){
               K[i][w] = 0;
               ans[i][w] = 0;

           }
           else if (ko[i-1].t <= w){
                K[i][w] = max(ko[i-1].m + K[i-1][w-ko[i-1].t],  K[i-1][w]);
                if(ko[i-1].m + K[i-1][w-ko[i-1].t] >  K[i-1][w]) ans[i][w] = ans[i-1][w]+1;
                else ans[i][w] = ans[i-1][w];
           }
           else{
                 K[i][w] = K[i-1][w];
                ans[i][w] = ans[i-1][w];
           }

       }
   }
   return K[n][W];
}

int main(){
    // freopen("in","r",stdin);
    // freopen("out","w",stdout);
    scanf("%d",&c);
    for(int i = 0; i < c;i++){

        scanf("%d %d %d",&n,&l,&j);
        for(int k = 0; k < n;k++){
            scanf("%d %d",&ko[k].m,&ko[k].t);

        }
        printf("Case #%d : %d\n",i+1,solve(j,l));

    }

}
