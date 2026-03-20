<?php
global $post, $posts;

$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?>

<div class="container">
<div class="row">

<?php if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>

<div class="col-12">

<div class="row">
<h2 class="text-black title-xlarge mb-3">Servizi in evidenza</h2>
</div>

<div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">

<!-- ========================= -->
<!-- VERSIONE DESKTOP -->
<!-- ========================= -->

<div class="d-none d-md-block">

<table class="table table-striped table-hover table-soft">

<thead>
<tr>
<th>Servizio</th>
<th>Categoria</th>
<th>Periodo</th>
<th>Stato</th>
</tr>
</thead>

<tbody>

<?php foreach ($servizi_evidenza as $servizio_id) {

$post = get_post($servizio_id);

$prefix = '_dci_servizio_';
$data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $post->ID);
$data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $post->ID);

$startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
$endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;

$oggi = new DateTime();

$stato_attivo = true;

if ($startDate && $endDate && $startDate < $endDate) {
$stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
}

$checkbox_stato = get_post_meta($post->ID, '_dci_servizio_stato', true);

if ($checkbox_stato == 'false') {
$stato_attivo = false;
}

$categorie = get_the_terms($post->ID, 'categorie_servizio');

$categoria = is_array($categorie)
? implode(", ", array_map(function($cat) {
return $cat->name;
}, $categorie))
: 'N/D';
?>

<tr>

<td>
<a class="text-decoration-none"
href="<?php echo get_permalink($post->ID); ?>">
<?php echo $post->post_title; ?>
</a>
</td>

<td><?php echo $categoria; ?></td>

<td>
<?php
if ($startDate && $endDate) {
echo $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
}
?>
</td>

<td>
<span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
<?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
</span>
</td>

</tr>

<?php } ?>

</tbody>
</table>

</div>


<!-- ========================= -->
<!-- VERSIONE MOBILE -->
<!-- ========================= -->

<div class="d-md-none">

<?php foreach ($servizi_evidenza as $servizio_id) {

$post = get_post($servizio_id);

$prefix = '_dci_servizio_';
$data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $post->ID);
$data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $post->ID);

$startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
$endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;

$oggi = new DateTime();

$stato_attivo = true;

if ($startDate && $endDate && $startDate < $endDate) {
$stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
}

$checkbox_stato = get_post_meta($post->ID, '_dci_servizio_stato', true);

if ($checkbox_stato == 'false') {
$stato_attivo = false;
}

$categorie = get_the_terms($post->ID, 'categorie_servizio');

$categoria = is_array($categorie)
? implode(", ", array_map(function($cat) {
return $cat->name;
}, $categorie))
: 'N/D';
?>

<div class="border rounded p-3 mb-3">

<div class="fw-bold mb-2">
<a class="text-decoration-none"
href="<?php echo get_permalink($post->ID); ?>">
<?php echo $post->post_title; ?>
</a>
</div>

<div class="small mb-1">
<strong>Categoria:</strong> <?php echo $categoria; ?>
</div>

<div class="small mb-1">
<strong>Periodo:</strong>
<?php
if ($startDate && $endDate) {
echo $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
}
?>
</div>

<div class="small">

<span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
<?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
</span>

</div>

</div>

<?php } ?>

</div>

</div>
</div>

<?php } ?>

</div>
</div>

<br>
