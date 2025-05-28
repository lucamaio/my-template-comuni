<?php $categorie_genitori = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false,
        'parent'=>0,
    ) );?>
<style>
        .mycontainer {
            width: 75%;
            margin: 0 auto;
            background-color:rgb(239,239,239);
        }
        h2 {
            font-size: 18px;
            background-color:rgb(236,236,236);
            padding: 10px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .content {
            display: none;
            padding: 10px;
        }
        .content a {
            display: block;
            margin: 5px 0;
            color: rgb(17, 17, 17);
            text-decoration: none;
            padding-left: 10px;
        }
        .content a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleContent(id) {
            var content = document.getElementById(id);
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        }
    </script>
<div class="mycontainer">
    <?php foreach($categorie_genitori as $genitore){
        $nome_genitore = esc_html($genitore->name);
        $id_genitore = 'cat_' . $genitore->term_id; // id HTML valido
    ?>
        <h2 onclick="toggleContent('<?= $id_genitore ?>')"><?= $nome_genitore ?></h2>
        <div id="<?= $id_genitore ?>" class="content">
            <?php
            // Recupera le sottocategorie del genitore corrente
            $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                'hide_empty' => false,
                'parent' => $genitore->term_id
            ));?>
            <ul class="link-list t-primary">
                <?php foreach ($sottocategorie as $sotto){
                    $link = get_term_link($sotto);
                    $nome_sotto = esc_html($sotto->name);?>
                    <li class="mb-3 mt-3">
                        <a class="list-item ps-0 title-medium underline" style="text-decoration:none;"
                            href="<?= $link; ?>">
                            <svg class="icon">
                                <use xlink:href="#it-arrow-right-triangle"></use>
                            </svg>
                            <span><?=  $nome_sotto; ?></span>
                        </a>
                    </li>
                <?php }?> 
            </ul>
        </div>
    <?php } ?>
</div>


