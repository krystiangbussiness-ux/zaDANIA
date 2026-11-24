<?php
declare(strict_types=1);

const SQL_KLASY = 'SELECT id, nazwa FROM klasa ORDER BY nazwa, id';

const SQL_WYCHOWAWCA = '
    SELECT imie, nazwisko
    FROM wychowawca
    WHERE id_klasy = ?
    ORDER BY id
    LIMIT 1
';

const SQL_OCENY = '
    SELECT u.Imie, u.Nazwisko, u.Srednia_ocen
    FROM uczen AS u
    JOIN klasa AS k ON k.id = u.id_klasy
    WHERE k.id = ?
    ORDER BY u.id
';

const SQL_DODAJ_UCZNIA = '
    INSERT INTO uczen (Imie, Nazwisko, Srednia_ocen, id_klasy)
    SELECT ?, ?, ?, id
    FROM klasa
    WHERE id = ?
';

function tekst(?string $wartosc): string
{
    return htmlspecialchars($wartosc ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function poleZFormularza(string $nazwa): string
{
    $wartosc = $_POST[$nazwa] ?? '';
    return is_string($wartosc) ? trim($wartosc) : '';
}

function wybranaKlasa(array $klasy): ?int
{
    $id = filter_var(poleZFormularza('klasa'), FILTER_VALIDATE_INT);
    return $id !== false && isset($klasy[$id]) ? $id : null;
}
