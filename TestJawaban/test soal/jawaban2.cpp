#include <iostream>

// Definition for singly-linked list.
struct ListNode {
    int val;
    ListNode* next;
    ListNode(int x) : val(x), next(nullptr) {}
};

// Adds two numbers represented by linked lists.
ListNode* addTwoNumbers(ListNode* l1, ListNode* l2) {
    ListNode dummy(0);
    ListNode* curr = &dummy;
    int carry = 0;
    
    while (l1 || l2 || carry) {
        int sum = carry;
        if (l1) { 
            sum += l1->val;
            l1 = l1->next;
        }
        if (l2) {
            sum += l2->val;
            l2 = l2->next;
        }
        carry = sum / 10;
        curr->next = new ListNode(sum % 10);
        curr = curr->next;
    }
    
    return dummy.next;
}

// The solve() function expected by the online judge.
void solve() {
    int n1, n2;
    std::cin >> n1 >> n2;
    
    // Build first linked list.
    ListNode* l1 = nullptr;
    ListNode* tail1 = nullptr;
    for (int i = 0; i < n1; i++) {
        int val;
        std::cin >> val;
        ListNode* node = new ListNode(val);
        if (!l1) {
            l1 = node;
            tail1 = node;
        } else {
            tail1->next = node;
            tail1 = node;
        }
    }
    
    // Build second linked list.
    ListNode* l2 = nullptr;
    ListNode* tail2 = nullptr;
    for (int i = 0; i < n2; i++) {
        int val;
        std::cin >> val;
        ListNode* node = new ListNode(val);
        if (!l2) {
            l2 = node;
            tail2 = node;
        } else {
            tail2->next = node;
            tail2 = node;
        }
    }
    
    // Compute the sum of the two numbers.
    ListNode* result = addTwoNumbers(l1, l2);
    
    // Print the result with values separated by spaces.
    bool first = true;
    for (ListNode* curr = result; curr != nullptr; curr = curr->next) {
        if (!first)
            std::cout << " ";
        std::cout << curr->val;
        first = false;
    }
    std::cout << "\n";
    
    // Clean up allocated memory.
    while (l1) {
        ListNode* tmp = l1;
        l1 = l1->next;
        delete tmp;
    }
    while (l2) {
        ListNode* tmp = l2;
        l2 = l2->next;
        delete tmp;
    }
    while (result) {
        ListNode* tmp = result;
        result = result->next;
        delete tmp;
    }
}
