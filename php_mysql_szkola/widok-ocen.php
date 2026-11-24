<?php
if (!isset($adresFormularza)) {
    http_response_code(404);
    exit;
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dziennik szkolny</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<main>
    <h1>Dziennik szkolny</h1>
    <nav aria-label="Widoki dziennika">
        <a href="index.php"<?= $adresFormularza === 'index.php' ? ' aria-current="page"' : '' ?>>Wersja proceduralna</a>
        <a href="index2.php"<?= $adresFormularza === 'index2.php' ? ' aria-current="page"' : '' ?>>Wersja obiektowa</a>
        <a href="insert.php">Dodaj ucznia</a>
    </nav>

    <section class="karta" aria-labelledby="oceny">
        <h2 id="oceny">Oceny klasy · <?= tekst($tytulWariantu) ?></h2>

        <?php if ($blad !== ''): ?>
            <p class="blad" role="alert"><?= tekst($blad) ?></p>
        <?php endif; ?>

        <?php if ($klasy !== []): ?>
            <form action="<?= tekst($adresFormularza) ?>" method="post" class="wybor-klasy">
                <label for="klasa">Klasa</label>
                <select id="klasa" name="klasa" required>
                    <option value="">Wybierz klasę</option>
                    <?php foreach ($klasy as $id => $nazwa): ?>
                        <option value="<?= $id ?>"<?= $id === $klasa ? ' selected' : '' ?>><?= tekst($nazwa) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Pokaż oceny</button>
            </form>
        <?php elseif ($blad === ''): ?>
            <p>W bazie nie ma jeszcze klas.</p>
        <?php endif; ?>

        <?php if ($klasa !== null && $blad === ''): ?>
            <h3>Klasa <?= tekst($klasy[$klasa]) ?></h3>
            <p>Wychowawca: <?= $wychowawca
                ? tekst($wychowawca['imie'] . ' ' . $wychowawca['nazwisko'])
                : 'brak w bazie' ?></p>

            <?php if ($liczbaUczniow > 0): ?>
                <table aria-label="Oceny uczniów">
                    <thead>
                    <tr>
                        <th scope="col">L.p.</th>
                        <th scope="col">Imię</th>
                        <th scope="col">Nazwisko</th>
                        <th scope="col">Średnia ocen</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($uczniowie as $indeks => $uczen): ?>
                        <tr>
                            <td><?= $indeks + 1 ?></td>
                            <td><?= tekst($uczen['Imie']) ?></td>
                            <td><?= tekst($uczen['Nazwisko']) ?></td>
                            <td><?= rtrim(rtrim(number_format((float) $uczen['Srednia_ocen'], 2, '.', ''), '0'), '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php $sredniaKlasy = array_sum(array_column($uczniowie, 'Srednia_ocen')) / $liczbaUczniow; ?>
                <p class="srednia">Średnia klasy: <strong><?= number_format($sredniaKlasy, 2, '.', '') ?></strong></p>
            <?php else: ?>
                <p>Do tej klasy nie przypisano jeszcze uczniów.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
