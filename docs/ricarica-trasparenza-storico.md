# Storico delle ricariche della Trasparenza

Su richiesta dell'utente, il 2 ottobre 2026 sono stati rimossi anteprima, gestione delle anomalie, proposte di spostamento e relativa guida amministrativa. Il pulsante **Aspetto > Ricarica Trasparenza > Ricarica Trasparenza** usa nuovamente la ricarica completa precedente, dopo la conferma. Le anomalie vengono gestite manualmente dalla gestione categorie. Restano le azioni separate per descrizioni, ordinamento e normativa.

Il ripristino del codice non modifica categorie, associazioni, impostazioni o operazioni già registrate nel database. La ricarica completa mantiene il comportamento precedente: può ricreare le voci standard mancanti dal ramo previsto anche se esistono altrove, e aggiornare i valori predefiniti. Non effettua la riconciliazione delle voci fuori posto.

## Consultazione

Lo storico, in fondo al pannello di ricarica, mostra date, operatore, durata, esito, conteggi e dettagli disponibili delle modifiche riuscite. Il menu **Operazioni da consultare** permette di consultare le ultime 10 operazioni di un tipo. Le nuove ricariche complete vengono registrate nel tipo **Struttura Trasparenza**, senza un secondo record di inizializzazione; le attivazioni del tema restano nel tipo di setup.

I record precedenti restano leggibili con gli stessi nomi delle opzioni e lo stesso formato, inclusi i dettagli degli spostamenti già registrati. Non viene ricostruita la storia precedente all'introduzione del registro. Non è un registro generale delle modifiche manuali di WordPress, un backup o uno strumento di annullamento.

## Limiti e risorse

Il registro usa opzioni separate con autoload disabilitato. Conserva 10 record per tipo (massimo ordinario 50), 50 dettagli per operazione e un limite di 24 KiB per record completato. I testi lunghi sono abbreviati. La conservazione limitata già prevista resta attiva per le nuove operazioni.

I dettagli sono accumulati in memoria senza query per ciascuna modifica. Ci sono due salvataggi del record (avvio e conclusione) e controlli di potatura limitati sul prefisso indicizzato di option_name. Nessun cron o accesso allo storico nelle pagine pubbliche. La consultazione legge al massimo un record per tipo, oppure fino a 14 record complessivi se viene selezionato un tipo.

Gli errori dello storico non bloccano la ricarica principale. Le interruzioni gestibili possono registrare modifiche parziali; un arresto forzato può lasciare un record avviato senza dettagli finali. La ricarica non è una transazione atomica.

## Verifica

Eseguire `php scripts/test-trasparenza-history.php`: i test usano dati in memoria senza database reale e controllano conteggi, limiti, autoload, potatura, errori e numero di scritture indipendente dalle modifiche.
