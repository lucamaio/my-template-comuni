<?php
/**
 * Capability individuale per consultare lo storico dell'Amministrazione Trasparente.
 *
 * Il file viene caricato automaticamente insieme alle tipologie del tema.
 */

if (!defined('DCI_AT_VIEW_ARCHIVE_CAP')) {
    define('DCI_AT_VIEW_ARCHIVE_CAP', 'visualizza_storico_trasparenza');
}

if (!function_exists('dci_user_can_view_trasparenza_archive')) {
    /**
     * Verifica se l'utente corrente può consultare i contenuti oltre il periodo
     * ordinario di pubblicazione.
     */
    function dci_user_can_view_trasparenza_archive()
    {
        return is_user_logged_in()
            && (
                current_user_can('manage_options')
                || current_user_can(DCI_AT_VIEW_ARCHIVE_CAP)
            );
    }
}

/**
 * Registra la capability in WordPress assegnandola al ruolo amministratore.
 * In questo modo User Role Editor la rileva e può gestirla per ruoli o utenti.
 */
function dci_register_trasparenza_archive_capability()
{
    $administrator = get_role('administrator');

    if ($administrator instanceof WP_Role && !$administrator->has_cap(DCI_AT_VIEW_ARCHIVE_CAP)) {
        $administrator->add_cap(DCI_AT_VIEW_ARCHIVE_CAP);
    }
}
add_action('init', 'dci_register_trasparenza_archive_capability', 20);
