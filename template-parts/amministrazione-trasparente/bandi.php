<?php 
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 9;
$query     = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$args = array(
    's'              => $query,
    'post_type'      => 'bando',
    'posts_per_page' => $max_posts,
    'meta_key'       => '_dci_bando_data_inizio',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
);

$the_query = new WP_Query($args);
?>

<main class="container mt-4">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Data Pubblicazione</th>
                    <th>Data Scadenza</th>
                    <th>Oggetto</th>
                    <th>Importo Aggiudicazione</th>
                    <th>Somme Liquidate</th>
                    <th>Struttura Proponente</th>
                    <th>CIG</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($the_query->have_posts()) : ?>
                    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                        <tr>
                            <td>
                                <?php
                                $data_inizio = get_post_meta(get_the_ID(), '_dci_bando_data_inizio', true);
                                echo $data_inizio ? date_i18n('d/m/Y', $data_inizio) : '-';
                                ?>
                            </td>
                            <td>
                                <?php
                                $data_fine = get_post_meta(get_the_ID(), '_dci_bando_data_fine', true);
                                echo $data_fine ? date_i18n('d/m/Y', $data_fine) : '-';
                                ?>
                            </td>
                            <td><?php echo wp_kses_post(get_post_meta(get_the_ID(), '_dci_bando_oggetto', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), '_dci_bando_importo_aggiudicazione', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), '_dci_bando_importo_somme_liquidate', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), '_dci_bando_struttura_proponente', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), '_dci_bando_cig', true)); ?></td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center">Nessun bando trovato.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
