#include <stdio.h>
#include <string.h>
#include <vector>
#include <unordered_map>

using namespace std;

class Solution {
public:
    vector<int> twoSum(vector<int>& nums, int target) {
        unordered_map<int, int> numMap;
        int n = nums.size();

        // Build the hash table
        for (int i = 0; i < n; i++) {
            numMap[nums[i]] = i;
        }

        // Find the complement
        for (int i = 0; i < n; i++) {
            int complement = target - nums[i];
            if (numMap.count(complement) && numMap[complement] != i) {
                return {i, numMap[complement]};
            }
        }

        return {}; 
    }
};

int main() {
    // freopen("in", "r", stdin);
    // freopen("out", "w", stdout);
    int c;
    scanf("%d", &c);
    Solution solution;

    for (int i = 0; i < c; i++) {
        int n, target;
        scanf("%d %d", &n, &target);
        vector<int> nums(n);
        for (int j = 0; j < n; j++) {
            scanf("%d", &nums[j]);
        }
        vector<int> result = solution.twoSum(nums, target);
        if (!result.empty()) {
            printf("Case #%d: %d %d\n", i + 1, result[0], result[1]);
        } else {
            printf("Case #%d: No solution\n", i + 1);
        }
    }

    return 0;
}