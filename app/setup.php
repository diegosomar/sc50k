<?php

use SC50k\Helpers;

/**
 * Admin assets
 */
add_action('admin_enqueue_scripts', function(){
  wp_enqueue_style('sc50k/admin.css', Helpers\asset_uri('css/admin.css'));
  wp_enqueue_script('sc50k/admin.js', Helpers\asset_uri('js/admin.js'));
});

/**
 * Theme assets
 */
add_action('wp_enqueue_scripts', function () {
  
  // Remove o jQuery / jQuery Migrate padrão. Eles serão carregados inline no <head>
  wp_deregister_script('jquery');

  // Registra e enfileira um jquery vazio
  wp_register_script( 'jquery', '' );
  wp_enqueue_script( 'jquery' );

  // Carrega os arquivos específicos de cada página
  if ( IS_HOME || IS_FRONT_PAGE ){
    wp_enqueue_style('sc50k/front-page.css', Helpers\asset_uri('css/layout-front-page.css'), false, null);
    wp_enqueue_script('sc50k/main.js', Helpers\asset_uri('js/layout-front-page.js'), [], null, false);
  }

  elseif ( IS_PAGE && ( !IS_HOME && !IS_FRONT_PAGE ) ){
    wp_enqueue_style('sc50k/page.css', Helpers\asset_uri('css/layout-page.css'), false, null);
    wp_enqueue_script('sc50k/main.js', Helpers\asset_uri('js/layout-page.js'), [], null, false);

    $template_name = get_page_template_slug( get_the_ID() );
  }

  // Adiciona o jQuery inline no <head>
  wp_add_inline_script('sc50k/main.js', file_get_contents( Helpers\asset_path('js/partials/jquery.js') ), 'before');

  $args = array(
    'url'   => admin_url( 'admin-ajax.php' )
  );
  wp_localize_script( 'sc50k/main.js', 'sc50k', $args );
}, 100);

/**
 * Inject critical assets in head as early as possible
 */
add_action('wp_head', function (): void {

  $critical_CSS = '';

  if ( IS_FRONT_PAGE || IS_HOME ) {
    $critical_CSS = Helpers\asset_path('css/critical-front-page.css');
  }
  elseif ( IS_PAGE && ( !IS_HOME && !IS_FRONT_PAGE ) ){
    $critical_CSS = Helpers\asset_path('css/critical-page.css');
  }

  if (file_exists($critical_CSS)) {
    echo '<style id="critical-css">' . file_get_contents($critical_CSS) . '</style>';
  }
}, 9);

/**
 * Remove o jQuery Migrate padrao do WP
 */
add_action( 'wp_default_scripts', function( $scripts ){
  if ( ! is_admin() ) {
    $script = $scripts->registered['jquery'];

    if ( $script->deps ) {
      $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
    }
  }
} );

/**
 * Theme setup
 */
add_action('after_setup_theme', function () {
  /**
   * Enable plugins to manage the document title
   * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
   */
  add_theme_support('title-tag');

  /**
   * Register navigation menus
   * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
   */
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'sc50k')
  ]);

  /**
   * Enable post thumbnails
   * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
   */
  add_theme_support('post-thumbnails', array( 'post' ));

  /**
   * Enable HTML5 markup support
   * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
   */
  add_theme_support('html5', ['caption', 'gallery']);

  /**
   * Enable selective refresh for widgets in customizer
   * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
   */
  add_theme_support('customize-selective-refresh-widgets');

  /**
   * Use main stylesheet for visual editor
   * @see resources/assets/css/layouts/_tinymce.scss
   */
  add_editor_style(Helpers\asset_path('css/main.css'));
}, 20);

/**
 * Adiciona o preload das fontes
 */
add_action( 'wp_head', function(){
  ?>
  <!-- <link rel="preload" type="font/woff2" href="<?php echo Helpers\asset_uri('') ?>" as="font" crossorigin> -->
  <?php
}, 1 );

/**
 * Remove junk from head
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script' );
remove_action('admin_print_styles', 'print_emoji_styles' );
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header', 11, 0 );
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_resource_hints', 2);

/**
 * Adiciona os preconnects no head 
 */
add_action( 'wp_head', function(){
  ?>
  <link rel='preconnect' href='https://www.google-analytics.com' />
  <?php
}, 2 );

/**
 * Adiciona o Google Analytics
 */
add_action( 'wp_head', function(){
  if ( SITE_ENV == 'production' ){
    get_template_part( 'views/partials/google', 'analytics' );
  }
}, 1 );

/**
 * Adiciona os códigos SVGs dos ícones no Footer
 */
add_action( 'wp_footer', function(){
  include get_template_directory() . '/views/partials/svg-defs.php';
}, 999 );

/**
 * Impede que o site seja indexado pelo Google, se estiver em desenvolvimento
 */
 add_action('wp_head', function(){
  if ( SITE_ENV == 'development' ){
    echo '<meta name="robots" content="noindex,nofollow">';
  }
 });

/**
 * Saves post type and taxonomy data to JSON files in the theme directory.
 * @param array $data Array of post type data that was just saved.
 */
add_action( 'cptui_after_update_post_type', 'Helpers\\pluginize_local_cptui_data' );
add_action( 'cptui_after_update_taxonomy', 'Helpers\\pluginize_local_cptui_data' );
