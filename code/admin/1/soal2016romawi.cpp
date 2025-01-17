//M 1000
//D 500
//C 100
//L 50
//X 10
//V 5
//I 1

#include <stdio.h>
#include <string.h>

char rom[30];

void int_to_rom (int n) {
  strcpy(rom, "");
  int idx = 0;
  while (n > 0) {
    if (n >= 1000) {
			rom[idx++] = 'M';
			n -= 1000;
    } else if (n >= 900) {
			rom[idx++] = 'C';
			rom[idx++] = 'M';
			n -= 900;
    } else if (n >= 500) {
			rom[idx++] = 'D';
			n -= 500;
    } else if (n >= 400) {
			rom[idx++] = 'C';
			rom[idx++] = 'D';
			n -= 400;
    } else if (n >= 100) {
			rom[idx++] = 'C';
			n -= 100;
    } else if (n >= 90) {
			rom[idx++] = 'X';
			rom[idx++] = 'C';
			n -= 90;
    } else if (n >= 50) {
			rom[idx++] = 'L';
			n -= 50;
    } else if (n >= 40) {
      rom[idx++] = 'X';
      rom[idx++] = 'L';
      n -= 40;
    } else if (n >= 10) {
			rom[idx++] = 'X';
			n -= 10;
    } else if (n >= 9) {
			rom[idx++] = 'I';
			rom[idx++] = 'X';
			n -= 9;
    } else if (n >= 5) {
			rom[idx++] = 'V';
			n -= 5;
    } else if (n >= 4) {
			rom[idx++] = 'I';
			rom[idx++] = 'V';
			n -= 4;
    } else {
			rom[idx++] = 'I';
			n--;
    }
  }
  rom[idx] = 0;
}

int rom_to_int () {
  int x = 0;
	int len = strlen(rom);
	for (int i = 0; i < len; i++) {
    switch (rom[i]) {
			case 'M' : x += 1000; break;
			case 'D' : x += 500; break;
			case 'L' : x += 50; break;
			case 'V' : x += 5; break;
      case 'C' : {
				if (i == len-1) x += 100;
				else {
					if (rom[i+1] == 'M') {
						x += 900;
						i++;
					} else if (rom[i+1] == 'D') {
						x += 400;
						i++;
					} else x += 100;
				}
				break;
      }
      case 'X' : {
				if (i == len-1) x += 10;
				else {
					if (rom[i+1] == 'C') {
						x += 90;
						i++;
					}	else if (rom[i+1] == 'L') {
						x += 40;
						i++;
					} else x += 10;
				}
				break;
      }
      case 'I' : {
				if (i == len-1) x += 1;
				else {
					if (rom[i+1] == 'X') {
						x += 9;
						i++;
					} else if (rom[i+1] == 'V') {
						x += 4;
						i++;
					} else x += 1;
				}
				break;
      }
    }
	}
	return x;
}

int main() {
	int tc, a, b, c;
	scanf("%d", &tc);
	for (int i = 1; i <= tc; i++) {
    getchar();
    scanf("%[^\n]", rom);
    a = rom_to_int();
    getchar();
    scanf("%[^\n]", rom);
    b = rom_to_int();
    c = a + b;
    int_to_rom(c);
    printf("Case #%d: %s\n", i, rom);
	}
	return 0;
}
