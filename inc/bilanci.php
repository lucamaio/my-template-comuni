<?php
/**
 * Pagina Bilanci: navigazione interna dei dati del provider configurato.
 *
 * @package Design_Comuni_Italia
 */

require_once __DIR__ . '/bilanci-provider.php';

/** Validazione sintattica; il fetch aggiunge allowlist e API HTTP sicure WP. */
function dci_bilanci_validate_url($value) {
    if (!is_string($value)) {
        return '';
    }
    $value = trim($value);
    if ($value === '' || preg_match('/[\x00-\x20\x7f<>"\\\\]/', $value)) {
        return '';
    }
    $parts = wp_parse_url($value);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])
        || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)
        || isset($parts['user']) || isset($parts['pass'])
        || (isset($parts['port']) && !in_array($parts['port'], array(80, 443), true))) {
        return '';
    }
    $host = strtolower($parts['host']);
    // Esclude indirizzi locali, IP riservati e nomi host non pubblici.
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return '';
        }
    } elseif (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
        || strpos($host, '.') === false
        || preg_match('/(?:^|\.)(?:localhost|local|internal|test|invalid)$/i', $host)
        || preg_match('/^[0-9.]+$/', $host)) {
        return '';
    }
    return esc_url_raw($value, array('http', 'https'));
}

/** Cerca solo pagine pubblicate, senza modificare pagine, permalink o tassonomie. */
function dci_bilanci_find_page($template) {
    static $pages = array();
    if (!array_key_exists($template, $pages)) {
        $ids = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_wp_page_template',
            'meta_value' => $template,
            'orderby' => 'ID',
            'order' => 'ASC',
        ));
        $pages[$template] = $ids ? get_permalink($ids[0]) : '';
    }
    return $pages[$template];
}

/** Il vincolo vale anche per anteprime amministrative: nessuna eccezione pubblica. */
function dci_bilanci_guard() {
    if (!is_page_template('page-templates/bilanci.php')) {
        return;
    }
    // Non lasciare copie pubbliche consultabili dopo la disattivazione.
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    nocache_headers();
    if ('true' !== dci_get_option('ck_abilita_trasparenza')) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
    }
}
add_action('template_redirect', 'dci_bilanci_guard', 0);

/** Asset caricati esclusivamente sul template Bilanci abilitato. */
function dci_bilanci_enqueue_assets() {
    if (is_404() || !is_page_template('page-templates/bilanci.php')
        || 'true' !== dci_get_option('ck_abilita_trasparenza')) {
        return;
    }
    wp_enqueue_style('dci-bilanci', get_template_directory_uri() . '/assets/css/bilanci.css',
        array('dci-wp-style'), filemtime(get_template_directory() . '/assets/css/bilanci.css'));
    wp_enqueue_script('dci-bilanci-navigation',
        get_template_directory_uri() . '/assets/js/bilanci-navigation.js', array(),
        filemtime(get_template_directory() . '/assets/js/bilanci-navigation.js'), true);
    wp_enqueue_script('dci-trasparenza-theme-colors',
        get_template_directory_uri() . '/assets/js/trasparenza-theme-colors.js', array(),
        filemtime(get_template_directory() . '/assets/js/trasparenza-theme-colors.js'), true);
}
add_action('wp_enqueue_scripts', 'dci_bilanci_enqueue_assets');

/** Riusa il renderer breadcrumb del tema, limitando il filtro a Bilanci. */
function dci_bilanci_breadcrumb_items($items) {
    if (is_404() || !is_page_template('page-templates/bilanci.php')) {
        return $items;
    }
    $url = dci_bilanci_find_page('page-templates/amministrazione-trasparente.php');
    $label = esc_html__('Amministrazione Trasparente', 'design_comuni_italia');
    $items = array(
        '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'design_comuni_italia') . '</a>',
        $url ? '<a href="' . esc_url($url) . '">' . $label . '</a>' : $label,
        esc_html__('Bilanci', 'design_comuni_italia'),
    );
    $source = dci_bilanci_source(dci_get_option('url_bilanci', 'trasparenza'));
    $state = $source ? dci_bilanci_request_state($source) : false;
    if ($state && $state['year'] !== '') {
        $items[2] = '<a href="' . esc_url(get_permalink(get_queried_object_id())) . '">' . esc_html__('Bilanci', 'design_comuni_italia') . '</a>';
        $year_label = 'Bilancio ' . $state['year'];
        $items[] = $state['type'] !== ''
            ? '<a href="' . esc_url(dci_bilanci_local_url($source, $state['year'])) . '">' . esc_html($year_label) . '</a>'
            : esc_html($year_label);
        if ($state['type'] !== '') { $items[] = esc_html($state['type']); }
    }
    return $items;
}
add_filter('breadcrumb_trail_items', 'dci_bilanci_breadcrumb_items');
