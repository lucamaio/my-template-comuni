<?php 

function dci_trasparenza_activation() {
    set_time_limit(400);  // Aumenta il timeout

    // Inserisce i termini di tassonomia
    $stats = insertTaxonomyTrasparenzaTerms();

    // Imposta un'opzione per indicare che il setup è avvenuto
    update_option("dci_has_installed", true);

    // Disabilita i commenti di default per i nuovi post
    if ('' != get_option('default_comment_status')) {
        update_option('default_comment_status', '');
    }

    return $stats;
}
add_action('after_switch_theme', 'dci_trasparenza_activation');
//dci_reload_trasparenza_option_page('themes.php', 'dci_trasparenza_activation');


// ===========================
// Pagina Admin per forzare la ricarica
// SOLO per utente ID = 1
// ===========================

function dci_reload_trasparenza_option_page() {

    // Sicurezza: blocca accesso diretto via URL
    if (get_current_user_id() != 1) {
        wp_die('Non hai i permessi per accedere a questa pagina.');
    }

    if (isset($_GET["action"]) && $_GET["action"] === "reload") {
        $stats = dci_trasparenza_activation(); // Esegue nuovamente l'attivazione
        $inserted = isset($stats['inserted']) ? (int) $stats['inserted'] : 0;
        $updated = isset($stats['updated']) ? (int) $stats['updated'] : 0;
        $descriptions = isset($stats['descriptions_updated']) ? (int) $stats['descriptions_updated'] : 0;
        echo '<div class="notice notice-success is-dismissible"><p>Dati ricaricati con successo. Voci inserite: <strong>' . esc_html($inserted) . '</strong>, voci aggiornate: <strong>' . esc_html($updated) . '</strong>, descrizioni aggiornate: <strong>' . esc_html($descriptions) . '</strong>.</p></div>';
    }

    echo "<div class='wrap'>";
    echo "<h1>Ricarica i dati della Trasparenza</h1>";
    echo '<p>Questa operazione reinserisce le tassonomie e opzioni di default relative alla sezione "Amministrazione Trasparente".</p>';
    echo '<a id="dci-reload-trasparenza-btn" href="' . esc_url(admin_url('themes.php?page=reload-trasparenza-theme-options&action=reload')) . '" class="button button-primary">Ricarica Trasparenza</a>';
    echo '<span id="dci-reload-trasparenza-loader" style="display:none; margin-left:12px; align-items:center;"><span class="spinner is-active" style="float:none; margin:0 8px 0 0;"></span>Ricaricamento in corso...</span>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        var reloadBtn = document.getElementById('dci-reload-trasparenza-btn');
        var loader = document.getElementById('dci-reload-trasparenza-loader');
        if (!reloadBtn || !loader) {
            return;
        }
        reloadBtn.addEventListener('click', function () {
            loader.style.display = 'inline-flex';
            reloadBtn.classList.add('disabled');
            reloadBtn.setAttribute('aria-disabled', 'true');
            reloadBtn.style.pointerEvents = 'none';
        });
    });
    </script>";
    echo "</div>";
}

function dci_add_trasparenza_theme_page() {

    // Mostra la pagina SOLO se utente ID = 1
    if (get_current_user_id() != 1) {
        return;
    }

    add_theme_page(
        'Ricarica Trasparenza',
        'Ricarica Trasparenza',
        'edit_theme_options',
        'reload-trasparenza-theme-options',
        'dci_reload_trasparenza_option_page'
    );
}

add_action('admin_menu', 'dci_add_trasparenza_theme_page');


