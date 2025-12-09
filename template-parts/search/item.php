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
            // Recupero del nome del gruppo
            $post_type = dci_get_group_name($post->post_type);

            if ($post_type) {

                // Tipologie escluse dalla visualizzazione del gruppo
                $excluded = ['Sito Tematico', 'Progetto PNRR', 'Consiglio Comunale', 'OSL'];

                if (!in_array($post_type, $excluded)) {
                    // Sistemo il link in base al tipo di post in modo che le pagine principali siano ragiungibili
                    switch ($post->post_type) {

                        case 'evento':
                            $link = home_url("vivere-il-comune/eventi");
                            $testo = $post_type . " / Eventi";
                            break;

                        case 'luogo':
                            $link = home_url("vivere-il-comune/luoghi");
                            $testo = $post_type . " / Luoghi";
                            break;
                        case 'documento_pubblico':
                            $link = home_url("amministrazione/documenti-e-dati/");
                            $testo = $post_type . " / Documenti Pubblici";
                            break;
                        case 'dataset':
                            $link = home_url("/dataset");
                            $testo = $post_type . " / Dataset";
                            break;
                        default:
                            $group = dci_get_group($post->post_type);
                            $link = get_permalink(get_page_by_path($group));
                            $testo = $post_type;
                            break;
                    }
                    ?>
                    
                    <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase"
                    href="<?php echo esc_url($link); ?>">
                        <?= esc_html($testo) ?>
                    </a>

            <?php
            }else{
                    // Sistemo il link per i tipi di post senza pagine principali
                    $link='#';
                    switch ($post->post_type) {
                        case 'sito_tematico':
                            $link =  home_url("sito_tematico");
                            break;
                        case 'progetto':
                             $link =  home_url("progetti");
                            break;
                        case 'consiglio':
                            $link =  home_url("elenco-consigli-comunali");
                            break;
                        case 'commissario':
                            $link =  home_url("amministrazione/commissario-osl");
                            break;
                    }
                    if($link != '#'){?>
                        <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="<?php echo $link; ?>">
                            <?= $post_type ?>				
                        </a>
                    <?php }else{
                    ?>                    
                        <span class="title-xsmall-bold mb-2 category text-uppercase text-secondary">
                            <?= $post_type ?>
                        </span>
                    <?php } ?>
                <?php
                }
            } else { ?>
                <span class="title-xsmall-bold mb-2 category text-uppercase text-secondary">Pagina</span>
            <?php
            }
            // Mostra le categorie dopo il nome del gruppo separato da "/"
            if (isset($categorie) && is_array($categorie) && count($categorie)) {
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
            <?php if(isset($descrizione) && $descrizione != '' && $descrizione != null){?>
                <p class="text-paragraph">
                    <?php echo $descrizione; ?>
                </p>
            <?php } ?>
            

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

