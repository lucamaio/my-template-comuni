<?php
global $sito_tematico_id, $siti_tematici;



$categorie_genitori = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => 0,
    'orderby' => 'ID',
    'order' => 'ASC'
));


// Ordina ulteriormente per 'ordinamento' (campo meta) se presente
usort($categorie_genitori, function($a, $b) {
    // Ottieni i valori del campo meta 'ordinamento' o usa un fallback
    $ordinamento_a = get_term_meta($a->term_id, 'ordinamento', true);
    $ordinamento_b = get_term_meta($b->term_id, 'ordinamento', true);

    // Se uno dei termini non ha un valore di 'ordinamento', usa un valore di fallback
    if (empty($ordinamento_a)) {
        $ordinamento_a = PHP_INT_MAX; // Usa un valore molto grande per mandarlo alla fine
    }
    if (empty($ordinamento_b)) {
        $ordinamento_b = PHP_INT_MAX; // Lo stesso per il secondo termine
    }

    // Confronta i valori di ordinamento
    return $ordinamento_a - $ordinamento_b;
});



$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<style>
    

/* TITOLO */
.title-custom {
    font-size: 22px;
    background-color: white;
    padding: 14px 20px;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    line-height: 1.3;
    color: #222;
    user-select: none;
    transition: background-color 0.3s ease, color 0.3s ease;
    margin-bottom: 8px;
}

.title-custom:hover {
    background-color: color-mix(in srgb, var(--main-color-trasparenza) 85%, white);
    color: white;
}

/* CONTENUTO */
.content {
    display: none;
    padding: 15px 25px;
    font-size: 18px;
    line-height: 1.6;
    color: #333;
    background-color: #fafafa;
    border-left: 3px solid var(--main-color-trasparenza);
    border-radius: 0 6px 6px 0;
    margin-bottom: 18px;
}

/* LINK DENTRO IL CONTENUTO */
.content a {
    display: block;
    margin: 10px 0;
    color: var(--main-color-trasparenza);
    text-decoration: none;
    padding-left: 20px;
    font-size: 18px;
    font-weight: 600;
    position: relative;
    transition: color 0.3s ease;
}

.content a::before {
    content: '▶';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: var(--main-color-trasparenza);
    transition: transform 0.3s ease;
}

.content a:hover {
    text-decoration: underline;
    color: var(--main-color-light-trasparenza);
}

.content a:hover::before {
    transform: translateY(-50%) rotate(90deg);
    color: var(--main-color-light-trasparenza);
}

/* LISTE INTERNE */
.sub-sub-list {
    margin-top: 15px;
    margin-left: 32px;
    padding-left: 18px;
    border-left: 2px solid #ccc;
    font-size: 17px;
    line-height: 1.5;
    color: #555;
    font-style: italic;
}

.sub-sub-list li {
    margin: 8px 0;
}

.sub-sub-list a {
    color: #555;
    font-style: italic;
    padding-left: 12px;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
}

.sub-sub-list a:hover {
    color: var(--main-color-trasparenza);
    text-decoration: underline;
}

.sub-sub-list .sub-sub-list {
    margin-left: 32px;
    border-left: 2px solid #ccc;
    padding-left: 18px;
    font-style: italic;
    font-size: 17px;
    color: #555;
    font-weight: 500;
    line-height: 1.5;
}

.sub-sub-list .sub-sub-list li {
    margin: 8px 0;
}

.sub-sub-list .sub-sub-list a {
    font-style: italic;
    font-size: 16px;
    color: #555;
    font-weight: 500;
    padding-left: 12px;
    transition: color 0.3s ease;
}

.sub-sub-list .sub-sub-list a:hover {
    color: var(--main-color-trasparenza);
    text-decoration: underline;
}

/* BOTTONE TOGGLE */
#toggle-all-container {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1.5rem;
}

#toggle-all-btn {
    font-size: 15px;
    height: 36px;
    padding: 6px 18px;
    cursor: pointer;
    border-radius: 5px;
    border: 1.5px solid var(--main-color-trasparenza);
    background-color: transparent;
    color: var(--main-color-trasparenza);
    font-weight: 600;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    user-select: none;
}

#toggle-all-btn:hover {
    background-color: var(--main-color-trasparenza);
    border-color: var(--main-color-light-trasparenza);
    color: white !important;
}

#toggle-all-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    font-weight: 700;
    font-size: 24px;
    color: #222;
    letter-spacing: 0.03em;
}



</style>

