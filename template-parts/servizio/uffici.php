<?php
    $amministrazione = dci_get_related_unita_amministrative();
if ( is_array($amministrazione) && count($amministrazione) ) { ?>
    <div class="container py-5" id="argomento">
        <h2 class="title-xxlarge mb-4">Uffici</h2>
        <div class="row g-4">       
            <?php foreach ($amministrazione as $item) {         
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                  <div class="card shadow-sm rounded">
                    <div class="card-body">
                        <a class="text-decoration-none" href="<?php echo $item['link']; ?>" data-element="news-category-link"><h3 class="card-title t-primary title-xlarge"><?php echo $item['title'];?></h3></a>
                    </div>
                  </div>
                </div>
              </div>
            <?php } ?>
        </div>
    </div>
 <?php } ?> 
