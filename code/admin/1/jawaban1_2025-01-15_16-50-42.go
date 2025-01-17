package main

import (
    "bufio"
    "fmt"
    "os"
)

const MAXN = 10010

type Item struct {
    m, t int
}

// Using 1D arrays instead of 2D for better cache performance
type Solution struct {
    dp  []int
    ans []int
    ko  []Item
}

func max(a, b int) int {
    if a > b {
        return a
    }
    return b
}

func NewSolution(maxWeight int) *Solution {
    return &Solution{
        dp:  make([]int, maxWeight+1),
        ans: make([]int, maxWeight+1),
        ko:  make([]Item, MAXN),
    }
}

func (s *Solution) solve(W, n int) int {
    // Create temporary array for previous state
    prev := make([]int, W+1)
    prevAns := make([]int, W+1)
    
    // Reset base case
    for w := 0; w <= W; w++ {
        s.dp[w] = 0
        s.ans[w] = 0
    }
    
    // Use 1D DP approach
    for i := 0; i < n; i++ {
        // Save previous state
        copy(prev, s.dp)
        copy(prevAns, s.ans)
        
        item := s.ko[i]
        for w := item.t; w <= W; w++ {
            include := item.m + prev[w-item.t]
            if include > prev[w] {
                s.dp[w] = include
                s.ans[w] = prevAns[w-item.t] + 1
            }
        }
    }
    
    return s.dp[W]
}

func main() {
    // Use buffered I/O for faster input
    scanner := bufio.NewScanner(os.Stdin)
    scanner.Split(bufio.ScanWords)
    
    read := func() int {
        scanner.Scan()
        var n int
        fmt.Sscanf(scanner.Text(), "%d", &n)
        return n
    }
    
    c := read()
    solution := NewSolution(MAXN)
    
    for i := 0; i < c; i++ {
        n := read()
        l := read()
        j := read()
        
        // Read items
        for k := 0; k < n; k++ {
            solution.ko[k].m = read()
            solution.ko[k].t = read()
        }
        
        fmt.Printf("Case #%d : %d\n", i+1, solution.solve(j, l))
    }
}