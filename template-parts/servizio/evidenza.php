<?php
global $post, $posts;

//Per selezionare i contenuti in evidenza tramite configurazione
$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?>

<div class="container">
    <div class="row">
        <?php
        if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>
            <div class="col-12">
                <div class="card shadow-sm px-4 pt-4 pb- rounded border border-light">
                    <div class="link-list-wrap">
                        <h3 class="title-large">
                            <span>Servizi in evidenza</span>
                        </h3>
                        <ul class="link-list t-primary">
                            <?php foreach ($servizi_evidenza as $servizio_id) {
                                $post = get_post($servizio_id);
                                ?>
                                <li class="mb-4 mt-4">
                                    <a class="list-item ps-0 title-medium underline" style="text-decoration:none;"
                                        href="<?php echo get_permalink($post->ID); ?>">
                                        <svg class="icon">
                                            <use xlink:href="#it-arrow-right-triangle"></use>
                                        </svg>
                                        <span><?php echo $post->post_title; ?></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<br>