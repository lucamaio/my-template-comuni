<?php
global $sito_tematico_id, $siti_tematici, $dci_amm_sidebar_column_classes, $elemento;

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

$at_search_term = isset($_GET['at_search']) && is_string($_GET['at_search'])
    ? sanitize_text_field(wp_unslash($_GET['at_search']))
    : '';
$at_search_page = isset($_GET['at_page']) && is_scalar($_GET['at_page'])
    ? max(1, absint($_GET['at_page']))
    : 1;
$at_search_query = null;
$at_category_results = [];
$at_search_too_short = $at_search_term !== ''
    && mb_strlen($at_search_term, 'UTF-8') < 2;

if ($at_search_term !== '' && !$at_search_too_short) {
    $at_category_query = get_terms([
        'taxonomy'   => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'search'     => $at_search_term,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'number'     => 50,
    ]);

    if (!is_wp_error($at_category_query)) {
        $at_category_results = array_values(array_filter(
            $at_category_query,
            static function ($term) {
                return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
            }
        ));
    }

    $at_search_query = new WP_Query([
        'post_type'           => 'elemento_trasparenza',
        'post_status'         => 'publish',
        'posts_per_page'      => 10,
        'paged'               => $at_search_page,
        's'                   => $at_search_term,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    ]);
}
?>

<script>
document.documentElement.classList.add('dci-at-menu-js');
</script>

<style>
.dci-at-main-search {
    margin-bottom: 2rem;
    padding: 1.25rem;
    background: #fff;
    border: 1px solid #d8e1ea;
    border-left: 4px solid #17324d;
    border-radius: 4px;
    box-shadow: 0 3px 12px rgba(23, 50, 77, 0.07);
}

.dci-at-main-search__title {
    margin-bottom: .35rem;
    color: #17324d;
    font-size: 1.35rem;
    line-height: 1.25;
    font-weight: 700;
}

.dci-at-main-search__intro {
    margin-bottom: 1rem;
    color: #455a64;
}

.dci-at-main-search__row {
    display: flex;
    align-items: stretch;
}

.dci-at-main-search__input {
    min-width: 0;
    min-height: 48px;
    border: 2px solid #b8c9da;
    border-right: 0;
    border-radius: 4px 0 0 4px;
    background: #fff;
}

.dci-at-main-search__input:focus {
    border-color: #17324d;
    box-shadow: 0 0 0 .2rem rgba(23, 50, 77, .16);
}

.dci-at-main-search__button {
    flex: 0 0 auto;
    min-width: 110px;
    min-height: 48px;
    border-radius: 0 4px 4px 0;
    font-weight: 700;
}

.dci-at-main-search__reset {
    display: inline-block;
    margin-top: .75rem;
    color: #17324d;
    font-weight: 600;
}

.dci-at-search-results {
    margin-bottom: 2rem;
}

.dci-at-search-results__heading {
    margin-bottom: 1.25rem;
    color: #17324d;
    font-size: 1.35rem;
    font-weight: 700;
}

.dci-at-search-results__group + .dci-at-search-results__group {
    margin-top: 2rem;
}

.dci-at-search-results__subheading {
    margin-bottom: .35rem;
    color: #17324d;
    font-size: 1.1rem;
    line-height: 1.35;
    font-weight: 700;
}

.dci-at-search-results__status {
    margin-bottom: .85rem;
    color: #455a64;
}

.dci-at-category-results {
    list-style: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    background: #fff;
    border: 1px solid #d8e1ea;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(23, 50, 77, .05);
}

.dci-at-category-results__link {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .85rem 1rem;
    color: #17324d;
    background: #fff;
    font-weight: 700;
    line-height: 1.35;
    text-decoration: none;
    transition: background-color .18s ease, color .18s ease;
}

.dci-at-category-results__item + .dci-at-category-results__item {
    border-top: 1px solid #e6edf3;
}

