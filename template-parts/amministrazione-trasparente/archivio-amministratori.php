<?php
/**
 * Divisore per i contenuti storici visibili agli utenti autorizzati.
 *
 * Visibilita riservata: il chiamante deve aver verificato l'autenticazione e
 * l'abilitazione tramite dci_user_can_view_trasparenza_archive(), e deve avere
 * risultati nella query dello storico. Questo wrapper non esegue un controllo
 * autonomo dei permessi. Attualmente e richiamato da titolare_incarico/tutti-titolari.php,
 * che crea la query storica solo per gli utenti abilitati; il ramo consulenti
 * delega inoltre ad avviso-storico.php, che ripete il controllo prima dell'HTML.
 *
 * @var array $args {
 *     @type int $start_year Primo anno visibile agli utenti finali.
 *     @type int $count      Numero di contenuti storici trovati.
 *     @type string $section Sezione per l'eventuale informativa specifica.
 * }
 */

$start_year = isset($args['start_year']) ? absint($args['start_year']) : (int) wp_date('Y') - 5;
$archive_count = isset($args['count']) ? absint($args['count']) : 0;

if (($args['section'] ?? '') === 'titolare_incarico') {
    get_template_part('template-parts/amministrazione-trasparente/titolare_incarico/avviso-storico', null, [
        'start_year' => $start_year,
        'count' => $archive_count,
    ]);
    return;
}

?>
<aside class="dci-at-notice mt-5 mb-4" role="note">
    <span class="dci-at-notice__label"><svg class="icon icon-sm" aria-hidden="true"><use href="#it-info-circle"></use></svg> Informazioni sulla pubblicazione</span>
    <h2 class="h5 mb-2">
        <?php esc_html_e('Contenuti non più soggetti all’obbligo di pubblicazione', 'design_comuni_italia'); ?>
    </h2>
    <p class="mb-0">
        <?php
            printf(
                esc_html__('I contenuti pubblicati prima del %1$d sono visibili esclusivamente agli utenti autorizzati alla consultazione dello storico. Elementi trovati: %2$s.', 'design_comuni_italia'),
                $start_year,
                esc_html(number_format_i18n($archive_count))
            );
        ?>
    </p>
</aside>
