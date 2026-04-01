<?php
global $the_query, $load_posts;


$count = 0;
$max_posts = isset($_GET['max_posts']) ? (int) $_GET['max_posts'] : 1000000;
$load_posts = 6;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;




if (!function_exists('dci_articolazione_normalize_list')) {
    function dci_articolazione_normalize_list($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        return [$value];
    }
}

if (!function_exists('dci_articolazione_is_list_of_ids')) {
    function dci_articolazione_is_list_of_ids($value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!(is_scalar($item) && $item !== '')) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('dci_articolazione_post_matches')) {
    function dci_articolazione_post_matches($post, $terms, $keywords)
    {
        $haystacks = [
            sanitize_title($post->post_title),
            sanitize_title($post->post_name),
        ];

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $haystacks[] = sanitize_title($term->slug);
                $haystacks[] = sanitize_title($term->name);
            }
        }

        foreach ($keywords as $keyword) {
            $needle = sanitize_title($keyword);
            foreach ($haystacks as $haystack) {
                if ($haystack !== '' && strpos($haystack, $needle) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('dci_articolazione_add_unique')) {
    function dci_articolazione_add_unique(&$collection, &$seen_ids, $post)
    {
        if (!isset($seen_ids[$post->ID])) {
            $collection[] = $post;
            $seen_ids[$post->ID] = true;
        }
    }
}

if (!function_exists('dci_articolazione_get_person_roles')) {
    function dci_articolazione_get_person_roles($person_id)
    {
        $roles = [];
        $incarichi = dci_articolazione_normalize_list(dci_get_meta('incarichi', '_dci_persona_pubblica_', $person_id));

        foreach ($incarichi as $incarico_id) {
            $incarico = get_post($incarico_id);
            if ($incarico instanceof WP_Post) {
                $roles[] = $incarico->post_title;
            }
        }

        return array_values(array_unique(array_filter($roles)));
    }
}

if (!function_exists('dci_articolazione_trim_text')) {
    function dci_articolazione_trim_text($text, $max_chars = 350)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $max_chars) {
                return $text;
            }

            return rtrim(mb_substr($text, 0, $max_chars)) . '...';
        }

        if (strlen($text) <= $max_chars) {
            return $text;
        }

        return rtrim(substr($text, 0, $max_chars)) . '...';
    }
}

if (!function_exists('dci_articolazione_get_sito_tematico_link')) {
    function dci_articolazione_get_sito_tematico_link($sito_tematico_id)
    {
        $prefix = '_dci_sito_tematico_';
        $custom_link = dci_get_meta('link', $prefix, $sito_tematico_id);
        $mostra_pagina = get_post_meta($sito_tematico_id, $prefix . 'mostra_pagina', true);

        if ((!empty($mostra_pagina) && $mostra_pagina) || empty($custom_link)) {
            return get_permalink($sito_tematico_id);
        }

        return $custom_link;
    }
}

if (!function_exists('dci_articolazione_render_sito_tematico_sidebar_card')) {
    function dci_articolazione_render_sito_tematico_sidebar_card($sito_tematico_id)
    {
        $sito_tematico = get_post($sito_tematico_id);
        if (!$sito_tematico instanceof WP_Post) {
            return;
        }

        $prefix = '_dci_sito_tematico_';
        $descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico_id);
        $immagine = dci_get_meta('immagine', $prefix, $sito_tematico_id);
        $colore = dci_get_meta('colore', $prefix, $sito_tematico_id);
        $link = dci_articolazione_get_sito_tematico_link($sito_tematico_id);
        ?>
        <li class="dci-at-theme-item">
            <a class="dci-at-theme-link text-decoration-none" href="<?php echo esc_url($link); ?>">
                <div class="dci-at-theme-head">
                    <?php if (!empty($immagine)) { ?>
                        <span class="dci-at-theme-avatar" aria-hidden="true">
                            <?php dci_get_img($immagine); ?>
                        </span>
                    <?php } ?>
                    <span class="dci-at-theme-title-wrap">
                        <span class="dci-at-theme-title"><?php echo esc_html($sito_tematico->post_title); ?></span>
                    </span>
                    <svg class="icon icon-md dci-at-theme-icon" aria-hidden="true" <?php echo !empty($colore) ? 'style="fill:' . esc_attr($colore) . ';"' : ''; ?>>
                        <use href="#it-external-link"></use>
                    </svg>
                </div>
                <?php if (!empty($descrizione)) { ?>
                    <span class="dci-at-theme-description"><?php echo esc_html($descrizione); ?></span>
                <?php } ?>
            </a>
        </li>
        <?php
    }
}

