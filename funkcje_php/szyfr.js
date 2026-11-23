const pole = document.querySelector('#szyfrowany');
const wynik = document.querySelector('#zaszyfrowany');
let aktualneZapytanie;

pole.addEventListener('input', async () => {
    aktualneZapytanie?.abort();
    const tekst = pole.value;
    if (tekst === '') {
        wynik.textContent = '';
        return;
    }

    const zapytanie = new AbortController();
    aktualneZapytanie = zapytanie;
    const dane = new URLSearchParams({ zadanie: 'szyfr', szyfrowany: tekst });

    try {
        const odpowiedz = await fetch(window.location.pathname, {
            method: 'POST',
            body: dane,
            signal: zapytanie.signal,
        });
        if (!odpowiedz.ok) {
            throw new Error('Błąd szyfrowania');
        }

        const szyfr = await odpowiedz.text();
        if (aktualneZapytanie === zapytanie && !zapytanie.signal.aborted) {
            wynik.textContent = szyfr;
        }
    } catch (blad) {
        if (aktualneZapytanie === zapytanie && blad.name !== 'AbortError') {
            wynik.textContent = 'Nie udało się zaszyfrować tekstu.';
        }
    }
});
