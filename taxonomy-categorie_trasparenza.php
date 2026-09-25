<?php 
/**
 * Archivio Tassonomia trasparenza
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @package Design_Comuni_Italia
 */


global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici, $tipo_personalizzato, $dci_amm_sidebar_column_classes, $sezione;

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

$obj = get_queried_object();

if ($obj instanceof WP_Term && isset($obj->taxonomy) && $obj->taxonomy === 'tipi_cat_amm_trasp') {
    $term_url = trim((string) get_term_meta($obj->term_id, 'term_url', true));
    $open_new_window = get_term_meta($obj->term_id, 'open_new_window', true);

    if ($term_url !== '') {
        $redirect_url = esc_url_raw($term_url);

        if (!empty($redirect_url)) {
            if (!empty($open_new_window)) {
                $fallback_url = home_url('/amministrazione-trasparente');
                ?>
                <!doctype html>
                <html <?php language_attributes(); ?>>
                <head>
                    <meta charset="<?php bloginfo('charset'); ?>">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title><?php echo esc_html(dci_format_trasparenza_section_title($obj->name)); ?></title>
                    <script>
                        window.addEventListener('load', function () {
                            window.open(<?php echo wp_json_encode($redirect_url); ?>, '_blank', 'noopener');
                            window.location.replace(<?php echo wp_json_encode($fallback_url); ?>);
                        });
                    </script>
                    <noscript>
                        <meta http-equiv="refresh" content="0;url=<?php echo esc_url($redirect_url); ?>">
                    </noscript>
                </head>
                <body></body>
                </html>
                <?php
                exit;
            }

            wp_redirect($redirect_url, 302);
            exit;
        }
    }
}

if (!function_exists('dci_render_trasparenza_not_applicable_notice')) {
    /**
     * Stampa l'avviso solo per una sezione della trasparenza esplicitamente
     * contrassegnata come non applicabile.
     */
    function dci_render_trasparenza_not_applicable_notice($term = null)
    {
        if (!$term instanceof WP_Term) {
            $term = get_queried_object();
        }

        if (
            !$term instanceof WP_Term
            || $term->taxonomy !== 'tipi_cat_amm_trasp'
            || '1' !== (string) get_term_meta($term->term_id, 'obbligo_non_applicabile', true)
        ) {
            return;
        }

        $message = function_exists('dci_get_trasparenza_not_applicable_message')
            ? dci_get_trasparenza_not_applicable_message($term->term_id)
            : __('L’obbligo di pubblicazione non è applicabile all’amministrazione.', 'design_comuni_italia');

        $notice_title_id = 'dci-at-not-applicable-title-' . (int) $term->term_id;
        ?>
        <aside
            class="dci-at-not-applicable"
            role="note"
            aria-labelledby="<?php echo esc_attr($notice_title_id); ?>"
        >
            <svg class="icon dci-at-not-applicable__icon" aria-hidden="true">
                <use href="#it-info-circle"></use>
            </svg>
            <div class="dci-at-not-applicable__content">
                <span class="dci-at-not-applicable__label">
                    <?php esc_html_e('Stato della sezione', 'design_comuni_italia'); ?>
                </span>
                <h2
                    id="<?php echo esc_attr($notice_title_id); ?>"
                    class="dci-at-not-applicable__title"
                >
                    <?php esc_html_e('Informazione sull’applicabilità', 'design_comuni_italia'); ?>
                </h2>
                <p class="dci-at-not-applicable__message">
                    <?php echo nl2br(esc_html($message)); ?>
                </p>
            </div>
        </aside>
        <?php
    }
}

get_header();

$dci_amm_sidebar_column_classes = 'pt-30 pt-lg-50 pb-lg-50';

