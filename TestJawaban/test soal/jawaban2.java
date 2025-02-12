import java.util.Scanner;

class Solution {
    static class ListNode {
        int val;
        ListNode next;
        ListNode(int x) { val = x; }
    }

    static Scanner scanner;
    
    static void init(Scanner s) {
        scanner = s;
    }

    static ListNode addTwoNumbers(ListNode l1, ListNode l2) {
        ListNode dummy = new ListNode(0);
        ListNode curr = dummy;
        int carry = 0;
        
        while (l1 != null || l2 != null || carry != 0) {
            int sum = carry;
            if (l1 != null) {
                sum += l1.val;
                l1 = l1.next;
            }
            if (l2 != null) {
                sum += l2.val;
                l2 = l2.next;
            }
            carry = sum / 10;
            curr.next = new ListNode(sum % 10);
            curr = curr.next;
        }
        return dummy.next;
    }

    static void solve() {
        int n1 = scanner.nextInt();
        int n2 = scanner.nextInt();
        scanner.nextLine(); // consume the rest of the line
        
        // Build first list
        ListNode l1 = null, tail1 = null;
        String[] values1 = scanner.nextLine().trim().split(" ");
        for (int i = 0; i < n1; i++) {
            int val = Integer.parseInt(values1[i]);
            ListNode node = new ListNode(val);
            if (l1 == null) {
                l1 = node;
                tail1 = node;
            } else {
                tail1.next = node;
                tail1 = node;
            }
        }
        
        // Build second list
        ListNode l2 = null, tail2 = null;
        String[] values2 = scanner.nextLine().trim().split(" ");
        for (int i = 0; i < n2; i++) {
            int val = Integer.parseInt(values2[i]);
            ListNode node = new ListNode(val);
            if (l2 == null) {
                l2 = node;
                tail2 = node;
            } else {
                tail2.next = node;
                tail2 = node;
            }
        }
        
        // Calculate result
        ListNode result = addTwoNumbers(l1, l2);
        
        // Print result
        ListNode curr = result;
        while (curr != null) {
            System.out.print(curr.val);
            if (curr.next != null) System.out.print(" ");
            curr = curr.next;
        }
        System.out.println();
    }
}