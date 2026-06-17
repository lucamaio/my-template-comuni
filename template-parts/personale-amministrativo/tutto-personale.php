<?php
global $the_query, $load_posts, $load_card_type;

$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 300;
$per_page = 12;
$load_posts = $per_page;

$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$personale_page = isset($_GET['personale_page']) ? max(1, absint($_GET['personale_page'])) : 1;
$personale_cessato_page = isset($_GET['personale_cessato_page']) ? max(1, absint($_GET['personale_cessato_page'])) : 1;
$today_ts = strtotime(date('Y-m-d', current_time('timestamp')));

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'persona_pubblica',
    'orderby'        => 'post_title',
    'order'          => 'ASC',
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

// Variabili per il conteggio e la separazione dei record senza incarichi politici.
$posts_attuali = array();
$posts_cessati = array();

if ($the_query->have_posts()) {
    while ($the_query->have_posts()) {
        $the_query->the_post();

        // Ottieni l'ID del post corrente
        $post_id = get_the_ID();
        $persona_scaduta = false;

        $data_conclusione_persona = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $post_id);
        if (!empty($data_conclusione_persona)) {
            $fine_persona_ts = is_numeric($data_conclusione_persona) ? intval($data_conclusione_persona) : strtotime(str_replace('/', '-', (string) $data_conclusione_persona));
            $persona_scaduta = $fine_persona_ts && $fine_persona_ts < $today_ts;
        }

        // Recupera gli incarichi associati al post
        $incarichi = dci_get_meta('incarichi') ?? [];  // Recupera gli incarichi associati
        $has_non_political_incarico = false;
        $has_active_non_political_incarico = false;

        // Se ci sono incarichi
        if (!empty($incarichi)) {
            foreach ($incarichi as $incarico_id) {
                // Recupera il tipo di incarico associato
                $tipo_incarico_terms = get_the_terms($incarico_id, 'tipi_incarico');

                // Verifica se ci sono termini di tipo incarico
                if (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms)) {
                    $is_political = false;

                    foreach ($tipo_incarico_terms as $term) {
                        $tipo_incarico_name = isset($term->name) ? strtolower($term->name) : '';
                        $tipo_incarico_slug = isset($term->slug) ? strtolower($term->slug) : '';

                        if ($tipo_incarico_name === 'politico' || $tipo_incarico_slug === 'politico') {
                            $is_political = true;
                            break;
                        }
                    }

                    // Se il tipo di incarico e "politico", salta questo elemento
                    if ($is_political) {
                        continue;
                    }

                    $has_non_political_incarico = true;

                    $data_fine = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $incarico_id);
                    if (empty($data_fine)) {
                        $has_active_non_political_incarico = true;
                        break;
                    }

                    $data_fine_ts = is_numeric($data_fine) ? intval($data_fine) : strtotime(str_replace('/', '-', (string) $data_fine));
                    if (!$data_fine_ts || $data_fine_ts >= $today_ts) {
                        $has_active_non_political_incarico = true;
                        break;
                    }
                }
            }
        } else {
            $has_non_political_incarico = true;
            $has_active_non_political_incarico = !$persona_scaduta;
        }

        if (!$has_non_political_incarico) {
            continue;
        }

        if ($persona_scaduta || !$has_active_non_political_incarico) {
            $posts_cessati[] = get_post($post_id);
        } else {
            $posts_attuali[] = get_post($post_id);
        }
    }

    wp_reset_postdata();
}

$total_records = count($posts_attuali);
$total_cessati = count($posts_cessati);
$max_pages = max(1, (int) ceil($total_records / $per_page));
$max_pages_cessati = max(1, (int) ceil($total_cessati / $per_page));
$personale_page = min($personale_page, $max_pages);
$personale_cessato_page = min($personale_cessato_page, $max_pages_cessati);
$posts = array_slice($posts_attuali, ($personale_page - 1) * $per_page, $per_page);
$posts_cessati_page = array_slice($posts_cessati, ($personale_cessato_page - 1) * $per_page, $per_page);
$the_query->posts = $posts;
$the_query->post_count = count($posts);
$the_query->found_posts = $total_records;
$the_query->max_num_pages = $max_pages;
$pagination_base_personale = function_exists('dci_get_pagination_base_url')
    ? dci_get_pagination_base_url('personale_page')
    : str_replace(999999999, '%#%', esc_url(add_query_arg('personale_page', 999999999)));
