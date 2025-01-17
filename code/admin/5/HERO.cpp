#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <algorithm>
using namespace std;
typedef struct equip{
    long long int pow;
    bool taken;
};

bool f1(equip a, equip b){
    return a.pow < b.pow ? false : true;
}
equip weapon[1000000];
equip armor[1000000];

int main(){

    long long int n;
    scanf("%lld",&n);
    for(int i = 0; i < n;i++){
        long long int a;
        scanf("%lld",&a);
        for(int j = 0;j < a;j++){

            scanf("%lld",&weapon[j].pow);
            weapon[j].taken = false;
        }
        for(int j = 0; j < a;j++){

            scanf("%lld",&armor[j].pow);
            armor[j].taken = false;

        }
        bool ans = true;
        // gw sort disini ( N LOG(N))

        sort(weapon,weapon+a,f1);
        long long int la = 0;
        // Cek semua kemungkinan O(N)
        for(int j = 0; j < a;j++){
            long long int idx = -1;

            long long int min = 9999999;
            long long int low = 0;
            long long int high = a-1;
            long long int mid;

                // gw binary search disini (LOG N)
            for(; low <= high;){
                mid = (low+high)/2;
                if(weapon[mid].pow-armor[j].pow+la >= 0  && !weapon[mid].taken  ){
                    if(weapon[mid].pow-armor[j].pow <= min){
                        idx = mid;
                        la  = weapon[mid].pow-armor[j].pow+la;
                        min = weapon[mid].pow-armor[j].pow;
                        low = mid+1;
                    }
                    else {
                        high = mid-1;
                    }
                }
                else{
                    high = mid-1;
                }
            }
            if(idx >= 0){
                weapon[idx].taken = true;
            }else{
                ans = false;
                break;
            }
            // total O(N LOG N)
        }

        // overall : O(N LOG N)
        printf("Case #%d : ",i+1);
        if(ans) printf("Yes he made it\n");
        else printf("IMPOSSIBLE\n");
    }
    return 0;
}
