<?php
/** Collegamento alla pagina Bilanci pubblicata, senza alterare le categorie. */
if ('true' !== dci_get_option('ck_abilita_trasparenza')) {
    return;
}
$bilanci_page_url = dci_bilanci_find_page('page-templates/bilanci.php');
if ($bilanci_page_url === '') {
    return;
}
?>
<div class="container mb-4">
    <a href="<?php echo esc_url($bilanci_page_url); ?>"><?php esc_html_e('Consulta i bilanci dell’Ente', 'design_comuni_italia'); ?></a>
</div>
