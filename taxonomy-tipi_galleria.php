<?php

/**
 * Archivio Tassonomia Tipi Galleria
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
  <div class="bg-grey-card py-5">
    <div class="gallery-container">
      <?php if (count($posts) > 0) { ?>
        <div class="gallery-grid">
          <?php foreach ($posts as $post) {
            get_template_part('template-parts/galleria/cards-list');
          } ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-info text-center" role="alert">
          <i class="bi bi-info-circle me-2" aria-hidden="true"></i>Nessun elemento è presente.
        </div>
      <?php } ?>
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
  .gallery-container {
    max-width: 1200px;
    height: auto;
    margin: 0 auto;
    padding: 0 20px;
    /* Aggiunto padding per margini laterali su schermi piccoli */
  }

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    justify-content: center;
    /* Centra le colonne nel contenitore, utile per 1 o 2 elementi */
    align-items: start;
    /* Allinea gli elementi in alto per uniformità */
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease;
  }

  .gallery-grid.is-loaded {
    opacity: 1;
    visibility: visible;
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
    width: 100%;
    /* Assicura che l'elemento occupi tutta la larghezza della colonna */
    max-width: 400px;
    /* Limita la larghezza massima per uniformità */
    margin: 0 auto;
    /* Centra l'elemento nella colonna */
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
    transition-delay: 0.05s;
  }

  .gallery-description {
    transition-delay: 0.1s;
  }

  .gallery-type {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: #0651c2ff;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 10;
  }

  @media (max-width: 768px) {
    .gallery-grid {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .gallery-item {
      max-width: 320px;
      /* Riduce leggermente la larghezza massima su tablet */
    }

    .header h1 {
      font-size: 2.2rem;
    }
  }

  @media (max-width: 480px) {
    .gallery-grid {
      grid-template-columns: 1fr;
      /* Una colonna su mobile per semplicità */
      gap: 20px;
    }

    .gallery-item {
      max-width: 100%;
      /* Occupa tutta la larghezza su mobile */
    }

    .header h1 {
      font-size: 1.8rem;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const galleryGrid = document.querySelector('.gallery-grid');
    if (!galleryGrid) return;

    const images = galleryGrid.querySelectorAll('img');

    if (images.length === 0) {
      galleryGrid.classList.add('is-loaded');
      return;
    }

    let loadedImages = 0;
    const totalImages = images.length;

    function imageLoaded() {
      loadedImages++;
      if (loadedImages === totalImages) {
        galleryGrid.classList.add('is-loaded');
      }
    }

    images.forEach(img => {
      if (img.complete && img.naturalHeight !== 0) {
        imageLoaded();
      } else {
        img.addEventListener('load', imageLoaded);
        img.addEventListener('error', imageLoaded);
      }
    });
  });
</script>