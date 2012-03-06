<?php
function custom_excerpt_length( $length ) {
  return 50;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


function new_excerpt_more($more) {
       global $post;
  return '<a class="read-more" href="'. get_permalink($post->ID) . '"> En savoir plus...</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');


function excerpt($num) {
$limit = $num+1;
$excerpt = explode(' ', the_excerpt(), $limit);
array_pop($excerpt);
$excerpt = implode(" ",$excerpt)."...";
echo $excerpt;
}

?>