<?php
declare(strict_types=1);

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/dziennik.php';

$tytulWariantu = 'MySQLi proceduralnie';
$adresFormularza = 'index.php';
$klasy = [];
$klasa = null;
$wychowawca = null;
$uczniowie = [];
$blad = '';
$polaczenie = null;

try {
    $polaczenie = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($polaczenie, 'utf8mb4');

    $wynikKlas = mysqli_query($polaczenie, SQL_KLASY);
    while ($wiersz = mysqli_fetch_assoc($wynikKlas)) {
        $klasy[(int) $wiersz['id']] = $wiersz['nazwa'];
    }
    mysqli_free_result($wynikKlas);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $klasa = wybranaKlasa($klasy);
        if ($klasa === null) {
            $blad = 'Wybierz klasę z listy.';
        } else {
            $zapytanie = mysqli_prepare($polaczenie, SQL_WYCHOWAWCA);
            mysqli_stmt_bind_param($zapytanie, 'i', $klasa);
            mysqli_stmt_execute($zapytanie);
            $wynik = mysqli_stmt_get_result($zapytanie);
            $wychowawca = mysqli_fetch_assoc($wynik);
            mysqli_free_result($wynik);
            mysqli_stmt_close($zapytanie);

            $zapytanie = mysqli_prepare($polaczenie, SQL_OCENY);
            mysqli_stmt_bind_param($zapytanie, 'i', $klasa);
            mysqli_stmt_execute($zapytanie);
            $wynik = mysqli_stmt_get_result($zapytanie);
            $liczbaUczniow = mysqli_num_rows($wynik);
            while ($wiersz = mysqli_fetch_assoc($wynik)) {
                $uczniowie[] = $wiersz;
            }
            mysqli_free_result($wynik);
            mysqli_stmt_close($zapytanie);
        }
    }
} catch (mysqli_sql_exception $wyjatek) {
    error_log($wyjatek->getMessage());
    http_response_code(503);
    $blad = 'Nie można odczytać danych. Sprawdź, czy MySQL działa i czy wgrano bazę szkola.';
} finally {
    if ($polaczenie instanceof mysqli) {
        mysqli_close($polaczenie);
    }
}

require __DIR__ . '/widok-ocen.php';