// ===========================
// Struttura delle tassonomie
// ===========================
if (!function_exists("dci_tipi_cat_amm_trasp_array")) {
    function dci_tipi_cat_amm_trasp_array() {
        return [
            'Disposizioni generali' => [
                "Piano triennale per la prevenzione della corruzione e della trasparenza (PTPCT)",
                'Atti generali' => [  // Nuove Sotto voci Atti generali
                    'Riferimenti normativi su organizzazione e attività',
                    'Atti amministrativi generali',
                    'Documenti di programmazione strategico gestionale',
                    'Statuti e leggi regionali',
                    'Codice disciplinare e codice di condotta'
                ],
                "Oneri informativi per cittadini e imprese"
            ],
            'Organizzazione' => [
                'Amministratori Cessati', // Nuova sottovoce
                'Titolari di incarichi politici di amministrazione di direzione o di governo', //nuova sottovoce
                // 'Organi di indirizzo politico-amministrativo', // Voce sostituita con quella sopra
                "Sanzioni per mancata comunicazione dei dati",
                "Rendiconti gruppi consiliari regionali/provinciali",
                "Articolazione degli uffici" =>[ // Nuove sotto voci richieste dal ANAC
                    "Articolazione uffici",  // Pagina Custom nel sito
                    "Organigramma"
                ],
                "Telefono e posta elettronica" // Pagina Custom
            ],
            'Consulenti e collaboratori' => [
                'Titolari di incarichi di collaborazione o consulenza'
            ],
            'Personale' => [
                // Nuove sotto-voci
                'Titolari di incarichi dirigenziali amministrativi di vertice', 
                'Titolari di Incarichi dirigenziali (dirigenti non generali)',
                'Dirigenti cessati',
                'Sanzioni per mancata comunicazione dei dati', 

                'Posizioni organizzative',

                // Nuove sotto-sotto-voci
                'Dotazione organica' =>  [
                    'Costo annuale del personale',
                    'Costo personale tempo indeterminato'
                ],
                'Personale non a tempo indeterminato' =>[
                    'Costo del personale non a tempo indeterminato'
                ],
                'Tassi di assenza',
                "Incarichi conferiti e autorizzati ai dipendenti",
                "Contrattazione collettiva",

                "Contrattazione integrativa" =>[ // Nuove sotto-sotto-voci
                    'Contratti integrativi',
                    'Costi contratti integrativi'
                ],
                "OIV"
            ],
            'Bandi di concorso' => [
                'Concorsi' 
            ],
            'Performance'=> [
                'Sistema di misurazione e valutazione della performance',
                "Piano della Performance",
                "Relazione sulla Performance",
                "Ammontare complessivo dei premi",
                "Benessere organizzativo"
            ],
            'Enti controllati' =>[
                "Enti pubblici vigilati",
                "Società partecipate" =>[
                    'Dati società partecipate',
                    'Provvedimenti'
                ],
                "Enti di diritto privato controllati",
                "Rappresentazione grafica"
            ],
            "Attività e procedimenti"=>[ 
                "Tipologie di procedimento",
                "Monitoraggio tempi procedimentali",
                "Dichiarazioni sostitutive e acquisizione d'ufficio dei dati"
            ],
            "Provvedimenti" =>[
                "Provvedimenti organi indirizzo-politico",
                "Provvedimenti dirigenti amministrativi" // Nuovo nome
            ],
            "Bandi di Gara e contratti"=>[
                "Atti e documenti di carattere generale riferiti a tutte le procedure" => [
                    "Automatizzazione delle procedure",
                    "Acqusizione interesse realizzazione opere incompiute",
                    "Mancata redazione programmazione",
                    "Documenti sul sistema di qualificazione",
                    "Gravi illeciti professionali",
                    "Progetti di investimento pubblico"
                ],

                //    "Contratti Pubblici", // Sezione non più necessaria per la trasparenza
                // Sezioni che sostituiscono "Contratti Pubblici" per la trasparenza
                "Pubblicazione",
                "Affidamento",
                "Esecutiva",
                "Sponsorizzazioni",
                "Procedure di somma urgenza e di protezione civile",
                "Finanza di progetto",

                 // Procedimenti fino al 31 / 12/2023
                "Procedimenti fino al 31/12/2023" => [
                    "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura",
                    "Informazioni sulle singole procedure in formato tabellare"
                ]
            ],
            "Sovvenzioni , contributi sussidi, vantaggi economici"=>[
                "Criteri e modalità",
                "Atti di concessione",
                // "Elenchi" // Voce eliminata
            ],
            "Bilanci"=>[
                "Bilancio preventivo e consuntivo" =>[
                    'Bilancio consuntivo',
                    'Bilancio preventivo'
                ],
                "Piano degli indicatori e risultati attesi di bilancio"
            ],
            "Beni immobili e gestione patrimonio"=>[
                "Patrimonio immobiliare",
                "Canoni di locazione o affitto"
            ],
            "Controlli e rilievi sull'amministrazione"=>[
                "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe",
                "Organi di revisione amministrativa e contabile",
                "Corte dei conti",
                // "Altri organismi",  // Voce rimossa
                // "Stato dei rilievi" // Voce rimossa
            ],
            "Servizi Erogati"=>[
                "Carta dei servizi e standard di qualità",
                "Class action", // Nuova sotto-voce
                "Costi contabilizzati",
                "Liste di attesa", // sotto-voce spostata
                "Gestione dei rifiuti", // Nuova sotto-voce
                // "Tempi medi di erogazione dei servizi"  
                "Servizi in rete"
            ],
            "Pagamenti dell'amministrazione" => [
                    "Dati sui pagamenti",
                    "Dati sui pagamenti del servizio sanitario nazionale",
                    "Indicatore di tempestività dei pagamenti"=>[
                        'Ammontare complessivo dei debiti' // Nuova sotto-sotto-voce
                    ],
                    "IBAN e pagamenti informatici"
            ],
            "Opere pubbliche" => [
                "Nuclei di valutazione e verifica degli investimenti pubblici",
                "Atti di programmazione delle opere pubbliche",
                "Tempi costi e indicatori di realizzazione delle opere pubbliche"
            ],
            "Pianificazione e governo del territorio" => [
                "Pianificazione e governo del territorio"
            ],
            "Informazioni ambientali" => [
                "Informazioni ambientali",
                
                // nuove sotto-voci
                "Stato dell’ambiente",
                "Fattori Inquinanti",
                "Misure incidenti sull’ambiente e relative analisi d’impatto",
                "Misure protezione sull’ambiente e relative analisi d’impatto",
                "Relazioni sull’attuazione della legislazione",
                "Stato della salute e della sicurezza umana",
                "Relazione sullo stato dell’ambiente del ministero dell’ambiente e della tutela del territorio"
            ],
            "Strutture sanitarie private accreditate" => [
                "Strutture sanitarie private accreditate"
            ],
            "Interventi straordinari e di emergenza" => [
                "Interventi straordinari e di emergenza"
            ],
            // Voce non neccsaria
            // "Utilizzo delle risorse pubbliche" => [
            //     "Spese dell’ente",
            //     "Costi dei servizi",
            //     "Indicatori di pagamento",
            //     "Dataset scaricabili"
            // ],
            "Altri contenuti" => [
                "Prevenzione della corruzione"=>[
                    // Nuove sotto-sotto-voci
                    "Piano triennale per la prevenzione della corruzione e della trasparenza",
                    "Responsabile della prevenzione della corruzione e della trasparenza",
                    "Regolamenti per la prevenzione e la repressione della corruzione e dell'illegalità",
                    "Relazione del responsabile della prevenzione della corruzione e della trasparenza",
                    "Provvedimenti adottati dall'A.N.AC. ed atti di adeguamento a tali provvedimenti",
                    "Atti di accertamento delle violazioni",
                    "Segnalazioni di illecito - whistleblower", // Aggiungere il link alla piattaforma
                    'Accesso Civico "semplice" concernente dati, documenti e informazioni soggetti a pubblicazione obbligatoria'
                ],
                "Accesso civico"=>[
                    'Accesso civico “generalizzato” concernente dati e documenti ulteriori',
                    'Registro degli accessi', 
                    'Catalogo dei dati , metadati e delle banche dei dati'
                ],
                "Accessibilità e Catalogo di dati, metadati e banche dati"=>[
                    'Regolamenti',
                    'Obiettivi di accessibilità',
                   'Azioni di sensibilizzazione rapporti con la società civile',
                ],
                "Azioni di sensibilizzazione e rapporti con la società civile" => [
                    "Autovetture di servizio"
                ],
                // "Dati ulteriori" // Voce non più neccessaria
            ]
        ];
    }
}



