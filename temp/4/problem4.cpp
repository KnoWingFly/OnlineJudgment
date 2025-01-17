#include <stdio.h>

int main(){
	int t;
	float x;
	freopen("in","r",stdin);
	freopen("out","w",stdout);
	scanf("%d", &t);
	for(int i=0; i<t; i++){
		scanf("%f", &x);
		printf("Case #%d: %.2f persen\n", i+1, 2/x*100);
	}
	
	return 0;
}
