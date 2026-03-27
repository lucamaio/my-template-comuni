<?php
global $the_query, $load_posts, $load_card_type, $tax_query, $additional_filter, $filter_ids;

$query = $_GET['search'] ?? null;

switch ($post->post_name){
    case 'politici': $tipo_incarico = 'politico'; $descrizione = 'del personale'; break;
    case 'personale-amministrativo': $tipo_incarico = 'amministrativo'; $descrizione = 'del personale'; break;
    case 'personale-sanitario': $tipo_incarico = 'sanitario'; $descrizione = 'del personale'; break;
    case 'personale-socio-assistenziale': $tipo_incarico = 'socio-assistenziale'; $descrizione = 'del personale'; break;
    case 'altro': $tipo_incarico = 'altro'; $descrizione = 'del personale'; break;
}

$tax_query = array(
    array (
        'taxonomy' => 'tipi_incarico',
        'field' => 'slug',
        'terms' => $tipo_incarico
    )
);

$args_incarichi = array(
    'post_type' => 'incarico',
    'tax_query' => $tax_query,
    'posts_per_page' => -1
);

$incarichi_posts = get_posts($args_incarichi);
$persone_ids = array();

foreach($incarichi_posts as $incarico) {
    $persone = get_post_meta($incarico->ID, '_dci_incarico_persona');
    foreach($persone as $persona) {
        $persone_ids[] = $persona;
    }
}

$filter_ids = array_unique($persone_ids);

$search_value = $_GET['search'] ?? null;

$args = array(
    's' => $search_value,
    'posts_per_page' => -1,
    'post_type' => 'persona_pubblica',
    'post_status' => 'publish',
    'orderby' => 'post_title',
    'order' => 'ASC',
    'post__in' => empty($persone_ids) ? [0] : $filter_ids,
);

$the_query = new WP_Query($args);
$persone = $the_query->posts;

/* =========================
   SPLIT ATTUALI / CESSATI
========================= */
$persone_attuali = [];
$persone_cessate = [];
$today_ts = current_time('timestamp');

foreach ($persone as $persona) {
    $person_id = $persona->ID;
    $is_current = true;

    // Data fine persona
    $data_conclusione = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $person_id);

    if (!empty($data_conclusione)) {
        $ts = is_numeric($data_conclusione) ? intval($data_conclusione) : strtotime($data_conclusione);
        if ($ts && $ts < $today_ts) {
            $is_current = false;
        }
    }

    // Controllo incarichi
    $incarichi_persona = dci_get_meta('incarichi', '_dci_persona_pubblica_', $person_id);

    if ($is_current && is_array($incarichi_persona)) {
        $found_valid = false;

        foreach ($incarichi_persona as $incarico_id) {
            $data_fine = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $incarico_id);

            if (empty($data_fine)) {
                $found_valid = true;
                break;
            }

            $data_fine_ts = is_numeric($data_fine) ? intval($data_fine) : strtotime($data_fine);

            if (!$data_fine_ts || $data_fine_ts >= $today_ts) {
                $found_valid = true;
                break;
            }
        }

        $is_current = $found_valid;
    }

    if ($is_current) {
        $persone_attuali[] = $persona;
    } else {
        $persone_cessate[] = $persona;
    }
}
?>

<div class="bg-grey-card py-3">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>

        <div class="container">
            <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">
                Elenco <?= $descrizione ?>
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
                            value="<?php echo $query; ?>"
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
                    <strong><?php echo $the_query->found_posts; ?></strong> risultati in ordine alfabetico
                </p>
            </div>

            <!-- PERSONE ATTUALI -->
            <div class="row g-2" id="load-more">
                <?php
                foreach ($persone_attuali as $post) {
                    setup_postdata($post);
                    get_template_part('template-parts/politici/cards-list');
                }
                wp_reset_postdata();
                ?>
            </div>

            <!-- TOGGLE CESSATI -->
            <?php if (!empty($persone_cessate)) { ?>
                <div class="mt-4">
                   <button type="button" id="toggle-cessati" class="btn btn-outline-primary d-flex align-items-center gap-2">
                        <span>Mostra persone non più in carica</span>
                        <svg class="icon icon-sm">
                            <use href="#it-chevron-right"></use>
                        </svg>
                    </button>
                </div>

                <div id="section-cessati" style="display:none;" class="row g-2 mt-3">
                    <?php
                    foreach ($persone_cessate as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/politici/cards-list');
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            <?php } ?>

            <?php
            $load_card_type = 'persona_pubblica';
            get_template_part("template-parts/search/more-results");
            ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggle-cessati');
    const section = document.getElementById('section-cessati');

    if (!btn || !section) return;

    btn.addEventListener('click', function (e) {
    e.preventDefault(); // sicurezza extra

    const isOpen = section.style.display === 'block';

    section.style.display = isOpen ? 'none' : 'block';
    btn.classList.toggle('is-open', !isOpen);

    const label = btn.querySelector('span');
    if (label) {
        label.textContent = isOpen
            ? 'Mostra persone non più in carica'
            : 'Nascondi persone non più in carica';
    }
});
});
</script>