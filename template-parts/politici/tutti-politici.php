<?php
global $the_query, $load_posts, $load_card_type, $tax_query, $additional_filter, $filter_ids;

$per_page = 12;

$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$politici_page = isset($_GET['politici_page']) ? max(1, absint($_GET['politici_page'])) : 1;
$politici_cessati_page = isset($_GET['politici_cessati_page']) ? max(1, absint($_GET['politici_cessati_page'])) : 1;
$today_ts = strtotime(date('Y-m-d', current_time('timestamp')));

$tipo_incarico = 'politico';
$descrizione = 'dei politici';
$additional_filter = is_array($additional_filter) ? $additional_filter : array();

// // Determina il tipo di incarico in base al nome del post corrente
// if (isset($post->post_name)) {
//     switch ($post->post_name) {
//         case 'politici':
//             $tipo_incarico = 'politico';
//             $descrizione = 'dei politici';
//             break;
//         case 'personale-amministrativo':
//             $tipo_incarico = 'amministrativo';
//             $descrizione = 'del personale amministrativo';
//             break;
//         case 'personale-sanitario':
//             $tipo_incarico = 'sanitario';
//             $descrizione = 'del personale sanitario';
//             break;
//         case 'personale-socio-assistenziale':
//             $tipo_incarico = 'socio-assistenziale';
//             $descrizione = 'del personale socio-assistenziale';
//             break;
//         case 'altro':
//             $tipo_incarico = 'altro';
//             $descrizione = 'del personale';
//             break;
//     }
// } Non necessario, il tipo di incarico è già impostato a 'politico' e la descrizione a 'dei politici'

$tax_query = array(
    array(
        'taxonomy' => 'tipi_incarico',
        'field' => 'slug',
        'terms' => $tipo_incarico,
    ),
);

$incarichi_politici_ids = get_posts(array(
    'post_type' => 'incarico',
    'tax_query' => $tax_query,
    'posts_per_page' => -1,
    'fields' => 'ids',
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
));

$persona_incarichi_politici = array();
$persone_ids = array();

foreach ($incarichi_politici_ids as $incarico_id) {
    $persone = get_post_meta((int) $incarico_id, '_dci_incarico_persona');

    foreach ($persone as $persona_id) {
        $persona_id = (int) $persona_id;
        if (!$persona_id) {
            continue;
        }

        $persone_ids[] = $persona_id;
        $persona_incarichi_politici[$persona_id][] = (int) $incarico_id;
    }
}

$persone_ids = array_values(array_unique(array_map('intval', $persone_ids)));
$filter_ids = $persone_ids;

if (!empty($persone_ids)) {
    $persone_query_args = array(
        's' => $query,
        'posts_per_page' => -1,
        'post_type' => 'persona_pubblica',
        'post_status' => 'publish',
        'orderby' => 'post_title',
        'order' => 'ASC',
        'post__in' => $persone_ids,
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    $persone_ids = get_posts($persone_query_args);
} else {
    $persone_ids = array();
}
// Separazione delle persone in attuali e cessate
$persone_attuali_ids = array();
$persone_cessate_ids = array();

foreach ($persone_ids as $person_id) {
    // Ricavo l'id della persona e verifico se ha incarichi attivi
    $person_id = (int) $person_id;
    $is_current = true;

    // Data fine persona
    $data_conclusione = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $person_id);

    if (!empty($data_conclusione)) {
        $fine_persona_ts = is_numeric($data_conclusione) ? intval($data_conclusione) : strtotime(str_replace('/', '-', (string) $data_conclusione));
        if ($fine_persona_ts && $fine_persona_ts < $today_ts) {
            $is_current = false;
        }
    }

    // Verifico se la persona ha incarichi attivi
    if ($is_current) {
        $has_active_political_incarico = false;
        $incarichi_persona = $persona_incarichi_politici[$person_id] ?? array();

        foreach ($incarichi_persona as $incarico_id) {
            $data_fine = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $incarico_id);

            if (empty($data_fine)) {
                $has_active_political_incarico = true;
                break;
            }

            $data_fine_ts = is_numeric($data_fine) ? intval($data_fine) : strtotime(str_replace('/', '-', (string) $data_fine));
            if (!$data_fine_ts || $data_fine_ts >= $today_ts) {
                $has_active_political_incarico = true;
                break;
            }
        }

        $is_current = $has_active_political_incarico;
    }

    if ($is_current) {
        $persone_attuali_ids[] = $person_id;
    } else {
        $persone_cessate_ids[] = $person_id;
    }
}
// Conteggio totale delle persone attuali e cessate
$total_attuali = count($persone_attuali_ids);
$total_cessate = count($persone_cessate_ids);