if (!function_exists("dci_tipi_procedura_contraente_array")) {
    function dci_tipi_procedura_contraente_array() {
        return [
            "01 - Procedura aperta",
            "02 - Procedura ristretta",
            "03 - Procedura negoziata previa pubblicazione",
            "04 - Procedura negoziata senza previa pubblicazione",
            "05 - Dialogo competitivo",
            "06 - Procedura negoziata senza previa i nozione cl gara (settori speciali)",
            "07 - Sistema dinamico dl acquisizione",
            "08 - Affloamento in economia - cottimo fiduciario",
            "14 - Procedura selettiva ex art 238 c7, d.lgs.",
            "17 - Affidamento diretto ex art. 5 cella legge",
            "21 - Procedura ristretta derivante da avvisi con cui si indice la gara",
            "22 - Procedura negoziata previa indizione dl gara (settori speciali}",
            "23 - Affidamento diretto",
            "24 - Affidamento diretto a societa' in house",
            "25 - Affidamento diretto a societa raggruppate/consorziate o controllate nelle concessioni e nei partenariati",
            "26 - Affldamento diretto in adesione ad accordo quadro/convenzione",
            "27 - Confronto competitivo in adesione ad accordo quadro/convenzione",
            "28 - Procedura al sensi dei regolamenti degli organi costituzionali",
            "29 - Procedura ristretta semplificata",
            "30 - Procedura derivante oa legge regionale",
            "31 - Affidamento diretto per variante superiore al dell'importo contrattuale",
            "32 - Affidamento riservato",
            "33 -Procedura negoziata per affidamenti sotto soglia",
            "34 - Procedura art. 16 comma 2. opr 280/2001 per opere urbanizzazione a scomputo primarie sotto soglia comunitaria",
            "35 - Parternariato per l'innovazione",
            "36 - Affidamento diretto per lavori. servizi o forniture supplementari",
            "37 - Procedura competitiva con negoziazione",
            "38 - Procedura disciplinata da regolamento interno per settori speciali",
            "39 - Diretto per modifiche contrattuali o varianti per le quali é necessaria una nuova procedura dl affidamento",
        ];
    }
}

if (!function_exists("dci_tipi_stato_bando_array")) {
    function dci_tipi_stato_bando_array() {
        return [
            "Attivo",
            "Scaduto",
            "Archiviato",
        ];
    }
}


