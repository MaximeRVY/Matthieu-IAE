<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 */

get_header(); ?>
    <div id="slider">
      <?php if (function_exists('easing_slider')){ easing_slider(); }; ?>
    </div>
    <div id="slogan">
      <?php bloginfo('description'); ?>
    </div>
    <div id="primary">
      <div id="content-home" role="main">
        <div id="pages">
          <?php /* get_page */ 
          query_posts(array(
            'post__in' => array(
                get_page_by_title( 'Un chef étoilé' )->ID, 
                get_page_by_title( 'Une carte raffinée' )->ID, 
                get_page_by_title( 'Une adresse rénommé')->ID ),
            'post_type' => 'page',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'ASC'));

          while (have_posts()) { the_post();
              /* Do whatever you want to do for every page... */
               get_template_part( 'thumb','page');
          }

          wp_reset_query();
                  
        ?>
        </div>
      <?php /*if ( have_posts() ) : ?>

        <?php twentyeleven_content_nav( 'nav-above' ); ?>

        <?php /* Start the Loop   ?>
        <?php while ( have_posts() ) : the_post(); ?>
          <?php get_template_part( 'thumb', get_post_format() ); ?>

        <?php endwhile; ?>

        <?php twentyeleven_content_nav( 'nav-below' ); ?>

      <?php else : ?>

        <article id="post-0" class="post no-results not-found">
          <header class="entry-header">
            <h1 class="entry-title"><?php _e( 'Nothing Found', 'twentyeleven' ); ?></h1>
          </header><!-- .entry-header -->

          <div class="entry-content">
            <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven' ); ?></p>
            <?php get_search_form(); ?>
          </div><!-- .entry-content -->
        </article><!-- #post-0 -->

      <?php endif;*/ ?>

      </div><!-- #content -->
    </div><!-- #primary -->

<?php get_footer(); ?>