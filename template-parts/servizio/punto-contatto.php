<?php
global $pc_id;
$prefix = '_dci_punto_contatto_';

$full_contatto = dci_get_full_punto_contatto($pc_id);
$contatto = get_post($pc_id);
$voci = dci_get_meta('voci', $prefix, $pc_id);

$other_contacts = array(
    'linkedin',
    'pec',
    'skype',
    'telegram',
    'twitter',
    'whatsapp'
);



// Verifica se il titolo del contatto è non vuoto
if (isset($contatto->post_title) && strlen($contatto->post_title) > 0):
?>

<div class="card card-teaser shadow mt-3 rounded">
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

    <div class="card-body">
        <h3 class="card-title h5">
            <span>
               <font color="black"> <?php echo $contatto->post_title; ?></font>
            </span>
        </h3>
        <div class="card-text">
            <!-- Controlla se è un indirizzo -->
            <?php if (isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count($full_contatto['indirizzo'])): ?>
                <?php foreach ($full_contatto['indirizzo'] as $value): ?>
                    <a href="https://www.google.com/maps/place/<?php echo $value; ?>" target="_blank"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Controlla se è un telefono -->
            <?php if (isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count($full_contatto['telefono'])): ?>
                <?php foreach ($full_contatto['telefono'] as $value): ?>
                    <a href="tel:<?php echo $value;?>"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Controlla se è un url -->
            <?php if (isset($full_contatto['url']) && is_array($full_contatto['url']) && count($full_contatto['url'])): ?>
                <?php foreach ($full_contatto['url'] as $value): ?>
                    <p>
                        <a 
                            target="_blank" 
                            aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
                            title="vai sul sito <?php echo $value; ?>" 
                            href="<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- controlla se è una email -->
            <?php if (isset($full_contatto['email']) && is_array($full_contatto['email']) && count($full_contatto['email'])): ?>
                <?php foreach ($full_contatto['email'] as $value): ?>
                    <a  
                        target="_blank" 
                        aria-label="invia un'email a <?php echo $value; ?>"
                        title="invia un'email a <?php echo $value; ?>" 
                        href="mailto:<?php echo $value; ?>">
                        <?php echo $value; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Gestione degli altri contatti -->
            <?php foreach ($other_contacts as $type): ?>
                <?php if (isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count($full_contatto[$type])): ?>
                    <?php foreach ($full_contatto[$type] as $value): ?>
                        <?php if ($type === 'pec'): ?>
                            <p><a href="mailto:<?php echo $value; ?>"><?php echo $value; ?></a></p>
                        <?php else: ?>
                            <!-- Altrimenti, mostra il tipo e il valore -->
                            <p><?php echo $type; ?>: <?php echo $value; ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
endif;
?>

