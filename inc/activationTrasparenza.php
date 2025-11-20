<?php 

function dci_trasparenza_activation() {
    set_time_limit(400);  // Aumenta il timeout

    // Inserisce i termini di tassonomia
    insertTaxonomyTrasparenzaTerms();

    // Imposta un'opzione per indicare che il setup è avvenuto
    update_option("dci_has_installed", true);

    // Disabilita i commenti di default per i nuovi post
    if ('' != get_option('default_comment_status')) {
        update_option('default_comment_status', '');
    }
}
add_action('after_switch_theme', 'dci_trasparenza_activation');
//dci_reload_trasparenza_option_page('themes.php', 'dci_trasparenza_activation');


// ===========================
// Pagina Admin per forzare la ricarica
// ===========================
function dci_reload_trasparenza_option_page() {
    if (isset($_GET["action"]) && $_GET["action"] === "reload") {
        dci_trasparenza_activation(); // Esegue nuovamente l'attivazione
        echo '<div class="notice notice-success is-dismissible"><p>Dati ricaricati con successo.</p></div>';
    }

    echo "<div class='wrap'>";
    echo "<h1>Ricarica i dati della Trasparenza</h1>";
    echo '<p>Questa operazione reinserisce le tassonomie e opzioni di default relative alla sezione "Amministrazione Trasparente".</p>';
    echo '<a href="' . esc_url(admin_url('themes.php?page=reload-trasparenza-theme-options&action=reload')) . '" class="button button-primary">Ricarica Trasparenza</a>';
    echo "</div>";
}

function dci_add_trasparenza_theme_page() {
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
                "Piano triennale per la prevenzione della corruzione e della trasparenza",
                'Atti generali',
                "Oneri informativi per cittadini e imprese"
            ],
            'Organizzazione' => [
                'Organi di indirizzo politico-amministrativo',
                "Sanzioni per mancata comunicazione dei dati",
                "Rendiconti gruppi consiliari regionali/provinciali",
                "Articolazione degli uffici",
                "Telefono e posta elettronica"
            ],
            'Consulenti e collaboratori' => [
                'Titolari di incarichi di collaborazione o consulenza'
            ],
            'Personale' => [
                'Incarichi amministrativi di vertice',
                'Dirigenti',
                'Posizioni organizzative',
                'Dotazione organica',
                'Personale non a tempo indeterminato',
                'Tassi di assenza',
                "Incarichi conferiti e autorizzati ai dipendenti",
                "Contrattazione collettiva",
                "Contrattazione integrativa",
                "OIV"
            ],
            'Bandi di concorso' => [
                'Concorsi'
            ],
            'Performance'=> [
                "Piano della Performance",
                "Relazione sulla Performance",
                "Ammontare complessivo dei premi",
                "Benessere organizzativo"
            ],
            'Enti controllati' =>[
                "Enti pubblici vigilati",
                "Società partecipate",
                "Enti di diritto privato controllati",
                "Rappresentazione grafica"
            ],
            "Attività e procedimenti"=>[
                "Dati aggregati attività amministrativa",
                "Tipologie di procedimento",
                "Monitoraggio tempi procedimentali",
                "Dichiarazioni sostitutive  e acquisizione d'ufficio dei dati"
            ],
            "Provvedimenti" =>[
                "Provvedimenti organi indirizzo-politico",
                "Provvedimenti dirigenti"
            ],
            "Bandi di Gara e contratti"=>[
               "Informazioni sulle singole procedure in formato tabellare",
               "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura",
               "Contratti Pubblici",
            ],
            "Sovvenzioni , contributi sussidi, vantaggi economici"=>[
                "Criteri e modalità",
                "Atti di concessione",
                "Elenchi"
            ],
            "Bilanci"=>[
                "Bilancio preventivo e consuntivo",
                "Piano degli indicatori e risultati attesi di bilancio"
            ],
            "Beni immobili e gestione patrimonio"=>[
                "Patrimonio immobiliare",
                "Canoni di locazione o affitto"
            ],
            "Controlli e rilievi sull'amministrazione"=>[
                "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe",
                "Organi di revisione amministrativa e contabile",
                "Corte dei conti"
            ],
            "Servizi Erogati"=>[
                "Carta dei servizi e standard di qualità",
                "Costi contabilizzati",
                "Tempi medi di erogazione dei servizi",
                "Liste di attesa"
            ],
            "Pagamenti dell'amministrazione" => [
                    "Dati sui pagamenti",
                    "Indicatore di tempestività dei pagamenti",
                    "IBAN e pagamenti informatici",
                    "Dati sui pagamenti del servizio sanitario nazionale"
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
                "Informazioni ambientali"
            ],
            "Strutture sanitarie private accreditate" => [
                "Strutture sanitarie private accreditate"
            ],
            "Interventi straordinari e di emergenza" => [
                "Interventi straordinari e di emergenza"
            ],
            "Altri contenuti" => [
                "Prevenzione della corruzione",
                "Accesso civico",
                "Accessibilità e Catalogo di dati, metadati e banche dati",
                "Dati ulteriori"
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

    /* --------------------------- */
    /* 1) Inserimento tassonomie   */
    /* --------------------------- */
    // Categorie Trasparenza
    $tipi_cat_amm_trasp_array = dci_tipi_cat_amm_trasp_array();
    // recursionInsertTaxonomy( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );
    recursionInsertTaxonomy1( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );

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

];

    foreach ( $descrizioni as $term_name => $new_desc ) {
        dci_update_term_description( $term_name, 'tipi_cat_amm_trasp', $new_desc );
    }
}

