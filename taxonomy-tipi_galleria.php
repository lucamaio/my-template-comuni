<?php

/**
 * Archivio Tassonomia Tipi Evento
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @link https://italia.github.io/design-comuni-pagine-statiche/sito/tipi_evento.html
 *
 * @package Design_Comuni_Italia
 */

global $the_query, $load_posts, $load_card_type, $evento, $additional_filter, $title, $description, $data_element, $hide_categories;

$obj = get_queried_object();
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : -1;
$load_posts = -1;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$args = array(
  's' => $query,
  'posts_per_page' => $max_posts,
  'post_type'      => 'galleria',
  'tipi_evento' => $obj->slug,
  'orderby' => 'meta_value',
  'order' => 'ASC',
  'meta_key' => '_dci_evento_data_orario_inizio',
);
$the_query = new WP_Query($args);
$eventi = $the_query->posts;

$additional_filter = array(
  array(
    'taxonomy' => 'tipi_evento',
    'field' => 'slug',
    'terms' => $obj->slug
  )
);

get_header();
?>
<main>
  <?php
  $title = $obj->name;
  $description = $obj->description;
  $data_element = 'data-element="page-name"';
  get_template_part("template-parts/hero/hero");
  ?>
  <div class="bg-grey-card">
    <div class="gallery-container mb-10">
      <br><br>
      <div class="gallery-grid">
        <?php foreach ($posts as $post) {
          get_template_part('template-parts/galleria/cards-list');
        } ?>
      </div>
    </div>
  </div>


  <?php //get_template_part("template-parts/galleria/categorie");
  ?>

  <?php echo get_template_part('template-parts/common/valuta-servizio'); ?>
  <?php echo get_template_part('template-parts/common/assistenza-contatti'); ?>
</main>
<?php
get_footer(); ?>

<style>
  /* * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #333;
        } */

  .gallery-container {
    max-width: 1200px;
    margin: 0 auto;
  }

  /* .header {
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 300;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        } */

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    justify-content: center;
  }

  .gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    cursor: pointer;
    aspect-ratio: 4/3;
    background: #000;
  }

  .gallery-item:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
  }

  .gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }

  .gallery-item:hover .gallery-image {
    transform: scale(1.1);
  }

  .overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 30px;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .gallery-item:hover .overlay {
    opacity: 1;
  }

  .gallery-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
    transform: translateY(20px);
    transition: transform 0.3s ease;
  }

  .gallery-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    line-height: 1.5;
    transform: translateY(20px);
    transition: transform 0.3s ease;
  }

  .gallery-item:hover .gallery-title,
  .gallery-item:hover .gallery-description {
    transform: translateY(0);
  }

  .gallery-title {
    transition-delay: 0.1s;
  }

  .gallery-description {
    transition-delay: 0.2s;
  }

  .gallery-type {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 10;
  }

  @media (max-width: 768px) {
    .gallery-grid {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .header h1 {
      font-size: 2.2rem;
    }
  }

  @media (max-width: 480px) {
    .gallery-grid {
      grid-template-columns: 1fr;
    }

    .header h1 {
      font-size: 1.8rem;
    }
  }
</style>