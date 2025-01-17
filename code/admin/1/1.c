#include <stdio.h>
#include <stdlib.h>

// Struktur untuk menyimpan data pelanggan
typedef struct {
    int money;
    int orders;
} Customer;

// Fungsi komparator untuk qsort
int compare(const void* a, const void* b) {
    Customer* customerA = (Customer*)a;
    Customer* customerB = (Customer*)b;
    
    // Menghitung profit per unit waktu
    float ratioA = (float)customerA->money / customerA->orders;
    float ratioB = (float)customerB->money / customerB->orders;
    
    // Sort descending (dari yang terbesar)
    if (ratioB > ratioA) return 1;
    if (ratioB < ratioA) return -1;
    return 0;
}

int main() {
    int C;
    scanf("%d", &C);
    
    // Iterasi untuk setiap kasus
    for (int caseNum = 1; caseNum <= C; caseNum++) {
        int N, L, J;
        scanf("%d %d %d", &N, &L, &J);
        
        // Alokasi array untuk menyimpan data pelanggan
        Customer* customers = (Customer*)malloc(N * sizeof(Customer));
        
        // Baca data pelanggan
        for (int i = 0; i < N; i++) {
            scanf("%d %d", &customers[i].money, &customers[i].orders);
        }
        
        // Sort pelanggan berdasarkan profit per unit waktu
        qsort(customers, N, sizeof(Customer), compare);
        
        int totalProfit = 0;
        int remainingProducts = J;
        int currentQueue = 0;
        
        // Proses pelanggan yang sudah diurutkan
        for (int i = 0; i < N && remainingProducts > 0; i++) {
            // Cek apakah masih ada kapasitas antrian dan produk
            if (currentQueue + customers[i].orders <= L) {
                // Hitung berapa produk yang bisa diambil
                int productsToTake = customers[i].orders;
                if (productsToTake > remainingProducts) {
                    productsToTake = remainingProducts;
                }
                
                // Update nilai
                totalProfit += customers[i].money;
                remainingProducts -= productsToTake;
                currentQueue += customers[i].orders;
            }
        }
        
        printf("Case #%d : %d\n", caseNum, totalProfit);
        
        // Bebaskan memori
        free(customers);
    }
    
    return 0;
}