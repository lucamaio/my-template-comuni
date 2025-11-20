<?php
global $obj;
$obj = get_queried_object();

if ($obj->taxonomy == "categorie_servizio")
	get_template_part("archive-servizio");
else if ( $obj->post_type=="documento_pubblico" ||  $obj->taxonomy == "tipi_documento" || $obj->taxonomy == "tipi_doc_albo_pretorio"){
	get_template_part( "archive-documento" );
}
else if ( $obj->post_type=="tipi_notizia" ||  $obj->taxonomy == "tipi_notizia" || $obj->taxonomy == "tipi_notizia"){
	get_template_part( "taxonomy-tipi_notizia" );
}
else if($obj->taxonomy == "argomenti"){
	get_template_part("archive-argomento");
} else if ($obj->post_type=="tipi_luogo" || $obj->taxonomy == "tipi_luogo") {
	get_template_part("archive-luogo");
} else if($obj->post_type=="tipi_progetto" ||  $obj->taxonomy == "tipi_progetto"){
	get_template_part( "taxonomy-tipi_progetto" );
} else if($obj->post_type=="tipi_commissario" ||  $obj->taxonomy == "tipi_commissario"){
	get_template_part( "taxonomy-tipi_commissario" );
}else if($obj->post_type=="tipi_cat_amm_trasp" ||  $obj->taxonomy == "tipi_cat_amm_trasp"){
	get_template_part( "taxonomy-categorie_trasparenza" );
}else if(f($obj->post_type=="tipi_galleria" ||  $obj->taxonomy == "tipi_galleria")){
	get_template_part( "taxonomy-tipi_galleria" );
}
else{
	get_template_part("archive");
}
?>
