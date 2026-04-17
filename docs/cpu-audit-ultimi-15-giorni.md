# Audit CPU - file modificati negli ultimi 15 giorni

Data analisi: 2026-04-15
Repository: `psr-theme-wordpress`
Finestra temporale analizzata: ultimi 15 giorni di commit Git.

## Metodo
- Elenco file toccati con `git log --since='15 days ago' --name-only`.
- Ispezione pattern ad alto costo (`WP_Query`, `get_posts`, `posts_per_page => -1`, loop annidati).
- Revisione mirata dei file più sensibili lato frontend (home/tassonomie) e utility globali.

## Principali candidati al consumo CPU

### 1) Homepage: query notizie in evidenza senza limite
File: `template-parts/home/notizie-auto-evidenza.php`

Punto critico:
- La query carica **tutte** le notizie evidenziate (`posts_per_page => -1`) e poi filtra in PHP.
- In presenza di molte notizie, il costo è alto su ogni request della home.

Dettagli:
- Query con `posts_per_page => -1`: riga 86.
- Subito dopo viene fatto filtro applicativo in ciclo `foreach` sui post trovati: righe 110-157.

Impatto:
- Crescita CPU e memoria proporzionale al numero totale di notizie evidenziate.
- Picchi più visibili in assenza di object/page cache.

### 2) Utility globali: doppio loop su dataset potenzialmente grandi
File: `inc/utils.php`

Punto critico:
- In `dci_get_related_bandi()` vengono caricati bandi e servizi, poi confrontati con loop annidati.
- La complessità è circa `O(bandi * servizi)` più accessi meta nel loop interno.

Dettagli:
- Caricamento bandi: riga 1032.
- Caricamento servizi ids senza limite (`numberposts => -1`): righe 1035-1039 e 1059.
- Loop annidato bandi × servizi: righe 1062-1066.

Impatto:
- Rallentamenti evidenti se il numero di elementi cresce.
- Potenziale saturazione CPU su endpoint/pagine che chiamano questa utility frequentemente.

### 3) Hook `init`: job one-shot che può essere pesante al primo giro
File: `functions.php`

Punto critico:
- Funzione su hook `init` che, se l'opzione non è impostata, carica tutte le notizie (`posts_per_page => -1`) e aggiorna meta per ciascun post.

Dettagli:
- Query completa notizie: righe 306-311.
- Loop con `update_post_meta` per ogni post: righe 312-322.
- Esecuzione agganciata a `init`: riga 329.

Impatto:
- Di norma è un costo "una tantum".
- Se l'opzione viene persa/non salvata (cache/object-cache anomalo, DB issue), il costo si ripete e può creare picchi importanti.

## Cosa NON sembra il principale colpevole
- `inc/lib/parsedown.php`: contiene molti loop interni, ma è una libreria parser; il costo dipende dall'uso applicativo e non mostra pattern di loop infinito o codice malevolo evidente.
- `inc/activationTrasparenza.php`: `set_time_limit(400)` aumenta timeout ma non crea da solo CPU alta; è più un segnale di operazioni lunghe durante attivazione.

## Priorità intervento consigliata
1. **Alta**: limitare la query home in `notizie-auto-evidenza.php` (evitare `-1` e spostare più filtri in query SQL/meta_query quando possibile).
2. **Alta**: riscrivere `dci_get_related_bandi()` per evitare loop annidati e accessi meta ripetuti.
3. **Media**: proteggere meglio la funzione one-shot su `init` (lock transiente + batch) per evitare riesecuzioni costose.

## Suggerimenti rapidi (safe)
- Impostare limiti espliciti (`posts_per_page`) anche quando poi si filtrano risultati.
- Usare `'fields' => 'ids'` quando non serve l'oggetto post completo.
- Precaricare meta (`update_meta_cache`) o ristrutturare i dati per evitare `get_post_meta` in loop annidati.
- Aggiungere caching (transient/object cache) ai risultati derivati più costosi.
