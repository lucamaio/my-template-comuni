<?php
// Galleria impostata in Configurazione > Vivere l'ente
global $sfondo_grigio, $gallery;
$sfondo_grigio = $sfondo_grigio ?? true;

$gallery = dci_get_option('gallery_items', 'galleria') ?: [];
$nome_sezione = dci_get_option('gallery_title', 'galleria') ?: "";

// Numero di elementi per pagina
$items_per_page = 6;

// Pagina corrente (se non definita, è la pagina 1)
$current_page = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Calcola l'indice iniziale e finale degli elementi da visualizzare
$offset = ($current_page - 1) * $items_per_page;
$total_items = count($gallery);

// Suddividi la galleria in pagine
$gallery_page = array_slice($gallery, $offset, $items_per_page);

// Calcola il numero totale di pagine
$total_pages = ceil($total_items / $items_per_page);
?>

<?php if (count($gallery) > 0) { ?>
    <section id="galleria">
        <?php if ($nome_sezione) { ?>
            <div class="section <?= $sfondo_grigio ? 'section-muted' : '' ?> px-lg-5 pt-0 py-0">
                <div class="container">
                    <div class="row row-title pt-3 pt-lg-60 pb-3">
                        <div class="col-12 d-lg-flex justify-content-between">
                            <h2 class="mb-lg-0"><?= $nome_sezione ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <div class="section <?= $sfondo_grigio ? 'section-muted' : '' ?> px-0 pt-0 ">
            <div class="container">
                <div class="row">
                    <?php
                    // Visualizza solo gli elementi della pagina corrente
                    foreach ($gallery_page as $item) {
                        ?>
                        <div class="col-md-4 mb-4"> <!-- Assicurati che ogni immagine sia in una colonna -->
                            <div class="card"> <!-- Aggiungi un contenitore card se necessario -->
                                <?php dci_get_img($item, 'galleria-img'); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1) { ?>
                    <!-- Paginazione -->
                    <nav aria-label="Navigazione della galleria">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $current_page - 1 ?>" aria-label="Precedente">
                                        <span aria-hidden="true">&laquo;</span>
                                        <span class="sr-only">Precedente</span>
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php } ?>
                            
                            <?php if ($current_page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $current_page + 1 ?>" aria-label="Successivo">
                                        <span aria-hidden="true">&raquo;</span>
                                        <span class="sr-only">Successivo</span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            </div>
        </div>
    </section>
<?php } ?>