// ===========================
// Funzione di inserimento tassonomie
// ===========================
function insertTaxonomyTrasparenzaTerms() {
    $stats = [
        'inserted' => 0,
        'updated' => 0,
        'descriptions_updated' => 0,
    ];

    /* --------------------------- */
    /* 1) Inserimento tassonomie   */
    /* --------------------------- */
    // Categorie Trasparenza
    $tipi_cat_amm_trasp_array = dci_tipi_cat_amm_trasp_array();
 // recursionInsertTaxonomy( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );
    $ordine = 1;
    recursionInsertTaxonomy1( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp', 0, $ordine, $stats );

    // Tipi di procedura contraente
    $tipi_procedura_contraente_array = dci_tipi_procedura_contraente_array();
    recursionInsertTaxonomy( $tipi_procedura_contraente_array, 'tipi_procedura_contraente' );

    // Tipi di stato bando
    $tipi_stato_bando_array = dci_tipi_stato_bando_array();
    recursionInsertTaxonomy( $tipi_stato_bando_array, 'tipi_stato_bando' );


    /* ----------------------------------------------------------- */
    /* 2) Aggiornamento descrizioni dettagliate di termini chiave  */
    /* ----------------------------------------------------------- */

    // Mappa: 'Nome termine' => 'Descrizione desiderata'
  $descrizioni = [

    // Disposizioni generali
    "Piano triennale per la prevenzione della corruzione e della trasparenza" => 
        "Documento programmatico che definisce le strategie e le misure per prevenire la corruzione "
      . "e garantire la trasparenza nelle attività dell’amministrazione, in ottemperanza agli artt. 1 e 10 del D.Lgs. 33/2013.",

    "Atti generali" =>
        "Documenti amministrativi di carattere generale che disciplinano l’organizzazione, "
      . "il funzionamento e le modalità operative dell’ente pubblico.",

    "Oneri informativi per cittadini e imprese" => 
        "Elenco delle informazioni e documenti che l’amministrazione è tenuta a pubblicare "
      . "e aggiornare per garantire la massima trasparenza nei confronti di cittadini e imprese.",

    // Organizzazione
    "Organi di indirizzo politico-amministrativo" =>
        "Informazioni relative agli organi politici e amministrativi, quali giunta, consiglio e dirigenti, "
      . "con relativi incarichi e competenze.",

    "Sanzioni per mancata comunicazione dei dati" =>
        "Dettagli sulle sanzioni previste per la mancata o ritardata comunicazione delle informazioni obbligatorie.",

    "Rendiconti gruppi consiliari regionali/provinciali" =>
        "Documentazione e rendicontazione economica dei gruppi consiliari a livello regionale e provinciale.",

    "Articolazione degli uffici" =>
        "Descrizione della struttura organizzativa interna dell’amministrazione, con indicazione di uffici, servizi e loro funzioni.",

    "Telefono e posta elettronica" =>
        "Elenco dei recapiti telefonici e indirizzi di posta elettronica istituzionali per il contatto con l’amministrazione.",

    // Consulenti e collaboratori
    "Titolari di incarichi di collaborazione o consulenza" =>
        "Elenco dei soggetti esterni incaricati di collaborazioni o consulenze, con dettagli sugli incarichi conferiti.",

    // Personale
    "Incarichi amministrativi di vertice" =>
        "Informazioni sugli incarichi di vertice politico-amministrativo conferiti all’interno dell’ente, con relativi nominativi e dati.",

    "Dirigenti" =>
        "Elenco dei dirigenti dell’amministrazione con dettagli sugli incarichi e qualifiche professionali.",

    "Posizioni organizzative" =>
        "Informazioni sulle posizioni organizzative intermedie, con relativa descrizione di funzioni e competenze.",

    "Dotazione organica" =>
        "Descrizione del personale in servizio in termini numerici e di qualifiche, suddivisa per aree e categorie.",

    "Personale non a tempo indeterminato" =>
        "Dettaglio sul personale assunto con contratti a tempo determinato o altre forme contrattuali non stabili.",

    "Tassi di assenza" =>
        "Dati statistici relativi ai tassi di assenza del personale, per monitorare l’efficienza e la produttività.",

    "Incarichi conferiti e autorizzati ai dipendenti" =>
        "Informazioni sugli incarichi aggiuntivi affidati ai dipendenti pubblici, con relativi dettagli e autorizzazioni.",

    "Contrattazione collettiva" =>
        "Dettagli sugli accordi e contratti collettivi applicati all’interno dell’amministrazione.",

    "Contrattazione integrativa" =>
        "Informazioni sulle negoziazioni e accordi integrativi stipulati per migliorare le condizioni di lavoro.",

    "OIV" =>
        "Dati sull’Organismo Indipendente di Valutazione, con funzioni di controllo e monitoraggio della performance.",

    // Bandi di concorso
    "Concorsi" =>
        "Elenco e informazioni relative ai bandi di concorso pubblico per l’assunzione nel settore pubblico.",

    // Performance
    "Piano della Performance" =>
        "Documento programmatico che definisce obiettivi e risultati attesi dall’amministrazione pubblica.",

    "Relazione sulla Performance" =>
        "Rapporto annuale che analizza i risultati ottenuti in relazione agli obiettivi prefissati.",

    "Ammontare complessivo dei premi" =>
        "Informazioni sul totale delle risorse erogate sotto forma di premi e incentivi al personale.",

    "Benessere organizzativo" =>
        "Dati e iniziative volte a migliorare il clima e il benessere lavorativo all’interno dell’ente.",

    // Enti controllati
    "Enti pubblici vigilati" =>
        "Elenco degli enti pubblici soggetti a vigilanza da parte dell’amministrazione.",

    "Società partecipate" =>
        "Informazioni sulle società a partecipazione pubblica, con dati su attività e governance.",

    "Enti di diritto privato controllati" =>
        "Dettaglio sugli enti privati sotto controllo pubblico e sulle modalità di controllo esercitate.",

    "Rappresentazione grafica" =>
        "Visualizzazioni grafiche e schemi relativi alla rete di enti e società controllate.",

    // Attività e procedimenti
    "Dati aggregati attività amministrativa" =>
        "Raccolta e sintesi statistica delle principali attività svolte dall’amministrazione.",

    "Tipologie di procedimento" =>
        "Classificazione e descrizione delle diverse tipologie di procedimenti amministrativi gestiti.",

    "Monitoraggio tempi procedimentali" =>
        "Dati e analisi sui tempi medi di esecuzione dei procedimenti amministrativi.",

    "Dichiarazioni sostitutive  e acquisizione d'ufficio dei dati" =>
        "Informazioni sulle modalità di autocertificazione e sull’acquisizione automatica dei dati da parte dell’amministrazione.",

    // Provvedimenti
    "Provvedimenti organi indirizzo-politico" =>
        "Elenco e dettagli dei provvedimenti adottati dagli organi di indirizzo politico-amministrativo.",

    "Provvedimenti dirigenti" =>
        "Informazioni relative ai provvedimenti emanati dai dirigenti dell’amministrazione.",

    // Bandi di Gara e contratti
    "Informazioni sulle singole procedure in formato tabellare" =>
        "Dati dettagliati e organizzati delle singole procedure di gara in formato facilmente consultabile.",

    "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura" =>
        "Documenti ufficiali relativi alle amministrazioni che aggiudicano le gare, organizzati per procedura.",

    "Contratti Pubblici" =>
        "Elenco completo e aggiornato dei contratti pubblici di lavori, servizi e forniture stipulati "
      . "dall’amministrazione, in conformità all’Art. 37 del D.Lgs. 50/2016.",

    // Sovvenzioni , contributi sussidi, vantaggi economici
    "Criteri e modalità" =>
        "Descrizione dei criteri e delle modalità con cui sono erogati sovvenzioni, contributi, sussidi e altri vantaggi economici.",

    "Atti di concessione" =>
        "Atti amministrativi con cui l’ente concede sovvenzioni, contributi, sussidi o vantaggi economici "
      . "a soggetti pubblici o privati, secondo quanto previsto dall’Art. 26 del D.Lgs. 33/2013.",

    "Elenchi" =>
        "Elenco dettagliato dei beneficiari di sovvenzioni, contributi, sussidi e vantaggi economici concessi dall’amministrazione.",

    // Bilanci
    "Bilancio preventivo e consuntivo" =>
        "Documenti contabili che illustrano le previsioni e i risultati finanziari dell’amministrazione, "
      . "pubblicati per garantire trasparenza nell’utilizzo delle risorse pubbliche.",

    "Piano degli indicatori e risultati attesi di bilancio" =>
        "Piano dettagliato degli indicatori di performance e degli obiettivi finanziari attesi dall’ente.",

    // Beni immobili e gestione patrimonio
    "Patrimonio immobiliare" =>
        "Informazioni sul patrimonio immobiliare dell’ente, comprese proprietà, beni e relativi dati gestionali.",

    "Canoni di locazione o affitto" =>
        "Dettaglio dei canoni di locazione o affitto pagati o incassati dall’amministrazione.",

    // Controlli e rilievi sull'amministrazione
    "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe" =>
        "Informazioni sugli organismi indipendenti che valutano la performance e l’efficienza dell’amministrazione.",

    "Organi di revisione amministrativa e contabile" =>
        "Dettagli sugli organi incaricati della revisione amministrativa e contabile interna.",

    "Corte dei conti" =>
        "Informazioni e documenti relativi ai controlli e alle decisioni della Corte dei conti sull’ente.",

    "Altri organismi" =>
        "Informazioni su eventuali altri organismi che esercitano funzioni di controllo o vigilanza sull’amministrazione.",

    "Stato dei rilievi" =>
        "Dettaglio dei rilievi emessi dagli organi di controllo e sullo stato di adempimento delle eventuali raccomandazioni.",

    // Servizi Erogati
    "Carta dei servizi e standard di qualità" =>
        "Documento che illustra i servizi offerti dall’amministrazione e gli standard di qualità garantiti.",

    "Costi contabilizzati" =>
        "Dati relativi ai costi sostenuti per l’erogazione dei servizi pubblici.",

    "Tempi medi di erogazione dei servizi" =>
        "Statistiche sui tempi medi necessari per la fornitura dei servizi ai cittadini.",

    "Liste di attesa" =>
        "Informazioni sulle liste di attesa per l’accesso a determinati servizi o prestazioni.",

    // Pagamenti dell'amministrazione
    "Dati sui pagamenti" =>
        "Dati aggregati relativi ai pagamenti effettuati dall’amministrazione verso fornitori e terzi.",

    "Indicatore di tempestività dei pagamenti" =>
        "Indicatore che misura la tempestività con cui l’ente effettua i pagamenti, in conformità alla normativa vigente.",

    "IBAN e pagamenti informatici" =>
        "Informazioni sugli IBAN utilizzati e sulle modalità di pagamento elettronico adottate dall’amministrazione.",

    "Dati sui pagamenti del servizio sanitario nazionale" =>
        "Dati specifici relativi ai pagamenti effettuati nell’ambito del servizio sanitario nazionale.",

    // Opere pubbliche
    "Nuclei di valutazione e verifica degli investimenti pubblici" =>
        "Informazioni sui nuclei incaricati della valutazione e verifica degli investimenti pubblici.",

    "Atti di programmazione delle opere pubbliche" =>
        "Documenti relativi alla programmazione e pianificazione delle opere pubbliche.",

    "Tempi costi e indicatori di realizzazione delle opere pubbliche" =>
        "Dati e indicatori relativi ai tempi, costi e qualità delle opere pubbliche realizzate.",

    // Pianificazione e governo del territorio
    "Pianificazione e governo del territorio" =>
        "Informazioni e documenti relativi alla pianificazione urbanistica e al governo del territorio comunale.",

    // Informazioni ambientali
    "Informazioni ambientali" =>
        "Dati e documenti riguardanti la gestione ambientale e le politiche di sostenibilità adottate dall’ente.",

    // Strutture sanitarie private accreditate
    "Strutture sanitarie private accreditate" =>
        "Elenco delle strutture sanitarie private accreditate dall’amministrazione con indicazione di servizi erogati e requisiti di qualità.",

    // Interventi straordinari e di emergenza
    "Interventi straordinari e di emergenza" =>
        "Informazioni sugli interventi adottati in situazioni straordinarie o di emergenza, comprese modalità operative e destinatari.",

    // Utilizzo delle risorse pubbliche
    "Spese dell’ente" =>
        "Dati sintetici e dettagliati sulle spese dell’amministrazione, suddivise per tipologia e missione.",

    "Costi dei servizi" =>
        "Informazioni sui costi sostenuti dall’amministrazione per i servizi erogati ai cittadini, con indicazione di eventuali tariffe e ripartizioni economiche.",

    "Indicatori di pagamento" =>
        "Dati e indicatori che mostrano la tempestività e regolarità dei pagamenti effettuati dall’ente.",

    "Dataset scaricabili" =>
        "File aperti (CSV, XLS, ODS) contenenti i dati pubblicati nella sezione, per permettere il riuso e la consultazione diretta.",

    // Altri contenuti
    "Prevenzione della corruzione" =>
        "Informazioni e misure adottate per prevenire fenomeni di corruzione e garantire integrità e trasparenza delle attività amministrative.",

    "Accesso civico" =>
        "Indicazioni per i cittadini su come richiedere informazioni e documenti in base all’accesso civico.",

    "Accessibilità e Catalogo di dati, metadati e banche dati" =>
        "Dati, metadati e banche dati accessibili ai cittadini, con indicazioni sulla modalità di consultazione e utilizzo.",

    // Nuove descrizioni aggiunte
    "Amministratori Cessati" => 
        "Elenco dei nominativi e informazioni relative agli amministratori che hanno cessato le proprie funzioni, "
      . "con indicazione delle date di incarico e di termine dell'incarico.",

    "Titolari di incarichi politici di amministrazione di direzione o di governo" =>
        "Informazioni sui soggetti titolari di incarichi politici di amministrazione, direzione o governo dell'ente, "
      . "con dettagli su nomine, competenze e responsabilità.",

    "Riferimenti normativi su organizzazione e attività" =>
        "Disposizioni normative e regolamentari che disciplinano l'organizzazione e lo svolgimento delle attività dell'amministrazione.",

    "Atti amministrativi generali" =>
        "Atti amministrativi generali adottati dall'ente per il governo ordinario e straordinario delle sue attività.",

    "Documenti di programmazione strategico gestionale" =>
        "Documento che contiene gli indirizzi strategici e gli obiettivi gestionali dell'amministrazione per il medio-lungo termine.",

    "Statuti e leggi regionali" =>
        "Statuti costitutivi dell'ente e leggi regionali o nazionali di riferimento per il suo funzionamento.",

    "Codice disciplinare e codice di condotta" =>
        "Norme e principi di comportamento obbligatori per i dipendenti e i collaboratori dell'amministrazione.",

    "Articolazione uffici" =>
        "Descrizione dettagliata della divisione organizzativa dell'ente in uffici, servizi e settori con relative funzioni.",

    "Organigramma" =>
        "Rappresentazione grafica della struttura organizzativa gerarchica dell'ente, con indicazione delle linee di autorità e di comunicazione.",

    "Titolari di incarichi dirigenziali amministrativi di vertice" =>
        "Elenco dei dirigenti di vertice con qualifiche, curriculum e incarichi assegnati, conformemente all'art. 24 del D.Lgs. 33/2013.",

    "Titolari di Incarichi dirigenziali (dirigenti non generali)" =>
        "Informazioni sui dirigenti non di vertice con dettaglio degli incarichi e delle responsabilità amministrative.",

    "Dirigenti cessati" =>
        "Elenco dei dirigenti che hanno cessato il servizio con date di fine incarico e dati anagrafici.",

    "Costo annuale del personale" =>
        "Importo complessivo della spesa annuale sostenuta dall'amministrazione per la gestione del personale.",

    "Costo personale tempo indeterminato" =>
        "Costo specifico della spesa destinata al personale con rapporto di lavoro a tempo indeterminato.",

    "Costo del personale non a tempo indeterminato" =>
        "Importo della spesa per il personale assunto con contratti a tempo determinato o altre forme contrattuali atipiche.",

    "Contratti integrativi" =>
        "Testi dei contratti integrativi stipulati fra l'amministrazione e le rappresentanze sindacali.",

    "Costi contratti integrativi" =>
        "Dati sulla spesa generata dai contratti integrativi ivi compresi scatti stipendiali e premialità.",

    "Sistema di misurazione e valutazione della performance" =>
        "Descrizione del sistema adottato per misurare e valutare i risultati della performance organizzativa e individuale.",

    "Dati società partecipate" =>
        "Informazioni dettagliate su composizione azionaria, amministratori, dati finanziari e attività delle società partecipate.",

    "Provvedimenti" =>
        "Atti e provvedimenti adottati riguardanti la gestione e il controllo delle società partecipate.",

    "Atti e documenti di carattere generale riferiti a tutte le procedure" =>
        "Documenti generali applicabili a tutte le procedure di affidamento, quali linee guida, regolamenti e standard di qualificazione.",

    "Automatizzazione delle procedure" =>
        "Informazioni sull'utilizzo di sistemi informatici e piattaforme digitali per l'automazione delle procedure di gara.",

    "Acqusizione interesse realizzazione opere incompiute" =>
        "Documenti relativi alle procedure per l'acquisizione dell'interesse nella realizzazione di opere rimaste incomplete.",

    "Mancata redazione programmazione" =>
        "Rendicontazione sulla mancata redazione di documenti di programmazione strategica quando prescritta dalle normative.",

    "Documenti sul sistema di qualificazione" =>
        "Documentazione relativa ai sistemi e criteri di qualificazione dei fornitori e degli appaltatori dell'amministrazione.",

    "Gravi illeciti professionali" =>
        "Elenco e documentazione dei gravi illeciti professionali commessi dai fornitori con relative conseguenze sanzionatorie.",

    "Progetti di investimento pubblico" =>
        "Descrizione dei progetti di investimento pubblico prioritari con indicazione di finalità, costi e cronoprogramma.",

    "Pubblicazione" =>
        "Sezione dedicata agli obblighi di pubblicazione dei dati relativi alle procedure di contrattazione pubblica secondo la normativa vigente.",

    "Affidamento" =>
        "Informazioni sulle diverse modalità e procedure di affidamento di lavori, servizi e forniture adottate dall'amministrazione.",

    "Esecutiva" =>
        "Dati e informazioni relative alla fase esecutiva dei contratti pubblici e verifica dell'adempimento degli obblighi contrattuali.",

    "Sponsorizzazioni" =>
        "Elenco delle convenzioni di sponsorizzazione stipulate dall'ente con relative modalità e importi economici.",

    "Procedure di somma urgenza e di protezione civile" =>
        "Documentazione relative alle procedure accelerate utilizzate in situazioni di somma urgenza o emergenza di protezione civile.",

    "Finanza di progetto" =>
        "Informazioni sui progetti realizzati tramite finanza di progetto (partenariato pubblico-privato), contratti e risultati attesi.",

    "Procedimenti fino al 31/12/2023" =>
        "Archivio storico delle procedure di gara e contratti gestiti fino al 31 dicembre 2023, conservati per esigenze di trasparenza storica.",

    "Bilancio consuntivo" =>
        "Documento contabile che rendiconti i risultati economici e finanziari effettivamente conseguiti dal l'ente nell'esercizio chiuso.",

    "Bilancio preventivo" =>
        "Documento programmatico che contiene le previsioni economiche e finanziarie dell'ente per l'esercizio futuro.",

    "Class action" =>
        "Informazioni sulle azioni collettive e le procedure di ricorso collettivo attivate nei confronti dell'amministrazione.",

    "Gestione dei rifiuti" =>
        "Dati e informazioni relative alla gestione dei rifiuti urbani e speciali da parte dell'amministrazione.",

    "Servizi in rete" =>
        "Elenco e descrizione dei servizi messi a disposizione dei cittadini tramite piattaforme digitali e telematiche.",

    "Ammontare complessivo dei debiti" =>
        "Importo totale dei debiti sostenuti dall'amministrazione verso fornitori e terzi, con indicazioni sulla composizione temporale.",

    "Stato dell'ambiente" =>
        "Rapporto descrittivo dello stato dell'ambiente nel territorio amministrato con dati su qualità dell'aria, acqua e suolo.",

    "Fattori Inquinanti" =>
        "Dati e analisi sui fattori che causano inquinamento ambientale nel territorio con indicazione di fonti e livelli di concentrazione.",

    "Misure incidenti sull'ambiente e relative analisi d'impatto" =>
        "Documentazione delle misure adottate che potrebbero avere effetti sull'ambiente, con allegata analisi di impatto ambientale.",

    "Misure protezione sull'ambiente e relative analisi d'impatto" =>
        "Elenco e documentazione delle misure di protezione e prevenzione ambientale attuate con studi di efficacia e impatto.",

    "Relazioni sull'attuazione della legislazione" =>
        "Rapporti periodici sullo stato di implementazione della normativa ambientale e sulla conformità dell'ente ai relativi obblighi.",

    "Stato della salute e della sicurezza umana" =>
        "Dati epidemiologici e indicatori di salute pubblica nel territorio amministrato, con analisi di fattori di rischio.",

    "Relazione sullo stato dell'ambiente del ministero dell'ambiente e della tutela del territorio" =>
        "Documento elaborato dal Ministero dell'Ambiente fornito all'ente per la conoscenza dello stato ambientale nazionale.",

    "Responsabile della prevenzione della corruzione e della trasparenza" =>
        "Dati identificativi del responsabile della prevenzione della corruzione, contatti, curriculum e incarichi attribuiti.",

    "Regolamenti per la prevenzione e la repressione della corruzione e dell'illegalità" =>
        "Norme e regolamenti adottati dall'ente per prevenire fenomeni corruttivi e contrastare comportamenti illegittimi.",

    "Relazione del responsabile della prevenzione della corruzione e della trasparenza" =>
        "Rapporto annuale redatto dal responsabile sugli esiti dei controlli, analisi dei rischi e proposte di miglioramento.",

    "Provvedimenti adottati dall'A.N.AC. ed atti di adeguamento a tali provvedimenti" =>
        "Raccolta dei provvedimenti e linee guida emanati dall'Autorità Nazionale Anticorruzione e atti di recepimento da parte dell'ente.",

    "Atti di accertamento delle violazioni" =>
        "Documentazione relativa ai provvedimenti di accertamento di violazioni della normativa anticorruzione e sulla trasparenza.",

    "Segnalazioni di illecito - whistleblower" =>
        "Informazioni sulla procedura di segnalazione degli illeciti e correttivi comportamenti (whistleblowing) con garanzie di riservatezza.",

    "Accesso Civico \"semplice\" concernente dati, documenti e informazioni soggetti a pubblicazione obbligatoria" =>
        "Procedure e modalità per richiedere accesso civico ai dati, documenti e informazioni che l'ente è obbligato a pubblicare.",

    "Accesso civico \"generalizzato\" concernente dati e documenti ulteriori" =>
        "Procedure per richiedere accesso civico generalizzato a dati, documenti e informazioni non soggetti a obbligo di pubblicazione.",

    "Registro degli accessi" =>
        "Registro trasmesso a livello centrale in cui sono annotate tutte le richieste di accesso civico e amministrativo presentate.",

    "Catalogo dei dati, metadati e delle banche dei dati" =>
        "Elenco descrittivo di tutti i dati, metadati e banche dati in possesso dell'ente, con indicazione di formati e modalità di riuso.",

    "Regolamenti" =>
        "Regolamenti e norme tecniche adottati per garantire l'accessibilità dei servizi e contenuti digitali secondo standard internazionali.",

    "Obiettivi di accessibilità" =>
        "Piano strategico degli obiettivi perseguiti dall'ente per migliorare l'accessibilità dei propri servizi e contenuti ai cittadini.",

    "Azioni di sensibilizzazione rapporti con la società civile" =>
        "Iniziative e programmi promossi dall'ente per sensibilizzare la cittadinanza sulla trasparenza e coinvolgere la società civile.",

    "Autovetture di servizio" =>
        "Elenco e dati sulle autovetture in dotazione all'ente utilizzate per i servizi pubblici con indicazione di utilizzo e manutenzione.",

    "Dati ulteriori" =>
        "Ulteriori dati pubblici e informazioni integrative utili alla piena trasparenza dell’ente, non classificabili nelle altre categorie."
];

    foreach ( $descrizioni as $term_name => $new_desc ) {
        dci_update_term_description( $term_name, 'tipi_cat_amm_trasp', $new_desc, $stats );
    }

    return $stats;
}

/**
 * Aggiorna la descrizione di un termine se assente o diversa.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param string $new_desc  Nuova descrizione (testo con \n\n per i paragrafi).
 */
function dci_update_term_description( $term_name, $taxonomy, $new_desc, &$stats = null ) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'name'       => $term_name,
        ]
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return;
    }

    foreach ( $terms as $term ) {
        if ( empty( $term->description ) || $term->description !== $new_desc ) {
            wp_update_term(
                $term->term_id,
                $taxonomy,
                [ 'description' => $new_desc ]
            );
            if ( is_array( $stats ) ) {
                $stats['descriptions_updated']++;
            }
        }
    }
}

