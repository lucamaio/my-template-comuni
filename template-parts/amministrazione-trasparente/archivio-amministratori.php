<?php
/**
 * Divisore per i contenuti storici visibili agli utenti autorizzati.
 *
 * @var array $args {
 *     @type int $start_year Primo anno visibile agli utenti finali.
 *     @type int $count      Numero di contenuti storici trovati.
 * }
 */

$start_year = isset($args['start_year']) ? absint($args['start_year']) : (int) wp_date('Y') - 5;
$archive_count = isset($args['count']) ? absint($args['count']) : 0;

?>
<aside class="alert alert-warning mt-5 mb-4" role="note">
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