.dci-at-category-results__link:hover {
    color: #17324d;
    background: #f5f7fa;
    text-decoration: none;
}

.dci-at-category-results__link:hover .dci-at-category-results__label {
    text-decoration: underline;
}

.dci-at-category-results__link:focus-visible {
    outline: 3px solid #17324d;
    outline-offset: 2px;
}

.dci-at-category-results__marker {
    flex: 0 0 auto;
    font-size: 1.25rem;
    line-height: 1;
}

.dci-at-category-results__label {
    min-width: 0;
    flex: 1 1 auto;
}

.dci-at-category-results__external {
    flex: 0 0 auto;
    fill: currentColor;
}

.dci-at-search-results__empty {
    padding: 1rem 1.15rem;
    background: #fff;
    border: 1px solid #d8e1ea;
    border-radius: 4px;
    color: #455a64;
}

.dci-at-document-result .cmp-card-latest-messages {
    margin: 0;
}

.dci-at-document-result .cmp-card-latest-messages .card {
    border: 1px solid #d8e1ea !important;
    border-radius: 4px !important;
    box-shadow: 0 2px 8px rgba(23, 50, 77, .06) !important;
}

.dci-at-document-result .cmp-card-latest-messages .card:hover {
    border-color: #b9c8d6 !important;
    box-shadow: 0 5px 14px rgba(23, 50, 77, .09) !important;
}

.dci-at-result-categories {
    display: grid;
    grid-template-columns: max-content minmax(0, 1fr);
    align-items: baseline;
    gap: .45rem;
    margin-bottom: .45rem;
    color: #455a64;
    font-size: .9rem;
    line-height: 1.4;
}

.dci-at-result-categories__label {
    flex: 0 0 auto;
    font-weight: 600;
}

.dci-at-result-categories__list {
    display: inline-flex;
    flex-wrap: wrap;
    gap: .25rem .65rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.dci-at-result-categories__link {
    color: #17324d;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.dci-at-result-categories__external {
    margin-left: .2rem;
    fill: currentColor;
}

.dci-at-search-pagination {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.dci-at-search-pagination .pagination {
    justify-content: center;
    flex-wrap: wrap;
    margin: 0;
}

@media (max-width: 575.98px) {
    .dci-at-main-search {
        padding: 1rem;
    }

    .dci-at-main-search__row {
        display: block;
    }

    .dci-at-main-search__input,
    .dci-at-main-search__button {
        width: 100%;
        border: 2px solid #b8c9da;
        border-radius: 4px;
    }

    .dci-at-main-search__button {
        margin-top: .75rem;
    }
}

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
    display: block;
    padding: 4px 14px;
    font-size: 18px;
    line-height: 1.6;
    color: #333;
    background-color: #fafafa;
    border-left: 3px solid var(--main-color-trasparenza);
    border-radius: 0 6px 6px 0;
    margin-bottom: 6px;
}

.dci-at-menu-js .content.js-category-content {
    display: none;
}

.content:not(.js-category-content) {
    display: none;
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

.content .subcat-toggle,
.content .sub-sub-toggle {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    min-height: 2rem;
    padding: .25rem .6rem;
    border: 1px solid #b8c9da;
    border-radius: 4px;
    background: #fff;
    color: var(--main-color-trasparenza);
    cursor: pointer;
    font-size: .85rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    transition: color .18s ease, background-color .18s ease, border-color .18s ease;
}

.content .subcat-toggle:hover,
.content .sub-sub-toggle:hover {
    color: var(--main-color-trasparenza);
    background: #f3f6f9;
    border-color: var(--main-color-trasparenza);
}

.content .subcat-toggle:focus-visible,
.content .sub-sub-toggle:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
}

.content .subcat-toggle .icon,
.content .sub-sub-toggle .icon {
    width: .9rem;
    height: .9rem;
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
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 0;
}

#toggle-all-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    min-height: 40px;
    padding: 8px 18px;
    font-size: 15px;
    cursor: pointer;
    border-radius: 5px;
    border: 1.5px solid var(--main-color-trasparenza);
    background-color: transparent;
    color: var(--main-color-trasparenza);
    font-weight: 600;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    user-select: none;
}