/**
 * Recupera il primo termine che corrisponde esattamente al nome richiesto.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int|null $parent
 * @return WP_Term|false
 */
function dci_find_trasparenza_term_by_name( $term_name, $taxonomy, $parent = null ) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'name'       => $term_name,
        ]
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return false;
    }

    foreach ( $terms as $term ) {
        if ( null === $parent || (int) $term->parent === (int) $parent ) {
            return $term;
        }
    }

    return false;
}

/**
 * Normalizza gli spazi per intercettare termini legacy creati con doppi spazi.
 *
 * @param string $term_name
 * @return string
 */
function dci_normalize_trasparenza_term_name( $term_name ) {
    return preg_replace( '/\s+/u', ' ', trim( (string) $term_name ) );
}

/**
 * Cerca un termine per nome normalizzato e parent.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int    $parent
 * @return WP_Term|false
 */
function dci_find_trasparenza_term_by_normalized_name( $term_name, $taxonomy, $parent = 0 ) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'parent'     => (int) $parent,
        ]
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return false;
    }

    $normalized_term_name = dci_normalize_trasparenza_term_name( $term_name );

    foreach ( $terms as $term ) {
        if ( dci_normalize_trasparenza_term_name( $term->name ) === $normalized_term_name ) {
            return $term;
        }
    }

    return false;
}

