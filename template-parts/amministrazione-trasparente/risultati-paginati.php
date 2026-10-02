<?php
global $the_query, $elemento, $load_card_type;

$current_term = get_queried_object();

$paged = max(
    1,
    (int) get_query_var('paged'),
    (int) get_query_var('page'),
    isset($_GET['paged']) ? absint($_GET['paged']) : 0
);

$max_posts = dci_sanitize_posts_per_page(
    isset($_GET['max_posts']) ? $_GET['max_posts'] : 10,
    10,
    50
);

$query = isset($_GET['search'])
    ? dci_removeslashes($_GET['search'])
    : null;

$order = isset($_GET['order_type'])
    ? sanitize_key($_GET['order_type'])
    : 'data_desc';

$current_year = (int) wp_date('Y');

/*
 * Periodo ordinario di pubblicazione:
 * anno corrente + cinque anni precedenti.
 *
 * Esempio:
 * nel 2026 vengono mostrati pubblicamente
 * gli elementi dal 2021 in poi.
 */
$public_start_year = $current_year - 5;

$available_years = range(
    $current_year,
    $public_start_year
);

$selected_year = isset($_GET['anno'])
    ? absint($_GET['anno'])
    : 0;

if (
    $selected_year !== 0
    && !in_array($selected_year, $available_years, true)
) {
    $selected_year = 0;
}


/*
|--------------------------------------------------------------------------
| Permessi archivio
|--------------------------------------------------------------------------
*/

$can_view_archive =
    function_exists('dci_user_can_view_trasparenza_archive')
    && dci_user_can_view_trasparenza_archive();

/*
 * Lo storico viene visualizzato solamente:
 *
 * - agli utenti autorizzati;
 * - quando non è selezionato uno specifico anno.
 */
$show_archive =
    $can_view_archive
    && $selected_year === 0;

$has_active_content_filters =
    trim((string) $query) !== ''
    || $selected_year > 0;


/*
|--------------------------------------------------------------------------
| Query contenuti nel periodo di pubblicazione
|--------------------------------------------------------------------------
*/

$args = array(
    'post_type'           => 'elemento_trasparenza',
    'post_status'         => 'publish',
    'posts_per_page'      => $max_posts,
    'ignore_sticky_posts' => true,

    'date_query' => array(
        array(
            'after' => array(
                'year' => $public_start_year,
            ),
            'inclusive' => true,
        ),
    ),
);


/*
 * Filtro per anno.
 */
if ($selected_year > 0) {

    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}


/*
 * Filtro per sezione di Amministrazione Trasparente.
 */
if (
    $current_term instanceof WP_Term
    && $current_term->taxonomy === 'tipi_cat_amm_trasp'
) {

    $args['tax_query'] = array(
        array(
            'taxonomy'         => 'tipi_cat_amm_trasp',
            'field'            => 'term_id',
            'terms'            => array(
                (int) $current_term->term_id,
            ),
            'include_children' => false,
        ),
    );
}


/*
 * Ricerca testuale.
 */
if ($query !== null && $query !== '') {
    $args['s'] = $query;
}


/*
 * Ordinamento.
 */
if (
    $order === 'alfabetico_asc'
    || $order === 'alfabetico_desc'
) {

    $args['orderby'] = 'title';

    $args['order'] =
        $order === 'alfabetico_desc'
            ? 'DESC'
            : 'ASC';

} else {

    $args['orderby'] = 'date';

    $args['order'] =
        $order === 'data_asc'
            ? 'ASC'
            : 'DESC';
}


/*
|--------------------------------------------------------------------------
| Archivio storico
|--------------------------------------------------------------------------
*/

$archive_query       = null;
$archive_posts       = array();
$archive_found_posts = 0;
$public_found_posts  = 0;


/*
 * Per gli utenti autorizzati, contenuti recenti e storico condividono
 * la stessa paginazione.
 */