$pagination_base_cessati = function_exists('dci_get_pagination_base_url')
    ? dci_get_pagination_base_url('personale_cessato_page')
    : str_replace(999999999, '%#%', esc_url(add_query_arg('personale_cessato_page', 999999999)));

?>












<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora il personale amministrativo
            </h2>
            <div>
                <div class="cmp-input-search">
                    <div class="form-group autocomplete-wrapper mb-0">
                        <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden"
                        >Cerca</label
                        >
                        <input
                        type="search"
                        class="autocomplete form-control"
                        placeholder="Cerca per parola chiave"
                        id="autocomplete-two"
                        name="search"
                        value="<?php echo esc_attr($query); ?>"
                        data-bs-autocomplete="[]"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" id="button-3">
                                Invio
                            </button>
                        </div>
                        <span class="autocomplete-icon" aria-hidden="true"
                        ><svg
                            class="icon icon-sm icon-primary"
                            role="img"
                            aria-labelledby="autocomplete-label"
                        >
                            <use
                            href="#it-search"
                            ></use></svg></span>
                        </div>
                        <p
                        id="autocomplete-label"
                        class="u-grey-light text-paragraph-card mt-2 mb-4 mt-lg-3 mb-lg-40"
                        >
                        <?php echo $total_records; ?> amministratori trovati in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php
                    $load_card_type = 'personale-amministrativo';
                    foreach ($posts as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/personale-amministrativo/cards-list');
                    }
                    wp_reset_postdata();
                ?>
            </div>

            <?php if ($max_pages > 1) { ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine personale amministrativo">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base' => $pagination_base_personale,
                            'format' => '',
                            'current' => $personale_page,
                            'total' => $max_pages,
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

            <?php if (!empty($posts_cessati)) { ?>
                <div class="mt-4">
                    <button type="button" id="toggle-personale-cessato" class="btn btn-outline-primary d-flex align-items-center gap-2" aria-expanded="false" aria-controls="section-personale-cessato">
                        <span>Mostra persone non piu in carica</span>
                        <svg class="icon icon-sm">
                            <use href="#it-chevron-right"></use>
                        </svg>
                    </button>
                </div>

                <div id="section-personale-cessato" hidden class="row g-4 mt-3">
                    <?php
                    foreach ($posts_cessati_page as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/personale-amministrativo/cards-list');
                    }
                    wp_reset_postdata();
                    ?>

                    <?php if ($max_pages_cessati > 1) { ?>
                        <div class="row my-4 col-12">
                            <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine personale non in carica">
                                <?php
                                $pagination_links = paginate_links(array(
                                    'base' => $pagination_base_cessati,
                                    'format' => '',
                                    'current' => $personale_cessato_page,
                                    'total' => $max_pages_cessati,
                                    'type' => 'array',
                                    'show_all' => false,
                                    'end_size' => 3,
                                    'mid_size' => 1,
                                    'prev_next' => true,
                                    'prev_text' => __('Inizio'),
                                    'next_text' => __('Fine'),
                                    'add_fragment' => '#section-personale-cessato',
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
        </div>
    </form>
</div>

<script>
(function () {
    var btn = document.getElementById('toggle-personale-cessato');
    var section = document.getElementById('section-personale-cessato');

    if (!btn || !section || btn.getAttribute('data-toggle-ready') === 'true') return;

    btn.setAttribute('data-toggle-ready', 'true');

    btn.addEventListener('click', function (event) {
        event.preventDefault();

        var isOpen = !section.hasAttribute('hidden');
        var label = btn.querySelector('span');

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

    if (window.location.hash === '#section-personale-cessato') {
        section.removeAttribute('hidden');
        btn.setAttribute('aria-expanded', 'true');
        btn.classList.add('is-open');
        if (btn.querySelector('span')) {
            btn.querySelector('span').textContent = 'Nascondi persone non piu in carica';
        }
    }
}());
</script>