/**
 * Costruisce uno slug univoco per la tassonomia Trasparenza tenendo conto del parent.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int    $parent
 * @return string
 */
function dci_build_trasparenza_term_slug( $term_name, $taxonomy, $parent = 0 ) {
    $base_slug = sanitize_title( $term_name );

    if ( (int) $parent <= 0 ) {
        return $base_slug;
    }

    $ancestors = get_ancestors( $parent, $taxonomy, 'taxonomy' );
    $ancestors = array_reverse( $ancestors );
    $parts     = [];

    foreach ( $ancestors as $ancestor_id ) {
        $ancestor = get_term( $ancestor_id, $taxonomy );

        if ( $ancestor && ! is_wp_error( $ancestor ) && ! empty( $ancestor->slug ) ) {
            $parts[] = $ancestor->slug;
        }
    }

    $parent_term = get_term( $parent, $taxonomy );
    if ( $parent_term && ! is_wp_error( $parent_term ) && ! empty( $parent_term->slug ) ) {
        $parts[] = $parent_term->slug;
    }

    $parts[] = $base_slug;

    return implode( '-', array_filter( $parts ) );
}

/**
 * Recupera un termine esistente oppure lo crea se manca.
 * In fase di reload riallinea sempre parent e slug per evitare duplicati.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param int    $parent    ID del termine genitore.
 * @return array{term_id:int,action:string}
 */