if ($show_archive) {

    /*
     * I due intervalli temporali restano separati visivamente,
     * ma condividono la stessa paginazione.
     *
     * I totali vengono calcolati attraverso query dedicate perché
     * WP_Query può restituire found_posts = 0 quando la pagina richiesta
     * non contiene più risultati recenti.
     *
     * Utilizzare tale valore come offset dello storico potrebbe provocare
     * il salto di alcuni elementi oppure pagine apparentemente vuote.
     */

    $page_offset =
        ($paged - 1) * $max_posts;


    /*
    |--------------------------------------------------------------------------
    | Conteggio elementi nel periodo pubblico
    |--------------------------------------------------------------------------
    */

    $public_count_args = $args;

    $public_count_args['posts_per_page'] = 1;
    $public_count_args['paged']          = 1;
    $public_count_args['fields']         = 'ids';
    $public_count_args['no_found_rows']  = false;

    $public_count_query =
        new WP_Query($public_count_args);

    $public_found_posts =
        (int) $public_count_query->found_posts;


    /*
     * Numero massimo di elementi pubblici da mostrare
     * nella pagina corrente.
     */
    $public_limit = min(
        $max_posts,
        max(
            0,
            $public_found_posts - $page_offset
        )
    );


    if ($public_limit > 0) {

        $public_page_args = $args;

        $public_page_args['posts_per_page'] =
            $public_limit;

        $public_page_args['offset'] =
            $page_offset;

        $the_query =
            new WP_Query($public_page_args);

    } else {

        /*
         * Mantiene un oggetto WP_Query valido
         * senza ripetere una query fuori intervallo.
         */
        $the_query = new WP_Query(
            array(
                'post_type'      => 'elemento_trasparenza',
                'post__in'       => array(0),
                'posts_per_page' => 1,
                'no_found_rows'  => true,
            )
        );
    }


    /*
     * Quantità di elementi pubblici effettivamente
     * visualizzati nella pagina corrente.
     */
    $public_posts_on_page =
        count($the_query->posts);


    /*
     * Spazio eventualmente disponibile nella pagina
     * per gli elementi dello storico.
     */
    $archive_limit = max(
        0,
        $max_posts - $public_posts_on_page
    );


    /*
     * Calcola da quale elemento storico partire.
     */
    $archive_offset = max(
        0,
        $page_offset - $public_found_posts
    );


    /*
    |--------------------------------------------------------------------------
    | Query archivio storico
    |--------------------------------------------------------------------------
    */

    $archive_args = $args;

    unset(
        $archive_args['date_query'],
        $archive_args['paged'],
        $archive_args['offset']
    );

    /*
     * Tutto ciò che è precedente al periodo
     * ordinario di pubblicazione.
     *
     * Esempio:
     * se $public_start_year = 2021,
     * vengono considerati gli elementi fino al 2020 compreso.
     */
    $archive_args['date_query'] = array(
        array(
            'before' => array(
                'year' => $public_start_year - 1,
            ),
            'inclusive' => true,
        ),
    );


    /*
    |--------------------------------------------------------------------------
    | Conteggio totale archivio storico
    |--------------------------------------------------------------------------
    */

    $archive_count_args = $archive_args;

    $archive_count_args['posts_per_page'] = 1;
    $archive_count_args['paged']          = 1;
    $archive_count_args['fields']         = 'ids';
    $archive_count_args['no_found_rows']  = false;

    $archive_count_query =
        new WP_Query($archive_count_args);

    $archive_found_posts =
        (int) $archive_count_query->found_posts;


    /*
     * Recupera gli elementi dello storico necessari
     * per completare la pagina corrente.
     */
    if (
        $archive_limit > 0
        && $archive_offset < $archive_found_posts
    ) {

        $archive_args['posts_per_page'] =
            $archive_limit;

        $archive_args['offset'] =
            $archive_offset;

        $archive_query =
            new WP_Query($archive_args);

        $archive_posts =
            $archive_query->posts;
    }


    /*
     * Numero totale delle pagine considerando
     * sia gli elementi pubblici sia quelli storici.
     */
    $total_pages = max(
        1,
        (int) ceil(
            (
                $public_found_posts
                + $archive_found_posts
            )
            / $max_posts
        )
    );

} else {

    /*
     * Query standard per utenti pubblici
     * oppure quando è selezionato un anno.
     */
    $args['paged'] = $paged;

    $the_query =
        new WP_Query($args);

    $public_found_posts =
        (int) $the_query->found_posts;

    $total_pages = max(
        1,
        (int) $the_query->max_num_pages
    );
}


