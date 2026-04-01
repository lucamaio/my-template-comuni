<?php
global $sito_tematico_id, $siti_tematici, $dci_amm_sidebar_column_classes;

$dci_amm_sidebar_column_classes = 'pt-30 pt-lg-50 pb-lg-50';

$categorie_genitori = get_terms('tipi_cat_amm_trasp', [
    'hide_empty' => false,
    'parent' => 0,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$categorie_genitori = array_filter($categorie_genitori, function ($term) {
    $visible = get_term_meta($term->term_id, 'visualizza_elemento', true);
    return $visible == 1;
});

usort($categorie_genitori, function ($a, $b) {
    $ordinamento_a = (int) get_term_meta($a->term_id, 'ordinamento', true);
    $ordinamento_b = (int) get_term_meta($b->term_id, 'ordinamento', true);

    if ($ordinamento_a === $ordinamento_b) {
        return strcmp($a->name, $b->name);
    }

    return $ordinamento_a <=> $ordinamento_b;
});

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<style>
.title-custom {
    font-size: 22px;
    background-color: white;
    padding: 14px 20px;
    padding-right: 56px;
    border: 1px solid #cfd9e5;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    line-height: 1.3;
    color: #17324d;
    user-select: none;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 8px;
    position: relative;
    box-shadow: 0 1px 3px rgba(23, 50, 77, 0.04);
}

.title-custom:hover {
    background-color: color-mix(in srgb, var(--main-color-trasparenza) 85%, white);
    color: white;
    border-color: color-mix(in srgb, var(--main-color-trasparenza) 55%, white);
    box-shadow: 0 3px 8px rgba(23, 50, 77, 0.08);
}

.title-custom__inner {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
}

.title-custom__external-icon {
    flex: 0 0 auto;
    fill: currentColor;
}

.title-custom::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 20px;
    width: 10px;
    height: 10px;
    border-right: 2px solid currentColor;
    border-bottom: 2px solid currentColor;
    transform: translateY(-65%) rotate(45deg);
    transition: transform 0.25s ease;
}

.title-custom.is-open::after {
    transform: translateY(-35%) rotate(225deg);
}

.title-custom.no-children {
    cursor: default;
}

.title-custom.no-children::after {
    display: none;
}

.title-custom:not(.no-children).is-open,
.title-custom:focus-visible {
    border-color: color-mix(in srgb, var(--main-color-trasparenza) 45%, white);
    box-shadow: 0 3px 8px rgba(23, 50, 77, 0.08);
    outline: none;
}

.content {
    display: none;
    padding: 4px 14px;
    font-size: 18px;
    line-height: 1.6;
    color: #333;
    background-color: #fafafa;
    border-left: 3px solid var(--main-color-trasparenza);
    border-radius: 0 6px 6px 0;
    margin-bottom: 6px;
}

.content a {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    margin: 2px 0;
    color: var(--main-color-trasparenza);
    text-decoration: none;
    padding-left: 0;
    font-size: 18px;
    font-weight: 600;
    position: relative;
    transition: color 0.3s ease;
}

.content a.is-external,
.content a.has-children {
    gap: 0.55rem;
}

.content .subcat-item-head,
.content .sub-sub-item-head {
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
}

.content .subcat-item-head > a,
.content .sub-sub-item-head > a {
    flex: 1 1 auto;
    min-width: 0;
}

.content a.is-external .external-link-icon {
    flex: 0 0 auto;
    fill: currentColor;
}

.content a.no-children {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.content a.no-children .terminal-link-icon {
    flex: 0 0 auto;
    fill: currentColor;
    opacity: .8;
}

.content .list-marker {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1rem;
    height: 1rem;
    margin-top: 0.18rem;
    color: currentColor;
}

.content .list-marker--arrow {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
    opacity: 0.9;
}

.content .list-marker--dash {
    width: 0.8rem;
    height: 1rem;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
    opacity: 0.9;
}

.content a.has-children::before {
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

.content a.has-children:hover::before {
    transform: translateY(-50%) rotate(90deg);
    color: var(--main-color-light-trasparenza);
}

.sub-sub-list {
    margin-top: 4px;
    margin-left: 10px;
    padding-left: 10px;
    border-left: 2px solid #ccc;
    font-size: 17px;
    line-height: 1.5;
    color: #555;
}

.sub-sub-list.depth-1 {
    margin-left: 10px;
    padding-left: 10px;
}

.sub-sub-list.depth-2 {
    margin-left: 14px;
    padding-left: 12px;
}

.sub-sub-list.depth-3 {
    margin-left: 18px;
    padding-left: 12px;
}

.sub-sub-list.depth-4 {
    margin-left: 22px;
    padding-left: 12px;
}

.sub-sub-list.depth-5 {
    margin-left: 26px;
    padding-left: 12px;
}

.sub-sub-list li {
    margin: 3px 0;
}

.sub-sub-list a {
    color: #555;
    padding-left: 0;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
}

.sub-sub-list a.no-children,
.sub-sub-list .sub-sub-list a.no-children {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.sub-sub-list a.no-children .terminal-link-icon,
.sub-sub-list .sub-sub-list a.no-children .terminal-link-icon {
    flex: 0 0 auto;
    fill: currentColor;
    opacity: .8;
}

.sub-sub-list a:hover {
    color: var(--main-color-trasparenza);
    text-decoration: underline;
}

.sub-sub-list .sub-sub-list {
    margin-left: 14px;
    border-left: 2px solid #ccc;
    padding-left: 12px;
    font-size: 17px;
    color: #555;
    font-weight: 500;
    line-height: 1.5;
}

.sub-sub-list .sub-sub-list li {
    margin: 3px 0;
}

.sub-sub-list .sub-sub-list a {
    font-size: 15px;
    color: #555;
    font-weight: 500;
    padding-left: 0;
    transition: color 0.3s ease;
}

.sub-sub-list.depth-2 .list-marker,
.sub-sub-list.depth-3 .list-marker,
.sub-sub-list.depth-4 .list-marker,
.sub-sub-list.depth-5 .list-marker,
.sub-sub-list .sub-sub-list .list-marker {
    display: none;
}

.sub-sub-list .sub-sub-list a:hover {
    color: var(--main-color-trasparenza);
    text-decoration: underline;
}

.content a.has-children::before,
.content a.has-children:hover::before {
    display: none;
}

.content .subcat-toggle,
.content .sub-sub-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.85rem;
    height: 1.85rem;
    padding: 0;
    border: 1px solid color-mix(in srgb, var(--main-color-trasparenza) 28%, white);
    border-radius: 999px;
    background: color-mix(in srgb, var(--main-color-trasparenza) 10%, white);
    color: var(--main-color-trasparenza);
    flex: 0 0 auto;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    transition: color 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.content .subcat-toggle:hover,
.content .sub-sub-toggle:hover {
    color: var(--main-color-trasparenza);
    background: color-mix(in srgb, var(--main-color-trasparenza) 18%, white);
    border-color: color-mix(in srgb, var(--main-color-trasparenza) 45%, white);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
}

.content .subcat-toggle .icon,
.content .sub-sub-toggle .icon {
    width: 1rem;
    height: 1rem;
    transition: transform 0.25s ease;
}

.content .subcat-toggle.is-open .icon,
.content .sub-sub-toggle.is-open .icon {
    transform: rotate(180deg);
}

.content .subcat-toggle.is-open,
.content .sub-sub-toggle.is-open {
    color: var(--main-color-trasparenza);
}

.sub-sub-list .sub-sub-item-head {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
}

.sub-sub-list a,
.sub-sub-list .sub-sub-list a {
    padding-left: 0;
}

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

<script>
const slimWrapper = document.querySelector('.it-header-center-wrapper');

if (slimWrapper) {
  const bgColor = getComputedStyle(slimWrapper).backgroundColor;
  document.documentElement.style.setProperty('--main-color-trasparenza', bgColor);

  function mixWithWhite(color, percentage = 85) {
    const rgb = color.match(/\d+/g).map(Number);
    if (rgb.length < 3) return color;

    const white = 255;
    const mix = (channel) => Math.round((channel * (percentage / 100)) + (white * (1 - percentage / 100)));

    return `rgb(${mix(rgb[0])}, ${mix(rgb[1])}, ${mix(rgb[2])})`;
  }

  const lightColor = mixWithWhite(bgColor, 85);
  document.documentElement.style.setProperty('--main-color-light-trasparenza', lightColor);
}
</script>

<script>
function toggleContent(id) {
    var allContents = document.querySelectorAll('.content');
    var allTitles = document.querySelectorAll('.title-custom');
    allContents.forEach(function(content) {
        if (content.id === id) {
            content.style.display = (content.style.display === "block") ? "none" : "block";
        } else {
            content.style.display = "none";
        }
    });
    allTitles.forEach(function(title) {
        var targetId = title.getAttribute('data-target');
        if (title.classList.contains('no-children')) {
            title.classList.remove('is-open');
            return;
        }
        if (targetId === id) {
            title.classList.toggle('is-open');
        } else {
            title.classList.remove('is-open');
        }
    });
    updateToggleAllButton();
}

function toggleAllCategories() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');
    var anyClosed = Array.from(allContents).some(content => content.style.display !== 'block');
    var nestedToggles = document.querySelectorAll('.js-subcat-toggle');
    var nestedPanels = document.querySelectorAll('.js-subcat-children');

    if (anyClosed) {
        allContents.forEach(content => content.style.display = 'block');
        document.querySelectorAll('.title-custom').forEach(title => {
            if (!title.classList.contains('no-children')) {
                title.classList.add('is-open');
            }
        });
        nestedPanels.forEach(function(panel) {
            panel.hidden = false;
        });
        nestedToggles.forEach(function(toggle) {
            toggle.setAttribute('aria-expanded', 'true');
            toggle.classList.add('is-open');
        });
        toggleAllBtn.textContent = 'Chiudi tutte le Voci';
    } else {
        allContents.forEach(content => content.style.display = 'none');
        document.querySelectorAll('.title-custom').forEach(title => title.classList.remove('is-open'));
        nestedPanels.forEach(function(panel) {
            panel.hidden = true;
        });
        nestedToggles.forEach(function(toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.classList.remove('is-open');
        });
        toggleAllBtn.textContent = 'Espandi tutte le Voci';
    }
}

function updateToggleAllButton() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');
    var allOpen = Array.from(allContents).every(content => content.style.display === 'block');
    toggleAllBtn.textContent = allOpen ? 'Chiudi tutte' : 'Espandi tutte le Voci';
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('.js-subcat-toggle');
    if (!toggle) {
        return;
    }

    var controlsId = toggle.getAttribute('aria-controls');
    var panel = controlsId ? document.getElementById(controlsId) : null;
    if (!panel) {
        return;
    }

    var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
    toggle.classList.toggle('is-open', !isExpanded);
    panel.hidden = isExpanded;
});
</script>

<main>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row align-items-start">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>

                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="mycontainer px-3 pb-3">
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
                                $genitore_term_url = get_term_meta($genitore->term_id, 'term_url', true);
                                $genitore_is_external = !empty($genitore_term_url);
                                $genitore_children = get_terms('tipi_cat_amm_trasp', [
                                    'hide_empty' => false,
                                    'parent' => $genitore->term_id
                                ]);
                                $genitore_children = array_filter($genitore_children, function($term) {
                                    $visible = get_term_meta($term->term_id, 'visualizza_elemento', true);
                                    return $visible == 1;
                                });
                                $genitore_has_children = !empty($genitore_children);
                            ?>
                                <h2 class="title-custom<?= !$genitore_has_children ? ' no-children' : ''; ?>" data-target="<?= $id_genitore ?>" onclick="<?= $genitore_has_children ? "toggleContent('{$id_genitore}')" : 'void(0)' ?>">
                                    <span class="title-custom__inner">
                                        <span><?= $nome_genitore ?></span>
                                        <?php if ($genitore_is_external) { ?>
                                            <svg class="icon icon-xs title-custom__external-icon" aria-hidden="true">
                                                <use href="#it-external-link"></use>
                                            </svg>
                                        <?php } ?>
                                    </span>
                                </h2>

                                <div id="<?= $id_genitore ?>" class="content">
                                    <?php
                                    $sottocategorie = get_terms('tipi_cat_amm_trasp', [
                                        'hide_empty' => false,
                                        'parent' => $genitore->term_id
                                    ]);

                                    $sottocategorie = array_filter($sottocategorie, function($term) {
                                        $visible = get_term_meta($term->term_id, 'visualizza_elemento', true);
                                        return $visible == 1;
                                    });

                                    if (!empty($sottocategorie) && !is_wp_error($sottocategorie)) {
                                        usort($sottocategorie, function($a, $b) {
                                            $ordinamento_a = (int) get_term_meta($a->term_id, 'ordinamento', true);
                                            $ordinamento_b = (int) get_term_meta($b->term_id, 'ordinamento', true);

                                            if ($ordinamento_a === $ordinamento_b) {
                                                return strcmp($a->name, $b->name);
                                            }

                                            return $ordinamento_a <=> $ordinamento_b;
                                        });
                                    }
                                    ?>
                                    <ul class="link-list t-primary">
                                        <?php foreach ($sottocategorie as $sotto) {
                                            $link = get_term_link($sotto);
                                            $nome_sotto = esc_html($sotto->name);
                                            $term_url = get_term_meta($sotto->term_id, 'term_url', true);
                                            $open_new_window = get_term_meta($sotto->term_id, 'open_new_window', true);
                                            $is_external = false;
                                            $child_terms = get_terms('tipi_cat_amm_trasp', [
                                                'hide_empty' => false,
                                                'parent' => $sotto->term_id
                                            ]);
                                            $child_terms = array_filter($child_terms, function($term) {
                                                $visible = get_term_meta($term->term_id, 'visualizza_elemento', true);
                                                return $visible == 1;
                                            });
                                            $has_children = !empty($child_terms);

                                            if (!empty($term_url)) {
                                                $link = $term_url;
                                                $target = $open_new_window ? ' target="_blank"' : '';
                                                $is_external = true;
                                            } else {
                                                $target = '';
                                            }
                                        ?>
                                            <li class="mb-3 mt-3">
                                                <div class="subcat-item-head">
                                                    <a class="list-item ps-0 title-medium underline<?= $is_external ? ' is-external' : ''; ?><?= $has_children ? ' has-children' : ' no-children'; ?>" style="text-decoration:none;" href="<?= esc_url($link); ?>"<?= $target; ?>>
                                                        <?php if ($has_children) { ?>
                                                            <span class="list-marker list-marker--arrow" aria-hidden="true">›</span>
                                                        <?php } else { ?>
                                                            <span class="list-marker list-marker--dash" aria-hidden="true">-</span>
                                                        <?php } ?>
                                                        <span><?= $nome_sotto; ?></span>
                                                        <?php if ($is_external) { ?>
                                                            <svg class="icon icon-xs external-link-icon" aria-hidden="true">
                                                                <use href="#it-external-link"></use>
                                                            </svg>
                                                        <?php } ?>
                                                    </a>

                                                    <?php if ($has_children) {
                                                        $toggle_id = 'subcat-children-' . $sotto->term_id;
                                                        ?>
                                                        <button class="subcat-toggle js-subcat-toggle" type="button" aria-expanded="false" aria-controls="<?= esc_attr($toggle_id); ?>">
                                                            <svg class="icon icon-xs" aria-hidden="true">
                                                                <use href="#it-expand"></use>
                                                            </svg>
                                                            <span class="visually-hidden">Mostra o nascondi le sottovoci di <?= esc_html($sotto->name); ?></span>
                                                        </button>
                                                    <?php } ?>
                                                </div>

                                                <?php if ($has_children) { ?>
                                                    <div id="<?= esc_attr($toggle_id); ?>" class="js-subcat-children" hidden>
                                                        <?php
                                                        $term_id = $sotto->term_id;
                                                        include locate_template('template-parts/amministrazione-trasparente/sottocategorie_list.php');
                                                        ?>
                                                    </div>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </form>
    </div>
</main>

<?php
$dci_amm_sidebar_column_classes = '';
