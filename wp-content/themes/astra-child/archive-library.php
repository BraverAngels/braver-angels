<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

  <?php get_sidebar(); ?>

<?php endif ?>

  <div id="primary" <?php astra_primary_class(); ?>>

    <?php astra_primary_content_top(); ?>

    <section class="ast-archive-description" style="text-align:center;border-radius: 3px;padding: 2rem 1.25rem;color: white;background:#23356c;background-image: url(https://www.better-angels.org/wp-content/uploads/2018/11/better-angels-depolarize-america-hero2-01-01.jpg); background-size:cover;background-position:center;">
      <h1 class="page-title ast-archive-title" style="color:white;">Better Angels Library</h1>
      <p>Member-recommended books and resources to encourage dialogue and promote mutual understanding</p>
    </section>

    <?php astra_entry_before(); ?>
    <div class="library-content-wrap" style="margin: 0 -20px;">

      <div class="library-sidebar ast-col-md-3">
        <?php get_template_part('template-parts/content-library-sidebar'); ?>
      </div>

      <div class="library-index-content ast-col-md-9">
        <?php $ba_docs_query = new WP_Query( array(
            'post_type' => 'library',
            'nopaging' => true,
            'posts_per_page' => '3',
            'tax_query' => array(
                array(
                    'taxonomy' => 'library_category',
                    'field' => 'slug',
                    'terms' => 'better-angels-readings',
                ),
            ),
        ) ); ?>
        <?php $ba_other_items = new WP_Query( array(
            'post_type' => 'library',
            'tax_query' => array(
                array(
                    'taxonomy' => 'library_category',
                    'field' => 'slug',
                    'terms' => 'better-angels-readings',
                    'operator'  => 'NOT IN'
                ),
            ),
        ) ); ?>

        <?php if ($ba_docs_query->have_posts() ) : $i = 1;
          echo "<div class='ast-col-sm-12 library-section-header'><h3>Better Angels Readings</h3><a class='library-view-all-link' href='" . home_url('/library/categories/better-angels-readings#primary') . "'>[View All]</a></div>";
          while ( $ba_docs_query->have_posts() ) : $ba_docs_query->the_post(); ?>
            <?php get_template_part('template-parts/content-library-index-item'); ?>
            <?php if ($i % 3 == 0) : ?>
              <div class="ba-clearfix ba-clearfix-lg"></div>
            <?php endif; ?>
            <?php if ($i % 2 == 0) : ?>
              <div class="ba-clearfix ba-clearfix-md"></div>
            <?php endif; ?>
          <?php $i++;
          endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>

        <?php if ($ba_other_items->have_posts() ) : $i = 1;
          echo "<div class='ast-col-sm-12 library-section-header'><h3>Member-recommended Readings</h3></div>";
          while ( $ba_other_items->have_posts() ) : $ba_other_items->the_post(); ?>
            <?php get_template_part('template-parts/content-library-index-item'); ?>
            <?php if ($i % 3 == 0) : ?>
              <div class="ba-clearfix ba-clearfix-lg"></div>
            <?php endif; ?>
            <?php if ($i % 2 == 0) : ?>
              <div class="ba-clearfix ba-clearfix-md"></div>
            <?php endif; ?>
          <?php $i++;
          endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
  </div>

<?php astra_entry_after(); ?>

<?php astra_pagination(); ?>

    <?php astra_primary_content_bottom(); ?>

  </div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

  <?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
