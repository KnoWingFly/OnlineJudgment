import sys
from array import array

class Item:
    __slots__ = ['m', 't']
    def __init__(self, m=0, t=0):
        self.m = m
        self.t = t

def solve(W, n, items):
    # Use 1D arrays instead of 2D to save memory and improve cache usage
    prev_K = array('i', [0] * (W + 1))
    curr_K = array('i', [0] * (W + 1))
    
    # Main DP loop
    for i in range(1, n + 1):
        item = items[i-1]
        for w in range(W + 1):
            if item.t <= w:
                curr_K[w] = max(item.m + prev_K[w-item.t], prev_K[w])
            else:
                curr_K[w] = prev_K[w]
        # Swap arrays
        prev_K, curr_K = curr_K, prev_K
    
    return prev_K[W]

def main():
    # Fast input
    input = sys.stdin.readline
    
    # Pre-allocate items array
    items = [Item() for _ in range(10000)]
    
    # Process test cases
    c = int(input())
    for i in range(c):
        n, l, j = map(int, input().split())
        
        # Read items
        for k in range(n):
            m, t = map(int, input().split())
            items[k].m = m
            items[k].t = t
            
        # Print result
        print(f"Case #{i+1} : {solve(j, l, items)}")

if __name__ == "__main__":
    main()