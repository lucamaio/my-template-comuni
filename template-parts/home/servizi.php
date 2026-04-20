<?php
/**
 * Servizi in evidenza - blocco con cache HTML
 */

if (!function_exists('dci_parse_date_to_datetime')) {
    function dci_parse_date_to_datetime($raw_date, DateTimeZone $tz, $end_of_day = false) {
        if (is_array($raw_date) || is_object($raw_date)) {
            return null;
        }

        $raw_date = trim((string) $raw_date);

        if ($raw_date === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('d/m/Y', $raw_date, $tz);
        $errors = DateTime::getLastErrors();

        if (
            !($dt instanceof DateTime) ||
            !is_array($errors) ||
            $errors['warning_count'] > 0 ||
            $errors['error_count'] > 0
        ) {
            return null;
        }

        if ($end_of_day) {
            $dt->setTime(23, 59, 59);
        } else {
            $dt->setTime(0, 0, 0);
        }

        return $dt;
    }
}

$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi'); // Recupero i servizi in evidenza

if (!is_array($servizi_evidenza) || count($servizi_evidenza) === 0) {
    return;
}

/**
 * Normalizzo gli ID ed elimino eventuali duplicati/vuoti
 */
$servizi_evidenza = array_values(array_unique(array_filter(array_map('absint', $servizi_evidenza))));

if (count($servizi_evidenza) === 0) {
    return;
}

$servizi_per_pagina = 3;
$tz                 = wp_timezone();
$oggi               = new DateTime('today', $tz);

/**
 * Cache key:
 * include gli ID selezionati così la cache cambia se cambia la selezione
 */
$cache_key = 'dci_servizi_evidenza_' . md5(wp_json_encode($servizi_evidenza));
$cached_html = get_transient($cache_key);

if ($cached_html !== false) {
    echo $cached_html;
    return;
}

/**
 * Preparo i dati una sola volta, così:
 * - calcolo solo i servizi realmente validi
 * - il totale pagine è corretto
 * - evito lavoro nel rendering
 */
$items = array();

foreach ($servizi_evidenza as $servizio_id) {
    $service_post = get_post($servizio_id);

    if (!$service_post instanceof WP_Post || $service_post->post_status !== 'publish') {
        continue;
    }

    $typePost = 'servizio';
    $prefix   = '_dci_' . $typePost . '_';

    $categorie = get_the_terms($servizio_id, 'categorie_servizio');
    if (!is_array($categorie) || is_wp_error($categorie)) {
        $categorie = array();
    }

    $descrizione_breve    = dci_get_meta('descrizione_breve', $prefix, $servizio_id);
    $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $servizio_id);
    $data_fine_servizio   = dci_get_meta('data_fine_servizio', $prefix, $servizio_id);

    $startDate = dci_parse_date_to_datetime($data_inizio_servizio, $tz, false);
    $endDate   = dci_parse_date_to_datetime($data_fine_servizio, $tz, true);

    /*
     * Stato del servizio:
     * - Di default attivo
     * - Se entrambe le date sono valide, è attivo solo se oggi rientra nell'intervallo
     * - Il checkbox può forzare lo stato a non attivo
     */
    $stato_attivo = true;

    if ($startDate && $endDate) {
        $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
    }

    $checkbox_stato = get_post_meta($servizio_id, '_dci_servizio_stato', true);
    if (empty($checkbox_stato) || $checkbox_stato === 'false' || $checkbox_stato === '0') {
        $stato_attivo = false;
    }

    $items[] = array(
        'post'               => $service_post,
        'id'                 => $servizio_id,
        'title'              => $service_post->post_title,
        'permalink'          => get_permalink($servizio_id),
        'categorie'          => $categorie,
        'descrizione_breve'  => $descrizione_breve,
        'startDate'          => $startDate,
        'endDate'            => $endDate,
        'stato_attivo'       => $stato_attivo,
    );
}

if (count($items) === 0) {
    return;
}

$totale_servizi = count($items);
$totale_pagine  = (int) ceil($totale_servizi / $servizi_per_pagina);

