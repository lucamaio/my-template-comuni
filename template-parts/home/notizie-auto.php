<?php
global $the_query, $load_posts, $load_card_type;

// Limite massimo hard per evitare query troppo grandi via parametro GET.
$requested_max_posts = isset($_GET['max_posts']) ? absint($_GET['max_posts']) : 0;

$load_posts = -1;
$load_card_type = 'notizia';
// $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$prefix = '_dci_notizia_';

// Leggo e mi salvo l'opzione nascondi le notizie scadute
$hide_notizie_old = dci_get_option('ck_hide_notizie_old', 'homepage');
$hide_old_enabled = ($hide_notizie_old === 'true');

// Leggo il numero di Notizie
$notizie_home = dci_get_option('numero_notizie_home', 'homepage');
$notizie_home = max(1, (int) $notizie_home);

/**
 * Mantiene abbastanza elementi per popolare la sezione,
 * ma senza allargare troppo la query.
 */
if ($hide_old_enabled) {
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 60) : max($notizie_home * 5, 24);
} else {
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 100) : max($notizie_home * 2, 12); // carico il doppio delle notizie in modo che se c'è ne sono nascoste gli slot non restino vuoti
}

/**
 * Normalizza un valore raw data in timestamp.
 * Supporta:
 * - timestamp numerico
 * - formato d/m/Y
 * - formato Y-m-d
 * - formato Y-m-d H:i:s
 *
 * Restituisce 0 se il valore non è valido.
 */
if (!function_exists('dci_normalize_date_to_timestamp')) {
    function dci_normalize_date_to_timestamp($raw_value) {
        if (is_array($raw_value) || is_object($raw_value)) {
            return 0;
        }

        $raw_value = trim((string) $raw_value);

        if ($raw_value === '') {
            return 0;
        }

        // Timestamp numerico
        if (is_numeric($raw_value)) {
            $timestamp = (int) $raw_value;

            // Se per errore è in millisecondi, lo riduco a secondi
            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            return ($timestamp > 0) ? $timestamp : 0;
        }

        // d/m/Y
        $dt = DateTime::createFromFormat('d/m/Y', $raw_value);
        if ($dt instanceof DateTime) {
            $dt->setTime(0, 0, 0);
            return $dt->getTimestamp();
        }

        // Y-m-d
        $dt = DateTime::createFromFormat('Y-m-d', $raw_value);
        if ($dt instanceof DateTime) {
            $dt->setTime(0, 0, 0);
            return $dt->getTimestamp();
        }

        // Y-m-d H:i:s
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $raw_value);
        if ($dt instanceof DateTime) {
            return $dt->getTimestamp();
        }

        // Fallback generale
        $timestamp = strtotime($raw_value);
        return ($timestamp !== false) ? (int) $timestamp : 0;
    }
}

/**
 * Ritorna il timestamp effettivo di pubblicazione:
 * - usa il meta data_pubblicazione se valido
 * - altrimenti usa la post_date di WordPress
 */
if (!function_exists('dci_get_effective_pubblicazione_timestamp')) {
    function dci_get_effective_pubblicazione_timestamp($post_id, $prefix) {
        $raw_pubblicazione = get_post_meta($post_id, $prefix . 'data_pubblicazione', true);
        $timestamp_pubblicazione = dci_normalize_date_to_timestamp($raw_pubblicazione);

        if ($timestamp_pubblicazione > 0) {
            return $timestamp_pubblicazione;
        }

        $post_timestamp = get_post_timestamp($post_id);
        return $post_timestamp ? (int) $post_timestamp : 0;
    }
}

/**
 * Ritorna il timestamp reale di scadenza:
 * - usa SOLO il meta data_scadenza raw
 * - se manca o non è valido, restituisce 0
 */
if (!function_exists('dci_get_real_scadenza_timestamp')) {
    function dci_get_real_scadenza_timestamp($post_id, $prefix) {
        $raw_scadenza = get_post_meta($post_id, $prefix . 'data_scadenza', true);
        return dci_normalize_date_to_timestamp($raw_scadenza);
    }
}

/**
 * Riduce un timestamp al giorno (00:00:00), utile per confronti "stessa data".
 */
if (!function_exists('dci_normalize_timestamp_to_day')) {
    function dci_normalize_timestamp_to_day($timestamp) {
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return 0;
        }

        return strtotime(date('Y-m-d', (int) $timestamp));
    }
}

/**
 * Query alleggerita:
 * - fields => ids per ridurre memoria
 * - meta cache attiva
 * - ordinamento iniziale per post_date
 *
 * L'ordinamento finale corretto viene fatto in PHP con la data pubblicazione effettiva.
 */
$args = array(
    //'s'                     => $query,
    'post_type'              => 'notizia',
    'post_status'            => 'publish',
    'fields'                 => 'ids',
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'posts_per_page'         => $max_posts,
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
    'cache_results'          => true,
);

$the_query = new WP_Query($args);
$post_ids = $the_query->posts;

if (empty($post_ids)) {
    return;
}

/**
 * Preparo i dati delle notizie con date normalizzate
 */
$posts_data = array();

foreach ($post_ids as $post_id) {
    $pubblicazione_ts = dci_get_effective_pubblicazione_timestamp($post_id, $prefix);
    $scadenza_ts = dci_get_real_scadenza_timestamp($post_id, $prefix);

    $posts_data[] = array(
        'post_id'           => $post_id,
        'pubblicazione_ts'  => $pubblicazione_ts,
        'pubblicazione_day' => dci_normalize_timestamp_to_day($pubblicazione_ts),
        'scadenza_ts'       => $scadenza_ts,
        'scadenza_day'      => dci_normalize_timestamp_to_day($scadenza_ts),
    );
}

/**
 * Ordino realmente per data pubblicazione effettiva DESC
 */
usort($posts_data, function ($a, $b) {
    return $b['pubblicazione_ts'] <=> $a['pubblicazione_ts'];
});

$count = 0;
$oggi = strtotime('today');

foreach ($posts_data as $item) {
    if ($count >= $notizie_home) {
        break;
    }

    $post_id = $item['post_id'];
    $mostra_scheda = true;

    // 1. Verifico se devo nascondere la notizia
    if (dci_get_meta('hide_home', $prefix, $post_id) === 'on') {
        continue;
    }

    // 2. Verifico se devo controllare la data di scadenza
    if ($hide_old_enabled) {
        $pubblicazione_day = $item['pubblicazione_day'];
        $scadenza_day = $item['scadenza_day'];
        

        /**
         * Se la scadenza non esiste davvero -> la notizia è valida
         * Se la scadenza coincide con la pubblicazione -> la considero come "nessuna vera scadenza"
         * Altrimenti controllo se è già passata
         */
        if ($scadenza_day > 0 && $scadenza_day !== $pubblicazione_day) {
            if ($scadenza_day < $oggi) {
                $mostra_scheda = false;
            }
        }
    }

    if (!$mostra_scheda) {
        continue;
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        continue;
    }

    $count++;
    setup_postdata($post);
    ?>
    <div class="col-12 col-md-6 col-lg-4">
        <?php get_template_part('template-parts/home/scheda-evidenza'); ?>
    </div>
    <?php
}

wp_reset_postdata();
?>