#include <stdio.h>

int luas, keliling;

void persegi()
{
    int sisi;
    printf ("Masukkan panjang sisi persegi = ");
    scanf ("%d",&sisi);
    luas = sisi*sisi;
    keliling = sisi*4;
    printf ("Luas persegi = %d\n",luas);
    printf ("Keliling persegi = %d\n",keliling);
    return;
}

void persegi_p()
{
    int p,l;
    printf ("Masukkan panjang = ");
    scanf ("%d",&p);
    printf ("Masukkan lebar = ");
    scanf ("%d",&l);
    luas = p*l;
    keliling = 2*(p+l);
    printf ("Luas persegi panjang = %d\n",luas);
    printf ("Keliling persegi panjang = %d\n",keliling);
    return;
}

void lingkaran()
{
    int r;
    printf ("Masukkan jari-jari lingkaran = ");
    scanf ("%d",&r);
    luas = 22/7*r*r;
    keliling = 22/7*2*r;
    printf ("Luas lingkaran = %d\n",luas);
    printf ("Keliling lingkaran = %d\n",keliling);
    return;
}

int main ()
{
    int kode;
    char ans;
    do
    {
        printf ("Masukkan kode = ");
        scanf ("%d",&kode);
        /* do
        {
            printf ("Masukkan kode = ");
            scanf ("%d",&kode);
        }while(kode!=0); */
        if (kode==1) persegi();
        if (kode==2) persegi_p();
        if (kode==3) lingkaran();
        if (kode==4) return 0;
        printf ("Masih mau menghitung? (y/n)");
        fflush(stdin);
        scanf ("%c",&ans);
    }while(ans!='n');
    return 0;
}
