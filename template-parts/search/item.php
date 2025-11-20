<?php
global $post;

$descrizione = dci_get_meta('descrizione_breve') ?: dci_get_meta('_dci_page_descrizione');

// Verifica se il post è un "servizio"
$is_servizio = ($post->post_type === 'servizio');

// Recupera le categorie e gli argomenti se è un servizio
if ($is_servizio) {
    $prefix = '_dci_servizio_';
    $categorie = get_the_terms($post->ID, 'categorie_servizio');
    $argomenti = get_the_terms($post->ID, 'post_tag'); // Argomenti (tag)

    $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $post->ID);
    $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $post->ID);

    // Converte le date in oggetti DateTime
    $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
    $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
    $oggi = new DateTime();

    // Calcola se il servizio è attivo
    $stato_attivo = true;
    if ($startDate && $endDate && $startDate < $endDate) {
        $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
    }
}
?>

<div class="cmp-card-latest-messages mb-3 mb-30" data-bs-toggle="modal" data-bs-target="#">
    <div class="card shadow-sm px-4 pt-4 pb-4 rounded">
        <div class="card-header border-0 p-0">
            <?php
            if ($post_type = dci_get_group_name($post->post_type)) {
            ?>
                <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="<?php echo get_permalink(get_page_by_path(dci_get_group($post->post_type))); ?>">
                    <?= $post_type ?>				
                </a>
            <?php
            } else {
            ?>
                <span class="title-xsmall-bold mb-2 category text-uppercase text-primary">Pagina</span>
            <?php
            }

            // Mostra le categorie dopo il nome del gruppo separato da "/"
            if (is_array($categorie) && count($categorie)) {
                echo ' / ';
                $count = 1;
                foreach ($categorie as $categoria) {
                    echo $count == 1 ? '' : ' - ';
                    echo '<a class="text-decoration-none category text-uppercase category-name-small" href="' . get_term_link($categoria->term_id) . '">';
                    echo $categoria->name;
                    echo '</a>';
                    ++$count;
                }
            }
            ?>
        </div>
        <div class="card-body p-0 my-2">
            <h3 class="green-title-big t-primary mb-8">
                <a class="text-decoration-none" href="<?= ($post->post_type == 'sito_tematico') ? dci_get_meta('link') : get_permalink() ?>" data-element="service-link"><?php echo the_title(); ?></a>
            </h3>
            <p class="text-paragraph">
                <?php echo $descrizione; ?>
            </p>

            <?php if ($is_servizio): ?>
              
                <!-- Mostra badge di stato per il servizio -->
                <div class="mt-2">
                    <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
                    </span>
                </div>

                <!-- Mostra periodo di validità -->
                <?php if ($startDate && $endDate) { ?>
                    <div class="service-period mt-1">
                        <small><strong>Periodo:</strong> <?php echo $startDate->format('d/m/Y'); ?> - <?php echo $endDate->format('d/m/Y'); ?></small>
                    </div>
                <?php } ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- CSS personalizzato per la categoria con font più piccolo -->
<style>
    .category-name-small {
        font-size: 0.85em; /* Font più piccolo per il nome della categoria */
    }
</style>

