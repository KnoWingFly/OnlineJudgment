
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

struct ListNode {
    int val;
    struct ListNode* next;
};

struct ListNode* newNode(int val) {
    struct ListNode* node = (struct ListNode*)malloc(sizeof(struct ListNode));
    node->val = val;
    node->next = NULL;
    return node;
}

void printList(struct ListNode* head) {
    while(head) {
        printf("%d", head->val);
        if(head->next)
            printf(" ");
        head = head->next;
    }
    printf("\n");
}

void freeList(struct ListNode* head) {
    while(head) {
        struct ListNode* temp = head;
        head = head->next;
        free(temp);
    }
}

struct ListNode* addTwoNumbers(struct ListNode* l1, struct ListNode* l2) {
    struct ListNode dummy = {0};
    struct ListNode* curr = &dummy;
    int carry = 0;
    while(l1 || l2 || carry) {
        int sum = carry;
        if(l1) { 
            sum += l1->val; 
            l1 = l1->next; 
        }
        if(l2) { 
            sum += l2->val; 
            l2 = l2->next; 
        }
        curr->next = newNode(sum % 10);
        carry = sum / 10;
        curr = curr->next;
    }
    return dummy.next;
}

// The solve() function expected by the online judge.
void solve() {
    int n1, n2;
    scanf("%d %d", &n1, &n2);
    
    struct ListNode *l1 = NULL, *curr = NULL;
    for(int i = 0; i < n1; i++) {
        int val;
        scanf("%d", &val);
        if(l1 == NULL) {
            l1 = newNode(val);
            curr = l1;
        } else {
            curr->next = newNode(val);
            curr = curr->next;
        }
    }
    
    struct ListNode *l2 = NULL;
    curr = NULL;
    for(int i = 0; i < n2; i++) {
        int val;
        scanf("%d", &val);
        if(l2 == NULL) {
            l2 = newNode(val);
            curr = l2;
        } else {
            curr->next = newNode(val);
            curr = curr->next;
        }
    }
    
    struct ListNode* result = addTwoNumbers(l1, l2);
    printList(result);
    
    freeList(l1);
    freeList(l2);
    freeList(result);
}
    

int main() {
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
