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
        <?php get_template_part( 'thumb','community'); ?>
        <?php get_template_part('thumb', 'contact-form'); ?>
        
       <?php get_template_part('thumb', 'articles'); ?>
        
      </div><!-- #content -->
    </div><!-- #primary -->

<?php get_footer(); ?>