function dci_upsert_trasparenza_term( $term_name, $taxonomy, $parent = 0 ) {
    $slug    = dci_build_trasparenza_term_slug( $term_name, $taxonomy, $parent );
    $term_id = 0;
    $term    = dci_find_trasparenza_term_by_name( $term_name, $taxonomy, $parent );

    if ( ! $term ) {
        $term = dci_find_trasparenza_term_by_normalized_name( $term_name, $taxonomy, $parent );
    }

    if ( ! $term ) {
        $term = get_term_by( 'slug', $slug, $taxonomy );
    }

    if ( $term instanceof WP_Term ) {
        $term_id = (int) $term->term_id;
        $args    = [];
        $action  = 'unchanged';

        if ( $term->name !== $term_name ) {
            $args['name'] = $term_name;
        }

        if ( (int) $term->parent !== (int) $parent ) {
            $args['parent'] = (int) $parent;
        }

        if ( $term->slug !== $slug ) {
            $args['slug'] = $slug;
        }

        if ( ! empty( $args ) ) {
            $updated = wp_update_term( $term_id, $taxonomy, $args );

            if ( ! is_wp_error( $updated ) && isset( $updated['term_id'] ) ) {
                $term_id = (int) $updated['term_id'];
            }
            $action = 'updated';
        }

        return [
            'term_id' => $term_id,
            'action'  => $action,
        ];
    }

    $result = wp_insert_term(
        $term_name,
        $taxonomy,
        [
            'parent' => (int) $parent,
            'slug'   => $slug,
        ]
    );

    if ( is_wp_error( $result ) ) {
        if ( 'term_exists' === $result->get_error_code() ) {
            return [
                'term_id' => (int) $result->get_error_data(),
                'action'  => 'unchanged',
            ];
        }

        return [
            'term_id' => 0,
            'action'  => 'error',
        ];
    }

    return [
        'term_id' => isset( $result['term_id'] ) ? (int) $result['term_id'] : 0,
        'action'  => 'inserted',
    ];
}