ob_start();
?>
<section id="servizi" aria-describedby="servizi-in-evidenzia">
    <div class="section-content">
        <div class="container">
            <div class="row">
                <h2 class="text-black title-xlarge mb-3">Servizi in Evidenza</h2>
            </div>

            <div class="py-4">
                <div class="row g-4" id="servizi-evidenza-wrapper">

                <?php
                // Loop per mostrare i servizi
                foreach ($items as $indice => $item) :
                    $service_post       = $item['post'];
                    $servizio_id        = $item['id'];
                    $categorie          = $item['categorie'];
                    $descrizione_breve  = $item['descrizione_breve'];
                    $startDate          = $item['startDate'];
                    $endDate            = $item['endDate'];
                    $stato_attivo       = $item['stato_attivo'];
                    $pagina_corrente    = (int) floor($indice / $servizi_per_pagina) + 1;

                    setup_postdata($service_post);
                ?>
                    <div class="col-12 col-sm-6 col-lg-4 servizio-paginato"
                         data-page="<?php echo esc_attr($pagina_corrente); ?>"
                         <?php echo ($pagina_corrente !== 1) ? 'style="display:none;"' : ''; ?>>
                        <div class="cmp-card-latest-messages card-wrapper h-100">
                            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light h-100">

                                <span class="visually-hidden">Categoria:</span>
                                <div class="card-header border-0 p-0">
                                    <?php
                                    if (count($categorie) > 0) {
                                        $count = 1;
                                        foreach ($categorie as $categoria) {
                                            echo $count === 1 ? '' : ' - ';
                                            echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="'
                                                . esc_url(get_term_link($categoria->term_id)) . '">';
                                            echo esc_html($categoria->name);
                                            echo '</a>';
                                            $count++;
                                        }
                                    }
                                    ?>
                                </div>

                                <div class="card-body p-0 my-2">
                                    <h3 class="green-title-big t-primary mb-8">
                                        <a class="text-decoration-none"
                                           href="<?php echo esc_url($item['permalink']); ?>"
                                           data-element="service-link">
                                            <?php echo esc_html($item['title']); ?>
                                        </a>
                                    </h3>

                                    <div class="mb-2">
                                        <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                                            <?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
                                        </span>

                                        <?php if ($startDate && $endDate) { ?>
                                            <small class="ms-2 text-muted">
                                                (<?php
                                                    echo esc_html($startDate->format('d/m/Y')) .
                                                         ' - ' .
                                                         esc_html($endDate->format('d/m/Y'));
                                                ?>)
                                            </small>
                                        <?php } ?>
                                    </div>

                                    <p class="text-paragraph">
                                        <?php echo esc_html($descrizione_breve); ?>
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; wp_reset_postdata(); ?>

                </div>

                <?php if ($totale_pagine > 1) : ?>
                    <div class="servizi-pagination d-flex justify-content-center gap-2 mt-4 t-primary" id="servizi-pagination">
                        <?php for ($i = 1; $i <= $totale_pagine; $i++) : ?>
                            <button type="button"
                                    class="servizi-dot <?php echo ($i === 1) ? 'active' : ''; ?>"
                                    data-page="<?php echo esc_attr($i); ?>"
                                    aria-label="Vai alla pagina <?php echo esc_attr($i); ?>">
                            </button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($totale_pagine > 1) : ?>
    <style>
       .servizi-pagination {
            color: inherit;
        }

        .servizi-dot {
            width: 12px;
            height: 12px;
            padding: 0;
            border-radius: 50%;
            border: 1px solid currentColor;
            background-color: transparent;
            cursor: pointer;
            transition: all 0.25s ease;
            opacity: 0.6;
        }

        .servizi-dot:hover,
        .servizi-dot:focus {
            opacity: 1;
            transform: scale(1.1);
            outline: none;
        }

        .servizi-dot.active {
            background-color: currentColor;
            border-color: currentColor;
            opacity: 1;
            transform: scale(1.15);
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dots  = document.querySelectorAll('#servizi-pagination .servizi-dot');
        const items = document.querySelectorAll('#servizi-evidenza-wrapper .servizio-paginato');

        if (!dots.length || !items.length) {
            return;
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const pagina = this.getAttribute('data-page');

                items.forEach(function (item) {
                    item.style.display = (item.getAttribute('data-page') === pagina) ? '' : 'none';
                });

                dots.forEach(function (d) {
                    d.classList.remove('active');
                });

                this.classList.add('active');
            });
        });
    });
    </script>
<?php endif; ?>
<?php
$html = ob_get_clean();

/**
 * Cache breve per alleggerire il rendering della home.
 * Puoi portarla a 15 minuti se i contenuti cambiano raramente.
 */
set_transient($cache_key, $html, 10 * MINUTE_IN_SECONDS);

echo $html;
?>