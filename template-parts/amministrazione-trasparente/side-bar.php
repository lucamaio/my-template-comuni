<?php
global $siti_tematici, $sito_tematico_id;

if (is_array($siti_tematici) && count($siti_tematici)) { ?>
    <div class="col-12 col-lg-4 pt-30 pt-lg-50 pb-lg-50">
        <div class="link-list-wrap">
            <h2 class="title-medium-semi-bold">Link Utili</h2>
            <ul class="link-list t-primary">
                <?php foreach ($siti_tematici as $item) { ?>
                    <li class="mb-3 mt-3">
                        <?php $sito_tematico_id = $item;
                        get_template_part("template-parts/sito-tematico/card"); ?>
                    </li>
                <?php } ?>
                <li>
                    <a class="list-item ps-0 text-button-xs-bold d-flex align-items-center text-decoration-none"
                        href="<?php echo get_permalink(get_page_by_path('Amministrazione Trasparente')); ?>">
                        <span class="mr-10">Ritorna alla trasparenza Amministrativa</span>
                        <svg class="icon icon-xs icon-primary">
                            <use href="#it-arrow-right"></use>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<?php } ?>