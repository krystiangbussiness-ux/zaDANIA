<?php
declare(strict_types=1);

function tekst(string $wartosc): string
{
    return htmlspecialchars($wartosc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$katalogDokumentow = __DIR__ . DIRECTORY_SEPARATOR . 'dokumenty';
$katalogRoboczy = getcwd();
$nazwa = '';
$tresc = '';
$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surowaNazwa = $_POST['nazwa'] ?? '';
    $surowaTresc = $_POST['tresc'] ?? '';
    $nazwa = is_string($surowaNazwa) ? trim($surowaNazwa) : '';
    $tresc = is_string($surowaTresc) ? $surowaTresc : '';

    if (!preg_match('/\A([A-Za-z0-9_-]{1,60})(?:\.txt)?\z/i', $nazwa, $fragmenty)) {
        $blad = 'Podaj nazwę złożoną z maksymalnie 60 liter A–Z, cyfr, myślników lub podkreśleń.';
    } elseif (preg_match('/\A(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])\z/i', $fragmenty[1])) {
        $blad = 'Podaj inną nazwę pliku. Ta nazwa jest zarezerwowana przez system.';
    } elseif (!is_string($surowaTresc) || strlen($tresc) > 8000) {
        $blad = 'Treść jest za długa.';
    } elseif (!is_dir($katalogDokumentow)) {
        $blad = 'Katalog dokumenty jest niedostępny.';
    } else {
        $sciezka = $katalogDokumentow . DIRECTORY_SEPARATOR . $fragmenty[1] . '.txt';

        if (file_exists($sciezka)) {
            $blad = 'Element o tej nazwie już istnieje.';
        } else {
            $plik = @fopen($sciezka, 'xb');
            if ($plik === false) {
                $blad = 'Nie udało się utworzyć pliku. Sprawdź nazwę i uprawnienia katalogu.';
            } else {
                $zapisano = $tresc === '' || fwrite($plik, $tresc) === strlen($tresc);
                $zamknieto = fclose($plik);
                if ($zapisano && $zamknieto) {
                    header('Location: index.php?utworzono=1', true, 303);
                    exit;
                }

                @unlink($sciezka);
                $blad = 'Nie udało się zapisać treści pliku.';
            }
        }
    }
}

$elementy = is_dir($katalogDokumentow) ? @scandir($katalogDokumentow) : false;
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Moje pliki</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <header>
        <h1>MOJE PLIKI</h1>
        <p>Aktualny katalog<br><code><?= tekst($katalogRoboczy ?: 'Niedostępny') ?></code></p>
    </header>

    <section aria-labelledby="zawartosc">
        <h2 id="zawartosc">ZAWARTOŚĆ KATALOGU</h2>
        <?php if ($elementy === false): ?>
            <p>Nie udało się odczytać katalogu dokumenty.</p>
        <?php elseif (count($elementy) === 2): ?>
            <p>Katalog dokumenty jest pusty.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr><th scope="col">Nazwa</th><th scope="col">Typ</th><th scope="col">Rozmiar</th></tr>
                </thead>
                <tbody>
                <?php foreach ($elementy as $element): ?>
                    <?php
                    if ($element === '.' || $element === '..') {
                        continue;
                    }
                    $sciezka = $katalogDokumentow . DIRECTORY_SEPARATOR . $element;
                    $czyPlik = is_file($sciezka);
                    $czyKatalog = is_dir($sciezka);
                    $rozmiar = $czyPlik ? @filesize($sciezka) : false;
                    ?>
                    <tr>
                        <td><?= tekst($element) ?></td>
                        <td><?= $czyPlik ? 'PLIK' : ($czyKatalog ? 'KATALOG' : 'INNY') ?></td>
                        <td><?= $rozmiar !== false ? $rozmiar . ' B' : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section aria-labelledby="nowy-plik">
        <h2 id="nowy-plik">Nowy plik tekstowy</h2>
        <?php if ($blad !== ''): ?>
            <p class="blad" role="alert"><?= tekst($blad) ?></p>
        <?php elseif (($_GET['utworzono'] ?? '') === '1'): ?>
            <p class="sukces" role="status">Plik został utworzony.</p>
        <?php endif; ?>
        <form method="post">
            <label for="nazwa">Nazwa pliku</label>
            <input id="nazwa" name="nazwa" value="<?= tekst($nazwa) ?>" placeholder="np. moja_notatka.txt" maxlength="64" required>
            <label for="tresc">Treść (opcjonalna)</label>
            <textarea id="tresc" name="tresc" rows="4" maxlength="2000"><?= tekst($tresc) ?></textarea>
            <button type="submit">Utwórz</button>
        </form>
    </section>
</main>
</body>
</html>
