<?php
global $siti_tematici, $dci_amm_sidebar_embedded, $dci_amm_sidebar_sections, $dci_amm_sidebar_column_classes;

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

        $ancestors = get_ancestors($term->term_id, $term->taxonomy);
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
        $colore = dci_get_meta('colore', $prefix, $sito_tematico_id);
        $immagine_id = 0;

        if (is_numeric($immagine)) {
            $immagine_id = (int) $immagine;
        } elseif (is_string($immagine) && !empty($immagine)) {
            $immagine_id = attachment_url_to_postid($immagine);
        }
        ?>
        <li class="dci-amm-sidebar__theme-item">
            <a class="dci-amm-sidebar__theme-link text-decoration-none" href="<?php echo esc_url(dci_amm_sidebar_get_sito_tematico_link($sito_tematico_id)); ?>">
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
                    <svg class="icon icon-md dci-amm-sidebar__theme-icon" aria-hidden="true" <?php echo !empty($colore) ? 'style="fill:' . esc_attr($colore) . ';"' : ''; ?>>
                        <use href="#it-external-link"></use>
                    </svg>
                </div>
            </a>
        </li>
        <?php
    }
}

if (!function_exists('dci_amm_sidebar_render_term_branch')) {
    function dci_amm_sidebar_render_term_branch($parent_term, $current_term, $level = 0, $max_level = 2)
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
                $is_active = dci_amm_sidebar_term_is_active($child, $current_term);
                $grandchildren = ($level < $max_level) ? dci_amm_sidebar_get_term_children($child->term_id, $child->taxonomy) : [];
                $has_children = !empty($grandchildren);
                $is_open = $is_active;
                $link_data = dci_amm_sidebar_get_term_link_data($child);
                ?>
                <li class="dci-amm-sidebar__term-item<?php echo $is_active ? ' is-active' : ''; ?><?php echo $is_open ? ' is-open' : ''; ?>">
                    <div class="dci-amm-sidebar__term-row">
                        <a class="dci-amm-sidebar__term-link text-decoration-none" href="<?php echo esc_url($link_data['url']); ?>"<?php echo $link_data['target']; ?>>
                            <span class="dci-amm-sidebar__term-marker dci-amm-sidebar__term-marker--level-<?php echo (int) $level; ?>" aria-hidden="true">
                                <?php if ($has_children) { ?>
                                    <span class="dci-amm-sidebar__term-marker-arrow">›</span>
                                <?php } else { ?>
                                    <span class="dci-amm-sidebar__term-marker-dash">-</span>
                                <?php } ?>
                            </span>
                            <span class="dci-amm-sidebar__term-label"><?php echo esc_html($child->name); ?></span>
                            <?php if (!empty($link_data['is_external'])) { ?>
                                <svg class="icon icon-xs dci-amm-sidebar__external-icon" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                            <?php } ?>
                        </a>
                        <?php if ($has_children) { ?>
                            <button
                                type="button"
                                class="dci-amm-sidebar__toggle"
                                aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                                aria-label="<?php echo esc_attr(sprintf('Mostra le sottovoci di %s', $child->name)); ?>">
                                <svg class="icon icon-sm" aria-hidden="true">
                                    <use href="#it-expand"></use>
                                </svg>
                            </button>
                        <?php } ?>
                    </div>
                    <?php if ($has_children && $level < $max_level) { ?>
                        <div class="dci-amm-sidebar__children"<?php echo $is_open ? '' : ' hidden'; ?>>
                            <?php dci_amm_sidebar_render_term_branch($child, $current_term, $level + 1, $max_level); ?>
                        </div>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <?php
    }
}

$current_term = get_queried_object();
$current_term = ($current_term instanceof WP_Term && isset($current_term->taxonomy) && $current_term->taxonomy === 'tipi_cat_amm_trasp')
    ? $current_term
    : null;

$root_term = dci_amm_sidebar_get_root_term($current_term);
$root_term = dci_amm_sidebar_term_is_visible($root_term) ? $root_term : null;
$embedded = !empty($dci_amm_sidebar_embedded);
$sidebar_column_classes = !empty($dci_amm_sidebar_column_classes) ? trim((string) $dci_amm_sidebar_column_classes) : '';
$sidebar_sections = is_array($dci_amm_sidebar_sections) ? array_values(array_filter($dci_amm_sidebar_sections)) : [];
?>