// Calcolo il numero massimo di pagine per le persone attuali e cessate
$max_pages_attuali = max(1, (int) ceil($total_attuali / $per_page));
$max_pages_cessate = max(1, (int) ceil($total_cessate / $per_page));


// Assicuro che le pagine correnti non superino il numero massimo di pagine
$politici_page = min($politici_page, $max_pages_attuali);
$politici_cessati_page = min($politici_cessati_page, $max_pages_cessate);

$persone_attuali_page_ids = array_slice($persone_attuali_ids, ($politici_page - 1) * $per_page, $per_page);
$persone_cessate_page_ids = array_slice($persone_cessate_ids, ($politici_cessati_page - 1) * $per_page, $per_page);

$persone_attuali = !empty($persone_attuali_page_ids) ? get_posts(array(
    'post_type' => 'persona_pubblica',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'post__in' => $persone_attuali_page_ids,
    'orderby' => 'post__in',
)) : array();

$persone_cessate = !empty($persone_cessate_page_ids) ? get_posts(array(
    'post_type' => 'persona_pubblica',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'post__in' => $persone_cessate_page_ids,
    'orderby' => 'post__in',
)) : array();

// Generazione dei link di paginazione per le persone attuali e cessate
$pagination_base_politici = function_exists('dci_get_pagination_base_url')
    ? dci_get_pagination_base_url('politici_page')
    : str_replace(999999999, '%#%', esc_url(add_query_arg('politici_page', 999999999)));
$pagination_base_cessati = function_exists('dci_get_pagination_base_url')
    ? dci_get_pagination_base_url('politici_cessati_page')
    : str_replace(999999999, '%#%', esc_url(add_query_arg('politici_cessati_page', 999999999)));
?>

