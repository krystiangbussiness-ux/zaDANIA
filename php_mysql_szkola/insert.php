<?php
declare(strict_types=1);

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/dziennik.php';

function poprawnaNazwa(string $wartosc): bool
{
    return strlen($wartosc) <= 255
        && preg_match("~\\A[\\p{L}\\p{M}][\\p{L}\\p{M} ’'\\-]+\\z~u", $wartosc) === 1;
}

session_start();
$_SESSION['token_dodawania'] ??= bin2hex(random_bytes(32));
$token = $_SESSION['token_dodawania'];
$dodano = $_SESSION['dodano_ucznia'] ?? false;
unset($_SESSION['dodano_ucznia']);

$klasy = [];
$klasa = null;
$imie = '';
$nazwisko = '';
$srednia = '';
$blad = '';
$polaczenie = null;

try {
    $polaczenie = new mysqli($host, $user, $pass, $db, $port);
    $polaczenie->set_charset('utf8mb4');

    $wynikKlas = $polaczenie->query(SQL_KLASY);
    while ($wiersz = $wynikKlas->fetch_assoc()) {
        $klasy[(int) $wiersz['id']] = $wiersz['nazwa'];
    }
    $wynikKlas->free();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $imie = poleZFormularza('imie');
        $nazwisko = poleZFormularza('nazwisko');
        $srednia = poleZFormularza('srednia');
        $ocena = filter_var(str_replace(',', '.', $srednia), FILTER_VALIDATE_FLOAT);
        $klasa = wybranaKlasa($klasy);

        if (!hash_equals($token, poleZFormularza('token'))) {
            http_response_code(400);
            $blad = 'Formularz wygasł. Sprawdź dane i spróbuj ponownie.';
        } elseif (!poprawnaNazwa($imie) || !poprawnaNazwa($nazwisko)) {
            $blad = 'Podaj imię i nazwisko. Każde powinno mieć co najmniej dwa znaki.';
        } elseif ($ocena === false || $ocena < 0 || $ocena > 6) {
            $blad = 'Podaj średnią ocen od 0 do 6.';
        } elseif ($klasa === null) {
            $blad = 'Wybierz klasę z listy.';
        } else {
            $zapytanie = $polaczenie->prepare(SQL_DODAJ_UCZNIA);
            $zapytanie->bind_param('ssdi', $imie, $nazwisko, $ocena, $klasa);
            $zapytanie->execute();
            $sukces = $zapytanie->affected_rows === 1;
            $zapytanie->close();

            if (!$sukces) {
                $blad = 'Wybrana klasa jest już niedostępna. Wybierz inną klasę.';
            }
        }
    }
} catch (mysqli_sql_exception $wyjatek) {
    error_log($wyjatek->getMessage());
    http_response_code(503);
    $blad = 'Nie można zapisać ucznia. Sprawdź, czy MySQL działa i czy wgrano bazę szkola.';
} finally {
    if ($polaczenie instanceof mysqli) {
        $polaczenie->close();
    }
}

if (!empty($sukces)) {
    $_SESSION['dodano_ucznia'] = true;
    header('Location: insert.php', true, 303);
    exit;
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dodaj ucznia · Dziennik szkolny</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<main>
    <h1>Dziennik szkolny</h1>
    <nav aria-label="Widoki dziennika">
        <a href="index.php">Wersja proceduralna</a>
        <a href="index2.php">Wersja obiektowa</a>
        <a href="insert.php" aria-current="page">Dodaj ucznia</a>
    </nav>

    <section class="karta" aria-labelledby="dodawanie">
        <h2 id="dodawanie">Dodaj ucznia</h2>
        <?php if ($blad !== ''): ?>
            <p class="blad" role="alert"><?= tekst($blad) ?></p>
        <?php elseif ($dodano): ?>
            <p class="sukces" role="status">Uczeń został dodany do dziennika.</p>
        <?php endif; ?>

        <?php if ($klasy !== []): ?>
            <form action="insert.php" method="post" class="dodawanie">
                <input type="hidden" name="token" value="<?= tekst($token) ?>">
                <label for="imie">Imię</label>
                <input id="imie" name="imie" value="<?= tekst($imie) ?>" minlength="2" maxlength="100" required>

                <label for="nazwisko">Nazwisko</label>
                <input id="nazwisko" name="nazwisko" value="<?= tekst($nazwisko) ?>" minlength="2" maxlength="100" required>

                <label for="srednia">Średnia ocen (0–6)</label>
                <input id="srednia" name="srednia" value="<?= tekst($srednia) ?>" inputmode="decimal" placeholder="np. 4,75" required>

                <label for="klasa">Klasa</label>
                <select id="klasa" name="klasa" required>
                    <option value="">Wybierz klasę</option>
                    <?php foreach ($klasy as $id => $nazwa): ?>
                        <option value="<?= $id ?>"<?= $id === $klasa ? ' selected' : '' ?>><?= tekst($nazwa) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Dodaj ucznia</button>
            </form>
        <?php elseif ($blad === ''): ?>
            <p>Najpierw dodaj klasę do bazy.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
