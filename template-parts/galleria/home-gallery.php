<?php

$gallery_ids = dci_get_option('gallerie_evidenziate', 'Galleria', true);
$prefix = '_dci_galleria_';

if (is_array($gallery_ids) && count($gallery_ids) > 1) { ?>
    <section id="galleria" class="py-5">
        <div class="container">
            <h2 class="title-xxlarge mb-4">Gallerie in evidenza</h2>
            <div class="gallery-grid">
                <?php foreach ($gallery_ids as $post) {
                    get_template_part('template-parts/galleria/cards-list');
                } ?>
            </div>
            <div class="text-center mt-5">
                <!-- <a href="<?php //echo get_permalink( get_page_by_path( 'galleria' )); ?>" class="btn btn-primary btn-lg custom-btn"> -->
                <a  href="<?php echo get_permalink( get_page_by_path( 'galleria' )); ?>" class="btn btn-primary" style="margin-left: 40px;">
                Vedi tutte le gallerie
                </a>
            </div>
            </div>
    </section>
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            justify-content: center;
        }
    
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease;
            cursor: pointer;
            aspect-ratio: 4/3;
            background: #fff;
        }
    
        .gallery-item:hover {
            transform: translateY(-0.5rem);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
    
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
    
        .gallery-item:hover .gallery-image {
            transform: scale(1.05);
        }
    
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.85) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
    
        .gallery-item:hover .overlay {
            opacity: 1;
        }
    
        .gallery-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transform: translateY(1rem);
            transition: transform 0.3s ease;
        }
    
        .gallery-description {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.4;
            transform: translateY(1rem);
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
            background: #e63946;
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
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
    </style>
<?php } ?>