<!-- PRENDI IL COLORE DI .it-header-center-wrapper E PASSALO A MAIN COLOR -->
<script>
  // Trova l'elemento da cui leggere il colore
  const slimWrapper = document.querySelector('.it-header-center-wrapper');

  if (slimWrapper) {
    const bgColor = getComputedStyle(slimWrapper).backgroundColor;

    // Imposta --main-color-trasparenza
    document.documentElement.style.setProperty('--main-color-trasparenza', bgColor);

    // Funzione per simulare color-mix con white
    function mixWithWhite(color, percentage = 85) {
      const rgb = color.match(/\d+/g).map(Number);
      if (rgb.length < 3) return color;

      const white = 255;
      const mix = (channel) => Math.round((channel * (percentage / 100)) + (white * (1 - percentage / 100)));

      return `rgb(${mix(rgb[0])}, ${mix(rgb[1])}, ${mix(rgb[2])})`;
    }

    // Calcola il colore schiarito
    const lightColor = mixWithWhite(bgColor, 85);

    // Imposta --main-color-light-trasparenza
    document.documentElement.style.setProperty('--main-color-light-trasparenza', lightColor);
  }
</script>



<script>
function toggleContent(id) {
    var allContents = document.querySelectorAll('.content');
    allContents.forEach(function(content) {
        if (content.id === id) {
            content.style.display = (content.style.display === "block") ? "none" : "block";
        } else {
            content.style.display = "none";
        }
    });
    updateToggleAllButton();
}

function toggleAllCategories() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');

    // Controlla se almeno una categoria è chiusa
    var anyClosed = Array.from(allContents).some(content => content.style.display !== 'block');

    if (anyClosed) {
        // Apri tutte
        allContents.forEach(content => content.style.display = 'block');
        toggleAllBtn.textContent = 'Chiudi tutte le Voci';
    } else {
        // Chiudi tutte
        allContents.forEach(content => content.style.display = 'none');
        toggleAllBtn.textContent = 'Espandi tutte le Voci';
    }
}

function updateToggleAllButton() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');

    var allOpen = Array.from(allContents).every(content => content.style.display === 'block');
    toggleAllBtn.textContent = allOpen ? 'Chiudi tutte' : 'Espandi tutte le Voci';
}
</script>

<main>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>

                    <!-- Colonna sinistra: categorie -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="mycontainer p-3">

                            <!-- BOTTONE PER ESPANDERE/CHIUDERE TUTTE -->
                            <div id="toggle-all-wrapper">
                                <div>Elenco di tutte le voci</div>
                                    <div id="toggle-all-container" class="d-flex justify-content-end mb-3">
                                        <button type="button" id="toggle-all-btn" class="btn btn-outline-primary py-1 px-3" style="font-size:14px; height: 30px;" onclick="toggleAllCategories()">
                                            Espandi tutte le Voci
                                        </button>
                                    </div>
                            </div>


                            <?php foreach ($categorie_genitori as $genitore) {
                                $nome_genitore = esc_html($genitore->name);
                                $id_genitore = 'cat_' . $genitore->term_id;
                            ?>
                                
                                <h2 class="title-custom" onclick="toggleContent('<?= $id_genitore ?>')"><?= $nome_genitore ?></h2>
                                
                                <div id="<?= $id_genitore ?>" class="content">
                                    <?php
                                    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                                        'hide_empty' => false,
                                        'parent' => $genitore->term_id
                                    ));
                                    ?>
                                    <ul class="link-list t-primary">
                                        <?php foreach ($sottocategorie as $sotto) {
                                                $link = get_term_link($sotto);
                                                $nome_sotto = esc_html($sotto->name);


                                                // Recupero i metadati dell'URL personalizzato e del flag per aprire in una nuova finestra
                                                $term_url = get_term_meta($sotto->term_id, 'term_url', true);
                                                $open_new_window = get_term_meta($sotto->term_id, 'open_new_window', true);
                                            
                                                // Se c'è un URL personalizzato, sostituisco il link con l'URL fornito
                                                if (!empty($term_url)) {
                                                    $link = $term_url; // Imposto l'URL personalizzato
                                                    $target = $open_new_window ? ' target="_blank"' : ''; // Se c'è la spunta "Apri in una nuova finestra"
                                                } else {
                                                    $target = ''; // Nessun target, se non c'è URL personalizzato
                                                }
                                            ?>
                                                <li class="mb-3 mt-3">
                                                    <a class="list-item ps-0 title-medium underline" style="text-decoration:none;" href="<?= esc_url($link); ?>"<?= $target; ?>>
                                                        <svg class="icon"></svg>
                                                        <span><?= $nome_sotto; ?></span>
                                                    </a>
                                            
                                                    <?php
                                                    // Include sottocategorie 3° e 4° livello
                                                    $term_id = $sotto->term_id;
                                                    include locate_template('template-parts/amministrazione-trasparente/sottocategorie_list.php');
                                                    ?>
                                                </li>
                                            <?php } ?>

                                    </ul>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </form>
    </div>
</main>




