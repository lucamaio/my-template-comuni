<?php
global $siti_tematici, $dci_amm_sidebar_embedded, $dci_amm_sidebar_sections, $dci_amm_sidebar_column_classes;

if (!function_exists('dci_format_trasparenza_section_title')) {
    function dci_format_trasparenza_section_title($title)
    {
        $title = (string) $title;
        if (!preg_match('/\p{Lu}{7,}/u', $title)) {
            return $title;
        }
        $lowercase_title = mb_strtolower($title, 'UTF-8');
        return mb_strtoupper(mb_substr($lowercase_title, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($lowercase_title, 1, null, 'UTF-8');
    }
}

if (!function_exists('dci_amm_sidebar_term_is_visible')) {
    function dci_amm_sidebar_term_is_visible($term)
    {
        if (!$term instanceof WP_Term) {
            return false;
        }

        $visible = get_term_meta($term->term_id, 'visualizza_elemento', true);
        return (string) $visible === '1';
    }
}

if (!function_exists('dci_amm_sidebar_get_term_link_data')) {
    function dci_amm_sidebar_get_term_link_data($term)
    {
        if (!$term instanceof WP_Term) {
            return [
                'url' => '#',
                'target' => '',
                'is_external' => false,
            ];
        }

        $term_url = get_term_meta($term->term_id, 'term_url', true);
        $open_new_window = get_term_meta($term->term_id, 'open_new_window', true);

        if (!empty($term_url)) {
            return [
                'url' => $term_url,
                'target' => $open_new_window ? ' target="_blank" rel="noopener noreferrer"' : '',
                'is_external' => true,
            ];
        }

        return [
            'url' => get_term_link($term),
            'target' => '',
            'is_external' => false,
        ];
    }
}

if (!function_exists('dci_amm_sidebar_get_term_children')) {
    function dci_amm_sidebar_get_term_children($parent_id, $taxonomy = 'tipi_cat_amm_trasp')
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'parent' => $parent_id,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        $terms = array_filter($terms, 'dci_amm_sidebar_term_is_visible');

        usort($terms, static function ($a, $b) {
            $ordinamento_a = (int) get_term_meta($a->term_id, 'ordinamento', true);
            $ordinamento_b = (int) get_term_meta($b->term_id, 'ordinamento', true);

            if ($ordinamento_a === $ordinamento_b) {
                return strcmp($a->name, $b->name);
            }

            return $ordinamento_a <=> $ordinamento_b;
        });

        return $terms;
    }
}

if (!function_exists('dci_amm_sidebar_get_root_term')) {
    function dci_amm_sidebar_get_root_term($term)
    {
        if (!$term instanceof WP_Term) {
            return null;
        }

        if ((int) $term->parent === 0) {
            return $term;
        }

        $ancestors = get_ancestors($term->term_id, $term->taxonomy, 'taxonomy');
        if (empty($ancestors)) {
            return $term;
        }

        $root_id = end($ancestors);
        $root = get_term($root_id, $term->taxonomy);

        return ($root instanceof WP_Term && !is_wp_error($root)) ? $root : $term;
    }
}

if (!function_exists('dci_amm_sidebar_term_is_active')) {
    function dci_amm_sidebar_term_is_active($term, $current_term)
    {
        if (!$term instanceof WP_Term || !$current_term instanceof WP_Term) {
            return false;
        }

        if ((int) $term->term_id === (int) $current_term->term_id) {
            return true;
        }

        $ancestors = get_ancestors($current_term->term_id, $current_term->taxonomy);
        return in_array((int) $term->term_id, array_map('intval', $ancestors), true);
    }
}

if (!function_exists('dci_amm_sidebar_get_sito_tematico_link')) {
    function dci_amm_sidebar_get_sito_tematico_link($sito_tematico_id)
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

if (!function_exists('dci_amm_sidebar_render_theme_item')) {
    function dci_amm_sidebar_render_theme_item($sito_tematico_id)
    {
        $sito_tematico = get_post($sito_tematico_id);
        if (!$sito_tematico instanceof WP_Post) {
            return;
        }

        $prefix = '_dci_sito_tematico_';
        $descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico_id);
        $immagine = dci_get_meta('immagine', $prefix, $sito_tematico_id);
        $immagine_id = 0;

        if (is_numeric($immagine)) {
            $immagine_id = (int) $immagine;
        } elseif (is_string($immagine) && !empty($immagine)) {
            $immagine_id = attachment_url_to_postid($immagine);
        }
        ?>
        <li class="dci-amm-sidebar__theme-item">
            <a class="dci-amm-sidebar__theme-link text-decoration-none t-primary" href="<?php echo esc_url(dci_amm_sidebar_get_sito_tematico_link($sito_tematico_id)); ?>">
                <div class="dci-amm-sidebar__theme-head">
                    <?php if (!empty($immagine_id)) { ?>
                        <span class="dci-amm-sidebar__theme-avatar" aria-hidden="true">
                            <?php echo wp_get_attachment_image($immagine_id, 'thumbnail', false, ['class' => 'img-fluid']); ?>
                        </span>
                    <?php } elseif (!empty($immagine) && filter_var($immagine, FILTER_VALIDATE_URL)) { ?>
                        <span class="dci-amm-sidebar__theme-avatar" aria-hidden="true">
                            <img src="<?php echo esc_url($immagine); ?>" alt="" class="img-fluid" />
                        </span>
                    <?php } ?>
                    <span class="dci-amm-sidebar__theme-copy">
                        <span class="dci-amm-sidebar__theme-title"><?php echo esc_html($sito_tematico->post_title); ?></span>
                        <?php if (!empty($descrizione)) { ?>
                            <span class="dci-amm-sidebar__theme-description"><?php echo esc_html($descrizione); ?></span>
                        <?php } ?>
                    </span>
                    <svg class="icon icon-md dci-amm-sidebar__theme-icon" aria-hidden="true">
                        <use href="#it-external-link"></use>
                    </svg>
                </div>
            </a>
        </li>
        <?php
    }
}

if (!function_exists('dci_amm_sidebar_render_term_branch')) {
    function dci_amm_sidebar_render_term_branch($parent_term, $current_term, $level = 0, $max_level = 20)
    {
        if (!$parent_term instanceof WP_Term || $level > $max_level) {
            return;
        }

        $children = dci_amm_sidebar_get_term_children($parent_term->term_id, $parent_term->taxonomy);
        if (empty($children)) {
            return;
        }
        ?>
        <ul class="dci-amm-sidebar__term-list dci-amm-sidebar__term-list--level-<?php echo (int) $level; ?>">
            <?php foreach ($children as $child) {
                // Ogni voce appartiene esclusivamente al ramo della sezione corrente.
                if ((int) $child->parent !== (int) $parent_term->term_id || $child->taxonomy !== $parent_term->taxonomy) {
                    continue;
                }
                $child_display_name = dci_format_trasparenza_section_title($child->name);
                $is_active = dci_amm_sidebar_term_is_active($child, $current_term);
                $is_current = $current_term instanceof WP_Term && (int) $child->term_id === (int) $current_term->term_id;
                $panel_id = wp_unique_id('trasparenza-sottovoci-');
                $grandchildren = ($level < $max_level) ? dci_amm_sidebar_get_term_children($child->term_id, $child->taxonomy) : [];
                $has_children = !empty($grandchildren);
                $is_open = $is_active;
                $link_data = dci_amm_sidebar_get_term_link_data($child);
                ?>
                <li class="dci-amm-sidebar__term-item<?php echo $is_active ? ' is-active' : ''; ?><?php echo $is_current ? ' is-current' : ''; ?><?php echo $is_open ? ' is-open' : ''; ?>">
                    <div class="dci-amm-sidebar__term-row">
                        <a class="dci-amm-sidebar__term-link text-decoration-none t-primary" href="<?php echo esc_url($link_data['url']); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?><?php echo $link_data['target']; ?>>
                            <span class="dci-amm-sidebar__term-copy"><span class="dci-amm-sidebar__term-label"><?php echo esc_html($child_display_name); ?></span></span>
                            <?php if (!empty($link_data['is_external'])) { ?>
                                <svg class="icon icon-xs dci-amm-sidebar__external-icon" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                            <?php } ?>
                        </a>
                        <?php if ($has_children) { ?>
                            <button
                                type="button"
                                class="dci-amm-sidebar__toggle t-primary"
                                aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                                aria-label="<?php echo esc_attr(sprintf('Espandi o comprimi le sottovoci di %s', $child_display_name)); ?>">
                                <svg class="icon icon-sm icon-primary" aria-hidden="true">
                                    <use href="#it-expand"></use>
                                </svg>
                            </button>
                        <?php } ?>
                    </div>
                    <?php if ($has_children && $level < $max_level) { ?>
                        <div id="<?php echo esc_attr($panel_id); ?>" class="dci-amm-sidebar__children"<?php echo $is_open ? '' : ' hidden'; ?>>
                            <?php dci_amm_sidebar_render_term_branch($child, $current_term, $level + 1, $max_level); ?>
                        </div>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <?php
    }
}

// Le pagine dedicate possono indicare la sezione senza alterare la query WP.
$current_term = $args['section_term'] ?? get_queried_object();
$sidebar_current_attribute = isset($args['section_term']) ? 'location' : 'page';
$sidebar_theme_sites = $args['siti_tematici'] ?? $siti_tematici;
$current_term = ($current_term instanceof WP_Term && isset($current_term->taxonomy) && $current_term->taxonomy === 'tipi_cat_amm_trasp')
    ? $current_term
    : null;

$root_term = dci_amm_sidebar_get_root_term($current_term);
$root_term = dci_amm_sidebar_term_is_visible($root_term) ? $root_term : null;
$embedded = !empty($dci_amm_sidebar_embedded);
$sidebar_column_classes = trim((string) ($args['column_classes'] ?? $dci_amm_sidebar_column_classes ?? ''));
$sidebar_sections = is_array($dci_amm_sidebar_sections) ? array_values(array_filter($dci_amm_sidebar_sections)) : [];
?>

<style>
    .dci-amm-sidebar {
        min-width: 0;
    }

    .dci-amm-sidebar__sticky {
        display: grid;
        gap: 1rem;
        margin-top: 0;
    }

    .dci-amm-sidebar__box {
        background: #ffffff;
        border: 1px solid #dfe3e7;
        border-radius: .375rem;
        padding: 1.25rem;
        box-shadow: 0 .125rem .25rem rgba(23, 50, 77, .08);
    }

    .dci-amm-sidebar__title {
        margin-bottom: 1rem;
    }

    .dci-amm-sidebar__term-root {
        margin-bottom: .75rem;
        font-weight: 700;
    }

    .dci-amm-sidebar__term-root a,
    .dci-amm-sidebar__term-link,
    .dci-amm-sidebar__section-link,
    .dci-amm-sidebar__back-link {
        text-decoration: none;
    }

    .dci-amm-sidebar__term-list,
    .dci-amm-sidebar__theme-list,
    .dci-amm-sidebar__section-list {
        list-style: none;
        margin: 0;
        padding-left: 0;
    }

    .dci-amm-sidebar__nav-head {
        display: flex;
        align-items: flex-start;
        gap: .25rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #dbd5d5;
    }
    .dci-amm-sidebar__nav-head > .icon {
        flex: 0 0 auto;
        padding: .5rem;
        width: 2.5rem;
        height: 2.5rem;
        background: #ffff;
        border-radius: .25rem;
    }
    .dci-amm-sidebar__nav {
        --dci-amm-sidebar-accent: var(--tema-primary, var(--bs-primary, #193e66));
    }
    .dci-amm-sidebar__nav .dci-amm-sidebar__title {
        color: var(--dci-amm-sidebar-accent) !important;
    }
    .dci-amm-sidebar__nav .dci-amm-sidebar__nav-head > .icon,
    .dci-amm-sidebar__nav .dci-amm-sidebar__group-icon .icon,
    .dci-amm-sidebar__nav .dci-amm-sidebar__external-icon,
    .dci-amm-sidebar__nav .dci-amm-sidebar__toggle .icon {
        fill: var(--dci-amm-sidebar-accent) !important;
    }
    .dci-amm-sidebar__back-link .icon,
    .dci-amm-sidebar__links .dci-amm-sidebar__theme-icon {
        fill: currentColor !important;
    }
    .dci-amm-sidebar__nav .dci-amm-sidebar__term-root > a,
    .dci-amm-sidebar__nav .dci-amm-sidebar__term-link,
    .dci-amm-sidebar__nav .dci-amm-sidebar__toggle {
        color: var(--dci-amm-sidebar-accent) !important;
    }
    .dci-amm-sidebar__back-link {
        color: var(--dci-amm-sidebar-accent);
    }
    .dci-amm-sidebar__nav .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row::after {
        background: var(--dci-amm-sidebar-accent);
    }
    .dci-amm-sidebar__nav .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row > a,
    .dci-amm-sidebar__nav .dci-amm-sidebar__term-root > a[aria-current] {
        border-left-color: var(--dci-amm-sidebar-accent);
    }
    @supports (background: color-mix(in srgb, red, white)) {
        .dci-amm-sidebar__nav .dci-amm-sidebar__nav-head > .icon,
        .dci-amm-sidebar__nav .dci-amm-sidebar__group-icon {
            background: color-mix(in srgb, var(--dci-amm-sidebar-accent) 11%, white);
        }
        .dci-amm-sidebar__nav .dci-amm-sidebar__term-row:hover,
        .dci-amm-sidebar__nav .dci-amm-sidebar__term-root > a:hover,
        .dci-amm-sidebar__nav .dci-amm-sidebar__toggle:hover {
            background: color-mix(in srgb, var(--dci-amm-sidebar-accent) 9%, white);
        }
        .dci-amm-sidebar__nav .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row,
        .dci-amm-sidebar__nav .dci-amm-sidebar__term-root > a[aria-current] {
            background: color-mix(in srgb, var(--dci-amm-sidebar-accent) 13%, white);
        }
    }
    .dci-amm-sidebar__nav-head .dci-amm-sidebar__title { margin: 0 0 .25rem; }
    .dci-amm-sidebar__intro { margin: 0; color: #536270; font-size: .875rem; line-height: 1.5; }
    .dci-amm-sidebar__term-root { margin: 0 0 .75rem; }
    .dci-amm-sidebar__term-root > a { width: 100%; padding: .65rem .75rem; border-left: 3px solid transparent; border-radius: .375rem; }
    .dci-amm-sidebar__section-entries {
        margin-top: 1.5rem;
        padding: .75rem .5rem;
        border: 1px solid #cbd3db;
        border-radius: .375rem;
        background: #fff;
    }
    .dci-amm-sidebar__section-entries > .dci-amm-sidebar__term-root {
        position: relative;
        width: fit-content;
        max-width: calc(100% - 1rem);
        margin: -2rem .5rem .75rem;
        background: #fff;
    }
    .dci-amm-sidebar__section-entries > .dci-amm-sidebar__term-root > a {
        padding: .35rem .5rem;
        overflow-wrap: anywhere;
    }
    .dci-amm-sidebar__term-list {
        margin-left: 1rem;
        padding-left: .85rem;
        border-left: 1px solid #cbd3db;
    }
    .dci-amm-sidebar__children > .dci-amm-sidebar__term-list { margin-top: .35rem; }
    .dci-amm-sidebar__term-item + .dci-amm-sidebar__term-item,
    .dci-amm-sidebar__section-item + .dci-amm-sidebar__section-item { margin-top: .35rem; }
    .dci-amm-sidebar__term-row { position: relative; display: flex; align-items: stretch; border-radius: .375rem; }
    .dci-amm-sidebar__term-row::before {
        content: ''; position: absolute; left: -.85rem; top: 1.3rem;
        width: .85rem; border-top: 1px solid #cbd3db;
    }
    .dci-amm-sidebar__term-row::after {
        content: ''; position: absolute; left: calc(-.85rem - 4px); top: calc(1.3rem - 3px);
        width: 7px; height: 7px; border-radius: 50%; background: #9cabb9;
        box-shadow: 0 0 0 2px #f6f7f8; pointer-events: none;
    }
    .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row::after { background: #193e66; }
    .dci-amm-sidebar__term-list--level-0 { margin-left: 1rem; padding-left: .85rem; border-left: 0; }
    .dci-amm-sidebar__term-list--level-0 > .dci-amm-sidebar__term-item + .dci-amm-sidebar__term-item { margin-top: .875rem; }
    .dci-amm-sidebar__term-list--level-0 > .dci-amm-sidebar__term-item > .dci-amm-sidebar__children > .dci-amm-sidebar__term-list { margin-left: 1rem; }
    .dci-amm-sidebar__term-list--level-0 > .dci-amm-sidebar__term-item { position: relative; padding-bottom: .35rem; }
    .dci-amm-sidebar__term-list--level-0 > .dci-amm-sidebar__term-item::before {
        content: ''; position: absolute; left: -.85rem; top: .25rem; bottom: .35rem;
        border-left: 1px solid #cbd3db; pointer-events: none;
    }
    .dci-amm-sidebar__group-icon {
        display: inline-flex; align-items: center; justify-content: center;
        flex: 0 0 2rem; width: 2rem; height: 2rem; margin-right: .35rem;
        border-radius: 50%; background: #e7edf3;
    }
    .dci-amm-sidebar__group-icon .icon { width: 1.15rem; height: 1.15rem; fill: currentColor; }
    .dci-amm-sidebar__term-link,
    .dci-amm-sidebar__section-link { display: flex; align-items: center; gap: .4rem; width: 100%; line-height: 1.45; }
    .dci-amm-sidebar__term-link { min-width: 0; padding: .6rem .5rem; border-left: 3px solid transparent; border-radius: .375rem; }
    .dci-amm-sidebar__term-copy { display: block; min-width: 0; overflow-wrap: anywhere; }
    .dci-amm-sidebar__term-label { display: block; font-size: .95rem; font-weight: 400; }
    .dci-amm-sidebar__term-list--level-0 > .dci-amm-sidebar__term-item > .dci-amm-sidebar__term-row .dci-amm-sidebar__term-label,
    .dci-amm-sidebar__term-item.is-active > .dci-amm-sidebar__term-row .dci-amm-sidebar__term-label { font-weight: 600; }
    .dci-amm-sidebar__term-root a { font-weight: 700; }
    .dci-amm-sidebar__term-row:hover, .dci-amm-sidebar__term-root > a:hover { background: #ffffff; }
    .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row,
    .dci-amm-sidebar__term-root > a[aria-current] { background: #e7eef5; }
    .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row > a,
    .dci-amm-sidebar__term-root > a[aria-current] { border-left-color: currentColor; font-weight: 700; }
    .dci-amm-sidebar__term-item.is-current > .dci-amm-sidebar__term-row .dci-amm-sidebar__term-label { font-weight: 700; }
    .dci-amm-sidebar__external-icon { flex: 0 0 auto; fill: currentColor; }
    .dci-amm-sidebar__toggle {
        flex: 0 0 2.5rem; min-height: 2.75rem; border: 0; border-radius: .2rem;
        background: transparent; padding: .35rem; display: inline-flex;
        align-items: center; justify-content: center; cursor: pointer;
    }
    .dci-amm-sidebar__toggle:hover { background: #ffffff; }
    .dci-amm-sidebar a:focus-visible, .dci-amm-sidebar button:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }
    .dci-amm-sidebar__toggle .icon { transition: transform .2s ease; }
    .dci-amm-sidebar__term-item.is-open > .dci-amm-sidebar__term-row .dci-amm-sidebar__toggle .icon { transform: rotate(180deg); }
    .dci-amm-sidebar__children[hidden] { display: none; }
    @media (prefers-reduced-motion: reduce) { .dci-amm-sidebar__toggle .icon { transition: none; } }
    .dci-amm-sidebar__theme-item + .dci-amm-sidebar__theme-item {
        margin-top: .875rem;
        padding-top: .875rem;
        border-top: 1px solid #e9eef4;
    }

    .dci-amm-sidebar__theme-link {
        display: block;
        color: var(--dci-amm-sidebar-accent, var(--tema-primary, var(--bs-primary, #193e66)));
    }

    .dci-amm-sidebar__theme-head {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
    }

    .dci-amm-sidebar__theme-avatar {
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

    .dci-amm-sidebar__theme-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dci-amm-sidebar__theme-copy {
        min-width: 0;
        flex: 1 1 auto;
    }

    .dci-amm-sidebar__theme-title {
        display: block;
        font-weight: 700;
        line-height: 1.35;
        color: inherit;
    }

    .dci-amm-sidebar__theme-description {
        display: block;
        margin-top: .35rem;
        font-size: .9rem;
        line-height: 1.5;
        color: #5c6f82;
    }

    .dci-amm-sidebar__theme-icon {
        flex: 0 0 auto;
        margin-top: .15rem;
        fill: currentColor;
    }
    .dci-amm-sidebar__links {
        background: #fff;
        border: 1px solid #e9eef4;
        border-radius: .5rem;
    }
    .dci-amm-sidebar__links .dci-amm-sidebar__theme-link,
    .dci-amm-sidebar__back-link {
        transition: color .2s ease, background-color .2s ease, border-color .2s ease;
    }

    .dci-amm-sidebar__back-link {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-weight: 600;
        width: 100%;
        padding: 1.25rem;
        color: inherit;
        user-select: none;
        -webkit-user-select: none;
    }

    .dci-amm-sidebar__back-link:hover,
    .dci-amm-sidebar__back-link:focus,
    .dci-amm-sidebar__back-link:active,
    .dci-amm-sidebar__back-link:visited {
        text-decoration: none;
    }

    .dci-amm-sidebar__back-box {
        padding: 0;
        overflow: hidden;
    }

    .dci-amm-sidebar__back-box:hover {
        background: #ffffff;
    }
    @supports (background: color-mix(in srgb, red, white)) {
        .dci-amm-sidebar__back-box:hover {
            background: color-mix(in srgb, var(--dci-amm-sidebar-accent) 9%, white);
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .dci-amm-sidebar__links .dci-amm-sidebar__theme-link,
        .dci-amm-sidebar__back-link { transition: none; }
    }

    @media (min-width: 992px) {
        .dci-amm-sidebar__sticky {
            position: sticky;
            top: 4.5rem;
        }
    }
</style>

<?php if (!$embedded) { ?>
    <div class="col-12 col-lg-4 dci-amm-sidebar<?php echo $sidebar_column_classes !== '' ? ' ' . esc_attr($sidebar_column_classes) : ''; ?>">
<?php } ?>

    <div class="dci-amm-sidebar<?php echo $embedded ? ' dci-amm-sidebar--embedded' : ''; ?>">
        <div class="dci-amm-sidebar__sticky">
            <?php if (!empty($sidebar_sections)) { ?>
                <div class="dci-amm-sidebar__box">
                    <h2 class="title-medium-semi-bold dci-amm-sidebar__title">Naviga la sezione</h2>
                    <ul class="dci-amm-sidebar__section-list">
                        <?php foreach ($sidebar_sections as $section) {
                            $label = $section['label'] ?? '';
                            $id = $section['id'] ?? '';
                            if ($label === '' || $id === '') {
                                continue;
                            }
                            ?>
                            <li class="dci-amm-sidebar__section-item">
                                <a class="dci-amm-sidebar__section-link text-decoration-none t-primary" href="#<?php echo esc_attr($id); ?>">
                                    <span><?php echo esc_html($label); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($root_term instanceof WP_Term) { ?>
                <?php $root_display_name = dci_format_trasparenza_section_title($root_term->name); ?>
                <nav class="dci-amm-sidebar__box dci-amm-sidebar__nav" aria-label="Sezioni dell'Amministrazione trasparente">
                    <div class="dci-amm-sidebar__nav-head">
                        <svg class="icon icon-primary" aria-hidden="true"><use href="#it-list"></use></svg>
                        <div>
                            <h2 class="title-medium-semi-bold dci-amm-sidebar__title">Voci della sezione</h2>
                            <p class="dci-amm-sidebar__intro">Esplora le voci e le sottovoci di <?php echo esc_html($root_display_name); ?>.</p>
                        </div>
                    </div>
                    <?php $root_link_data = dci_amm_sidebar_get_term_link_data($root_term); ?>
                    <div class="dci-amm-sidebar__section-entries">
                    <p class="dci-amm-sidebar__term-root<?php echo dci_amm_sidebar_term_is_active($root_term, $current_term) ? ' is-active' : ''; ?>">
                        <a class="text-decoration-none t-primary d-inline-flex align-items-center gap-1" href="<?php echo esc_url($root_link_data['url']); ?>"<?php echo $current_term instanceof WP_Term && (int) $root_term->term_id === (int) $current_term->term_id ? ' aria-current="' . esc_attr($sidebar_current_attribute) . '"' : ''; ?><?php echo $root_link_data['target']; ?>>
                            <span class="dci-amm-sidebar__group-icon" aria-hidden="true"><svg class="icon"><use href="#it-list"></use></svg></span>
                            <span><?php echo esc_html($root_display_name); ?></span>
                            <?php if (!empty($root_link_data['is_external'])) { ?>
                                <svg class="icon icon-xs dci-amm-sidebar__external-icon" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                            <?php } ?>
                        </a>
                    </p>
                        <?php dci_amm_sidebar_render_term_branch($root_term, $current_term); ?>
                    </div>
                </nav>
            <?php } ?>

            <?php if (is_array($sidebar_theme_sites) && count($sidebar_theme_sites)) { ?>
                <div class="dci-amm-sidebar__box dci-amm-sidebar__links">
                    <h2 class="title-medium-semi-bold dci-amm-sidebar__title">Link utili</h2>
                    <ul class="dci-amm-sidebar__theme-list">
                        <?php foreach ($sidebar_theme_sites as $sito_tematico_id) {
                            dci_amm_sidebar_render_theme_item($sito_tematico_id);
                        } ?>
                    </ul>
                </div>
            <?php } ?>


            <div class="dci-amm-sidebar__box dci-amm-sidebar__back-box">
                <a class="title-medium-semi-bold dci-amm-sidebar__back-link text-decoration-none t-primary"
                   href="<?php echo esc_url(home_url('/index.php/amministrazione-trasparente')); ?>">
                    <svg class="icon icon-sm icon-primary me-2" aria-hidden="true">
                        <use href="#it-arrow-left"></use>
                    </svg>
                    <span>Torna all'Amministrazione trasparente</span>
                </a>
            </div>
        </div>
    </div>

<?php if (!$embedded) { ?>
    </div>
<?php } ?>

<script>
    document.querySelectorAll('.dci-amm-sidebar__toggle').forEach(function (button) {
        if (button.dataset.sidebarBound) { return; }
        button.dataset.sidebarBound = 'true';
        button.addEventListener('click', function () {
            var item = button.closest('.dci-amm-sidebar__term-item');
            var panel = item ? item.querySelector(':scope > .dci-amm-sidebar__children') : null;
            if (!item || !panel) {
                return;
            }

            var isOpen = !panel.hasAttribute('hidden');
            if (isOpen) {
                panel.setAttribute('hidden', 'hidden');
                item.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
            } else {
                panel.removeAttribute('hidden');
                item.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });
</script>