/*
|--------------------------------------------------------------------------
| Verifica presenza contenuti storici
|--------------------------------------------------------------------------
*/

$historical_content_exists =
    $archive_found_posts > 0;


/*
 * Per gli utenti che non possono consultare direttamente lo storico
 * è comunque necessario sapere:
 *
 * 1. se esistono elementi antecedenti;
 * 2. quanti elementi sono presenti.
 *
 * La verifica viene effettuata solo quando:
 *
 * - l'utente non può vedere lo storico;
 * - non sono applicati filtri;
 * - non esistono contenuti nel periodo pubblico.
 */
if (
    !$can_view_archive
    && !$has_active_content_filters
    && $public_found_posts === 0
) {

    $historical_check_args = $args;

    unset(
        $historical_check_args['paged'],
        $historical_check_args['offset'],
        $historical_check_args['date_query']
    );


    /*
     * È sufficiente recuperare un solo ID.
     *
     * no_found_rows deve però essere FALSE perché
     * abbiamo bisogno del valore totale found_posts.
     */
    $historical_check_args['posts_per_page'] = 1;
    $historical_check_args['paged']          = 1;
    $historical_check_args['fields']         = 'ids';
    $historical_check_args['no_found_rows']  = false;


    $historical_check_args['date_query'] = array(
        array(
            'before' => array(
                'year' => $public_start_year - 1,
            ),
            'inclusive' => true,
        ),
    );


    $historical_check_query =
        new WP_Query($historical_check_args);


    /*
     * Numero effettivo di elementi antecedenti.
     */
    $archive_found_posts =
        (int) $historical_check_query->found_posts;


    /*
     * Determina se lo storico contiene almeno un elemento.
     */
    $historical_content_exists =
        $archive_found_posts > 0;
}


/*
 * Etichetta dinamica corretta anche in caso di un solo elemento.
 *
 * Esempi:
 * 1 elemento
 * 12 elementi
 */
$archive_items_count_label = sprintf(
    _n(
        '%s elemento',
        '%s elementi',
        $archive_found_posts,
        'design_comuni_italia'
    ),
    number_format_i18n($archive_found_posts)
);


/*
|--------------------------------------------------------------------------
| Accesso civico e login archivio
|--------------------------------------------------------------------------
*/

$accesso_civico_url = '';
$archive_login_url  = '';


if (
    $historical_content_exists
    && !$can_view_archive
) {

    /*
     * Ricerca della sezione relativa all'accesso civico.
     */
    foreach (
        array(
            'accesso-civico-generalizzato-concernente-dati-e-documenti-ulteriori',
            'accesso-civico',
        ) as $accesso_civico_slug
    ) {

        $accesso_civico_term = get_term_by(
            'slug',
            $accesso_civico_slug,
            'tipi_cat_amm_trasp'
        );


        if (
            !$accesso_civico_term
            instanceof WP_Term
        ) {
            continue;
        }


        $accesso_civico_term_link =
            get_term_link(
                $accesso_civico_term
            );


        if (
            !is_wp_error(
                $accesso_civico_term_link
            )
        ) {

            $accesso_civico_url =
                $accesso_civico_term_link;

            break;
        }
    }


    /*
     * URL di accesso per operatori autorizzati.
     */
    if (!is_user_logged_in()) {

        $archive_redirect_url = '';


        if (
            $current_term
            instanceof WP_Term
        ) {

            $archive_redirect_url =
                get_term_link(
                    $current_term
                );


            if (
                is_wp_error(
                    $archive_redirect_url
                )
            ) {

                $archive_redirect_url = '';
            }
        }


        $archive_login_url =
            wp_login_url(
                $archive_redirect_url
            );
    }
}
?>


