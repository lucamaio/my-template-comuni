<?php
/**
 * Plugin Name: PSR - Blocco WP File Manager
 * Description: Impedisce il caricamento e la riattivazione di WP File Manager; non modifica gli upload WordPress.
 * Version: 1.0.0
 * Installare come MU-plugin dopo revisione, non dentro il tema.
 * PSR-FILE-MANAGER-POLICY
 */
if (!defined('ABSPATH')) { exit; }

function psr_fmp_denied_plugin($plugin) {
    if (!is_string($plugin)) { return false; }
    $path = strtolower(ltrim(str_replace('\\', '/', $plugin), '/'));
    $directory = explode('/', $path, 2)[0];
    return in_array($directory, array('wp-file-manager', 'wp-file-manager-pro'), true);
}
function psr_fmp_active_plugins($plugins) {
    if (!is_array($plugins)) { return $plugins; }
    return array_values(array_filter($plugins, static function ($plugin) { return !psr_fmp_denied_plugin($plugin); }));
}
function psr_fmp_network_plugins($plugins) {
    if (!is_array($plugins)) { return $plugins; }
    foreach ($plugins as $plugin => $time) {
        if (psr_fmp_denied_plugin($plugin)) { unset($plugins[$plugin]); }
    }
    return $plugins;
}
// I MU-plugin vengono caricati prima dei plugin ordinari e di rete.
add_filter('option_active_plugins', 'psr_fmp_active_plugins', PHP_INT_MAX);
add_filter('site_option_active_sitewide_plugins', 'psr_fmp_network_plugins', PHP_INT_MAX);

function psr_fmp_activate_cap($caps, $cap, $user_id, $args) {
    if ($cap === 'activate_plugin' && isset($args[0]) && psr_fmp_denied_plugin($args[0])) { return array('do_not_allow'); }
    return $caps;
}
add_filter('map_meta_cap', 'psr_fmp_activate_cap', PHP_INT_MAX, 4);

function psr_fmp_install_source($source, $remote_source = '', $upgrader = null, $hook_extra = array()) {
    if (is_wp_error($source) || !is_string($source)) { return $source; }
    $directory = basename(rtrim(str_replace('\\', '/', $source), '/'));
    if (psr_fmp_denied_plugin($directory . '/plugin.php')) {
        return new WP_Error('psr_file_manager_denied', 'WP File Manager non e\' consentito dalla politica PSR.');
    }
    return $source;
}
add_filter('upgrader_source_selection', 'psr_fmp_install_source', PHP_INT_MAX, 4);

function psr_fmp_blocked_request($page, $action) {
    $pages = array('wp_file_manager','wp_file_manager_settings','wp_file_manager_preferences',
        'wp_file_manager_sys_properties','wp_file_manager_shortcode_doc','wpfm-logs','wpfm-backup');
    $actions = array('mk_file_folder_manager','mk_fm_close_fm_help','mk_filemanager_verify_email',
        'verify_filemanager_email','mk_file_folder_manager_media_upload','mk_file_manager_backup',
        'mk_file_manager_backup_remove','mk_file_manager_single_backup_remove',
        'mk_file_manager_single_backup_logs','mk_file_manager_single_backup_restore');
    return (is_string($page) && in_array($page, $pages, true))
        || (is_string($action) && in_array($action, $actions, true));
}
function psr_fmp_admin_gate() {
    $page = isset($_GET['page']) && is_string($_GET['page']) ? wp_unslash($_GET['page']) : '';
    $action = isset($_REQUEST['action']) && is_string($_REQUEST['action']) ? wp_unslash($_REQUEST['action']) : '';
    if (!psr_fmp_blocked_request($page, $action)) { return; }
    nocache_headers();
    wp_die('WP File Manager non consentito dalla politica PSR.', 'PSR - Plugin bloccato', array('response'=>403));
}
add_action('admin_init', 'psr_fmp_admin_gate', -1000);

function psr_fmp_rest_gate($result, $server, $request) {
    if (is_wp_error($result) || !is_object($request) || !method_exists($request, 'get_route')) { return $result; }
    if (preg_match('~^/v1/fm/(?:backup|backupall)(?:/|$)~i', (string)$request->get_route())) {
        return new WP_Error('psr_file_manager_denied', 'WP File Manager non consentito.', array('status'=>403));
    }
    return $result;
}
add_filter('rest_pre_dispatch', 'psr_fmp_rest_gate', -1000, 3);
