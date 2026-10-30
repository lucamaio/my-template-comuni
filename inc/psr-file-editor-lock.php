<?php
/**
 * Disabilita gli editor PHP di temi e plugin dentro wp-admin.
 * Non blocca aggiornamenti, attivazione di plugin autorizzati o modifiche CSS.
 * Gli aggiornamenti dal repository Git/Plesk restano disponibili.
 */
if (!defined('ABSPATH')) { exit; }
function psr_file_editor_lock_enabled() {
    return !defined('PSR_ALLOW_DASHBOARD_FILE_EDITOR') || !PSR_ALLOW_DASHBOARD_FILE_EDITOR;
}
function psr_file_editor_lock_caps($caps, $cap, $user_id, $args) {
    if (psr_file_editor_lock_enabled() && in_array($cap, array('edit_plugins', 'edit_themes', 'edit_files'), true)) {
        return array('do_not_allow');
    }
    return $caps;
}
add_filter('map_meta_cap', 'psr_file_editor_lock_caps', PHP_INT_MAX, 4);
function psr_file_editor_lock_mods($allowed, $context) {
    if (psr_file_editor_lock_enabled() && $context === 'capability_edit') { return false; }
    return $allowed;
}
add_filter('file_mod_allowed', 'psr_file_editor_lock_mods', PHP_INT_MAX, 2);
