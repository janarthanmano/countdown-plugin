<?php
/**
 * The template for displaying archive countdown posts
 */

get_header();
 if ( have_posts() ) :
    // Start the Loop.
     ?>
    <div id="countdown_archive">
    <?php
    while ( have_posts() ) :
        the_post();

        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php the_title( sprintf( '<h2 class="entry-title default-max-width"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

            <div class="entry-content">
                <?php the_excerpt(); ?>
            </div><!-- .entry-content -->


        </article>
        <?php

        // End the loop.
    endwhile;

    ?>
    </div>
    <img class="d-none" id="loading" src="../<?= get_option('site_url'); ?>wp-content/plugins/countdown-post/assets/loading.gif">
    <button data-page="2" id="load_more">Load more ...</button>
    <p class="d-none" id="all_loaded">All countdown posts loaded!</p>
    <?php

endif;
get_footer();
