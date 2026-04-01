<?php
global $post, $with_shadow;

get_header();
?>

<main>
    <?php while ( have_posts() ) : the_post(); ?>

        <?php
        $with_shadow = true; 
        get_template_part("template-parts/hero/hero"); 
        ?>

        <br>

        <?php get_template_part("template-parts/uffici/organigramma-uffici"); ?>    

        <br>
         
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>    
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>            

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
