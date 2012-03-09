 <?php 
  query_posts(array('post_type' => 'post', 'posts_per_page' => '3'));
  if ( have_posts() ) : ?>
  <?php /* Start the Loop  */ ?>
  <div id="articles" class="thumb-part">
    <h3 class="thumb-title">Retrouvez nos actualités</h3>
    <div id="articles-content">
      <?php while ( have_posts()) : the_post(); ?>
        <?php get_template_part( 'thumb', 'article' ); ?>
      <?php endwhile; ?>
    </div>
    <?php wp_link_pages(); ?>
    <a href="<?php echo get_page_link(get_page_by_title( 'Archives' )->ID); ?>" class="all-actu">Voir toutes les actualités</a>
  </div>
<?php endif; ?>
