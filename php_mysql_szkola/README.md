# PHP #5 · Współpraca PHP z bazą danych

Projekt korzysta z bazy `szkola` z materiałów lekcji. Obie wersje dziennika pobierają dostępne klasy z MySQL, pokazują wychowawcę, numerowane oceny i średnią klasy obliczoną do dwóch miejsc po przecinku. Formularz zapisu sprawdza imię, nazwisko, klasę i średnią w skali 0–6.

## Uruchomienie w XAMPP

1. Skopiuj folder `php_mysql_szkola` do `C:\xampp\htdocs\`.
2. Uruchom **Apache** i **MySQL**. W [phpMyAdmin](http://localhost/phpmyadmin/) utwórz pustą bazę `szkola` z kodowaniem `utf8mb4_unicode_ci`. Wybierz tę bazę i zaimportuj do niej plik `szkola.sql` z tego folderu.
3. Otwórz [dziennik szkolny](http://localhost/php_mysql_szkola/), wybierz klasę **2a** i kliknij **Pokaż oceny**. Na danych początkowych wychowawcą jest Bożena Michalska, a średnia klasy wynosi **4.19**.

`dbconnect.php` zawiera domyślne ustawienia lokalnego XAMPP. Jeśli MySQL używa innego portu lub użytkownika, ustaw odpowiednie zmienne środowiskowe `SZKOLA_DB_HOST`, `SZKOLA_DB_PORT`, `SZKOLA_DB_USER` i `SZKOLA_DB_PASS`.

| Plik | Zastosowanie |
| --- | --- |
| `index.php` | Odczyt w wariancie proceduralnym MySQLi |
| `index2.php` | Odczyt w wariancie obiektowym MySQLi |
| `insert.php` | Formularz dodawania ucznia |
| `szkola.sql` | Tabele i dane przykładowe do importu |
| `dziennik.php` | Zapytania SQL i walidacja wyboru klasy |

Materiały kursu udostępniła [Pasja informatyki](https://pasja-informatyki.pl/programowanie-webowe/php-mysqli-baza-danych/).