if (!function_exists('dci_render_trasparenza_light_bg_style')) {
    function dci_render_trasparenza_light_bg_style()
    {
        ?>
        <style>
            :root {
                /* --dci-at-primary: var(--bs-primary, rgb(6, 62, 138));
                --dci-at-primary-dark: var(--bs-primary, rgb(6, 62, 138));
                --dci-at-primary-soft: #f3f7fb;
                --dci-at-border: #dfe7f0;
                --dci-at-text: #455a64; */
            }

            .dci-at-tools {
                background: #ffffff;
                border: 1px solid var(--dci-at-border);
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(23, 50, 77, 0.08);
                padding: 1.25rem;
                margin-bottom: 1.75rem;
            }

            .dci-at-tools__title {
                margin-bottom: 0.35rem;
                font-size: 1.35rem;
            }

            .dci-at-tools__intro {
                margin-bottom: 1rem;
            }

            .dci-at-section-update {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                min-height: 52px;
                margin-top: 1rem;
                margin-bottom: 0;
                padding: 0.75rem 1rem;
                color: #334e68;
                background: #fff;
                border: 2px solid #b8c9da;
                border-radius: 6px;
                font-size: 0.95rem;
                line-height: 1.4;
            }

            .dci-at-section-update__icon {
                flex: 0 0 auto;
                width: 1.1rem;
                height: 1.1rem;
                fill: var(--dci-at-theme-color, #17324d);
            }

            .dci-at-section-update__label {
                font-weight: 700;
            }

            .dci-at-tools .cmp-input-search {
                margin-bottom: 0;
            }

            .dci-at-tools .cmp-input-search .autocomplete-wrapper {
                margin-bottom: 0 !important;
            }

            .dci-at-search-row {
                display: flex;
                align-items: stretch;
                position: relative;
                padding: 0.3rem;
                overflow: hidden;
                background: linear-gradient(
                    135deg,
                    #ffffff 0%,
                    rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.045) 100%
                );
                border: 1px solid rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.32);
                border-left: 4px solid var(--dci-at-theme-color, #17324d);
                border-radius: 6px;
                box-shadow: 0 5px 16px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.08);
                transition: border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .dci-at-search-row:focus-within {
                border-color: var(--dci-at-theme-color, #17324d);
                box-shadow: 0 0 0 3px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.14),
                    0 8px 20px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.1);
            }

            .dci-at-search-row .input-group {
                align-items: stretch;
            }

            .dci-at-tools .form-control,
            .dci-at-tools .autocomplete {
                min-height: 52px;
                border: 2px solid #b8c9da;
                background: #fff;
                border-radius: 6px;
            }

            .dci-at-search-row .autocomplete {
                min-width: 0;
                padding-left: 3.35rem;
                border: 0 !important;
                border-radius: 3px !important;
                background: transparent;
                box-shadow: none !important;
            }

            .dci-at-search-row .autocomplete::placeholder {
                color: #5c7185;
                opacity: 1;
            }

            .dci-at-search-row .autocomplete::-webkit-search-cancel-button {
                display: none;
                -webkit-appearance: none;
                appearance: none;
            }

            .dci-at-search-clear {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                align-self: center;
                flex: 0 0 auto;
                width: 2.15rem;
                height: 2.15rem;
                margin-left: 0.25rem;
                padding: 0;
                color: #fff;
                background: var(--bs-danger, #d9364f);
                border: 1px solid var(--bs-danger, #d9364f);
                border-radius: 4px;
                cursor: pointer;
                box-shadow: 0 3px 9px rgba(173, 32, 52, 0.18);
                transition: background-color 0.18s ease, border-color 0.18s ease, filter 0.18s ease, transform 0.18s ease;
            }

            .dci-at-search-clear[hidden] {
                display: none !important;
            }

            .dci-at-search-clear .icon {
                width: 0.85rem;
                height: 0.85rem;
                fill: currentColor;
            }

            .dci-at-search-clear:hover,
            .dci-at-search-clear:focus-visible {
                color: #fff;
                background: var(--bs-danger, #d9364f);
                border-color: var(--bs-danger, #d9364f);
                filter: brightness(0.9);
                transform: scale(1.04);
            }

            .dci-at-search-clear:focus-visible {
                outline: 2px solid var(--bs-danger, #d9364f);
                outline-offset: 2px;
            }

            .dci-at-search-row .input-group-append {
                display: flex;
                align-items: stretch;
                padding-left: 0.35rem;
            }

            .dci-at-search-row .autocomplete-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: 50%;
                left: 0.9rem;
                z-index: 4;
                width: 2rem;
                height: 2rem;
                padding: 0;
                background: rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.1);
                border-radius: 50%;
                transform: translateY(-50%);
                pointer-events: none;
            }

            .dci-at-search-row .autocomplete-icon .icon {
                width: 1.05rem;
                height: 1.05rem;
                fill: var(--dci-at-theme-color, #17324d);
            }

            .dci-at-tools .form-control:focus,
            .dci-at-tools .autocomplete:focus {
                border-color: var(--dci-at-primary);
                box-shadow: 0 0 0 0.2rem rgba(6, 62, 138, 0.16);
            }

            .dci-at-tools .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.45rem;
                min-width: 130px;
                font-weight: 700;
                color: #fff;
                background: var(--dci-at-theme-color, #17324d);
                border-color: var(--dci-at-theme-color, #17324d);
                border-radius: 4px !important;
                box-shadow: 0 3px 9px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.18);
            }

            .dci-at-search-button .icon {
                width: 1rem;
                height: 1rem;
                fill: currentColor;
            }

            .dci-at-tools .btn-primary:hover,
            .dci-at-tools .btn-primary:focus {
                color: #fff;
                background: var(--dci-at-theme-color, #17324d);
                border-color: var(--dci-at-theme-color, #17324d);
                filter: brightness(1.08);
            }

            .dci-at-tools .autocomplete-icon .icon,
            .dci-at-empty__icon .icon {
                fill: var(--dci-at-theme-color, #17324d);
            }

            .dci-at-tools__count {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.35rem;
                margin-top: 1rem;
                margin-bottom: 0;
                padding: 0.75rem 0.9rem;
                color: #334e68;
                background: rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.07);
                border: 1px solid rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.22);
                border-left: 4px solid var(--dci-at-theme-color, #17324d);
                border-radius: 6px;
                line-height: 1.4;
            }

            .dci-at-tools__count-value {
                color: var(--dci-at-theme-color, #17324d);
                font-size: 1.1rem;
                font-weight: 700;
            }

            .dci-at-filters {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 1.1rem;
                padding-top: 1rem;
                border-top: 1px solid #e6edf5;
            }

            .dci-at-filter {
                position: static;
                margin-bottom: 0;
            }

            .dci-at-tools .dci-at-filter > .dci-at-filter__label {
                display: block !important;
                position: static !important;
                top: auto !important;
                left: auto !important;
                margin-bottom: 0.45rem;
                padding: 0;
                font-size: 0.95rem;
                font-weight: 700;
                background: transparent;
                transform: none !important;
                line-height: 1.4;
            }

            .dci-at-filter .form-control {
                display: block;
                width: 100%;
                margin: 0;
                padding: 0.75rem 1rem;
                appearance: auto;
            }

            .dci-at-tools a,
            .dci-at-empty a,
            .dci-at-tools .text-decoration-none,
            .dci-at-empty .text-decoration-none {
                color: var(--dci-at-primary) !important;
                text-decoration-color: var(--dci-at-primary) !important;
            }

            .dci-at-tools a:hover,
            .dci-at-empty a:hover,
            .dci-at-tools .text-decoration-none:hover,
            .dci-at-empty .text-decoration-none:hover {
                color: var(--dci-at-primary) !important;
                text-decoration-color: var(--dci-at-primary) !important;
            }

            .dci-at-empty {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1.2rem 1.35rem;
                border: 1px solid var(--dci-at-border);
                border-left: 5px solid var(--dci-at-primary);
                border-radius: 6px;
                background: #ffffff;
            }

            .dci-at-empty__icon {
                flex: 0 0 auto;
                width: 2.25rem;
                height: 2.25rem;
                border: 1px solid #8aa0b8;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--dci-at-primary);
            }

            .dci-at-empty__icon .icon {
                fill: currentColor;
            }

            .dci-at-empty__content {
                min-width: 0;
            }

            .dci-at-empty__title {
                margin-bottom: 0.2rem;
                font-size: 1rem;
                font-weight: 700;
            }

            .dci-at-empty__text {
                margin-bottom: 0;
            }

            .dci-at-empty__law {
                margin: 0.9rem 0 0;
                padding: 0.8rem 0.9rem;
                color: #334e68;
                background: rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.065);
                border-left: 4px solid var(--dci-at-theme-color, #17324d);
                border-radius: 4px;
                line-height: 1.5;
            }

            .dci-at-empty__law-title {
                display: block;
                margin-bottom: 0.2rem;
                color: var(--dci-at-theme-color, #17324d);
                font-weight: 700;
            }

            .dci-at-empty__access {
                margin: 0.75rem 0 0;
                padding: 0.8rem 0.9rem;
                color: #455a64;
                background: #fff;
                border: 1px solid #d8e1ea;
                border-left: 4px solid var(--dci-at-theme-color, #17324d);
                border-radius: 4px;
                line-height: 1.5;
            }

            .dci-at-empty__access-list {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                margin-top: 0.85rem;
            }

            .dci-at-empty__access-list .dci-at-empty__access {
                margin-top: 0;
            }

            .dci-at-empty__access-title {
                display: block;
                margin-bottom: 0.25rem;
                color: var(--dci-at-theme-color, #17324d);
                font-weight: 700;
            }

            .dci-at-empty__action {
                display: table;
                margin-top: 0.65rem;
                color: var(--dci-at-theme-color, #17324d);
                font-weight: 700;
                text-decoration: underline;
                text-underline-offset: 0.18em;
            }

            .dci-at-empty__action:hover,
            .dci-at-empty__action:focus {
                color: var(--dci-at-theme-color, #17324d);
                text-decoration-thickness: 2px;
            }

            .dci-at-empty__access-note {
                display: block;
                margin-top: 0.5rem;
                font-weight: 600;
            }

            .dci-at-empty__source {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                margin-top: 0.7rem;
                font-weight: 700;
            }

            .dci-at-empty__source .icon {
                width: 0.95rem;
                height: 0.95rem;
                fill: currentColor;
            }

            @media (max-width: 767.98px) {
                .dci-at-empty__access-list {
                    grid-template-columns: 1fr;
                }
            }

            .dci-at-layout {
                padding-bottom: 2.5rem;
            }

            .dci-at-not-applicable {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                margin: 0 0 1.75rem;
                padding: 1.15rem 1.25rem;
                border: 1px solid #b9cee2;
                border-left: 4px solid var(--dci-at-primary, #0066cc);
                border-radius: 6px;
                background: linear-gradient(135deg, rgba(237, 246, 255, 0.96), rgba(255, 255, 255, 0.92));
                box-shadow: 0 8px 24px rgba(23, 50, 77, 0.06);
                color: #17324d;
            }

            .dci-at-not-applicable__icon {
                flex: 0 0 auto;
                width: 2rem;
                height: 2rem;
                margin-top: 0.1rem;
                fill: var(--dci-at-primary, #0066cc);
            }

            .dci-at-not-applicable__content {
                min-width: 0;
            }

            .dci-at-not-applicable__label {
                display: block;
                margin-bottom: 0.2rem;
                color: var(--dci-at-primary, #0066cc);
                font-size: 0.78rem;
                line-height: 1.3;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .dci-at-not-applicable__title {
                margin: 0 0 0.35rem;
                font-size: 1.05rem;
                line-height: 1.4;
                font-weight: 700;
            }

            .dci-at-not-applicable__message {
                margin: 0;
                line-height: 1.55;
            }

            .tax-tipi_cat_amm_trasp #custom-section .it-hero-text-wrapper {
                padding-bottom: 1.25rem !important;
            }

            .dci-at-normativa-wrap {
                padding-top: 0;
                padding-bottom: 0.75rem;
            }

            .dci-at-normativa {
                display: flex;
                align-items: flex-start;
                gap: 0.9rem;
                margin: 0;
                padding: 1rem 1.15rem;
                color: #17324d;
                background: linear-gradient(
                    135deg,
                    rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.08) 0%,
                    rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.025) 100%
                );
                border: 1px solid rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.28);
                border-left: 4px solid var(--dci-at-theme-color, #17324d);
                border-radius: 4px;
                box-shadow: 0 4px 14px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.08);
            }

            .dci-at-normativa__icon {
                flex: 0 0 auto;
                width: 1.5rem;
                height: 1.5rem;
                margin-top: 0.1rem;
                fill: var(--dci-at-theme-color, #17324d);
            }

            .dci-at-normativa__content {
                min-width: 0;
            }

            .dci-at-normativa__label {
                display: block;
                margin-bottom: 0.25rem;
                color: var(--dci-at-theme-color, #17324d);
                font-size: 0.78rem;
                line-height: 1.3;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .dci-at-normativa__text {
                margin: 0;
                color: #334e68;
                font-size: 0.95rem;
                line-height: 1.55;
            }

            .dci-at-context-actions {
                border-left-color: var(--dci-at-theme-color, #17324d);
                border-radius: 4px;
                box-shadow: 0 3px 12px rgba(var(--dci-at-theme-color-rgb, 23, 50, 77), 0.08);
            }

            @media (max-width: 767.98px) {
                .dci-at-tools {
                    padding: 1rem;
                }

                .dci-at-tools .btn-primary {
                    min-width: 100px;
                }

                .dci-at-filters {
                    grid-template-columns: 1fr;
                }

                .dci-at-empty {
                    align-items: flex-start;
                }

                .dci-at-layout {
                    padding-bottom: 2rem;
                }

                .dci-at-not-applicable {
                    gap: 0.75rem;
                    padding: 1rem;
                }

                .dci-at-normativa-wrap {
                    padding-bottom: 0.5rem;
                }

                .dci-at-normativa {
                    gap: 0.7rem;
                    padding: 0.9rem 1rem;
                }
            }
        </style>
        <script>
            (function () {
                var selectors = [
                    '.it-header-center-wrapper',
                    '.it-header-navbar-wrapper',
                    '.it-header-slim-wrapper'
                ];

                for (var index = 0; index < selectors.length; index += 1) {
                    var element = document.querySelector(selectors[index]);
                    if (!element) {
                        continue;
                    }

                    var color = window.getComputedStyle(element).backgroundColor;
                    var match = color && color.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i);

                    if (!match || color === 'transparent' || color === 'rgba(0, 0, 0, 0)') {
                        continue;
                    }

                    document.documentElement.style.setProperty('--dci-at-theme-color', color);
                    document.documentElement.style.setProperty(
                        '--dci-at-theme-color-rgb',
                        match[1] + ', ' + match[2] + ', ' + match[3]
                    );
                    break;
                }
            }());
        </script>
        <?php
    }
}

dci_render_trasparenza_light_bg_style();

// Recupera il numero di pagina corrente in modo robusto (paged/page/querystring).
$paged = max(
    1,
    (int) get_query_var('paged'),
    (int) get_query_var('page'),
    isset($_GET['paged']) ? (int) $_GET['paged'] : 0
);

$max_posts = dci_sanitize_posts_per_page(isset($_GET['max_posts']) ? $_GET['max_posts'] : 10, 10, 50);
$load_posts = -1;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

$prefix = '_dci_elemento_trasparenza_';

// Gestione dell'ordinamento
$order = isset($_GET['order_type']) ? $_GET['order_type'] : 'data_desc'; // Default è data_desc
$current_year = (int) wp_date('Y');
$public_start_year = $current_year - 5;
$available_years = range($current_year, $public_start_year);
$selected_year = isset($_GET['anno']) ? absint($_GET['anno']) : 0;

if ($selected_year !== 0 && !in_array($selected_year, $available_years, true)) {
    $selected_year = 0;
}

$can_view_trasparenza_archive = function_exists('dci_user_can_view_trasparenza_archive')
    && dci_user_can_view_trasparenza_archive();

global $wp_query;
$the_query = $wp_query;

// Il conteggio deve riflettere gli stessi elementi mostrati nell'elenco.
$count_query_args = array(
    'post_type' => 'elemento_trasparenza',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'no_found_rows' => false,
    'ignore_sticky_posts' => true,
    'tax_query' => array(
        array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field' => 'term_id',
            'terms' => array((int) $obj->term_id),
            'include_children' => false,
        ),
    ),
);

if ($query !== null && $query !== '') {
    $count_query_args['s'] = $query;
}

if ($selected_year > 0) {
    $count_query_args['date_query'] = array(
        array('year' => $selected_year),
    );
} elseif (!$can_view_trasparenza_archive) {
    $count_query_args['date_query'] = array(
        array(
            'after' => array('year' => $public_start_year),
            'inclusive' => true,
        ),
    );
}

$the_query = new WP_Query($count_query_args);

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
$internal_custom_sections_enabled = function_exists('dci_trasparenza_internal_custom_sections_enabled')
    && dci_trasparenza_internal_custom_sections_enabled();
?>

<main>
    <?php
    $title = dci_format_trasparenza_section_title($obj->name);
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    get_template_part("template-parts/hero/hero");
    $title = $obj->name;
    $normativa = trim((string) get_term_meta($obj->term_id, 'normativa', true));
    if ($normativa !== '') {
        ?>
        <div class="container dci-at-normativa-wrap">
            <div class="row justify-content-start">
                <div class="col-12 col-lg-10">
                    <aside class="dci-at-normativa" aria-label="Riferimento normativo">
                        <svg class="icon dci-at-normativa__icon" aria-hidden="true">
                            <use href="#it-info-circle"></use>
                        </svg>
                        <div class="dci-at-normativa__content">
                            <span class="dci-at-normativa__label">
                                <?php esc_html_e('Riferimento normativo', 'design_comuni_italia'); ?>
                            </span>
                            <p class="dci-at-normativa__text">
                                <?php echo nl2br(esc_html($normativa)); ?>
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
        <?php
    }
    get_template_part("template-parts/amministrazione-trasparente/sottocategorie");
    if (function_exists('dci_render_trasparenza_contextual_actions')) {
        dci_render_trasparenza_contextual_actions($obj);
    }
    ?>

    <div class="bg-grey-card dci-at-layout">
        
      <?php 
          if ($obj->name == "Atti, documenti e link a BDNCP" && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
                <div class="container my-5">
                    <div class="row g-4">
                        <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                        <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                            <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
                        </div>
                        <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                    </div>
                </div>
            </div>
    
    <?php } else if ($obj->name == "Atti di concessione" && dci_get_option("ck_attidiconcessione", "Trasparenza") !== 'false' && dci_get_option("ck_attidiconcessione", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti gli Atti di Concessione</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/atto-concessione/tutti-gli-atti"); ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </div>
     <?php } else if ($obj->name == "Incarichi conferiti e autorizzati ai dipendenti"  && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== 'false' && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti gli Incarichi conferiti e autorizzati ai dipendenti</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-autorizzazioni/tutti-gli-incarichi"); ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </div>		    
    <?php } else if ($obj->name == "Titolari di incarichi di collaborazione o consulenza" && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti i Titolari di incarichi di collaborazione o consulenza</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/titolare_incarico/tutti-titolari"); ?>
                    </div>
                    <?php
                    $dci_amm_sidebar_column_classes = 'pt-20 pt-lg-20 pb-lg-20';
                    get_template_part("template-parts/amministrazione-trasparente/side-bar");
                    ?>
                </div>
            </div>
        </div>
    
   <?php } else if($obj->name === "Telefono e posta elettronica" && $internal_custom_sections_enabled ){?>
        <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora i contatti del ente</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php dci_render_trasparenza_not_applicable_notice($obj); ?>
                        <?php get_template_part("template-parts/amministrazione-trasparente/contatti/tutti-contatti"); ?>
                </div>
                <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div>
        </div>
   <?php } else if($obj->name === "Articolazione uffici" && $internal_custom_sections_enabled){?>
        <div class="container py-5">
            <h2 class="visually-hidden">Esplora l'articolazione degli uffici comunali</h2>
            <?php dci_render_trasparenza_not_applicable_notice($obj); ?>
            <?php get_template_part("template-parts/amministrazione-trasparente/articolazione-uffici/tutti-uffici"); ?>
        </div>
            </div>
   <?php } else if($obj->name === "Titolari di incarichi dirigenziali amministrativi di vertice" && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== ''){?>
         <div class="container my-5">
            <div class="row g-4">
                <h2 class="visually-hidden">Titolari di incarichi dirigenziali amministrativi di vertice </h2>
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                    <?php dci_render_trasparenza_not_applicable_notice($obj); ?>
                     <?php $sezione = $obj->name; ?>
                    <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-dirigenziali/tutti-incarichi"); ?>
                </div>
                 <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div> 
        </div>
    </div>
   <?php } else if($obj->name === "Incarichi dirigenziali a qualsiasi titolo conferiti" && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== ''){?>
         <div class="container my-5">
            <div class="row g-4">
                <h2 class="visually-hidden">Incarichi dirigenziali a qualsiasi titolo conferiti</h2>
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                    <?php dci_render_trasparenza_not_applicable_notice($obj); ?>
                    <?php $sezione = $obj->name; ?>
                    <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-dirigenziali/tutti-incarichi"); ?>
                </div>
                 <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div> 
        </div>
    </div>
   <?php } else if($obj->name === "Dirigenti cessati" && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_incarichidirigenzialitemplatepersonalizzato", "Trasparenza") !== ''){?>
         <div class="container my-5">
            <div class="row g-4">
                <h2 class="visually-hidden">Dirigenti cessati</h2>
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                    <?php dci_render_trasparenza_not_applicable_notice($obj); ?>
                    <?php $sezione = $obj->name; ?>
                    <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-dirigenziali/tutti-incarichi"); ?>
                </div>
                 <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div>
        </div>
    </div>
   <?php } else if (
        in_array($obj->name, array('Il Sindaco', 'Giunta Comunale', 'Consiglio Comunale'), true)
        && $internal_custom_sections_enabled
        && (string) get_term_meta($obj->term_id, 'visualizza_elemento', true) !== '0'
   ) { ?>
        <div class="container my-5">
            <div class="row g-4">
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                    <?php
                    get_template_part(
                        'template-parts/amministrazione-trasparente/organi-politici/tutti-componenti',
                        null,
                        array('section_name' => $obj->name)
                    );
                    ?>
                </div>
                <?php get_template_part('template-parts/amministrazione-trasparente/side-bar'); ?>
            </div>
        </div>
    </div>
   <?php } else {
        // Ultima modifica della sezione classica, indipendente dai filtri correnti.
        $last_updated_query_args = array(
            'post_type' => 'elemento_trasparenza',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'orderby' => 'modified',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'tipi_cat_amm_trasp',
                    'field' => 'term_id',
                    'terms' => array((int) $obj->term_id),
                    'include_children' => false,
                ),
            ),
        );

        if (!$can_view_trasparenza_archive) {
            $last_updated_query_args['date_query'] = array(
                array(
                    'after' => array('year' => $public_start_year),
                    'inclusive' => true,
                ),
            );
        }

        $last_updated_query = new WP_Query($last_updated_query_args);
        $section_last_updated_id = !empty($last_updated_query->posts) ? (int) $last_updated_query->posts[0] : 0;
        $section_last_updated_timestamp = $section_last_updated_id
            ? (int) get_post_modified_time('U', true, $section_last_updated_id)
            : 0;
        $classic_section_url = get_term_link($obj);
        if (is_wp_error($classic_section_url)) {
            $classic_section_url = '';
        }
        ?>
        
        <form role="search" id="search-form" method="get" class="search-form" action="<?php echo esc_url($classic_section_url); ?>">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                    <!-- Colonna sinistra: risultati -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="dci-at-tools" aria-label="Strumenti di ricerca e ordinamento">
                            <h3 class="dci-at-tools__title text-decoration-none">Cerca e ordina i contenuti</h3>
                            <p class="dci-at-tools__intro text-decoration-none">Usa la ricerca per trovare rapidamente un documento e scegli l'ordinamento che preferisci.</p>

                            <div class="cmp-input-search">
                                <div class="form-group autocomplete-wrapper mb-2 mb-lg-3">
                                    <div class="input-group dci-at-search-row">
                                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                        <input type="search" class="autocomplete form-control"
                                            placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                            value="<?php echo esc_attr((string) $query); ?>" data-bs-autocomplete="[]">
                                        <button
                                            class="dci-at-search-clear"
                                            type="button"
                                            aria-label="Cancella il testo della ricerca"
                                            <?php echo empty($query) ? 'hidden' : ''; ?>
                                        >
                                            <svg class="icon" aria-hidden="true">
                                                <use href="#it-close-big"></use>
                                            </svg>
                                        </button>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary dci-at-search-button" type="submit" id="button-3">
                                                <span>Cerca</span>
                                                <svg class="icon" aria-hidden="true">
                                                    <use href="#it-search"></use>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="autocomplete-icon" aria-hidden="true">
                                            <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                                <use href="#it-search"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="dci-at-filters">
                                <div class="form-group mb-0 dci-at-filter">
                                    <label for="year-select" class="dci-at-filter__label text-decoration-none">Filtra per anno</label>
                                    <select id="year-select" name="anno" class="form-control">
                                        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni disponibili</option>
                                        <?php foreach ($available_years as $available_year) { ?>
                                            <option value="<?php echo esc_attr($available_year); ?>" <?php selected($selected_year, $available_year); ?>>
                                                <?php echo esc_html($available_year); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group mb-0 dci-at-filter">
                                    <label for="order-select" class="dci-at-filter__label text-decoration-none">Ordina per</label>
                                    <select id="order-select" name="order_type" class="form-control">
                                        <option value="data_desc" <?php echo ($order == 'data_desc') ? 'selected' : ''; ?>>Data (Descendente)</option>
                                        <option value="data_asc" <?php echo ($order == 'data_asc') ? 'selected' : ''; ?>>Data (Ascendente)</option>
                                        <option value="alfabetico_asc" <?php echo ($order == 'alfabetico_asc') ? 'selected' : ''; ?>>Alfabetico (Ascendente)</option>
                                        <option value="alfabetico_desc" <?php echo ($order == 'alfabetico_desc') ? 'selected' : ''; ?>>Alfabetico (Discendente)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-0 dci-at-filter">
                                    <label for="results-per-page-select" class="dci-at-filter__label text-decoration-none">Risultati per pagina</label>
                                    <select id="results-per-page-select" name="max_posts" class="form-control">
                                        <?php foreach (array(10, 20, 30, 50) as $results_per_page) { ?>
                                            <option value="<?php echo esc_attr($results_per_page); ?>" <?php selected($max_posts, $results_per_page); ?>>
                                                <?php echo esc_html($results_per_page); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="dci-at-section-update">
                                <svg class="icon dci-at-section-update__icon" aria-hidden="true">
                                    <use href="#it-calendar"></use>
                                </svg>
                                <span>
                                    <span class="dci-at-section-update__label">Ultimo aggiornamento della sezione:</span>
                                    <?php if ($section_last_updated_timestamp > 0) { ?>
                                        <time datetime="<?php echo esc_attr(wp_date(DATE_W3C, $section_last_updated_timestamp)); ?>">
                                            <?php echo esc_html(wp_date('j F Y', $section_last_updated_timestamp)); ?>
                                        </time>
                                    <?php } else { ?>
                                        <span>non disponibile</span>
                                    <?php } ?>
                                </span>
                            </div>

                            <?php $results_count = (int) $the_query->found_posts; ?>
                            <p id="autocomplete-label" class="dci-at-tools__count">
                                <strong class="dci-at-tools__count-value">
                                    <?php echo esc_html(number_format_i18n($results_count)); ?>
                                </strong>
                                <span>
                                    <?php
                                    echo esc_html(_n(
                                        'elemento trovato',
                                        'elementi trovati',
                                        $results_count,
                                        'design_comuni_italia'
                                    ));
                                    ?>
                                    in ordine
                                    <?php echo ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') ? 'alfabetico' : 'di pubblicazione'; ?>
                                    <?php echo ($order == 'data_desc' || $order == 'alfabetico_desc') ? '(Discendente)' : '(Ascendente)'; ?>
                                </span>
                            </p>
                        </div>

                        <?php dci_render_trasparenza_not_applicable_notice($obj); ?>

                        <!-- Risultati della ricerca -->
                        <?php dci_get_template_part_async("trasparenza-risultati-paginati"); ?>
                    </div>

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>

                
            </div>
        </form>
    <?php } ?>
    </div>
</main>

<?php


//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

// Se è attiva la trasparenza esterna, non visualizzare questi elementi
if ($portalesoloperusoesterno !== 'true') {
            get_template_part("template-parts/common/valuta-servizio");
            get_template_part("template-parts/common/assistenza-contatti");
}


get_footer();
?>

<script>
    (function () {
        var form = document.getElementById('search-form');

        if (!form) {
            return;
        }

        var searchInput = document.getElementById('autocomplete-two');
        var clearSearchButton = form.querySelector('.dci-at-search-clear');

        if (searchInput && clearSearchButton) {
            var updateClearSearchButton = function () {
                clearSearchButton.hidden = searchInput.value.length === 0;
            };

            searchInput.addEventListener('input', updateClearSearchButton);
            clearSearchButton.addEventListener('click', function () {
                searchInput.value = '';
                updateClearSearchButton();
                searchInput.focus();
            });
            updateClearSearchButton();
        }

        ['order-select', 'year-select', 'results-per-page-select'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field) {
                return;
            }

            field.addEventListener('change', function () {
                form.submit();
            });
        });
    }());
</script>

<?php $dci_amm_sidebar_column_classes = ''; ?>
