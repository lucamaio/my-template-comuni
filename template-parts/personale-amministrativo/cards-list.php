<?php 
    global $posts;

        $description = dci_get_meta('descrizione_breve');

        $incarichi = dci_get_meta('incarichi');

        $incarico = is_array($incarichi) ? $incarichi[0]: null;

    
        $tipo = is_array(get_the_terms($incarico, 'tipi_incarico')) ? get_the_terms($incarico, 'tipi_incarico')[0] : null;

        //var_dump($tipo);

        $prefix = '_dci_incarico_';
        $nome_incarico = $tipo != NULL ? dci_get_meta('nome', $prefix, $tipo->term_id) : "";

        $tipo_name = $tipo != NULL ? $tipo->name : "";

        //var_dump($nome_incarico);
        $img = dci_get_meta('foto');
        if($tipo_name != "politico") {
        if ($img) {
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="col-4 order-2 order-md-1">
                <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                </div>
                <div class="col-8 order-1 order-md-2">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                                    <?php
                                    // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                                    if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
                                        $titolo = ucfirst(strtolower($post->post_title));
                                    } else {
                                        $titolo = $post->post_title;
                                    }
                                    ?>                       
                        <h3 class="h5 card-title"><?php echo $titolo; ?></h3>
                    </a>
                    <p class="card-text d-none d-md-block">
                                     <?php
                                    // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                                    if (preg_match('/[A-Z]{5,}/', $description)) {
                                        $descrizione = ucfirst(strtolower($description));
                                    } else {
                                        $descrizione = $description;
                                    }
                                    ?>    
                        <?php echo $descrizione; ?>
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
                                 <?php
                                    // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                                    if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
                                        $titolo = ucfirst(strtolower($post->post_title));
                                    } else {
                                        $titolo = $post->post_title;
                                    }
                                    ?>                       
                                <h3 class="h5 card-title"><?php echo $titolo; ?></h3>
                             
                            </a>
                            <p class="card-text d-none d-md-block">                             
                                     <?php
                                    // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                                    if (preg_match('/[A-Z]{5,}/', $description)) {
                                        $descrizione = ucfirst(strtolower($description));
                                    } else {
                                        $descrizione = $description;
                                    }
                                    ?>    
                                 <?php echo $descrizione; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } }?>
