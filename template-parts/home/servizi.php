<?php
global $post;

$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi'); // Recupero i servizi in evidenza

if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) {
?>
<section id="servizi" aria-describedby="servizi-in-evidenzia">
    <div class="section-content">
        <div class="container">
            <div class="row">
                <h2 class="text-black title-xlarge mb-3">Servizi in Evidenza</h2>
            </div>
            <div class="py-4">
                <div class="row g-4">

                <?php
                foreach ($servizi_evidenza as $servizio_id) {

                    $post = get_post($servizio_id);
                    if (!$post) {
                        continue;
                    }

                    setup_postdata($post);

                    // Alias chiaro
                    $servizio    = $post;
                    $servizio_id = $servizio->ID;

                    $typePost = 'servizio';
                    $prefix   = '_dci_' . $typePost . '_';

                    // Recupero categorie
                    $categorie = get_the_terms($servizio_id, 'categorie_servizio');

                    // Descrizione breve
                    $descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $servizio_id);

                    // Recupero date dal meta
                    $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $servizio_id);
                    $data_fine_servizio   = dci_get_meta('data_fine_servizio', $prefix, $servizio_id);

                    // Inizializzo date in modo sicuro
                    $startDate = null;
                    $endDate   = null;

                    if (!empty($data_inizio_servizio)) {
                        $tmp = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                        if ($tmp !== false) {
                            $startDate = $tmp;
                        }
                    }

                    if (!empty($data_fine_servizio)) {
                        $tmp = DateTime::createFromFormat('d/m/Y', $data_fine_servizio);
                        if ($tmp !== false) {
                            $endDate = $tmp;
                        }
                    }

                    $oggi = new DateTime();

                    /*
                     * Stato del servizio:
                     * - Di default NON attivo
                     * - Diventa attivo solo se entrambe le date sono valide
                     *   e la data corrente è compresa tra inizio e fine.
                     */
                    $stato_attivo = true;

                    if ($startDate && $endDate) {
                        $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
                    }

                    /*
                     * Stato forzato tramite checkbox:
                     * se il valore è vuoto, 0, "0" o "false" → servizio non attivo
                     */
                    $checkbox_stato = get_post_meta($servizio_id, '_dci_servizio_stato', true);
                    if (empty($checkbox_stato) || $checkbox_stato === 'false' || $checkbox_stato === '0') {
                        $stato_attivo = false;
                    }
                ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="cmp-card-latest-messages card-wrapper">
                            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">

                                <span class="visually-hidden">Categoria:</span>
                                <div class="card-header border-0 p-0">
                                    <?php 
                                    if (is_array($categorie) && count($categorie) > 0) {
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
                                           href="<?php echo esc_url(get_permalink($servizio_id)); ?>"
                                           data-element="service-link">
                                            <?php echo esc_html($servizio->post_title); ?>
                                        </a>
                                    </h3>

                                    <!-- STATO E PERIODO -->
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

                                    <!-- DESCRIZIONE BREVE -->
                                    <p class="text-paragraph">
                                        <?php echo esc_html($descrizione_breve); ?>
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php
                }
                wp_reset_postdata();
                ?>

                </div>
            </div>
        </div>
    </div>
</section>
<?php } ?>
