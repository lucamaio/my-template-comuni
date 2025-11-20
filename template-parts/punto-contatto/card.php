<?php
global $pc_id;
$prefix = '_dci_punto_contatto_';

$full_contatto = dci_get_full_punto_contatto($pc_id);
$contatto = get_post($pc_id);
$voci = dci_get_meta('voci', $prefix, $pc_id);

$other_contacts = array(
    'linkedin',
    'skype',
    'telegram',
    'twitter',
    'whatsapp'
);
// Verifica se il titolo del contatto è non vuoto
if (isset($contatto->post_title) && strlen($contatto->post_title) > 0):
?>

<div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
    <?php
    // Controlla il tipo di contatto per decidere quale icona mostrare
    if (isset($full_contatto['email']) && is_array($full_contatto['email']) && count($full_contatto['email'])):
        // Icona per email
        echo '<svg class="icon" aria-hidden="true"><use xlink:href="#it-mail"></use></svg>';
    elseif (isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count($full_contatto['pec'])):
        // Icona per PEC
        echo '<svg class="icon pec-icon" aria-hidden="true"><use xlink:href="#it-mail"></use></svg>';

    elseif (isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count($full_contatto['telefono'])):
        // Icona per telefono
        echo '<svg class="icon" aria-hidden="true"><use xlink:href="#it-telephone"></use></svg>';
    elseif (isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count($full_contatto['indirizzo'])):
        // Icona per indirizzo
        echo '<svg class="icon" aria-hidden="true"><use xlink:href="#it-map-marker-circle"></use></svg>';
    elseif (isset($full_contatto['url']) && is_array($full_contatto['url']) && count($full_contatto['url'])):
        // Icona per indirizzo
        echo '<svg class="icon" aria-hidden="true"><use xlink:href="#it-map-marker-circle"></use></svg>';        
    endif;
    ?>


    
    <div class="card-body pe-3">


        
        <h5 class="card-title">
            <?php echo $contatto->post_title; ?>
        </h5>
        <div class="card-text">
            
            <?php if (array_key_exists('indirizzo', $full_contatto) && is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) )   
                 {
                echo '<div class="mb-3"><p>';
                foreach ($full_contatto['indirizzo'] as $value) {        
                    if($value) {                         
                        echo $value; }
                    echo '</p>';
                } 
                echo '</div>';
            } ?>

    
            <?php if (array_key_exists('telefono', $full_contatto) && is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                foreach ($full_contatto['telefono'] as $value) {
                   ?>
                    <p>
                        Telefono: 
                        <a 
                        target="_blank" 
                        aria-label="contatta telefonicamente tramite il numero <?php echo $value; ?>" 
                        title="chiama <?php echo $value; ?>" 
                        href="tel:<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
                    <?php
                }
            } ?>
            <?php if (array_key_exists('url', $full_contatto) && is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
                foreach ($full_contatto['url'] as $value) { ?>
                    <p>
                        Collegamento web:
                        <a 
                        target="_blank" 
                        aria-label="scopri di pi첫 su   <?php echo $value; ?> - link esterno - apertura nuova scheda" 
                        title="vai sul sito   <?php echo $value; ?>" 
                        href="<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
               <?php }
            } ?>
          <?php if (array_key_exists('email', $full_contatto) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                foreach ($full_contatto['email'] as $value) { ?>
                    <p>
                        Indirizzo Email:
                        <a  
                        target="_blank" 
                        aria-label="invia un'email a <?php echo $value; ?>"
                        title="invia un'email a <?php echo $value; ?>" 
                        href="mailto:<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
               <?php }
            } ?>
            <?php if (array_key_exists('pec', $full_contatto) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                foreach ($full_contatto['pec'] as $value) { ?>  
                        <p>
                        Indirizzo Pec:
                        <a  
                        target="_blank" 
                        aria-label="invia un'email a <?php echo $value; ?>"
                        title="invia un'email a <?php echo $value; ?>" 
                        href="mailto:<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
               <?php }
            } ?>

              <?php foreach ($other_contacts as $type) {
                if (array_key_exists($type, $full_contatto) &&  is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                    foreach ($full_contatto[$type] as $value) {
                        echo '<p>Altro: '.$type.': '.$value.'</p>';
                    }
                } 

    
            } ?>     
  
        </div>
    </div>
</div><p></p>
<?php
endif;
?>
