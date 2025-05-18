<?php
$pagine = dci_get_children_pages("Vivere Il Comune");
?>
<div class="container py-5" id="argomento">
   <h2 class="title-xxlarge mb-4">Esplora per categoria</h2>
   <div class="row g-4">
      <?php foreach ($pagine as $argomento) { ?>
      <div class="col-md-6 col-xl-4">
         <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
            <div class="card shadow-sm rounded">
               <div class="card-body">
                  <a class="text-decoration-none" href="<?php echo get_permalink($argomento["id"]); ?>">
                     <h3 class="card-title t-primary title-xlarge"><?php echo $argomento["title"]; ?></h3>
                  </a>
                  <p class="titillium text-paragraph mb-0 description"><?php echo $argomento["description"]; ?>
                  </p>
               </div>
            </div>
         </div>
      </div>
      
   <?php } ?>
   </div>
</div>
