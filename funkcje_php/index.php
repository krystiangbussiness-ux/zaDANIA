<?php
declare(strict_types=1);

require __DIR__ . '/funkcje.php';

function tekst(string $wartosc): string
{
    return htmlspecialchars($wartosc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pole(string $nazwa): string
{
    $wartosc = $_POST[$nazwa] ?? '';
    return is_string($wartosc) ? $wartosc : '';
}

function liczba(string $pole, bool $calkowita = false, int|float|null $minimum = null): int|float
{
    $wartosc = filter_var(pole($pole), $calkowita ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT);
    if ($wartosc === false || !is_finite($wartosc) || ($minimum !== null && $wartosc < $minimum)) {
        throw new InvalidArgumentException('Podaj poprawne wartości liczbowe.');
    }

    return $wartosc;
}

function dataUrodzenia(string $pole): DateTimeImmutable
{
    $wartosc = pole($pole);
    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $wartosc);
    if ($data === false || $data->format('Y-m-d') !== $wartosc || $data->format('Y') === '0000') {
        throw new InvalidArgumentException('Podaj dwie poprawne daty w formacie RRRR-MM-DD.');
    }

    return $data;
}

function formatujLiczbe(int|float $wartosc): string
{
    if (!is_finite($wartosc)) {
        return 'Wynik poza zakresem';
    }

    return str_replace('.', ',', (string) $wartosc);
}

$operacje = ['suma' => 'Suma', 'roznica' => 'Różnica', 'iloczyn' => 'Iloczyn', 'iloraz' => 'Iloraz'];
$dwieLiczby = [['a', 'Pierwsza liczba', 'number'], ['b', 'Druga liczba', 'number']];
$zadania = [
    'suma' => ['Suma dwóch liczb całkowitych', [['a', 'Pierwsza liczba', 'number', '1'], ['b', 'Druga liczba', 'number', '1']]],
    'podstawy' => ['Różnica, iloczyn i iloraz', $dwieLiczby],
    'kalkulator' => ['Kalkulator', [...$dwieLiczby, ['dzialanie', 'Działanie', 'select']]],
    'maks' => ['Największa z trzech liczb', [...$dwieLiczby, ['c', 'Trzecia liczba', 'number']]],
    'wzrost' => ['Klasyfikacja wzrostu', [['wzrost', 'Wzrost (cm)', 'number', 'any', '0.01']]],
    'bmi' => ['BMI', [['wzrost', 'Wzrost (cm)', 'number', 'any', '0.01'], ['waga', 'Masa ciała (kg)', 'number', 'any', '0.01']]],
    'starszy' => ['Kto jest starszy', [['data1', 'Data urodzenia pierwszej osoby', 'date'], ['data2', 'Data urodzenia drugiej osoby', 'date']]],
    'przestepny' => ['Rok przestępny', [['rok', 'Rok', 'number', '1', '1']]],
    'sila' => ['Siła hasła', [['haslo', 'Przykładowe hasło', 'password']]],
    'trojkat' => ['Nierówność trójkąta', [['a', 'Pierwszy bok', 'number', '1'], ['b', 'Drugi bok', 'number', '1'], ['c', 'Trzeci bok', 'number', '1']]],
];

$zadanie = $_SERVER['REQUEST_METHOD'] === 'POST' ? pole('zadanie') : '';
if ($zadanie === 'szyfr') {
    header('Content-Type: text/plain; charset=UTF-8');
    $wejscie = pole('szyfrowany');
    if (strlen($wejscie) > 800) {
        http_response_code(400);
        exit;
    }
    echo SZYFR($wejscie);
    exit;
}

$wyniki = [];
$blad = '';
try {
    switch ($zadanie) {
        case 'suma':
            $wyniki[] = 'Suma: ' . formatujLiczbe(SUMA(liczba('a', true), liczba('b', true)));
            break;
        case 'podstawy':
            foreach (PODSTAWY(liczba('a'), liczba('b')) as $nazwa => $wartosc) {
                $wyniki[] = $nazwa . ': ' . ($wartosc === null ? 'nie można dzielić przez zero' : formatujLiczbe($wartosc));
            }
            break;
        case 'kalkulator':
            $wyniki[] = 'Wynik: ' . formatujLiczbe(KALKULATOR(liczba('a'), liczba('b'), pole('dzialanie')));
            break;
        case 'maks':
            $wyniki[] = 'Największa liczba: ' . formatujLiczbe(MAKS(liczba('a'), liczba('b'), liczba('c')));
            break;
        case 'wzrost':
            $wyniki[] = WZROST(liczba('wzrost', minimum: 0.01));
            break;
        case 'bmi':
            [$wskaznik, $komentarz] = BMI(liczba('wzrost', minimum: 0.01), liczba('waga', minimum: 0.01));
            if (!is_finite($wskaznik)) {
                throw new InvalidArgumentException('Wynik poza zakresem.');
            }
            $wyniki[] = 'BMI: ' . number_format($wskaznik, 2, ',', ' ') . ' kg/m². ' . $komentarz;
            break;
        case 'starszy':
            $wyniki[] = STARSZY(dataUrodzenia('data1'), dataUrodzenia('data2'));
            break;
        case 'przestepny':
            $rok = liczba('rok', true, 1);
            $wyniki[] = PRZESTĘPNY($rok) ? "$rok jest rokiem przestępnym." : "$rok nie jest rokiem przestępnym.";
            break;
        case 'sila':
            $haslo = pole('haslo');
            if ($haslo === '' || strlen($haslo) > 800) {
                throw new InvalidArgumentException('Podaj przykładowe hasło.');
            }
            $wyniki[] = SIŁA($haslo);
            break;
        case 'trojkat':
            $wyniki[] = TROJKAT(liczba('a', true), liczba('b', true), liczba('c', true))
                ? 'Z tych boków można zbudować trójkąt.'
                : 'Z tych boków nie można zbudować trójkąta.';
            break;
    }
} catch (InvalidArgumentException | ValueError $wyjatek) {
    $blad = $wyjatek instanceof InvalidArgumentException
        ? $wyjatek->getMessage()
        : 'Podaj poprawne dane.';
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Funkcje PHP</title>
    <link rel="stylesheet" href="../style.css">
    <script src="szyfr.js" defer></script>
</head>
<body>
<main>
    <a href="../">Wszystkie zadania</a>
    <h1>Funkcje PHP</h1>
    <div class="siatka">
        <?php foreach ($zadania as $id => [$tytul, $pola]): ?>
            <section class="karta" id="<?= $id ?>" aria-labelledby="tytul-<?= $id ?>">
                <h2 id="tytul-<?= $id ?>"><?= tekst($tytul) ?></h2>
                <form method="post" action="#<?= $id ?>">
                    <input type="hidden" name="zadanie" value="<?= $id ?>">
                    <?php foreach ($pola as $definicja): ?>
                        <?php
                        [$nazwa, $etykieta, $typ] = $definicja;
                        $wartosc = $zadanie === $id && $typ !== 'password' ? pole($nazwa) : '';
                        ?>
                        <label><?= tekst($etykieta) ?>
                            <?php if ($typ === 'select'): ?>
                                <select name="<?= $nazwa ?>" required>
                                    <?php foreach ($operacje as $opcja => $opis): ?>
                                        <option value="<?= $opcja ?>"<?= $wartosc === $opcja ? ' selected' : '' ?>><?= $opis ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= $typ ?>" name="<?= $nazwa ?>" value="<?= tekst($wartosc) ?>"
                                    <?php if ($typ === 'number'): ?>
                                        step="<?= $definicja[3] ?? 'any' ?>"
                                        <?php if (isset($definicja[4])): ?>min="<?= $definicja[4] ?>"<?php endif; ?>
                                    <?php elseif ($typ === 'password'): ?>
                                        maxlength="200" autocomplete="new-password"
                                    <?php endif; ?>
                                    required>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                    <button type="submit">Sprawdź</button>
                </form>
                <?php if ($id === $zadanie && $blad !== ''): ?>
                    <p class="blad" role="alert"><?= tekst($blad) ?></p>
                <?php endif; ?>
                <?php if ($id === 'kalkulator' || ($id === $zadanie && $wyniki !== [])): ?>
                    <div class="wynik"<?= $id === 'kalkulator' ? ' id="wynik"' : '' ?> role="status"
                         <?= $id !== $zadanie || $wyniki === [] ? 'hidden' : '' ?>>
                        <?php if ($id === $zadanie): ?>
                            <?= implode('<br>', array_map('tekst', $wyniki)) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <section class="karta" id="szyfr" aria-labelledby="tytul-szyfr">
            <h2 id="tytul-szyfr">Szyfr Cezara +2</h2>
            <div class="szyfr-uklad">
                <label>Tekst
                    <input id="szyfrowany" name="szyfrowany" maxlength="200" autocomplete="off">
                </label>
                <div>
                    <label for="zaszyfrowany">Wynik</label>
                    <output id="zaszyfrowany" class="wynik" for="szyfrowany" aria-live="polite"></output>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
