<?php
declare(strict_types=1);

function SUMA(int $a, int $b): int|float
{
    return $a + $b;
}

function PODSTAWY(float $a, float $b): array
{
    return [
        'Różnica' => $a - $b,
        'Iloczyn' => $a * $b,
        'Iloraz' => $b != 0.0 ? $a / $b : null,
    ];
}

function KALKULATOR(float $a, float $b, string $dzialanie): float
{
    return match ($dzialanie) {
        'suma' => $a + $b,
        'roznica' => $a - $b,
        'iloczyn' => $a * $b,
        'iloraz' => $b != 0.0 ? $a / $b : throw new InvalidArgumentException('Nie można dzielić przez zero.'),
        default => throw new InvalidArgumentException('Wybierz działanie.'),
    };
}

function MAKS(float $a, float $b, float $c): float
{
    return max($a, $b, $c);
}

function WZROST(float $centymetry): string
{
    if ($centymetry < 150) {
        return 'Niski';
    }

    return $centymetry > 180 ? 'Wysoki' : 'Średni';
}

function BMI(float $centymetry, float $kilogramy): array
{
    $metry = $centymetry / 100;
    $wskaznik = $kilogramy / ($metry ** 2);
    $komentarz = match (true) {
        $wskaznik < 18.5 => 'za mało!',
        $wskaznik > 25 => 'za dużo!',
        default => 'OK!',
    };

    return [$wskaznik, $komentarz];
}

function STARSZY(DateTimeImmutable $pierwsza, DateTimeImmutable $druga): string
{
    if ($pierwsza == $druga) {
        return 'Obie osoby urodziły się tego samego dnia.';
    }

    return $pierwsza < $druga ? 'Pierwsza osoba jest starsza.' : 'Druga osoba jest starsza.';
}

function PRZESTĘPNY(int $rok): bool
{
    return $rok % 400 === 0 || ($rok % 4 === 0 && $rok % 100 !== 0);
}

function SIŁA(string $haslo): string
{
    $dlugosc = preg_match_all('/./us', $haslo);
    if (
        $dlugosc <= 4
        || !preg_match('/\p{Nd}/u', $haslo)
        || !preg_match('/\p{Lu}/u', $haslo)
        || !preg_match('/\p{Ll}/u', $haslo)
        || !preg_match('/[\p{P}\p{S}]/u', $haslo)
    ) {
        return 'Hasło słabe';
    }

    return $dlugosc >= 8 ? 'Hasło mocne' : 'Hasło średnie';
}

function TROJKAT(int $a, int $b, int $c): bool
{
    $boki = [$a, $b, $c];
    sort($boki, SORT_NUMERIC);

    return $boki[0] > 0 && $boki[0] > $boki[2] - $boki[1];
}

function SZYFR(string $tekst): string
{
    return strtr(
        $tekst,
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'cdefghijklmnopqrstuvwxyzabCDEFGHIJKLMNOPQRSTUVWXYZAB'
    );
}
