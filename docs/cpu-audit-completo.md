# Audit CPU completo (intero codice tema)

Data analisi: 2026-04-15
Ambito: tutti i file PHP del tema (non solo ultimi 15 giorni).

## Metodo
- Scansione globale pattern costosi: `wp_remote_get`, `WP_Query`, `get_posts`, `posts_per_page => -1`, `numberposts => -1`, `set_time_limit`.
- Revisione manuale dei punti ad alto impatto runtime (header/footer/home/template parti più usate).
- Verifica ottimizzazioni già introdotte e ricerca di ulteriori hotspot.

## Hotspot principali trovati su tutto il codice

1. **Fetch HTTP esterni durante il rendering template**
   - Area: `inc/funzionalita_trasversali.php` + `header.php`/`footer.php`.
   - Rischio: blocco request frontend quando l'endpoint esterno è lento/non raggiungibile.
   - Stato: mitigato con cache transient + timeout ridotti + snapshot condiviso.

2. **Feed servizi esterni Maggioli richiamato in più template**
   - Aree: 
     - `template-parts/servizio/servizi_esterni_maggioli.php`
     - `template-parts/servizio/card_maggioli.php`
     - `template-parts/argomento/servizi-detail_maggioli.php`
   - Rischio: chiamate HTTP ripetute e parsing JSON duplicato in pagine diverse.
   - Stato: mitigato con helper condiviso `dci_get_maggioli_services_data()` con cache + timeout.

3. **Query potenzialmente grandi in utility**
   - Area: `inc/utils.php` (`dci_get_related_bandi`, `dci_get_related_unita_amministrative`).
   - Rischio: crescita costo con dataset grande.
   - Stato: `dci_get_related_bandi` già ottimizzata (lookup map); monitorare ancora su siti con molte relazioni.

## Intervento aggiuntivo applicato in questo passaggio
- Creato helper centralizzato `dci_get_maggioli_services_data()` in `inc/utils.php`:
  - cache positiva 5 minuti
  - cache negativa 2 minuti
  - timeout request ridotto (4s)
- Refactor dei template Maggioli per usare l'helper condiviso invece di fare `wp_remote_get` diretto ad ogni inclusione.

## Cosa resta da monitorare
- Funzioni one-shot su `init` che fanno query globali (impatto tipicamente una tantum).
- Pagine tassonomiche con filtri e ordinamenti complessi in siti con molti contenuti.
- Eventuali plugin attivi lato produzione (fuori da questo repository) che possono incidere più del tema.
