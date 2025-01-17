import java.util.Scanner;

public class jawaban1 {
    static class Item {
        int m, t;
        Item(int m, int t) {
            this.m = m;
            this.t = t;
        }
    }
    
    static final int MAX_SIZE = 10010;
    static int[][] K = new int[MAX_SIZE][MAX_SIZE];
    static int[][] ans = new int[MAX_SIZE][MAX_SIZE];
    static Item[] ko = new Item[MAX_SIZE];
    
    static int solve(int W, int n) {
        for (int i = 0; i <= n; i++) {
            for (int w = 0; w <= W; w++) {
                if (i == 0 || w == 0) {
                    K[i][w] = 0;
                    ans[i][w] = 0;
                }
                else if (ko[i-1].t <= w) {
                    K[i][w] = Math.max(ko[i-1].m + K[i-1][w-ko[i-1].t], K[i-1][w]);
                    if (ko[i-1].m + K[i-1][w-ko[i-1].t] > K[i-1][w]) {
                        ans[i][w] = ans[i-1][w-ko[i-1].t] + 1;
                    } else {
                        ans[i][w] = ans[i-1][w];
                    }
                }
                else {
                    K[i][w] = K[i-1][w];
                    ans[i][w] = ans[i-1][w];
                }
            }
        }
        return K[n][W];
    }
    
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        int c = scanner.nextInt();
        
        // Initialize ko array
        for (int i = 0; i < MAX_SIZE; i++) {
            ko[i] = new Item(0, 0);
        }
        
        for (int i = 0; i < c; i++) {
            int n = scanner.nextInt();
            int l = scanner.nextInt();
            int j = scanner.nextInt();
            
            for (int k = 0; k < n; k++) {
                ko[k].m = scanner.nextInt();
                ko[k].t = scanner.nextInt();
            }
            
            System.out.printf("Case #%d : %d%n", i+1, solve(j, l));
        }
        
        scanner.close();
    }
}