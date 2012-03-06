<?php
/**
 * The template used for displaying page content in home
 *
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <header class="entry-header">
    <h3 class="entry-title"><?php the_title(); ?></h3>
  </header><!-- .entry-header -->
  <div class="entry-post-thumbnail">
    <?php echo get_the_post_thumbnail($post->ID,'thumbnail'); ?>
  </div>
  <div class="entry-content">
    <?php the_excerpt(); ?>
  </div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->