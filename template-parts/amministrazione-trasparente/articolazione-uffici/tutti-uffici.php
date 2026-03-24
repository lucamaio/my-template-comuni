<?php
global $the_query, $load_posts;
global $siti_tematici, $dci_amm_sidebar_embedded, $dci_amm_sidebar_sections;

$count = 0;
$max_posts = isset($_GET['max_posts']) ? (int) $_GET['max_posts'] : 1000000;
$load_posts = 6;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$siti_tematici = !empty(dci_get_option('siti_tematici', 'trasparenza')) ? dci_get_option('siti_tematici', 'trasparenza') : [];
$dci_amm_sidebar_embedded = true;
$dci_amm_sidebar_sections = [
    ['id' => 'organi-indirizzo', 'label' => 'Organi di indirizzo politico'],
    ['id' => 'organi-gestione', 'label' => 'Organi di amministrazione e gestione'],
    ['id' => 'articolazione-uffici', 'label' => 'Articolazione degli uffici'],
];

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

foreach ($posts as $post) {
    $terms = wp_get_post_terms($post->ID, 'tipi_unita_organizzativa');
    $term_slugs = [];

    if (!empty($terms) && !is_wp_error($terms)) {
        $term_slugs = array_map(
            static function ($term) {
                return sanitize_title($term->slug);
            },
            $terms
        );
    }

    if (
        in_array('ufficio', $term_slugs, true) ||
        in_array('area', $term_slugs, true)
    ) {
        dci_articolazione_add_unique($articolazioni, $seen_articolazioni, $post);
        continue;
    }

    if (dci_articolazione_post_matches($post, $terms, ['consiglio comunale', 'consiglio-comunale', 'consiglio'])) {
        dci_articolazione_add_unique($organi_indirizzo, $seen_indirizzo, $post);
        continue;
    }

    if (dci_articolazione_post_matches($post, $terms, ['giunta comunale', 'giunta-comunale', 'giunta', 'sindaco'])) {
        dci_articolazione_add_unique($organi_gestione, $seen_gestione, $post);
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
    .dci-at-wrap {
        width: 100%;
        max-width: none;
        padding: 0;
        background: transparent;
        box-shadow: none !important;
        border-radius: 0;
    }

    .dci-at-wrap .dci-at-section + .dci-at-section {
        margin-top: 3rem;
    }

    .dci-at-wrap .dci-at-section:last-child,
    .dci-at-wrap .dci-at-main-content > :last-child {
        margin-bottom: 0;
    }

    .dci-at-wrap .dci-at-main-content {
        min-width: 0;
    }

    .dci-at-wrap .dci-at-section-title {
        margin-bottom: 1.5rem;
    }

    .dci-at-wrap .dci-at-office-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .dci-at-wrap .dci-at-office-cell {
        min-width: 0;
    }

    .dci-at-wrap .card-body.card-img-none {
        padding: 1.5rem;
    }

    .dci-at-wrap .dci-at-card-icon {
        display: inline-flex;
        align-items: center;
    }

    .dci-at-wrap .dci-at-office-card {
        padding: 0;
        background: #fff;
        overflow: hidden;
    }

    .dci-at-wrap .dci-at-office-head {
        padding: 1.125rem 1.125rem .875rem;
        border-bottom: 1px solid #e4eaf1;
    }

    .dci-at-wrap .dci-at-office-content {
        display: grid;
        gap: 0;
        padding: 0 1.125rem 1.125rem;
    }

    .dci-at-wrap .dci-at-detail-block {
        padding: .875rem 0;
        background: transparent;
        border: 0;
        border-bottom: 1px solid #e9eef4;
        border-radius: 0;
    }

    .dci-at-wrap .dci-at-detail-label {
        margin-bottom: .35rem;
        font-size: .8125rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        color: #5c6f82;
    }

    .dci-at-wrap .dci-at-detail-block:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .dci-at-wrap .dci-at-richtext p:last-child,
    .dci-at-wrap .dci-at-contact-item:last-child,
    .dci-at-wrap .dci-at-person:last-child {
        margin-bottom: 0;
    }

    .dci-at-wrap .dci-at-richtext p,
    .dci-at-wrap .dci-at-contact-item,
    .dci-at-wrap .dci-at-person-role {
        font-size: .95rem;
        line-height: 1.5;
        color: #455a64;
    }

    .dci-at-wrap .dci-at-person + .dci-at-person,
    .dci-at-wrap .dci-at-contact-item + .dci-at-contact-item {
        margin-top: .5rem;
    }

    .dci-at-wrap .dci-at-person-name {
        font-weight: 700;
        color: #17324d;
    }

    .dci-at-wrap .dci-at-office-card .card-title {
        font-size: 1.18rem;
        line-height: 1.25;
    }

    .dci-at-wrap .dci-at-contact-list a,
    .dci-at-wrap .dci-at-person a {
        word-break: break-word;
    }

    .dci-at-wrap .dci-at-contact-item {
        display: flex;
        align-items: flex-start;
    }

    .dci-at-wrap .dci-at-contact-item .icon {
        flex: 0 0 auto;
        margin-top: .15rem;
        fill: var(--bs-primary, #0066cc);
    }

    .dci-at-wrap .dci-at-sidebar .link-list li a,
    .dci-at-wrap .dci-at-back-link {
        text-decoration: none;
    }

    .dci-at-wrap .dci-at-sidebar {
        min-width: 0;
    }

    .dci-at-wrap .dci-at-sidebar .link-list li a {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        width: 100%;
        line-height: 1.4;
    }

    .dci-at-wrap .dci-at-sidebar-box {
        background: #fff;
        border: 1px solid #e9eef4;
        border-radius: .5rem;
        padding: 1.25rem;
        box-shadow: 0 .125rem .25rem rgba(23, 50, 77, .08);
    }

    .dci-at-wrap .dci-at-sidebar-box .link-list {
        margin-bottom: 0;
    }

    .dci-at-wrap .dci-at-sidebar-box .link-list li:last-child {
        margin-bottom: 0 !important;
    }

    .dci-at-wrap .dci-at-sidebar-sticky > :last-child {
        margin-bottom: 0;
    }

    .dci-at-wrap .dci-at-sidebar-box + .dci-at-sidebar-box {
        margin-top: 1rem;
    }

    .dci-at-wrap .dci-at-back-link {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-weight: 600;
    }

    .dci-at-wrap .dci-at-theme-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .dci-at-wrap .dci-at-theme-item + .dci-at-theme-item {
        margin-top: .875rem;
        padding-top: .875rem;
        border-top: 1px solid #e9eef4;
    }

    .dci-at-wrap .dci-at-theme-link {
        display: block;
    }

    .dci-at-wrap .dci-at-theme-head {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
    }

    .dci-at-wrap .dci-at-theme-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 999px;
        overflow: hidden;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e9eef4;
    }

    .dci-at-wrap .dci-at-theme-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dci-at-wrap .dci-at-theme-title-wrap {
        min-width: 0;
        flex: 1 1 auto;
    }

    .dci-at-wrap .dci-at-theme-title {
        display: block;
        font-weight: 700;
        line-height: 1.35;
        color: #17324d;
    }

    .dci-at-wrap .dci-at-theme-icon {
        flex: 0 0 auto;
        margin-top: .1rem;
        fill: var(--bs-primary, #0066cc);
    }

    .dci-at-wrap .dci-at-theme-description {
        display: block;
        margin-top: .4rem;
        font-size: .9rem;
        line-height: 1.5;
        color: #5c6f82;
    }

    .dci-at-wrap .dci-at-pagination {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: 1.5rem;
        justify-content: center;
    }

    .dci-at-wrap .dci-at-pagination-link,
    .dci-at-wrap .dci-at-pagination-current {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 .875rem;
        border-radius: .5rem;
        border: 1px solid #dbe5ee;
        background: #fff;
        color: #17324d;
        font-weight: 600;
        text-decoration: none;
    }

    .dci-at-wrap .dci-at-pagination-current {
        border-color: var(--bs-primary, #0066cc);
        color: var(--bs-primary, #0066cc);
    }

    @media (min-width: 992px) {
        .dci-at-wrap .dci-at-sidebar-sticky {
            position: sticky;
            top: 2rem;
        }
    }

    .dci-at-wrap .dci-at-office-head .card-text {
        font-size: .96rem;
        line-height: 1.55;
        color: #455a64;
    }

    .dci-at-wrap .dci-at-contact-item + .dci-at-contact-item {
        margin-top: .35rem;
    }

    .dci-at-wrap .row.g-4 {
        --bs-gutter-y: 1.25rem;
    }

    @media (max-width: 991.98px) {
        .dci-at-wrap .dci-at-office-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dci-at-wrap .dci-at-sidebar {
            margin-top: 1.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .dci-at-wrap .dci-at-office-grid {
            grid-template-columns: 1fr;
        }

        .dci-at-wrap .dci-at-office-head,
        .dci-at-wrap .dci-at-office-content {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<div class="dci-at-wrap">
    <div class="row g-4">
        <div class="col-12 col-lg-9 dci-at-main-content">
            <section id="organi-indirizzo" class="dci-at-section">
                <h2 class="title-large dci-at-section-title">Organi di indirizzo politico</h2>
                <div class="row g-4">
                    <?php if (!empty($organi_indirizzo)) {
                        foreach ($organi_indirizzo as $post) {
                            dci_articolazione_render_simple_card($post);
                        }
                    } else { ?>
                        <div class="col-12">
                            <p class="mb-0">Nessun organo di indirizzo politico disponibile.</p>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <section id="organi-gestione" class="dci-at-section">
                <h2 class="title-large dci-at-section-title">Organi di amministrazione e gestione</h2>
                <div class="row g-4">
                    <?php if (!empty($organi_gestione)) {
                        foreach ($organi_gestione as $post) {
                            dci_articolazione_render_simple_card($post);
                        }
                    } else { ?>
                        <div class="col-12">
                            <p class="mb-0">Nessun organo di amministrazione e gestione disponibile.</p>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <section id="articolazione-uffici" class="dci-at-section">
                <h2 class="title-large dci-at-section-title">Articolazione degli uffici</h2>
                <div class="dci-at-office-grid">
                    <?php if (!empty($articolazioni_paged)) {
                        foreach ($articolazioni_paged as $post) {
                            dci_articolazione_render_office_card($post);
                        }
                    } else { ?>
                        <div class="dci-at-office-cell">
                            <p class="mb-0">Nessun ufficio disponibile.</p>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($articolazioni_total_pages > 1) { ?>
                    <nav class="dci-at-pagination" aria-label="Paginazione uffici">
                        <?php for ($page = 1; $page <= $articolazioni_total_pages; $page++) {
                            $page_url = add_query_arg('uffici_page', $page);
                            $page_url .= '#articolazione-uffici';
                            if ($page === $articolazioni_page) { ?>
                                <span class="dci-at-pagination-current"><?php echo (int) $page; ?></span>
                            <?php } else { ?>
                                <a class="dci-at-pagination-link" href="<?php echo esc_url($page_url); ?>"><?php echo (int) $page; ?></a>
                            <?php }
                        } ?>
                    </nav>
                <?php } ?>
            </section>
        </div>

        <div class="col-12 col-lg-3 dci-at-sidebar">
            <?php get_template_part('template-parts/amministrazione-trasparente/side-bar'); ?>
        </div>
    </div>
</div>

<?php
$dci_amm_sidebar_embedded = false;
$dci_amm_sidebar_sections = [];
wp_reset_postdata();
?>
