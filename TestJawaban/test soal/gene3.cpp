#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <limits.h>

#define MAX_N 100
#define NUM_CASES 20

// Helper function to generate sorted array
void generateSortedArray(int size, int* arr) {
    int current = rand() % 10; // Start with small number
    for(int i = 0; i < size; i++) {
        arr[i] = current;
        current += rand() % 5; // Increment by 0-4 to maintain sorting
    }
}

// Calculate median for verification
double calculateMedian(int* nums1, int nums1Size, int* nums2, int nums2Size) {
    int totalSize = nums1Size + nums2Size;
    int merged[MAX_N * 2];
    int i = 0, j = 0, k = 0;
    
    // Merge arrays
    while (i < nums1Size && j < nums2Size) {
        if (nums1[i] <= nums2[j]) {
            merged[k++] = nums1[i++];
        } else {
            merged[k++] = nums2[j++];
        }
    }
    
    while (i < nums1Size) merged[k++] = nums1[i++];
    while (j < nums2Size) merged[k++] = nums2[j++];
    
    // Calculate median
    if (totalSize % 2 == 0) {
        return (merged[totalSize/2 - 1] + merged[totalSize/2]) / 2.0;
    } else {
        return merged[totalSize/2];
    }
}

void generateTestCase() {
    int nums1[MAX_N], nums2[MAX_N];
    int size1 = 1 + rand() % (MAX_N/2); // Keep sizes reasonable
    int size2 = 1 + rand() % (MAX_N/2);
    
    generateSortedArray(size1, nums1);
    generateSortedArray(size2, nums2);
    
    // Print input format
    printf("%d %d\n", size1, size2);
    for(int i = 0; i < size1; i++) {
        printf("%d ", nums1[i]);
    }
    printf("\n");
    for(int i = 0; i < size2; i++) {
        printf("%d ", nums2[i]);
    }
    printf("\n");
    
    // Calculate and print expected output
    double median = calculateMedian(nums1, size1, nums2, size2);
    fprintf(stderr, "%.1f\n", median);
}

int main() {
    freopen("in", "w", stdout);
    freopen("out", "w", stderr);
    srand(time(NULL));

    printf("%d\n", NUM_CASES);
    
    for(int t = 1; t <= NUM_CASES; t++) {
        printf("=== Case %d ===\n", t);
        fprintf(stderr, "=== Case %d ===\n", t);
        generateTestCase();
    }
    
    return 0;
}