if (!function_exists('dci_articolazione_render_simple_card')) {
    function dci_articolazione_render_simple_card($post)
    {
        $prefix = '_dci_unita_organizzativa_';
        $description = dci_get_meta('descrizione_breve', $prefix, $post->ID);

        if (empty($description)) {
            $description = get_the_excerpt($post->ID);
        }
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr h-100">
                <div class="card no-after rounded h-100">
                    <div class="row g-2 g-md-0 flex-md-column h-100">
                        <div class="col-12 order-1 order-md-2 h-100">
                            <div class="card-body card-img-none rounded-top h-100 d-flex flex-column">
                                <span class="dci-at-card-icon mb-3" aria-hidden="true">
                                    <svg class="icon icon-primary icon-sm">
                                        <use href="#it-pa"></use>
                                    </svg>
                                </span>
                                <a class="text-decoration-none" href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                    <h3 class="h5 card-title mb-2"><?php echo esc_html($post->post_title); ?></h3>
                                </a>
                                <?php if (!empty($description)) { ?>
                                    <p class="card-text mb-0"><?php echo esc_html($description); ?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('dci_articolazione_render_office_card')) {
    function dci_articolazione_render_office_card($post)
    {
        $prefix = '_dci_unita_organizzativa_';
        $description = dci_get_meta('descrizione_breve', $prefix, $post->ID);
        $competenze = dci_get_meta('competenze', $prefix, $post->ID);
        $competenze_plain = trim(wp_strip_all_tags((string) $competenze));
        $competenze_excerpt = dci_articolazione_trim_text($competenze_plain, 350);
        $responsabili = dci_articolazione_normalize_list(dci_get_meta('responsabile', $prefix, $post->ID));
        $referente_id = dci_get_meta('assessore_riferimento', $prefix, $post->ID);
        $sede_principale = dci_get_meta('sede_principale', $prefix, $post->ID);
        $raw_punti_contatto = dci_get_meta('contatti', $prefix, $post->ID);
        $punti_contatto = dci_articolazione_is_list_of_ids($raw_punti_contatto)
            ? $raw_punti_contatto
            : [];
        $full_contacts = [];

        foreach ($punti_contatto as $contact_id) {
            $contact = dci_get_full_punto_contatto($contact_id);
            if (!empty($contact) && is_array($contact)) {
                $full_contacts[] = $contact;
            }
        }

        $contact_lines = [];

        if (!empty($sede_principale)) {
            $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $sede_principale);
            if (!empty($indirizzo)) {
                $contact_lines[] = [
                    'type' => 'indirizzo',
                    'value' => $indirizzo,
                ];
            }
        }

        foreach ($full_contacts as $contact) {
            foreach (['telefono', 'email', 'pec', 'url', 'indirizzo'] as $type) {
                if (empty($contact[$type]) || !is_array($contact[$type])) {
                    continue;
                }

                foreach ($contact[$type] as $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $contact_lines[] = [
                        'type' => $type,
                        'value' => $value,
                    ];
                }
            }
        }

        $serialized_contacts = [];
        foreach ($contact_lines as $contact_line) {
            $serialized_contacts[] = wp_json_encode($contact_line);
        }
        $serialized_contacts = array_values(array_unique($serialized_contacts));
        $contact_lines = array_map(
            static function ($serialized_contact) {
                return json_decode($serialized_contact, true);
            },
            $serialized_contacts
        );
        ?>
        <div class="dci-at-office-cell">
            <div class="card card-teaser shadow-sm rounded border border-light h-100 dci-at-office-card">
                <div class="card-body d-flex flex-column">
                    <div class="dci-at-office-head">
                        <a class="text-decoration-none" href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                            <h3 class="h4 card-title mb-2"><?php echo esc_html($post->post_title); ?></h3>
                        </a>
                        <?php if (!empty($description)) { ?>
                            <p class="card-text mb-0"><?php echo esc_html($description); ?></p>
                        <?php } ?>
                    </div>

                    <div class="dci-at-office-content">
                        <?php if ($competenze_excerpt !== '') { ?>
                            <div class="dci-at-detail-block">
                                <p class="dci-at-detail-label">Competenze</p>
                                <div class="dci-at-richtext"><p><?php echo esc_html($competenze_excerpt); ?></p></div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($responsabili)) { ?>
                            <div class="dci-at-detail-block">
                                <p class="dci-at-detail-label">Responsabile</p>
                                <?php foreach ($responsabili as $responsabile_id) {
                                    $responsabile = get_post($responsabile_id);
                                    if (!$responsabile instanceof WP_Post) {
                                        continue;
                                    }

                                    $roles = dci_articolazione_get_person_roles($responsabile_id);
                                    ?>
                                    <div class="dci-at-person">
                                        <p class="dci-at-person-name mb-1">
                                            <a class="text-decoration-none" href="<?php echo esc_url(get_permalink($responsabile_id)); ?>">
                                                <?php echo esc_html($responsabile->post_title); ?>
                                            </a>
                                        </p>
                                        <?php if (!empty($roles)) { ?>
                                            <p class="dci-at-person-role mb-0">Qualifica: <?php echo esc_html(implode(', ', $roles)); ?></p>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (!empty($contact_lines)) { ?>
                            <div class="dci-at-detail-block">
                                <p class="dci-at-detail-label">Contatti ufficio</p>
                                <div class="dci-at-contact-list">
                                    <?php foreach ($contact_lines as $line) {
                                        $type = $line['type'] ?? '';
                                        $value = $line['value'] ?? '';

                                        if (empty($value)) {
                                            continue;
                                        }

                                        $icon = '#it-link';
                                        if ($type === 'telefono') {
                                            $icon = '#it-telephone';
                                        } elseif ($type === 'email' || $type === 'pec') {
                                            $icon = '#it-mail';
                                        } elseif ($type === 'indirizzo') {
                                            $icon = '#it-pin';
                                        }
                                        ?>
                                        <div class="dci-at-contact-item">
                                            <svg class="icon icon-sm me-2" aria-hidden="true">
                                                <use href="<?php echo esc_attr($icon); ?>"></use>
                                            </svg>
                                            <?php if ($type === 'telefono') { ?>
                                                <a class="text-decoration-none" href="tel:<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></a>
                                            <?php } elseif ($type === 'email' || $type === 'pec') { ?>
                                                <a class="text-decoration-none" href="mailto:<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></a>
                                            <?php } elseif ($type === 'url') { ?>
                                                <a class="text-decoration-none" href="<?php echo esc_url($value); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($value); ?></a>
                                            <?php } else { ?>
                                                <span><?php echo esc_html($value); ?></span>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($referente_id)) {
                            $referente = get_post($referente_id);
                            if ($referente instanceof WP_Post) {
                                $roles = dci_articolazione_get_person_roles($referente_id);
                                ?>
                                <div class="dci-at-detail-block">
                                    <p class="dci-at-detail-label">Assessore di riferimento</p>
                                    <div class="dci-at-person">
                                        <p class="dci-at-person-name mb-1">
                                            <a class="text-decoration-none" href="<?php echo esc_url(get_permalink($referente_id)); ?>">
                                                <?php echo esc_html($referente->post_title); ?>
                                            </a>
                                        </p>
                                        <?php if (!empty($roles)) { ?>
                                            <p class="dci-at-person-role mb-0">Qualifica: <?php echo esc_html(implode(', ', $roles)); ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

$args = [
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'unita_organizzativa',
    'orderby' => 'post_title',
    'order' => 'ASC',
    'post_status' => 'publish',
];

$the_query = new WP_Query($args);
$posts = $the_query->posts;

$organi_indirizzo = [];
$organi_gestione = [];
$articolazioni = [];
$seen_indirizzo = [];
$seen_gestione = [];
$seen_articolazioni = [];
$uffici_senza_area = [];
$aree = [];
$uffici = [];

$aree = [];
$uffici = [];

foreach ($posts as $post) {
    $terms = wp_get_post_terms($post->ID, 'tipi_unita_organizzativa');

    if (empty($terms) || is_wp_error($terms)) {
        continue;
    }

    $term_slugs = array_map(function ($term) {
        return sanitize_title($term->slug);
    }, $terms);

    // ✅ ORGANI INDIRIZZO
    if (dci_articolazione_post_matches($post, $terms, ['consiglio comunale', 'consiglio-comunale', 'consiglio'])) {
        dci_articolazione_add_unique($organi_indirizzo, $seen_indirizzo, $post);
        continue;
    }

    // ✅ ORGANI GESTIONE
    if (dci_articolazione_post_matches($post, $terms, ['giunta comunale', 'giunta-comunale', 'giunta', 'sindaco'])) {
        dci_articolazione_add_unique($organi_gestione, $seen_gestione, $post);
        continue;
    }

    // ✅ AREA
    if (in_array('area', $term_slugs, true)) {
        $aree[$post->ID] = [
            'post' => $post,
            'uffici' => []
        ];
        continue;
    }

    // ✅ UFFICIO
    if (in_array('ufficio', $term_slugs, true)) {
        $uffici[] = $post;
    }
}

foreach ($uffici as $ufficio) {

    $raw = dci_get_meta('unita_organizzativa_genitore', '_dci_unita_organizzativa_', $ufficio->ID);

    $genitori = maybe_unserialize($raw);

    if (!is_array($genitori)) {
        $genitori = [$genitori];
    }

    $assegnato = false;

    foreach ($genitori as $area_id) {
        if (!empty($area_id) && isset($aree[$area_id])) {
            $aree[$area_id]['uffici'][] = $ufficio;
            $assegnato = true;
        }
    }

    // 👉 se NON assegnato a nessuna area
    if (!$assegnato) {
        $uffici_senza_area[] = $ufficio;
    }
}


$articolazioni_per_page = 10;
$articolazioni_page = isset($_GET['uffici_page']) ? max(1, (int) $_GET['uffici_page']) : 1;
$articolazioni_total = count($articolazioni);
$articolazioni_total_pages = max(1, (int) ceil($articolazioni_total / $articolazioni_per_page));

if ($articolazioni_page > $articolazioni_total_pages) {
    $articolazioni_page = $articolazioni_total_pages;
}

$articolazioni_offset = ($articolazioni_page - 1) * $articolazioni_per_page;
$articolazioni_paged = array_slice($articolazioni, $articolazioni_offset, $articolazioni_per_page);
?>

<style>

/* RESET SICUREZZA */
* {
    box-sizing: border-box;
}

html, body {
    overflow-x: hidden;
}

/* FIX BOOTSTRAP ROW (evita overflow laterale) */
.row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

/* WRAP */
.dci-at-wrap {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 16px;
}

/* GRID UFFICI */
.dci-at-office-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}

/* CARD */
.dci-at-office-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
}

/* HEAD */
.dci-at-office-head {
    padding: 1.25rem;
    border-bottom: 1px solid #e4eaf1;
}

/* CONTENUTO */
.dci-at-office-content {
    padding: 1.25rem;
    flex: 1;
}

/* TESTO - evita sforamenti */
.dci-at-office-card,
.dci-at-office-head,
.dci-at-office-content {
    word-break: break-word;
    overflow-wrap: break-word;
}

/* DETTAGLI */
.dci-at-detail-block {
    border-bottom: 1px solid #e9eef4;
    padding: 0.8rem 0;
}

.dci-at-detail-block:last-child {
    border-bottom: 0;
}

/* LABEL */
.dci-at-detail-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #5c6f82;
    margin-bottom: 0.4rem;
}

/* PERSONA */
.dci-at-person-name {
    font-weight: 600;
}

/* TITOLO AREA */
.dci-at-area-block h3 {
    margin-bottom: 1.2rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* RESPONSIVE */
@media (max-width: 1200px) {
    .dci-at-office-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .dci-at-wrap {
        padding: 0 12px;
    }

    .dci-at-office-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .dci-at-office-head,
    .dci-at-office-content {
        padding: 1rem;
    }
}

    .dci-at-section {
    margin-bottom: 4rem;
}

</style>

<div class="container">
    <div class="dci-at-wrap">
        <div class="row g-4 justify-content-center">

            <!-- CONTENUTO PRINCIPALE -->
            <div class="col-12 dci-at-main-content">
                
                <!-- ORGANI INDIRIZZO -->
                <section id="organi-indirizzo" class="dci-at-section">
                    <h2 class="title-large dci-at-section-title">Organi di indirizzo politico</h2>

                    <div class="row g-4">
                        <?php if (!empty($organi_indirizzo)) {
                            foreach ($organi_indirizzo as $post) {
                                dci_articolazione_render_simple_card($post);
                            }
                        } else { ?>
                            <div class="col-12">
                                <p>Nessun organo disponibile.</p>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- ORGANI GESTIONE -->
                <section id="organi-gestione" class="dci-at-section">
                    <h2 class="title-large dci-at-section-title">Organi di amministrazione e gestione</h2>

                    <div class="row g-4">
                        <?php if (!empty($organi_gestione)) {
                            foreach ($organi_gestione as $post) {
                                dci_articolazione_render_simple_card($post);
                            }
                        } else { ?>
                            <div class="col-12">
                                <p>Nessun organo disponibile.</p>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- UFFICI -->
<section id="articolazione-uffici" class="dci-at-section">


    <!-- AREE -->
    <?php if (!empty($aree)) { ?>

        <?php foreach ($aree as $area) { ?>
            
            <div class="dci-at-area-block mb-5">

                <!-- TITOLO AREA -->
                   <h3 class="h4 mb-4">
                    <?php 
                    $count = !empty($area['uffici']) ? count($area['uffici']) : 0;
                    echo mb_strtoupper(esc_html($area['post']->post_title)) . " (" . $count . ")";
                    ?>
                </h3>
                
                <!-- UFFICI DELL’AREA -->
                <?php if (!empty($area['uffici'])) { ?>
                    <div class="dci-at-office-grid">
                        <?php foreach ($area['uffici'] as $post) {
                            dci_articolazione_render_office_card($post);
                        } ?>
                    </div>
                <?php } ?>

            </div>

        <?php } ?>

    <?php } ?>

    <!-- UFFICI SENZA AREA (SEMPRE VISIBILI SE ESISTONO) -->
    <?php if (!empty($uffici_senza_area)) { ?>

        <div class="dci-at-area-block mb-5">

            <h3 class="h4 mb-4">Uffici senza area</h3>

            <div class="dci-at-office-grid">
                <?php foreach ($uffici_senza_area as $post) {
                    dci_articolazione_render_office_card($post);
                } ?>
            </div>

        </div>

    <?php } ?>

    <!-- FALLBACK -->
    <?php if (empty($aree) && empty($uffici_senza_area)) { ?>
        <p>Nessun ufficio disponibile.</p>
    <?php } ?>

</section>

            </div>

        </div>
    </div>
</div>

<?php
$dci_amm_sidebar_embedded = false;
$dci_amm_sidebar_sections = [];
wp_reset_postdata();
?>
