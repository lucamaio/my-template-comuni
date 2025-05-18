<?php
global $luoghi, $luogo;
$prefix = '_dci_luogo_';
$arr_luoghi = array();


   $post_title = $luogo->post_title;
   $permalink = get_permalink($luogo);





$c=0;
foreach ($luoghi as $luogo) {
    $posizione_gps = dci_get_meta("posizione_gps", $prefix, $luogo->ID);
    if ($posizione_gps && $posizione_gps["lat"] && $posizione_gps["lng"]) {
        $indirizzo = dci_get_meta("indirizzo", $prefix, $luogo->ID);

        $quartiere = dci_get_meta("quartiere", $prefix, $luogo->ID);
        $circoscrizione = dci_get_meta("circoscrizione", $prefix, $luogo->ID);
	    
        $arr_luoghi[$c]["post_title"] = $luogo->post_title;
        $arr_luoghi[$c]["permalink"] = get_permalink($luogo);
        $arr_luoghi[$c]["gps"] = $posizione_gps;
        $arr_luoghi[$c]["indirizzo"] = $indirizzo;
        $c++;
    }
}

if($c) { ?>
<div class="card card-bg rounded mt-4 no-after">

    
 <div class="card-header">
            <?php 
                  if(isset($indirizzo) && $indirizzo != ""){ ?>
			<div class="d-block"><?php echo $indirizzo; ?></div>
		<?php } ?>

        <?php if($quartiere || $circoscrizione) { ?>
            <small class="d-block"><?php echo $quartiere; ?> <?php if($quartiere && $circoscrizione) { echo "-"; } ?> <?php echo $circoscrizione; ?></small>
        <?php } ?>

        <?php if(isset($cap) && $cap != ""){ ?>
			<div class="location-title">
			    <span><?php _e( "CAP", "design_comuni_italia" ); ?></span>
            </div>
            <div class="location-content">
                <p><?php echo $cap; ?></p>
            </div>
		<?php } ?>
	</div><!-- /card-header -->

    <div class="card-body p-0">
            <div class="map-wrapper">
                <div class="map" id="map_all"></div>
            </div>

    <script>
        jQuery(function() {
            var mymap = L.map('map_all', {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([<?php echo $arr_luoghi[0]["gps"]["lat"]; ?>, <?php echo $arr_luoghi[0]["gps"]["lng"]; ?>], 13);

            let marker;
            <?php foreach ($arr_luoghi as $marker){ ?>

            marker = L.marker([<?php echo $marker["gps"]["lat"]; ?>, <?php echo $marker["gps"]["lng"]; ?>, { title: '<?php echo addslashes($marker["post_title"]); ?>'}]).addTo(mymap);
            marker.bindPopup('<b><a href="<?php echo $marker["permalink"] ?>"><?php echo addslashes($marker["post_title"]); ?></a></b><br><?php echo addslashes(preg_replace("/[\\n\\r]+/", " ", $marker["indirizzo"])); ?>');

            <?php } ?>

            // add the OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '',
                maxZoom: 18,
            }).addTo(mymap);

            var arrayOfMarkers = [<?php foreach ($arr_luoghi as $marker){ ?> [ <?php echo $marker["gps"]["lat"]; ?>, <?php echo $marker["gps"]["lng"]; ?>], <?php } ?>];
            var bounds = new L.LatLngBounds(arrayOfMarkers);
            mymap.fitBounds(bounds);
        });
    </script>
<?php } ?> 
    <div class="card-footer py-3 mb-0">
        <svg class="icon">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#it-map-marker"></use>
        </svg>
        <a title="Indicazioni stradali di <?php echo addslashes($indirizzo); ?>" target="_black" href="https://www.google.com/maps/dir/<?php echo $posizione_gps["lat"]; ?>,<?php echo $posizione_gps["lng"]; ?>/@<?php echo $posizione_gps["lat"]; ?>,<?php echo $posizione_gps["lng"]; ?>,15z?hl=it">Indicazioni stradali su Google Maps</a>
    </div>
</div>
</div>