/**
 * Aggiorna la descrizione di un termine se assente o diversa.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param string $new_desc  Nuova descrizione (testo con \n\n per i paragrafi).
 */
function dci_update_term_description( $term_name, $taxonomy, $new_desc ) {
    $term = get_term_by( 'name', $term_name, $taxonomy );

    if ( $term && ( empty( $term->description ) || $term->description !== $new_desc ) ) {
        wp_update_term(
            $term->term_id,
            $taxonomy,
            [ 'description' => $new_desc ]
        );
    }
}



/**
 * Inserisce / aggiorna i termini e IMPOSTA SEMPRE
 *   – meta 'ordinamento' progressivo
 *   – meta 'visualizza_elemento'  (0 = nascosto, 1 = visibile)
 *   – aggiorna sempre lo slug in base al nome del termine
 */

function recursionInsertTaxonomy1( $terms, $taxonomy, $parent = 0, &$ordine = 1 ) {

    $to_hide = dci_terms_to_hide();

    foreach ( $terms as $key => $children ) {

        if ( is_int( $key ) ) {
            $term_name = $children;
            $children  = [];
        } else {
            $term_name = $key;
        }

        $result  = wp_insert_term( $term_name, $taxonomy, [ 'parent' => $parent ] );
        $term_id = ( is_wp_error( $result ) && 'term_exists' === $result->get_error_code() )
                 ? (int) $result->get_error_data()
                 : ( ! is_wp_error( $result ) ? (int) $result['term_id'] : 0 );

        if ( ! $term_id ) {
            continue;
        }

        update_term_meta( $term_id, 'ordinamento', $ordine );

        $visible = '1';
            foreach ($to_hide as $hide_term) {
                // Rimuovi tutte le virgole dai termini da nascondere e dal termine corrente
                $cleaned_term_name = str_replace(',', '', trim($term_name));
                $cleaned_hide_term = str_replace(',', '', trim($hide_term));
            
                // Confronta i termini senza virgole
                if (strcasecmp($cleaned_term_name, $cleaned_hide_term) === 0) {
                    $visible = '0';
                    break;
                }
            }
        update_term_meta( $term_id, 'visualizza_elemento', $visible );

        $ordine++;

        if ( ! empty( $children ) && is_array( $children ) ) {
            recursionInsertTaxonomy1( $children, $taxonomy, $term_id, $ordine );
        }
    }
}






/**
 * Termini che NON devono comparire nei radio‑button di CMB2.
 * Scrivi i nomi esattamente come compaiono nell’array principale.
 */
function dci_terms_to_hide() {
    return [
        'Incarichi conferiti e autorizzati ai dipendenti',
        'Contratti Pubblici',
        'Atti di concessione',
    ];
}


?>

