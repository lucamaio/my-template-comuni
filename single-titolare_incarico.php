<?php
/**
 * Template single – Incarichi conferiti ai dipendenti
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
<?php
while ( have_posts() ) :
    the_post();

    $prefix = '_dci_titolare_incarico_';                          
    $id     = get_the_ID();

    // Meta fields
    $oggetto      = get_post_meta( $id, $prefix . 'oggetto', true );
    $compenso_raw = get_post_meta( $id, $prefix . 'compenso', true );
    $compenso_num = floatval( str_replace( [ '.', ',' ], [ '', '.' ], $compenso_raw ) );
    $compenso     = $compenso_num > 0 ? number_format( $compenso_num, 2, ',', '.' ) . '€' : 'Non specificato';

    $data_inizio = get_post_meta( $id, $prefix . 'data_inizio', true );
    $data_fine   = get_post_meta( $id, $prefix . 'data_fine', true );
    $durata      = get_post_meta( $id, $prefix . 'durata', true );
    $atto        = get_post_meta( $id, $prefix . 'atto_conferimento_incarico', true );
    $situazioni  = get_post_meta( $id, $prefix . 'situazioni_conflitto', true );

    // Allegati e Curriculum
    $documenti  = get_post_meta( $id, $prefix . 'allegati', true );
    $curriculum = get_post_meta( $id, $prefix . 'cv_allegati', true );

    $data_pubbl = get_the_date( 'j F Y', $id );
?>

    <div class="container" id="main-container">
         <div class="row">
                    <div class="col px-lg-4">
                        <?php get_template_part("template-parts/common/breadcrumb"); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 px-lg-4 py-lg-2">
                        <h1 data-audio><?php the_title(); ?></h1>
                        <h2 class="visually-hidden" data-audio>Dettagli Titolare incarico</h2>
                        <!-- <p data-audio>
                            <?php // echo esc_html( get_the_title()); ?>
                        </p> -->
                        
                        <div class="row mt-5 mb-4">
                            <div class="col-6">
                                <small>Data di pubblicazione:</small>
                                <p class="fw-semibold font-monospace">
                                    <?php echo esc_html( $data_pubbl ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-lg-3 offset-lg-1">
                        <?php
                          /*$inline = true;
                        get_template_part('template-parts/single/actions2');*/
                        ?>
                    </div>                -->
                </div>                      
    </div>

    <div class="container">
        <div class="row border-top border-light row-column-border row-column-menu-left">
             <aside class="col-lg-4">
                 <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                            <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                                <div class="navbar-custom" id="navbarNavProgress">
                                    <div class="menu-wrapper">
                                        <div class="link-list-wrapper">
                                            <div class="accordion">
                                                <div class="accordion-item">
                                                    <span class="accordion-header" id="accordion-title-one">
                                                        <button
                                                            class="accordion-button pb-10 px-3 text-uppercase"
                                                            type="button"
                                                            aria-controls="collapse-one"
                                                            aria-expanded="true"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse-one"
                                                        >INDICE DELLA PAGINA
                                                            <svg class="icon icon-sm icon-primary align-top">
                                                                <use xlink:href="#it-expand"></use>
                                                            </svg>
                                                        </button>
                                                    </span>
                                                    <div class="progress">
                                                        <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                                        <div class="accordion-body">
                                                            <ul class="link-list" data-element="page-index">
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#oggetto">
                                                                    <span class="title-medium">Oggetto Incarico</span>
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#dati">
                                                                    <span class="title-medium">Dati Principali</span>
                                                                    </a>
                                                                </li>
                                                                <?php if(isset($documenti) AND !empty($documenti)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#documenti">
                                                                    <span class="title-medium">Documenti</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                 <?php if(isset($curriculum) AND !empty($curriculum)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#curriculum">
                                                                    <span class="title-medium">Curriculum</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
            </aside>
            <section class="col-lg-8 it-page-sections-container border-light mb-5">

                <article class="it-page-section anchor-offset">
                    <h4 id="oggetto">Oggetto incarico</h4>
                    <div class="richtext-wrapper lora"><?php echo nl2br( esc_html( $oggetto ?: '-' ) ); ?></div>
                </article>

                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="dati">Dati principali</h4>
                    <div class="card card-border-top mb-0">
                        <ul class="mb-0 list-unstyled">
                            <li><strong>Atto di conferimento:</strong> <?php echo esc_html( $atto ?: '-' ); ?></li>
                            <li><strong>Compenso lordo:</strong> <?php echo esc_html( $compenso ); ?></li>
                            <li><strong>Data inizio:</strong> <?php echo $data_inizio ? date_i18n( 'd/m/Y', $data_inizio ) : '-'; ?></li>
                            <li><strong>Data fine:</strong> <?php echo $data_fine ? date_i18n( 'd/m/Y', $data_fine ) : '-'; ?></li>
                            <li><strong>Durata:</strong> <?php echo esc_html( $durata ?: '-' ); ?></li>
                            <li><strong>Situazioni conflitto:</strong> <?php echo esc_html( $situazioni ?: '-' ); ?></li>
                        </ul>
                    </div>
                </article>

                <?php if ( ! empty( $documenti ) && is_array( $documenti ) ) : ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="documenti">Documenti</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ( $documenti as $file_url ) : 
                                $file_id = attachment_url_to_postid( $file_url );
                                $allegato = get_post( $file_id );
                                if ( $allegato ) : 
                                    $title_allegato = $allegato->post_title;
                                    if ( strlen( $title_allegato ) > 50 ) $title_allegato = substr( $title_allegato, 0, 50 ) . '...';
                                    if ( preg_match( '/[A-Z]{5,}/', $title_allegato ) ) $title_allegato = ucfirst( strtolower( $title_allegato ) );
                            ?>
                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#it-clip"></use>
                                    </svg>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Scarica l'allegato <?php echo esc_attr( $allegato->post_title ); ?>" title="Scarica l'allegato <?php echo esc_attr( $allegato->post_title ); ?>">
                                                <?php echo esc_html( $title_allegato ); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ( ! empty( $curriculum ) && is_array( $curriculum ) ) : ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="curriculum">Curriculum</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ( $curriculum as $file_url ) : 
                                $file_id = attachment_url_to_postid( $file_url );
                                $allegato = get_post( $file_id );
                                if ( $allegato ) : 
                                    $title_allegato = $allegato->post_title;
                                    if ( strlen( $title_allegato ) > 50 ) $title_allegato = substr( $title_allegato, 0, 50 ) . '...';
                                    if ( preg_match( '/[A-Z]{5,}/', $title_allegato ) ) $title_allegato = ucfirst( strtolower( $title_allegato ) );
                            ?>
                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#it-clip"></use>
                                    </svg>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Scarica il curriculum <?php echo esc_attr( $allegato->post_title ); ?>" title="Scarica il curriculum <?php echo esc_attr( $allegato->post_title ); ?>">
                                                <?php echo esc_html( $title_allegato ); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>

            </section>
        </div>
    </div>

    <?php 
    get_template_part( 'template-parts/common/valuta-servizio' ); 
    get_template_part( 'template-parts/common/assistenza-contatti' ); 
    ?>

<?php
endwhile;
?>
</main>

<?php get_footer(); ?>
