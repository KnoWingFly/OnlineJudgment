#include<stdio.h>
#define INF 1000000007

using namespace std;
// n!/(n-r)!r!

long long sqr(long long a) {
    return a*a;
}

long long bigmod(long long b, long long p) {
    if (p == 0) return 1;
    else if (p == 1) return b;
    else if (p%2 == 0) return sqr(bigmod(b,p/2)%INF)%INF;
    else return ((b%INF) * (bigmod(b,p-1)%INF))%INF;
}

long long solve(long long n, long long k) {
    long long ans = 1;
    for (int i = 1; i <= k; i++) {
        ans = (ans%INF * ((n - i + 1)%INF)) % INF;
        ans = (ans%INF * (bigmod((long long)i,INF-2)%INF)) % INF;
    }
    return ans;
}

int main() {
    int tc;
    freopen("in","r",stdin);
    freopen("out","w",stdout);
    scanf("%d",&tc);
    long long n, k;
    for (int t = 1; t <= tc; t++) {

        scanf("%lld %lld",&n,&k);
        printf("Case #%d: %lld\n",t, solve(n,k));
    }
    return 0;
}
