#include<bits/stdc++.h>

using namespace std;

int prime[1000000];
int p[250000];
typedef vector<pair<int,pair<int,int > > > vii;
vii edgelist;

void sieve(unsigned long long upperbound){
	long long sieve_size = upperbound + 1;
	prime[0] = prime[1] = 1;
	for(long long i = 2;i <= sieve_size;i++) if(!prime[i]){
		for(long long j = i*i;j <= sieve_size;j += i) prime[j] = 1;
	}
}

void init (int n) {
	for (int i= 0; i <= n; i++) p[i] = i;
	}

int findset(int x) {
	return (p[x] == x ? x : (p[x] = findset(p[x])) );
	}

bool issameset(int x, int y) {
	return findset(x) == findset(y);
	}

void unionset(int x, int y) {
	if (!issameset(x,y)) p[findset(x)] = y;
}



int main(){
    freopen("file.in","r",stdin);
    freopen("file.out","w",stdout);
    sieve(1000000);
    int tc;
    scanf("%d",&tc);
    for(int i = 0;i < tc;i++){
        int n,m,ctr = 0;
        edgelist.clear();
        scanf("%d %d",&n,&m);
        init(n);
        for(int j = 0;j < m;j++){
            int v1,v2,w;
            scanf("%d %d %d",&v1,&v2,&w);
          //   if(i == 44) printf("%d %d\n",w,prime[w]);
            if(!prime[w]){

              edgelist.push_back(make_pair(w,make_pair(v1,v2)));
              ctr++;
            }
        }

        sort(edgelist.begin(),edgelist.end());
        int mst = 0;
        for(int j = ctr-1; j >= 0;j--){
            pair<int,pair<int,int> > temp = edgelist[j];
            int z = temp.first;
            pair<int,int> node = temp.second;
            if (!issameset(node.first,node.second)) {
                unionset(node.first,node.second);
                mst += z;
            }
        }
        printf("Case #%d: %d\n",i+1,mst);
    }
    return 0;
}

/*
100
5 5
1 2 3
2 3 4
1 3 5
3 4 7
4 5 11
*/
