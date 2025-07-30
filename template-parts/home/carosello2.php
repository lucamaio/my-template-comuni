<?php 
// Galleria impostata in Configurazione > Vivere l'ente
global $gallery;
$gallery = dci_get_option('immagine', 'homepage') ?: [];
?>

<?php if (count($gallery) > 0) : ?>
<section id="carosello" class="carosello-section">
	<?php 
	if (count($gallery) === 1) {
		$url_immagine = array_values($gallery)[0];
		?>
		<div class="bg-image">
			<?php dci_get_img($url_immagine, 'immagine-home'); ?>
		</div>
		<style>
			.bg-image img {
				width: 100%;
				height: 450px;
				object-fit: cover;
				object-position: center;
			}
		</style>
	<?php 
	} else {
		get_template_part("template-parts/single/gallery-carosello"); 
	}
	?>
</section>
<?php endif; ?>