<div class="bg-grey-card py-3">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>

        <div class="container">
            <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">
                Elenco <?php echo esc_html($descrizione); ?>
            </h2>

            <!-- SEARCH -->
            <div class="cmp-input-search">
                <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                    <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                        <input
                            type="search"
                            class="autocomplete form-control"
                            placeholder="Cerca una parola chiave"
                            id="autocomplete-two"
                            name="search"
                            value="<?php echo esc_attr($query); ?>"
                            data-bs-autocomplete="[]"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                Invio
                            </button>
                        </div>
                        <span class="autocomplete-icon" aria-hidden="true">
                            <svg class="icon icon-sm icon-primary">
                                <use href="#it-search"></use>
                            </svg>
                        </span>
                    </div>
                </div>

                <p class="mb-4">
                    <strong><?php echo (int) $total_attuali; ?></strong> risultati in ordine alfabetico
                </p>
            </div>

            <!-- PERSONE ATTUALI -->
            <div class="row g-2" id="load-more">
                <?php
                if (!empty($persone_attuali)) {
                    foreach ($persone_attuali as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/politici/cards-list');
                    }
                    wp_reset_postdata();
                } else {
                    get_template_part('template-parts/content', 'none');
                }
                ?>
            </div>

             <!-- TOGGLE CESSATI -->
            <?php if ($max_pages_attuali > 1) { ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine politici">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base' => $pagination_base_politici,
                            'format' => '',
                            'current' => $politici_page,
                            'total' => $max_pages_attuali,
                            'type' => 'array',
                            'show_all' => false,
                            'end_size' => 3,
                            'mid_size' => 1,
                            'prev_next' => true,
                            'prev_text' => __('Inizio'),
                            'next_text' => __('Fine'),
                            'add_fragment' => '#search-form',
                        ));
                        if ($pagination_links) {
                            echo '<div class="pagination"><ul class="pagination">';
                            foreach ($pagination_links as $link) {
                                echo '<li class="page-item ' . (strpos($link, 'current') !== false ? 'active' : '') . '">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                            }
                            echo '</ul></div>';
                        }
                        ?>
                    </nav>
                </div>
            <?php } ?>

            <?php if (!empty($persone_cessate_ids)) { ?>
                <div class="mt-4">
                    <button type="button" id="toggle-cessati" class="btn btn-outline-primary d-flex align-items-center gap-2" aria-expanded="false" aria-controls="section-cessati">
                        <span>Mostra persone non piu in carica</span>
                        <svg class="icon icon-sm">
                            <use href="#it-chevron-right"></use>
                        </svg>
                    </button>
                </div>

                <div id="section-cessati" hidden class="row g-2 mt-3">
                    <?php
                    foreach ($persone_cessate as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/politici/cards-list');
                    }
                    wp_reset_postdata();
                    ?>

                    <?php if ($max_pages_cessate > 1) { ?>
                        <div class="row my-4 col-12">
                            <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine politici non in carica">
                                <?php
                                $pagination_links = paginate_links(array(
                                    'base' => $pagination_base_cessati,
                                    'format' => '',
                                    'current' => $politici_cessati_page,
                                    'total' => $max_pages_cessate,
                                    'type' => 'array',
                                    'show_all' => false,
                                    'end_size' => 3,
                                    'mid_size' => 1,
                                    'prev_next' => true,
                                    'prev_text' => __('Inizio'),
                                    'next_text' => __('Fine'),
                                    'add_fragment' => '#section-cessati',
                                ));
                                if ($pagination_links) {
                                    echo '<div class="pagination"><ul class="pagination">';
                                    foreach ($pagination_links as $link) {
                                        echo '<li class="page-item ' . (strpos($link, 'current') !== false ? 'active' : '') . '">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                                    }
                                    echo '</ul></div>';
                                }
                                ?>
                            </nav>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php
                // Non è necessario caricare un template separato per i risultati, poiché stiamo già visualizzando le persone attuali e cessate direttamente in questo file.
                // $load_card_type = 'persona_pubblica';
                // get_template_part("template-parts/search/more-results");
            ?>
        </div>
    </form>
</div>

<!-- Script per il toggle delle persone cessate -->
<script>
(function () {
    let btn = document.getElementById('toggle-cessati');
    let section = document.getElementById('section-cessati');

    if (!btn || !section || btn.getAttribute('data-toggle-ready') === 'true') return;

    btn.setAttribute('data-toggle-ready', 'true');

    btn.addEventListener('click', function (event) {
        event.preventDefault();

        let isOpen = !section.hasAttribute('hidden');
        let label = btn.querySelector('span');

        if (isOpen) {
            section.setAttribute('hidden', 'hidden');
            btn.setAttribute('aria-expanded', 'false');
        } else {
            section.removeAttribute('hidden');
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.classList.toggle('is-open', !isOpen);

        if (label) {
            label.textContent = isOpen
                ? 'Mostra persone non piu in carica'
                : 'Nascondi persone non piu in carica';
        }
    });

    if (window.location.hash === '#section-cessati') {
        section.removeAttribute('hidden');
        btn.setAttribute('aria-expanded', 'true');
        btn.classList.add('is-open');
        if (btn.querySelector('span')) {
            btn.querySelector('span').textContent = 'Nascondi persone non piu in carica';
        }
    }
}());
</script>