.toggle-all-btn__icon {
    display: inline-block;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1;
    transform: rotate(90deg);
    transition: transform .2s ease;
}

#toggle-all-btn.is-open .toggle-all-btn__icon {
    transform: rotate(-90deg);
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
    var content = document.getElementById(id);
    var title = document.querySelector('.title-custom[data-target="' + id + '"]');

    if (!content || !title || title.classList.contains('no-children')) {
        return;
    }

    var isOpen = window.getComputedStyle(content).display !== 'none';
    content.style.display = isOpen ? 'none' : 'block';
    title.classList.toggle('is-open', !isOpen);
    title.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    updateToggleAllButton();
}

function setSubcategoryToggleState(toggle, isExpanded) {
    toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    toggle.classList.toggle('is-open', isExpanded);

    var label = toggle.querySelector('.js-subcat-toggle-label');
    if (label) {
        label.textContent = isExpanded ? 'Nascondi sottovoci' : 'Mostra sottovoci';
    }
}

function setToggleAllButtonState(button, allOpen) {
    if (!button) {
        return;
    }

    var label = button.querySelector('.js-toggle-all-label');
    if (label) {
        label.textContent = allOpen ? 'Chiudi tutte le sezioni' : 'Espandi tutte le sezioni';
    }

    button.classList.toggle('is-open', allOpen);
}

function toggleAllCategories() {
    var allContents = document.querySelectorAll('.js-category-content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');
    var anyClosed = Array.from(allContents).some(content => window.getComputedStyle(content).display === 'none');
    var nestedToggles = document.querySelectorAll('.js-subcat-toggle');
    var nestedPanels = document.querySelectorAll('.js-subcat-children');

    if (anyClosed) {
        allContents.forEach(content => content.style.display = 'block');
        document.querySelectorAll('.title-custom').forEach(title => {
            if (!title.classList.contains('no-children')) {
                title.classList.add('is-open');
                title.setAttribute('aria-expanded', 'true');
            }
        });
        nestedPanels.forEach(function(panel) {
            panel.hidden = false;
        });
        nestedToggles.forEach(function(toggle) {
            setSubcategoryToggleState(toggle, true);
        });
        setToggleAllButtonState(toggleAllBtn, true);
    } else {
        allContents.forEach(content => content.style.display = 'none');
        document.querySelectorAll('.title-custom').forEach(title => {
            title.classList.remove('is-open');
            if (!title.classList.contains('no-children')) {
                title.setAttribute('aria-expanded', 'false');
            }
        });
        nestedPanels.forEach(function(panel) {
            panel.hidden = true;
        });
        nestedToggles.forEach(function(toggle) {
            setSubcategoryToggleState(toggle, false);
        });
        setToggleAllButtonState(toggleAllBtn, false);
    }
}

function updateToggleAllButton() {
    var allContents = document.querySelectorAll('.js-category-content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');
    var allOpen = Array.from(allContents).every(content => window.getComputedStyle(content).display !== 'none');
    setToggleAllButtonState(toggleAllBtn, allOpen);
}

document.addEventListener('click', function(event) {
    var categoryTitle = event.target.closest('.title-custom:not(.no-children)');
    if (categoryTitle) {
        toggleContent(categoryTitle.getAttribute('data-target'));
        return;
    }

    var toggle = event.target.closest('.js-subcat-toggle');
    if (!toggle) {
        return;
    }

    var controlsId = toggle.getAttribute('aria-controls');
    var panel = controlsId ? document.getElementById(controlsId) : null;
    if (!panel) {
        return;
    }

    var nextExpanded = panel.hidden;
    panel.hidden = !nextExpanded;

    document.querySelectorAll('.js-subcat-toggle').forEach(function(candidate) {
        if (candidate.getAttribute('aria-controls') !== controlsId) {
            return;
        }

        setSubcategoryToggleState(candidate, nextExpanded);
    });
});

