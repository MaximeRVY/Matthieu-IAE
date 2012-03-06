<article id="post-<?php the_ID(); ?>">
  <div class="thumb-post-thumbnail">
    <?php echo get_the_post_thumbnail($post->ID,array(70,70)); ?>
  </div>
  <div class="thumb-content">
    <span class="title-article"><?php the_title(); ?></span>
    <div class="content">
      <?php  $excerpt = explode(' ', get_the_excerpt(), 6);
            array_pop($excerpt);
            $excerpt = implode(" ",$excerpt).'<a class="read-more" href="'. get_permalink($post->ID) . '"> En savoir plus...</a>';
            echo $excerpt; ?>
    </div><!-- .entry-content -->
  </div>
</article><!-- #post-<?php the_ID(); ?> -->