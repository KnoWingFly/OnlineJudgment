class ListNode:
    def __init__(self, val=0, next=None):
        self.val = val
        self.next = next

def addTwoNumbers(l1, l2):
    dummy = ListNode(0)
    curr = dummy
    carry = 0
    
    while l1 or l2 or carry:
        sum_val = carry
        
        if l1:
            sum_val += l1.val
            l1 = l1.next
        if l2:
            sum_val += l2.val
            l2 = l2.next
            
        carry = sum_val // 10
        curr.next = ListNode(sum_val % 10)
        curr = curr.next
        
    return dummy.next

def solve():
    n1, n2 = map(int, input().split())
    
    # Build first linked list
    values = list(map(int, input().split()))
    l1 = dummy1 = ListNode(0)
    for val in values[:n1]:
        l1.next = ListNode(val)
        l1 = l1.next
    l1 = dummy1.next
    
    # Build second linked list
    values = list(map(int, input().split()))
    l2 = dummy2 = ListNode(0)
    for val in values[:n2]:
        l2.next = ListNode(val)
        l2 = l2.next
    l2 = dummy2.next
    
    # Compute sum and print result
    result = addTwoNumbers(l1, l2)
    output = []
    while result:
        output.append(str(result.val))
        result = result.next
    print(" ".join(output))