<?php
/*
|--------------------------------------------------------------------------
| Elementi nel periodo pubblico
|--------------------------------------------------------------------------
*/
?>

<?php if (!empty($the_query->posts)) { ?>

    <?php
    $categoria = $the_query->posts;
    ?>

    <div
        class="row g-4"
        id="load-more"
    >

        <?php
        foreach ($categoria as $elemento) {

            $load_card_type =
                'elemento_trasparenza';

            get_template_part(
                'template-parts/amministrazione-trasparente/card'
            );
        }
        ?>

    </div>


<?php } elseif (empty($archive_posts)) { ?>


    <?php
    /*
    |--------------------------------------------------------------------------
    | Messaggio se non ci sono elementi pubblici
    |--------------------------------------------------------------------------
    */
    ?>

    <div
        class="dci-at-empty text-decoration-none"
        role="status"
        aria-live="polite"
    >

        <span
            class="dci-at-empty__icon"
            aria-hidden="true"
        >
            <svg class="icon icon-sm">
                <use href="#it-info-circle"></use>
            </svg>
        </span>


        <div class="dci-at-empty__content">


            <?php if (!$has_active_content_filters) { ?>


                <?php if ($historical_content_exists) { ?>


                    <p class="dci-at-empty__title text-decoration-none">
                        Sono presenti elementi degli anni precedenti
                    </p>


                    <p class="dci-at-empty__text text-decoration-none">

                        <p class="mb-2">In questa sezione, dal
                        <?php echo esc_html($public_start_year); ?>
                        a oggi, non risultano nuovi elementi pubblicati.</p>

                        <p class="mb-2"> Sono tuttavia presenti
                        <strong>
                            <?php
                            echo esc_html(
                                $archive_items_count_label
                            );
                            ?>
                        </strong>
                        antecedenti al
                        <?php echo esc_html($public_start_year); ?>.</p>

                        <p class="mb-2">Tali contenuti non sono mostrati nell'elenco pubblico
                        poiché è terminato il relativo periodo ordinario
                        di pubblicazione online.</p>

                    </p>


                <?php } else { ?>


                    <p class="dci-at-empty__title text-decoration-none">
                        Nessun nuovo elemento nel periodo di pubblicazione
                    </p>


                    <p class="dci-at-empty__text text-decoration-none">

                        In questa sezione non risultano elementi pubblicati dal
                        <?php echo esc_html($public_start_year); ?>
                        a oggi.
                          
                        <p class="mb-2">
                            Non risultano, inoltre, pubblicazioni antecedenti a tale periodo.
                        </p>
                    </p>


                <?php } ?>


                <?php
                /*
                |--------------------------------------------------------------------------
                | Riferimento normativo
                |--------------------------------------------------------------------------
                */
                ?>

                <div class="dci-at-empty__law">

                    <span class="dci-at-empty__law-title">
                        Durata dell'obbligo di pubblicazione
                    </span>

                    L'art. 8, comma 3, del d.lgs. 33/2013 prevede,
                    in via ordinaria, la pubblicazione di dati,
                    informazioni e documenti per cinque anni,
                    decorrenti dal 1° gennaio dell'anno successivo
                    a quello da cui decorre l'obbligo, e comunque
                    finché gli atti pubblicati producono i loro effetti,
                    salvo i diversi termini previsti dalla legge.

                    <br>

                    <a
                        class="dci-at-empty__source"
                        href="https://www.normattiva.it/eli/id/2013/04/05/13G00076/CONSOLIDATED"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        Consulta il d.lgs. 33/2013 su Normattiva

                        <svg
                            class="icon"
                            aria-hidden="true"
                        >
                            <use href="#it-external-link"></use>
                        </svg>

                    </a>

                </div>


                <?php
                /*
                |--------------------------------------------------------------------------
                | Indicazioni accesso allo storico
                |--------------------------------------------------------------------------
                */
                ?>


                <?php if (
                    $historical_content_exists
                    && $archive_found_posts > 0
                    && $can_view_archive
                ) { ?>


                    <div class="dci-at-empty__access">

                        <span class="dci-at-empty__access-title">
                            Consultazione dello storico
                        </span>

                        Il tuo account è autorizzato:
                        gli elementi degli anni precedenti
                        sono visualizzati qui sotto.

                    </div>


                <?php } elseif ($historical_content_exists) { ?>


                    <div class="dci-at-empty__access-list">


                        <div class="dci-at-empty__access">

                            <span class="dci-at-empty__access-title">
                                Operatori autorizzati
                            </span>

                            Gli operatori abilitati possono accedere
                            con le proprie credenziali per consultare
                            gli elementi degli anni precedenti.


                            <?php if ($archive_login_url !== '') { ?>

                                <!--
                                <a
                                    class="dci-at-empty__action"
                                    href="<?php echo esc_url($archive_login_url); ?>"
                                >
                                    Accedi per consultare lo storico
                                </a>
                                -->

                            <?php } elseif (is_user_logged_in()) { ?>


                                <span class="dci-at-empty__access-note">

                                    Il tuo account è autenticato,
                                    ma non dispone del permesso
                                    per consultare lo storico.

                                </span>


                            <?php } ?>

                        </div>


                        <div class="dci-at-empty__access">

                            <span class="dci-at-empty__access-title">
                                Cittadini
                            </span>

                            Puoi richiedere i documenti tramite
                            la procedura di accesso civico generalizzato,
                            prevista dall'art. 5, comma 2,
                            del d.lgs. 33/2013.


                            <?php if ($accesso_civico_url !== '') { ?>

                                <a
                                    class="dci-at-empty__action"
                                    href="<?php echo esc_url($accesso_civico_url); ?>"
                                >
                                    Consulta la procedura di accesso civico
                                </a>

                            <?php } ?>

                        </div>


                    </div>


                <?php } ?>


            <?php } else { ?>


                <p class="dci-at-empty__title text-decoration-none">
                    Nessun contenuto disponibile
                </p>


                <p class="dci-at-empty__text text-decoration-none">

                    Non ci sono elementi o post da mostrare
                    con i filtri attuali.

                    Prova a cambiare ricerca o ordinamento.

                </p>


            <?php } ?>


        </div>

    </div>


<?php } ?>


