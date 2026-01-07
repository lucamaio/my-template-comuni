<?php

$gallery_ids = dci_get_option('gallerie_evidenziate', 'Galleria', true);
$prefix = '_dci_galleria_';

// Mostra la sezione se esiste almeno una galleria selezionata
if (is_array($gallery_ids) && count($gallery_ids) >= 1) { ?>
    <section id="galleria" class="py-5">
        <div class="gallery-container">
            <h2 class="title-xxlarge mb-4">Gallerie in evidenza</h2>

            <div class="gallery-grid">
                <?php 
                foreach ($gallery_ids as $post_id) {
                    // Imposta il post globale corretto
                    $post = get_post($post_id);
                    setup_postdata($post);

                    get_template_part('template-parts/galleria/cards-list');
                }
                wp_reset_postdata();
                ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?php echo get_permalink(get_page_by_path('galleria')); ?>" 
                   class="btn btn-primary" 
                   style="margin-left: 40px;">
                    Vedi tutte le gallerie
                </a>
            </div>

        </div>
    </section>
            <style>
            .gallery-container {
                max-width: 1300px; /* Limita la larghezza massima */
                width: 100%;       /* Occupa tutta la larghezza disponibile fino al max-width */
                margin: 0 auto;    /* Centra il contenitore orizzontalmente */
                padding: 0 15px;   /* Padding laterale per spazi su schermi piccoli */
                display: block;    /* Garantisce il comportamento standard del blocco */
                text-align: left; 
            }
            .gallery-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 30px;
                justify-content: center; /* Centra le colonne nel contenitore, utile per 1 o 2 elementi */
                align-items: start; /* Allinea gli elementi in alto per uniformità */
            }
        
            .gallery-item {
                position: relative;
                overflow: hidden;
                border-radius: 10px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
                cursor: pointer;
                aspect-ratio: 4/3;
                background: #fff;
                width: 100%; /* Assicura che l'elemento occupi tutta la larghezza della colonna */
                max-width: 400px; /* Limita la larghezza massima per uniformità */
                margin: 0 auto; /* Centra l'elemento nella colonna */
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
                border-radius: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                z-index: 2;
            }
    

            /* Stili per il nuovo pulsante */
            .custom-btn {
                display: inline-block;
                padding: 1rem 2.5rem;
                font-size: 1rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #fff;
                background-color: #007bff;
                border-radius: 50px;
                box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
                transition: all 0.3s ease-in-out;
                text-decoration: none;
                border: none;
            }

            .custom-btn:hover,
            .custom-btn:focus {
                background-color: #0056b3;
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
                color: #fff;
                text-decoration: none;
            }
        
            @media (max-width: 768px) {
                .gallery-grid {
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 20px;
                }

                .gallery-item {
                    max-width: 320px; /* Riduce leggermente la larghezza massima su tablet */
                }
            }

            @media (max-width: 480px) {
                .gallery-grid {
                    grid-template-columns: 1fr; /* Una colonna su mobile per semplicità */
                    gap: 20px;
                }

                .gallery-item {
                    max-width: 100%; /* Occupa tutta la larghezza su mobile */
                }
            }
        </style>
    <?php } ?>

