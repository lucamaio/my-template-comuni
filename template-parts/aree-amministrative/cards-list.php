<?php 
    global $posts;

        $description = dci_get_meta('descrizione_breve');

        $incarichi = dci_get_meta('incarichi');

        if($incarichi) {
            $incarico = $incarichi[0];
        }

        $tipo = get_the_terms($post, 'tipi_unita_organizzativa')[0];

        //Var_dump($tipo);

        $prefix = '_dci_incarico_';
        $nome_incarico = dci_get_meta('nome', $prefix, $tipo->term_id);

        //var_dump($nome_incarico);
        
        $prefix = '_dci_unita_organizzativa_';
        // Diciarazzione variabile $img
        $img = dci_get_meta('immagine', $prefix);
        
        if($tipo->slug == "area") {
        if($img) {
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="img-wrapper">
                <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                </div>
                <div class="col-8 order-1 order-md-2">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                       <h3 class="h5 card-title"><svg class="icon icon-primary icon-sm me-2" aria-hidden="true"><use href="#it-pa"></use></svg><?php echo the_title(); ?></h3>
                    </a>
                    <p class="card-text d-none d-md-block">
                        <?php echo $description; ?>
                    </p>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <?php } else { ?>

    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                              <svg class="icon icon-primary icon-sm me-2" aria-hidden="true"><use href="#it-pa"></use></svg>
                                <h3 class="h5 card-title"><?php echo the_title(); ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $description; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } } ?>
