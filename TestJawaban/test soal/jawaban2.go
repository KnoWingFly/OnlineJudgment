package main

import (
    "bufio"
    "fmt"
    "os"
    "strconv"
    "strings"
)

type ListNode struct {
    Val  int
    Next *ListNode
}

func addTwoNumbers(l1, l2 *ListNode) *ListNode {
    dummy := &ListNode{Val: 0}
    curr := dummy
    carry := 0
    
    for l1 != nil || l2 != nil || carry != 0 {
        sum := carry
        if l1 != nil {
            sum += l1.Val
            l1 = l1.Next
        }
        if l2 != nil {
            sum += l2.Val
            l2 = l2.Next
        }
        carry = sum / 10
        curr.Next = &ListNode{Val: sum % 10}
        curr = curr.Next
    }
    return dummy.Next
}

func solve() {
    scanner := bufio.NewScanner(os.Stdin)
    scanner.Split(bufio.ScanWords)
    
    // Read n1 and n2
    scanner.Scan()
    n1, _ := strconv.Atoi(scanner.Text())
    scanner.Scan()
    n2, _ := strconv.Atoi(scanner.Text())
    
    // Build first list
    var l1, tail1 *ListNode
    for i := 0; i < n1; i++ {
        scanner.Scan()
        val, _ := strconv.Atoi(scanner.Text())
        node := &ListNode{Val: val}
        if l1 == nil {
            l1 = node
            tail1 = node
        } else {
            tail1.Next = node
            tail1 = node
        }
    }
    
    // Build second list
    var l2, tail2 *ListNode
    for i := 0; i < n2; i++ {
        scanner.Scan()
        val, _ := strconv.Atoi(scanner.Text())
        node := &ListNode{Val: val}
        if l2 == nil {
            l2 = node
            tail2 = node
        } else {
            tail2.Next = node
            tail2 = node
        }
    }
    
    // Calculate result
    result := addTwoNumbers(l1, l2)
    
    // Print result
    var output []string
    for curr := result; curr != nil; curr = curr.Next {
        output = append(output, strconv.Itoa(curr.Val))
    }
    fmt.Println(strings.Join(output, " "))
}

func main() {
    solve()
}