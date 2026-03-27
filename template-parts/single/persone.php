<?php
global $persone, $persone_cessate;
$persone = is_array($persone) ? $persone : [];
$persone_cessate = is_array($persone_cessate) ? $persone_cessate : [];

// Stili aggiornati
echo '<style>

/* Pulsante moderno migliorato */
#toggle-persone-cessate{
    display:inline-flex;
    align-items:center;
    gap:.6rem;
    padding:.85rem 1.4rem;
    font-size:.9rem;
    font-weight:600;
    letter-spacing:.05em;
    border-radius:6px;
    border:1px solid #d0d7de;
    background:linear-gradient(135deg,#f8fafc,#eef2f6);
    color:#17324d;
    transition:all .25s ease;
    margin-bottom:.4rem; /* 🔽 riduce spazio sotto */
}
#toggle-persone-cessate .icon{
    fill:currentColor;
    transition:transform .25s ease;
}
#toggle-persone-cessate:hover{
    background:linear-gradient(135deg,#eef2f6,#e2e8ee);
    box-shadow:0 4px 12px rgba(0,0,0,.08);
}
#toggle-persone-cessate.is-open .icon{
    transform:rotate(90deg);
}

/* Contenitore persone cessate */
#section-persone-cessate{
    background:#f1f3f5;
    padding:1.5rem;
    border-radius:8px;
    margin-top:.3rem; /* 🔽 ridotto da 1rem */
}

/* Avatar a destra migliorato */
.avatar-wrapper{
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    margin-left: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-wrapper img{
    width: 100%;
    height: 100%;
    object-fit: cover;          /* evita deformazioni */
    border-radius: 4px;         /* stile coerente */
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
    background: #fff;
}

/* Migliore adattamento mobile */
@media (max-width: 768px){
    .avatar-wrapper{
        width: 60px;
        height: 60px;
        margin-left: .75rem;
    }
}
</style>';

function dci_render_person_card($person_id) {
    $prefix = '_dci_persona_pubblica_';
    $nome = dci_get_meta('nome', $prefix, $person_id);
    $cognome = dci_get_meta('cognome', $prefix, $person_id);
    $descrizione = dci_get_meta('descrizione_breve', $prefix, $person_id);
    $img = dci_get_meta('foto', $prefix, $person_id);

    ob_start();
    ?>
    <div class="col-12 col-md-8 col-lg-6 mb-30">
        <div class="card-wrapper rounded h-auto mt-10">
            <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
                <div class="card-body pe-3">
                    <p class="card-title text-paragraph-regular-medium-semi mb-3">
                        <a href="<?php echo esc_url(get_permalink($person_id)); ?>">
                            <span class="chip-label"><?php echo esc_html(trim($nome . ' ' . $cognome)); ?></span>
                        </a>
                    </p>
                    <div class="card-text">
                        <div class="richtext-wrapper lora">
                            <?php echo wp_kses_post($descrizione); ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($img)) : ?>
                    <div class="avatar-wrapper">
                        <?php dci_get_img($img, 'avatar-img'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Persone in carica
if (!empty($persone)) {
    echo '<div class="row" id="persone-in-carica">';
    foreach ($persone as $person_id) {
        echo dci_render_person_card($person_id);
    }
    echo '</div>';
} else {
    echo '<p>Nessuna persona attualmente in carica trovata.</p>';
}

// Persone cessate
if (!empty($persone_cessate)) {
    echo '<button id="toggle-persone-cessate" type="button">
            <span>Mostra persone non piu in carica</span>
            <svg class="icon icon-sm"><use href="#it-chevron-right"></use></svg>
          </button>';

    echo '<div id="section-persone-cessate" style="display:none;">';
    echo '<div class="row">';
    foreach ($persone_cessate as $person_id) {
        echo dci_render_person_card($person_id);
    }
    echo '</div></div>';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('toggle-persone-cessate');
            var section = document.getElementById('section-persone-cessate');
            var label = button.querySelector('span');

            button.addEventListener('click', function () {
                var isOpen = section.style.display === 'block';

                section.style.display = isOpen ? 'none' : 'block';
                button.classList.toggle('is-open', !isOpen);

                if (label) {
                    label.textContent = isOpen
                        ? 'Mostra persone non piu in carica'
                        : 'Nascondi persone non piu in carica';
                }
            });
        });
    </script>
    <?php
}