document.addEventListener('keydown', function(event) {
    var categoryTitle = event.target.closest('.title-custom:not(.no-children)');
    if (!categoryTitle || (event.key !== 'Enter' && event.key !== ' ')) {
        return;
    }

    event.preventDefault();
    toggleContent(categoryTitle.getAttribute('data-target'));
});
</script>

<main>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <div class="container">
                <div class="row align-items-start">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>

                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <section class="dci-at-main-search" aria-labelledby="dci-at-main-search-title">
                            <h2 class="dci-at-main-search__title" id="dci-at-main-search-title">
                                Cerca nei documenti pubblicati
                            </h2>
                            <p class="dci-at-main-search__intro" id="dci-at-main-search-help">
                                Inserisci almeno due caratteri per cercare in tutta l’Amministrazione Trasparente.
                            </p>
                            <div class="dci-at-main-search__row">
                                <label class="visually-hidden" for="dci-at-main-search-input">
                                    Cerca nei documenti pubblicati
                                </label>
                                <input
                                    class="form-control dci-at-main-search__input"
                                    id="dci-at-main-search-input"
                                    type="search"
                                    name="at_search"
                                    value="<?= esc_attr($at_search_term); ?>"
                                    placeholder="Cerca per titolo o parola chiave"
                                    minlength="2"
                                    aria-describedby="dci-at-main-search-help"
                                >
                                <button class="btn btn-primary dci-at-main-search__button" type="submit">
                                    Cerca
                                </button>
                            </div>

                            <?php if ($at_search_term !== '') { ?>
                                <a class="dci-at-main-search__reset" href="<?= esc_url(remove_query_arg(['at_search', 'at_page'])); ?>">
                                    Cancella la ricerca
                                </a>
                            <?php } ?>
                        </section>

                        <?php if ($at_search_too_short) { ?>
                            <div class="dci-at-search-results" role="status">
                                <p class="dci-at-search-results__empty">
                                    Inserisci almeno due caratteri per avviare la ricerca.
                                </p>
                            </div>
                        <?php } elseif ($at_search_query instanceof WP_Query) { ?>
                            <section class="dci-at-search-results" aria-labelledby="dci-at-search-results-title">
                                <h2 class="dci-at-search-results__heading" id="dci-at-search-results-title">
                                    Risultati della ricerca
                                </h2>

                                <?php if (!empty($at_category_results)) { ?>
                                    <div class="dci-at-search-results__group">
                                        <h3 class="dci-at-search-results__subheading">
                                            <?php
                                            printf(
                                                esc_html(_n(
                                                    '%s categoria trovata',
                                                    '%s categorie trovate',
                                                    count($at_category_results),
                                                    'design_comuni_italia'
                                                )),
                                                esc_html(number_format_i18n(count($at_category_results)))
                                            );
                                            ?>
                                        </h3>
                                        <ul class="dci-at-category-results">
                                            <?php foreach ($at_category_results as $at_category) {
                                                $at_category_link = get_term_link($at_category);
                                                $at_category_url = trim((string) get_term_meta($at_category->term_id, 'term_url', true));
                                                $at_category_new_window = !empty(get_term_meta($at_category->term_id, 'open_new_window', true));
                                                $at_category_is_external = $at_category_url !== '';

                                                if ($at_category_is_external) {
                                                    $at_category_link = $at_category_url;
                                                }

                                                if (is_wp_error($at_category_link)) {
                                                    continue;
                                                }

                                                $at_category_name = dci_format_trasparenza_section_title($at_category->name);
                                                ?>
                                                <li class="dci-at-category-results__item">
                                                    <a
                                                        class="dci-at-category-results__link"
                                                        href="<?= esc_url($at_category_link); ?>"
                                                        <?php if ($at_category_is_external && $at_category_new_window) { ?>
                                                            target="_blank" rel="noopener noreferrer"
                                                        <?php } ?>
                                                    >
                                                        <span class="dci-at-category-results__marker" aria-hidden="true">›</span>
                                                        <span class="dci-at-category-results__label"><?= esc_html($at_category_name); ?></span>
                                                        <?php if ($at_category_is_external) { ?>
                                                            <svg class="icon icon-xs dci-at-category-results__external" aria-hidden="true">
                                                                <use href="#it-external-link"></use>
                                                            </svg>
                                                            <span class="visually-hidden">
                                                                <?= $at_category_new_window
                                                                    ? esc_html__('Sezione esterna. Si apre in una nuova finestra', 'design_comuni_italia')
                                                                    : esc_html__('Sezione esterna', 'design_comuni_italia'); ?>
                                                            </span>
                                                        <?php } ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>

                                <div class="dci-at-search-results__group">
                                    <h3 class="dci-at-search-results__subheading">Documenti</h3>
                                    <p class="dci-at-search-results__status" role="status">
                                        <?php
                                        printf(
                                            esc_html(_n(
                                                '%s documento trovato',
                                                '%s documenti trovati',
                                                $at_search_query->found_posts,
                                                'design_comuni_italia'
                                            )),
                                            esc_html(number_format_i18n($at_search_query->found_posts))
                                        );
                                        ?>
                                    </p>

                                    <?php if ($at_search_query->have_posts()) { ?>
                                        <?php
                                        $at_previous_elemento = $elemento;
                                        foreach ($at_search_query->posts as $elemento) {
                                            echo '<div class="dci-at-document-result mb-3">';
                                            get_template_part(
                                                'template-parts/amministrazione-trasparente/card',
                                                null,
                                                ['show_search_categories' => true]
                                            );
                                            echo '</div>';
                                        }
                                        $elemento = $at_previous_elemento;
                                        ?>

                                        <?php if ($at_search_query->max_num_pages > 1) { ?>
                                            <nav class="pagination-wrapper justify-content-center dci-at-search-pagination" aria-label="Pagine dei documenti">
                                                <?php
                                                $at_pagination_base = add_query_arg(
                                                    [
                                                        'at_search' => $at_search_term,
                                                        'at_page'   => 999999999,
                                                    ],
                                                    remove_query_arg('at_page')
                                                );

                                                $at_pagination_links = paginate_links([
                                                    'base'      => str_replace('999999999', '%#%', esc_url($at_pagination_base)),
                                                    'format'    => '',
                                                    'current'   => $at_search_page,
                                                    'total'     => $at_search_query->max_num_pages,
                                                    'type'      => 'array',
                                                    'show_all'  => false,
                                                    'end_size'  => 2,
                                                    'mid_size'  => 1,
                                                    'prev_text' => __('« Precedente', 'design_comuni_italia'),
                                                    'next_text' => __('Successiva »', 'design_comuni_italia'),
                                                    'add_fragment' => '#dci-at-search-results-title',
                                                ]);

                                                if (is_array($at_pagination_links)) {
                                                    echo '<div class="pagination"><ul class="pagination">';
                                                    foreach ($at_pagination_links as $at_pagination_link) {
                                                        $at_is_current = strpos($at_pagination_link, 'current') !== false;
                                                        $at_pagination_link = str_replace('page-numbers', 'page-link', $at_pagination_link);
                                                        echo '<li class="page-item' . ($at_is_current ? ' active' : '') . '">'
                                                            . wp_kses_post($at_pagination_link)
                                                            . '</li>';
                                                    }
                                                    echo '</ul></div>';
                                                }
                                                ?>
                                            </nav>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <p class="dci-at-search-results__empty">
                                            Nessun documento corrisponde ai termini inseriti.
                                        </p>
                                    <?php } ?>
                                </div>
                            </section>
                        <?php } ?>

                        <?php if ($at_search_term === '') { ?>
                        <div class="mycontainer px-3 pb-3">
                            <div id="toggle-all-wrapper">
                                <div>Sezioni dell’Amministrazione Trasparente</div>
                                <div id="toggle-all-container" class="d-flex justify-content-end">
                                    <button type="button" id="toggle-all-btn" class="btn btn-outline-primary" onclick="toggleAllCategories()">
                                        <span class="toggle-all-btn__icon" aria-hidden="true">»</span>
                                        <span class="js-toggle-all-label">Espandi tutte le sezioni</span>
                                    </button>
                                </div>
                            </div>

                            <?php foreach ($categorie_genitori as $genitore) {
                                $nome_genitore = dci_format_trasparenza_section_title($genitore->name);
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
                                <h2
                                    class="title-custom<?= $genitore_has_children ? '' : ' no-children'; ?>"
                                    data-target="<?= esc_attr($id_genitore); ?>"
                                    <?php if ($genitore_has_children) { ?>
                                        role="button"
                                        tabindex="0"
                                        aria-expanded="false"
                                        aria-controls="<?= esc_attr($id_genitore); ?>"
                                        aria-label="<?= esc_attr(sprintf('Apri o chiudi la categoria %s', $nome_genitore)); ?>"
                                    <?php } ?>
                                >
                                    <span class="title-custom__inner">
                                        <span><?= esc_html($nome_genitore); ?></span>
                                        <?php if ($genitore_is_external) { ?>
                                            <svg class="icon icon-xs title-custom__external-icon" aria-hidden="true">
                                                <use href="#it-external-link"></use>
                                            </svg>
                                        <?php } ?>
                                    </span>
                                </h2>

                                <div id="<?= esc_attr($id_genitore); ?>" class="content<?= $genitore_has_children ? ' js-category-content' : ''; ?>">
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
                                            $nome_sotto = dci_format_trasparenza_section_title($sotto->name);
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
                                            $toggle_id = $has_children ? 'subcat-children-' . $sotto->term_id : '';

                                            if (!empty($term_url)) {
                                                $link = $term_url;
                                                $target = $open_new_window ? ' target="_blank" rel="noopener noreferrer"' : '';
                                                $is_external = true;
                                            } else {
                                                $target = '';
                                            }
                                        ?>
                                            <li class="mb-3 mt-3">
                                                <div class="subcat-item-head">
                                                    <a class="list-item ps-0 title-medium underline<?= $is_external ? ' is-external' : ''; ?><?= $has_children ? ' has-children' : ' no-children'; ?>" style="text-decoration:none;" href="<?= esc_url($link); ?>" aria-label="<?= esc_attr($nome_sotto); ?>"<?= $target; ?>>
                                                        <span class="list-marker list-marker--dash" aria-hidden="true">-</span>
                                                        <span><?= esc_html($nome_sotto); ?></span>
                                                        <?php if ($is_external) { ?>
                                                            <svg class="icon icon-xs external-link-icon" aria-hidden="true">
                                                                <use href="#it-external-link"></use>
                                                            </svg>
                                                        <?php } ?>
                                                    </a>

                                                    <?php if ($has_children) { ?>
                                                        <button class="subcat-toggle js-subcat-toggle" type="button" aria-expanded="false" aria-controls="<?= esc_attr($toggle_id); ?>" title="<?= esc_attr(sprintf('Mostra o nascondi le sottovoci di %s', $nome_sotto)); ?>">
                                                            <span class="js-subcat-toggle-label">Mostra sottovoci</span>
                                                            <svg class="icon icon-xs" aria-hidden="true">
                                                                <use href="#it-expand"></use>
                                                            </svg>
                                                            <span class="visually-hidden">Mostra o nascondi le sottovoci di <?= esc_html($nome_sotto); ?></span>
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
                        <?php } ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </form>
    </div>
</main>

<?php
$dci_amm_sidebar_column_classes = '';
