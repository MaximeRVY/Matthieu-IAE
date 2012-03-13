<div id="all-footer">
  <div id="propulsed-by">
    Site créé par Ludivine et Matthieu.
  </div>
  <div id="footer-menu">
  <?php 
      wp_nav_menu(
        array (
            'menu'            => 'main-menu',
            'container'       => FALSE,
            'container_id'    => FALSE,
            'menu_class'      => '',
            'menu_id'         => FALSE,
            'depth'           => 1
        )
      );
    ?>
  </div>
</div>