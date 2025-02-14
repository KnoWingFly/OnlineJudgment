#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <limits.h>
#include <math.h>

#define MAX_N 100

double findMedianSortedArrays(int* nums1, int nums1Size, int* nums2, int nums2Size) {
    if (nums1Size > nums2Size) {
        int* temp = nums1;
        nums1 = nums2;
        nums2 = temp;
        
        int tempSize = nums1Size;
        nums1Size = nums2Size;
        nums2Size = tempSize;
    }
    
    int x = nums1Size;
    int y = nums2Size;
    int low = 0, high = x;
    
    while (low <= high) {
        int partitionX = (low + high) / 2;
        int partitionY = (x + y + 1) / 2 - partitionX;
        
        int maxX = (partitionX == 0) ? INT_MIN : nums1[partitionX - 1];
        int maxY = (partitionY == 0) ? INT_MIN : nums2[partitionY - 1];
        
        int minX = (partitionX == x) ? INT_MAX : nums1[partitionX];
        int minY = (partitionY == y) ? INT_MAX : nums2[partitionY];
        
        if (maxX <= minY && maxY <= minX) {
            if ((x + y) % 2 == 0) {
                return ((double)fmax(maxX, maxY) + (double)fmin(minX, minY)) / 2;
            } else {
                return (double)fmax(maxX, maxY);
            }
        } else if (maxX > minY) {
            high = partitionX - 1;
        } else {
            low = partitionX + 1;
        }
    }
    
    return 1;
}

void solve() {
    int nums1[MAX_N], nums2[MAX_N];
    int size1, size2;
    
    // Read input
    scanf("%d %d", &size1, &size2);
    
    for(int i = 0; i < size1; i++) {
        scanf("%d", &nums1[i]);
    }
    
    for(int i = 0; i < size2; i++) {
        scanf("%d", &nums2[i]);
    }
    
    // Calculate and print result
    double result = findMedianSortedArrays(nums1, size1, nums2, size2);
    printf("%.1f\n", result);
}

int main() {
    // freopen("in", "r", stdin);
    // freopen("out", "w", stdout);
    
    int T;
    scanf("%d", &T);
    
    char marker[100];
    for(int t = 1; t <= T; t++) {
        scanf(" %[^\n]", marker);
        printf("%s\n", marker);
        solve();
    }
    
    return 0;
}