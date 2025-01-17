#include <stdio.h>
#include <stdlib.h>
#include <conio.h>
int min(int a, int b){
    return a > b;
}
int max(int a,int b){
    return a < b;
}
int main(){
    int a;
    int list[100];
    scanf("%d",&a);
    for(int i = 0; i < a;i++){
        scanf("%d",&list[i]);
    }

    bool x = true;
    while(x){
        system("cls");
        int opsi;
        printf("1. Sort data Ascending\n");
        printf("2. Sort data Descending\n");
        printf("3. Exit\n");
        printf("Pilihan anda : ");
        scanf("%d",&opsi);
        switch(opsi){
            case 1 :
                for(int k = a-1; k >= 0;k--){
                    for(int j = k/2; j >=0;j--){
                        //start of heapify
                        for(int i = j; i <(k+1)/2;i++){
                            int swap;
                            if(i*2+2 >= k) swap = i*2+1;
                            else swap = min(list[i*2+1], list[i*2+2]) ? i*2+2 : i*2+1;
                            if( min(list[i],list[swap])){
                                int temp = list[i];
                                list[i] = list[swap];
                                list[swap] = temp;
                            }
                        }
                    }
                    int temp = list[0];
                    list[0] = list[k];
                    list[k] = temp;
                }
                for(int i = 0,c = 1; i < a;i++){
                    printf(" %d ",list[i]);
                }
                printf("\n");
                getch();
                break;
            case 2 :

                for(int k = a-1; k >= 0;k--){
                    for(int j = k/2; j >=0;j--){
                        //start of heapify
                        for(int i = j; i <(k+1)/2;i++){
                            int swap;
                            if(i*2+2 >= k) swap = i*2+1;
                            else swap = max(list[i*2+1], list[i*2+2]) ? i*2+2 : i*2+1;
                            if( max(list[i],list[swap])){
                                int temp = list[i];
                                list[i] = list[swap];
                                list[swap] = temp;
                            }
                        }
                    }
                    int temp = list[0];
                    list[0] = list[k];
                    list[k] = temp;
                }
                for(int i = 0,c = 1; i < a;i++){
                    printf(" %d ",list[i]);
                }
                printf("\n");
                getch();
                break;
            case 3 :
                x = false;
                break;
        }
    }


    return 0;
}