<?php
/*
|--------------------------------------------------------------------------
| Preparazione paginazione
|--------------------------------------------------------------------------
*/

$pagination_args = array();


if ($query !== null && $query !== '') {

    $pagination_args['search'] =
        $query;
}


if (!empty($order)) {

    $pagination_args['order_type'] =
        $order;
}


if (!empty($max_posts)) {

    $pagination_args['max_posts'] =
        (int) $max_posts;
}


if ($selected_year > 0) {

    $pagination_args['anno'] =
        $selected_year;
}


/*
 * Base standard della paginazione.
 */
$pagination_base = str_replace(
    999999999,
    '%#%',
    esc_url(
        get_pagenum_link(
            999999999
        )
    )
);

$pagination_format =
    '?paged=%#%';


/*
 * Base specifica per tassonomia.
 */
if (
    $current_term instanceof WP_Term
    && $current_term->taxonomy === 'tipi_cat_amm_trasp'
) {

    $term_link =
        get_term_link(
            $current_term
        );


    if (!is_wp_error($term_link)) {

        $pagination_base =
            trailingslashit(
                $term_link
            )
            . 'page/%#%/';

        $pagination_format = '';
    }
}


/*
 * Generazione link paginazione.
 */
$pages = paginate_links(
    array(
        'base'      => esc_url($pagination_base),
        'format'    => $pagination_format,
        'current'   => $paged,
        'total'     => $total_pages,
        'type'      => 'array',
        'show_all'  => false,
        'end_size'  => 3,
        'mid_size'  => 1,
        'prev_next' => true,
        'prev_text' => __('« '),
        'next_text' => __(' »'),
        'add_args'  => $pagination_args,
        'add_fragment' => '',
    )
);


$pagination_markup = '';


