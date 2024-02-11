Preduslovi
Pre nego što započnete sa instalacijom, uverite se da imate instaliran Composer i PHP na vašem sistemu.

Instalacioni proces

1. Kloniranje repozitorijuma
Započnite sa kloniranjem koda na vaš lokalni sistem:

git clone git@github.com:bjelicb/password_manager_api.git

2. Ulazak u direktorijum projekta
Nakon kloniranja, promenite trenutni direktorijum na direktorijum projekta:

cd password_manager_api

3. Instalacija zavisnosti
Koristite Composer da instalirate sve potrebne PHP zavisnosti:

composer install

4. Konfigurisanje okruženja
Kopirajte .env.example fajl u .env kako biste postavili konfiguracione promenljive:

cp .env.example .env

5. Generisanje ključa aplikacije
Generišite jedinstveni ključ aplikacije:

php artisan key:generate

6. Kreiranje baze podataka
Ako baza podataka još uvek nije kreirana, koristite sledeću komandu koja će kreirati bazu podataka definisanu u .env fajlu:

php artisan database:create-if-not-exist

Ova komanda takođe izvršava sve migracije potrebne za aplikaciju.

7. JWT token

php artisan jwt:secret

8. Pokretanje servera
Za kraj, pokrenite ugrađeni razvojni server:

php artisan serve

Otvorite http://localhost:8000.