<?php
/**
 * Richiamo all'informativa generale sulla durata della pubblicazione.
 * Questo componente non controlla i permessi e non contiene dati dello storico.
 * Quando incluso da personale-avviso-storico.php, eredita la visibilita riservata
 * del chiamante; il testo generale in informativa-durata.php e usato anche negli
 * avvisi pubblici degli elenchi vuoti.
 */
get_template_part('template-parts/amministrazione-trasparente/informativa-durata', null, ['post_type' => $args['post_type'] ?? '']);
