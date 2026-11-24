<?php
declare(strict_types=1);

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/dziennik.php';

$tytulWariantu = 'MySQLi obiektowo';
$adresFormularza = 'index2.php';
$klasy = [];
$klasa = null;
$wychowawca = null;
$uczniowie = [];
$blad = '';
$polaczenie = null;

try {
    $polaczenie = new mysqli($host, $user, $pass, $db, $port);
    $polaczenie->set_charset('utf8mb4');

    $wynikKlas = $polaczenie->query(SQL_KLASY);
    while ($wiersz = $wynikKlas->fetch_object()) {
        $klasy[(int) $wiersz->id] = $wiersz->nazwa;
    }
    $wynikKlas->free();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $klasa = wybranaKlasa($klasy);
        if ($klasa === null) {
            $blad = 'Wybierz klasę z listy.';
        } else {
            $zapytanie = $polaczenie->prepare(SQL_WYCHOWAWCA);
            $zapytanie->bind_param('i', $klasa);
            $zapytanie->execute();
            $wynik = $zapytanie->get_result();
            $wychowawca = $wynik->fetch_assoc();
            $wynik->free();
            $zapytanie->close();

            $zapytanie = $polaczenie->prepare(SQL_OCENY);
            $zapytanie->bind_param('i', $klasa);
            $zapytanie->execute();
            $wynik = $zapytanie->get_result();
            $liczbaUczniow = $wynik->num_rows;
            while ($uczen = $wynik->fetch_object()) {
                $uczniowie[] = (array) $uczen;
            }
            $wynik->free();
            $zapytanie->close();
        }
    }
} catch (mysqli_sql_exception $wyjatek) {
    error_log($wyjatek->getMessage());
    http_response_code(503);
    $blad = 'Nie można odczytać danych. Sprawdź, czy MySQL działa i czy wgrano bazę szkola.';
} finally {
    if ($polaczenie instanceof mysqli) {
        $polaczenie->close();
    }
}

require __DIR__ . '/widok-ocen.php';