if (is_array($pages)) {

    $pagination_markup =
        '<div class="pagination"><ul class="pagination">';


    foreach ($pages as $page_link) {

        $pagination_markup .=
            '<li class="page-item'
            . (
                strpos(
                    $page_link,
                    'current'
                ) !== false
                    ? ' active'
                    : ''
            )
            . '">'
            . str_replace(
                'page-numbers',
                'page-link',
                $page_link
            )
            . '</li>';
    }


    $pagination_markup .=
        '</ul></div>';
}
?>


<?php
/*
|--------------------------------------------------------------------------
| Archivio storico
|--------------------------------------------------------------------------
*/
?>

<?php if (!empty($archive_posts)) { ?>


    <aside
        class="dci-at-notice mt-5 mb-4"
        role="note"
        aria-labelledby="dci-at-archive-title"
    >
    <span class="dci-at-notice__label"><svg class="icon icon-sm" aria-hidden="true"><use href="#it-info-circle"></use></svg> Informazioni sulla pubblicazione</span>

        <h2
            id="dci-at-archive-title"
            class="h5 mb-3"
        >
            Contenuti non più soggetti all’obbligo di pubblicazione
        </h2>


        <p>

            Di seguito sono riportati i contenuti per i quali
            è cessato l’obbligo di pubblicazione e che,
            pertanto, non risultano più accessibili attraverso
            la consultazione pubblica ordinaria del portale.

        </p>


        <p>

            Ai sensi dell’<strong>art. 8, comma 3,
            del decreto legislativo 14 marzo 2013, n. 33</strong>,
            i dati, le informazioni e i documenti oggetto
            di pubblicazione obbligatoria sono, in via ordinaria,
            pubblicati per un periodo di <strong>cinque anni</strong>,
            decorrenti dal 1° gennaio dell’anno successivo
            a quello da cui decorre l’obbligo di pubblicazione,
            fatti salvi i diversi termini previsti dalla normativa
            vigente e i casi in cui gli atti continuino
            a produrre i propri effetti.

        </p>


        <p>

            Decorso il periodo previsto per la pubblicazione,
            tali contenuti non sono più resi direttamente
            disponibili agli utenti mediante la consultazione
            pubblica della sezione
            <strong>“Amministrazione Trasparente”</strong>.

        </p>


        <p>

            I contenuti restano disponibili agli utenti autorizzati,
            secondo i rispettivi livelli di accesso,
            per le attività amministrative interne,
            di verifica, controllo e conservazione degli atti.

        </p>


        <p>

            Resta ferma, per i soggetti esterni,
            la possibilità di richiedere l’accesso ai dati
            e ai documenti secondo le modalità previste
            dall’<strong>art. 5 del d.lgs. 33/2013</strong>
            e dalle altre disposizioni vigenti in materia
            di accesso, nel rispetto degli eventuali limiti
            previsti dalla normativa.

        </p>


        <p class="mb-0">

            In questa sezione sono pertanto riportati

            <strong>
                <?php
                echo esc_html(
                    $archive_items_count_label
                );
                ?>
            </strong>

            pubblicati prima del

            <strong>
                <?php
                echo esc_html(
                    $public_start_year
                );
                ?>
            </strong>,

            non più soggetti, in via ordinaria,
            all’obbligo di pubblicazione.

        </p>

    </aside>


    <div
        class="row g-4"
        id="archived-items"
    >

        <?php
        foreach ($archive_posts as $elemento) {

            $load_card_type =
                'elemento_trasparenza';

            get_template_part(
                'template-parts/amministrazione-trasparente/card'
            );
        }
        ?>

    </div>


<?php } ?>


<?php
/*
|--------------------------------------------------------------------------
| Paginazione
|--------------------------------------------------------------------------
*/
?>

<?php if ($pagination_markup !== '') { ?>

    <div class="row my-4">

        <div class="col-12 d-flex">

            <nav
                class="pagination-wrapper"
                aria-label="Navigazione pagine"
            >

                <?php
                echo $pagination_markup;
                ?>

            </nav>

        </div>

    </div>

<?php } ?>


<?php
wp_reset_postdata();
?>