<style>
    .dci-amm-sidebar {
        min-width: 0;
    }

    .dci-amm-sidebar__sticky {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
    }

    .dci-amm-sidebar__box {
        background: #fff;
        border: 1px solid #e9eef4;
        border-radius: .5rem;
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

    .dci-amm-sidebar__term-list--level-1,
    .dci-amm-sidebar__term-list--level-2 {
        margin-top: .5rem;
        margin-left: .875rem;
        padding-left: .875rem;
        border-left: 1px solid #dbe5ee;
    }

    .dci-amm-sidebar__term-item + .dci-amm-sidebar__term-item,
    .dci-amm-sidebar__section-item + .dci-amm-sidebar__section-item {
        margin-top: .625rem;
    }

    .dci-amm-sidebar__term-row {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
    }

    .dci-amm-sidebar__term-link,
    .dci-amm-sidebar__section-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        width: 100%;
        line-height: 1.4;
        color: #17324d;
    }

    .dci-amm-sidebar__term-link {
        font-weight: 500;
    }

    .dci-amm-sidebar__term-label {
        font-weight: 500;
    }

    .dci-amm-sidebar__term-item.is-active > .dci-amm-sidebar__term-row .dci-amm-sidebar__term-label,
    .dci-amm-sidebar__term-root.is-active span {
        font-weight: 700;
        color: #17324d;
    }

    .dci-amm-sidebar__term-list--level-2 .dci-amm-sidebar__term-link {
        color: #455a64;
    }

    .dci-amm-sidebar__term-marker {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1rem;
        height: 1rem;
        color: currentColor;
    }

    .dci-amm-sidebar__term-marker-arrow {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1;
        opacity: .9;
    }

    .dci-amm-sidebar__term-marker-dash {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1;
        opacity: .9;
    }

    .dci-amm-sidebar__term-root a {
        font-weight: 700;
    }

    .dci-amm-sidebar__external-icon {
        flex: 0 0 auto;
        fill: currentColor;
    }

    .dci-amm-sidebar__toggle {
        flex: 0 0 auto;
        border: 0;
        background: transparent;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #17324d;
        cursor: pointer;
    }

    .dci-amm-sidebar__toggle .icon {
        transition: transform .2s ease;
    }

    .dci-amm-sidebar__term-item.is-open > .dci-amm-sidebar__term-row .dci-amm-sidebar__toggle .icon {
        transform: rotate(180deg);
    }

    .dci-amm-sidebar__theme-item + .dci-amm-sidebar__theme-item {
        margin-top: .875rem;
        padding-top: .875rem;
        border-top: 1px solid #e9eef4;
    }

    .dci-amm-sidebar__theme-link {
        display: block;
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
        color: #17324d;
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
        fill: var(--bs-primary, #0066cc);
    }

    .dci-amm-sidebar__back-link {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-weight: 600;
        width: calc(100% + 2.5rem);
        margin: -1.25rem;
        padding: 1.25rem;
        min-height: 100%;
    }

    .dci-amm-sidebar__back-link:hover {
        color: #17324d;
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
                                <a class="dci-amm-sidebar__section-link" href="#<?php echo esc_attr($id); ?>">
                                    <span><?php echo esc_html($label); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($root_term instanceof WP_Term) { ?>
                <div class="dci-amm-sidebar__box">
                    <h2 class="title-medium-semi-bold dci-amm-sidebar__title">Voci della sezione</h2>
                    <?php $root_link_data = dci_amm_sidebar_get_term_link_data($root_term); ?>
                    <p class="dci-amm-sidebar__term-root<?php echo dci_amm_sidebar_term_is_active($root_term, $current_term) ? ' is-active' : ''; ?>">
                        <a class="text-decoration-none d-inline-flex align-items-center gap-1" href="<?php echo esc_url($root_link_data['url']); ?>"<?php echo $root_link_data['target']; ?>>
                            <span><?php echo esc_html($root_term->name); ?></span>
                            <?php if (!empty($root_link_data['is_external'])) { ?>
                                <svg class="icon icon-xs dci-amm-sidebar__external-icon" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                            <?php } ?>
                        </a>
                    </p>
                    <?php dci_amm_sidebar_render_term_branch($root_term, $current_term, 0, 2); ?>
                </div>
            <?php } ?>

            <?php if (is_array($siti_tematici) && count($siti_tematici)) { ?>
                <div class="dci-amm-sidebar__box">
                    <h2 class="title-medium-semi-bold dci-amm-sidebar__title">Link utili</h2>
                    <ul class="dci-amm-sidebar__theme-list">
                        <?php foreach ($siti_tematici as $sito_tematico_id) {
                            dci_amm_sidebar_render_theme_item($sito_tematico_id);
                        } ?>
                    </ul>
                </div>
            <?php } ?>


            <div class="dci-amm-sidebar__box">
                <a class="dci-amm-sidebar__back-link text-decoration-none" href="<?php echo esc_url(home_url('/amministrazione-trasparente')); ?>">
                    <svg class="icon icon-sm" aria-hidden="true">
                        <use href="#it-arrow-left"></use>
                    </svg>
                    <span>Torna ad Amministrazione trasparente</span>
                </a>
            </div>
        </div>
    </div>

<?php if (!$embedded) { ?>
    </div>
<?php } ?>

<script>
    document.querySelectorAll('.dci-amm-sidebar__toggle').forEach(function (button) {
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