/**
 * Inserisce / aggiorna i termini e IMPOSTA SEMPRE
 *   – meta 'ordinamento' progressivo
 *   – meta 'visualizza_elemento'  (0 = nascosto, 1 = visibile)
 *   – aggiorna sempre lo slug in base al nome del termine
 */

function recursionInsertTaxonomy1( $terms, $taxonomy, $parent = 0, &$ordine = 1, &$stats = null ) {

    foreach ( $terms as $key => $children ) {

        if ( is_int( $key ) ) {
            $term_name = $children;
            $children  = [];
        } else {
            $term_name = $key;
        }

        $upsert  = dci_upsert_trasparenza_term( $term_name, $taxonomy, $parent );
        $term_id = isset( $upsert['term_id'] ) ? (int) $upsert['term_id'] : 0;

        if ( ! $term_id ) {
            continue;
        }

        if ( is_array( $stats ) ) {
            if ( isset( $upsert['action'] ) && 'inserted' === $upsert['action'] ) {
                $stats['inserted']++;
            } elseif ( isset( $upsert['action'] ) && 'updated' === $upsert['action'] ) {
                $stats['updated']++;
            }
        }

        $current_order = (string) get_term_meta( $term_id, 'ordinamento', true );
        $current_visible = (string) get_term_meta( $term_id, 'visualizza_elemento', true );
        $new_visible = dci_should_hide_trasparenza_term( $term_name ) ? '0' : '1';

        if ( $current_order !== (string) $ordine ) {
            update_term_meta( $term_id, 'ordinamento', $ordine );
            if ( is_array( $stats ) && ( ! isset( $upsert['action'] ) || 'inserted' !== $upsert['action'] ) ) {
                $stats['updated']++;
            }
        }

        if ( $current_visible !== $new_visible ) {
            update_term_meta( $term_id, 'visualizza_elemento', $new_visible );
            if ( is_array( $stats ) && ( ! isset( $upsert['action'] ) || 'inserted' !== $upsert['action'] ) ) {
                $stats['updated']++;
            }
        }
        







        

        $ordine++;

        if ( ! empty( $children ) && is_array( $children ) ) {
            recursionInsertTaxonomy1( $children, $taxonomy, $term_id, $ordine, $stats );
        }
    }
}






/**
 * Termini che NON devono comparire nei radio‑button di CMB2.
 * Scrivi i nomi esattamente come compaiono nell’array principale.
 */
function dci_should_hide_trasparenza_term( $term_name ) {
    $term_name = mb_strtolower( trim( (string) $term_name ) );

    foreach ( dci_terms_to_hide() as $hide_term ) {
        if ( $term_name === mb_strtolower( trim( (string) $hide_term ) ) ) {
            return true;
        }
    }

    return false;
}

function dci_terms_to_hide() {
    return [
        'Incarichi conferiti e autorizzati ai dipendenti',
        'Contratti Pubblici',
        // 'Pubblicazione',
        // 'Affidamento',
        // 'Esecutiva',
        // 'Sponsorizzazioni',
        // 'Atti di concessione',
    ];
}


?>








