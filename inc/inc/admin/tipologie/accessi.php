<?php
// ================================
// MENU ADMIN - ACCESSI HOMEPAGE AVANZATO
// ================================
function wpc_accessi_admin_menu() {
    add_menu_page(
        'Accessi Homepage',
        'Accessi Homepage',
        'manage_options',
        'wpc-accessi',
        'wpc_accessi_admin_page',
        'dashicons-visibility',
        80
    );
}
add_action('admin_menu', 'wpc_accessi_admin_menu');

// ================================
// PAGINA ADMIN CON SELEZIONE DATA
// ================================
function wpc_accessi_admin_page() {
    if (!current_user_can('manage_options')) return;

    $daily_visits = get_option('wpc_home_daily_visits', array());
    $daily_counts = get_option('wpc_home_daily_counts', array());

    $selected_date = isset($_GET['data']) ? sanitize_text_field($_GET['data']) : date('Y-m-d');
    $visits = $daily_visits[$selected_date] ?? array();
    $count = $daily_counts[$selected_date] ?? 0;

    echo '<div class="wrap"><h1>Accessi Homepage</h1>';

    // Form calendario
    echo '<form method="get" style="margin-bottom:15px;">';
    echo '<input type="hidden" name="page" value="wpc-accessi">';
    echo '<label for="data">Seleziona giorno: </label>';
    echo '<input type="date" id="data" name="data" value="' . esc_attr($selected_date) . '">';
    echo '<input type="submit" class="button button-primary" value="Mostra">';
    echo '</form>';

    echo "<p><strong>Accessi univoci:</strong> $count</p>";

    if (empty($visits)) {
        echo '<p>Nessun accesso registrato in questa data.</p></div>';
        return;
    }

    // Tabella dettagliata
  // Tabella dettagliata
echo '<table class="widefat fixed striped">';
echo '<thead><tr><th>#</th><th>IP</th><th>Ora</th><th>Browser/User Agent</th></tr></thead><tbody>';

$i = 1;
$current_user = wp_get_current_user(); // recupera dati utente loggato
$admin_id = 1; // ID dell'admin principale di default WordPress

foreach ($visits as $v) {
    echo '<tr>';
    echo '<td>'.$i.'</td>';

    // Mostra IP completo solo all'admin principale (ID = 1)
    if ($current_user->ID === $admin_id) {
        $ip_display = $v['ip'];
    } else {
        // Maschera ultimi 3 caratteri dell'IP
        $ip_parts = explode('.', $v['ip']);
        if (count($ip_parts) === 4) {
            $ip_parts[3] = '*** (Nascosto per Privacy)';
            $ip_display = implode('.', $ip_parts);
        } else {
            $ip_display = $v['ip']; // fallback
        }
    }

    echo '<td>'.esc_html($ip_display).'</td>';
    echo '<td>'.esc_html($v['time']).'</td>';
    echo '<td>'.esc_html($v['user_agent']).'</td>';
    echo '</tr>';
    $i++;
}
echo '</tbody></table></div>';

}


