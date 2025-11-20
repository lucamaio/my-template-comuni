<?php get_header(); ?>

<h1><?php single_term_title(); ?></h1>

<?php if (have_posts()) : ?>
    <ul>
        <?php while (have_posts()) : the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else : ?>
    <p>Nessuna persona pubblica trovata in questa categoria.</p>
<?php endif; ?>

<?php get_footer(); ?>
