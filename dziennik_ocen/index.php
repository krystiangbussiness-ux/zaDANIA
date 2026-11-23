<?php
declare(strict_types=1);

function tekst(string $wartosc): string
{
    return htmlspecialchars($wartosc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function poleTekstowe(string $nazwa): string
{
    $wartosc = $_POST[$nazwa] ?? '';
    return is_string($wartosc) ? trim($wartosc) : '';
}

function dopiszOcene(string $sciezka, string $wiersz): bool
{
    $plik = @fopen($sciezka, 'ab');
    if ($plik === false) {
        return false;
    }

    try {
        if (!flock($plik, LOCK_EX)) {
            return false;
        }

        $stan = fstat($plik);
        if ($stan === false) {
            return false;
        }

        $zapisano = @fwrite($plik, $wiersz);
        if ($zapisano !== strlen($wiersz) || !@fflush($plik)) {
            @ftruncate($plik, $stan['size']);
            return false;
        }
        return true;
    } finally {
        fclose($plik);
    }
}

$sciezkaOcen = __DIR__ . '/dane/oceny.txt';
$ocenyDoWyboru = ['1', '2', '3', '4', '5', '6'];
$uczen = '';
$przedmiot = '';
$ocena = '';
$bladZapisu = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uczen = poleTekstowe('uczen');
    $przedmiot = poleTekstowe('przedmiot');
    $ocena = poleTekstowe('ocena');
    $wzorzecPola = '/\A[^|\p{C}]{1,100}\z/u';

    if (!preg_match($wzorzecPola, $uczen) || !preg_match($wzorzecPola, $przedmiot)) {
        $bladZapisu = 'Uzupełnij nazwisko i przedmiot. Każde pole może mieć do 100 znaków, bez separatora |.';
    } elseif (!in_array($ocena, $ocenyDoWyboru, true)) {
        $bladZapisu = 'Wybierz ocenę od 1 do 6.';
    } elseif (dopiszOcene($sciezkaOcen, "$uczen | $przedmiot | ocena: $ocena\n")) {
        header('Location: index.php?zapisano=1', true, 303);
        exit;
    } else {
        $bladZapisu = 'Nie udało się zapisać oceny. Sprawdź uprawnienia katalogu dane.';
    }
}

$zapisy = [];
$bladOdczytu = false;
if (file_exists($sciezkaOcen)) {
    $plik = @fopen($sciezkaOcen, 'rb');
    if ($plik === false) {
        $bladOdczytu = true;
    } else {
        if (flock($plik, LOCK_SH)) {
            while (($wiersz = fgets($plik)) !== false) {
                $wiersz = rtrim($wiersz, "\r\n");
                if ($wiersz !== '') {
                    $zapisy[] = $wiersz;
                }
            }
            $bladOdczytu = !feof($plik);
        } else {
            $bladOdczytu = true;
        }
        fclose($plik);
    }
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dziennik ocen</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<main>
    <a href="../">Wszystkie zadania</a>
    <h1>Dziennik ocen</h1>
    <section class="karta" aria-labelledby="dodaj">
        <h2 id="dodaj">Dodaj ocenę</h2>
        <?php if ($bladZapisu !== ''): ?>
            <p class="blad" role="alert"><?= tekst($bladZapisu) ?></p>
        <?php elseif (($_GET['zapisano'] ?? '') === '1'): ?>
            <p class="sukces" role="status">Ocena została zapisana.</p>
        <?php endif; ?>
        <form method="post">
            <label>Imię i nazwisko ucznia
                <input name="uczen" value="<?= tekst($uczen) ?>" maxlength="100" required>
            </label>
            <label>Ocena
                <select name="ocena" required>
                    <option value="">Wybierz ocenę</option>
                    <?php foreach ($ocenyDoWyboru as $opcja): ?>
                        <option value="<?= $opcja ?>"<?= $ocena === $opcja ? ' selected' : '' ?>><?= $opcja ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Przedmiot
                <input name="przedmiot" value="<?= tekst($przedmiot) ?>" maxlength="100" required>
            </label>
            <button type="submit">Zapisz ocenę</button>
        </form>
    </section>

    <section class="karta" aria-labelledby="zapisane">
        <h2 id="zapisane">Zapisane oceny</h2>
        <?php if ($bladOdczytu): ?>
            <p class="blad" role="alert">Nie udało się odczytać wszystkich ocen.</p>
        <?php endif; ?>
        <?php if ($zapisy !== []): ?>
            <ul class="lista-ocen">
                <?php foreach ($zapisy as $zapis): ?>
                    <li><?= tekst($zapis) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (!$bladOdczytu): ?>
            <p>Nie ma jeszcze zapisanych ocen.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
