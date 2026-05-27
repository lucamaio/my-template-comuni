<?php
global $post;

// Recupera i dati dal feed (con cache, se disponibile)
$data = function_exists('dci_get_maggioli_services_data') ? dci_get_maggioli_services_data() : array();

// Controlla se il JSON è stato decodificato correttamente
if (!is_array($data)) {
    echo "Errore durante la decodifica del JSON.";
    return;
}

// Definisci la variabile di ricerca, usando il parametro di WordPress 's'
$search_text = isset($_GET['s']) ? $_GET['s'] : '';

// Filtra i dati in base al testo di ricerca nel titolo (campo "nome")
if (!empty($search_text)) {
    $filtered_data = array_filter($data, function($item) use ($search_text) {
        return stripos($item['nome'], $search_text) !== false;
    });
} else {
    // Se non c'è testo di ricerca, mostra tutti i risultati
    $filtered_data = $data;
}

// Conta il numero di risultati trovati
$total_services = count($filtered_data);

// Visualizza il totale dei servizi trovati
echo "<p><strong>Servizi aggiuntivi trovati: $total_services</strong></p>";

// Se non ci sono risultati, mostra un messaggio
if (empty($filtered_data)) {
    echo "Nessun servizio aggiuntivo trovato per '$search_text'.";
    return;
}

// Cicla attraverso i risultati filtrati e visualizzali
foreach ($filtered_data as $item) {
    ?>
    <div class="cmp-card-latest-messages mb-3 mb-30" data-bs-toggle="modal" data-bs-target="#">
        <div class="card shadow-sm px-4 pt-4 pb-4 rounded">
            <div class="card-header border-0 p-0">
                <span class="title-xsmall-bold mb-2 category text-uppercase text-primary">
                    Servizio
                </span>
            </div>
            <div class="card-body p-0 my-2">
                <h3 class="green-title-big t-primary mb-8">
                    <a class="text-decoration-none" href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" data-element="service-link">
                        <?php echo htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h3>
                <p class="text-paragraph">
                    <?php echo htmlspecialchars($item['descrizione_breve'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}
?>

