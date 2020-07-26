<?php

namespace SC50k;

define( 'BLOGINFO_URL', \get_bloginfo('url') );
define( 'BLOGINFO_TEMPLATE_URL', \get_bloginfo('template_url') );
define( 'BLOGINFO_NAME', \get_bloginfo('name') );
define( 'BLOGINFO_DESCRIPTION', \get_bloginfo('description') );

\add_action( 'template_redirect', function(){
  define( 'IS_HOME', \is_home() );
  define( 'IS_PAGE', \is_page() );

  $page_id = \get_the_ID();
  if ( !defined( 'IS_FRONT_PAGE' ) ) define( 'IS_FRONT_PAGE', ( IS_PAGE && $page_id == \get_option('page_on_front') ) );

  define( 'IS_SINGLE', \is_single() );
  define( 'IS_SEARCH', \is_search() );
  define( 'IS_ARCHIVE', \is_archive() );
  define( 'CUR_POST_TYPE', \get_post_type() );
}, 1 );


/**
 * Add ENV constants
 */
require( 'env.php' );

/**
 * Required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
$required_files = ['helpers', 'setup', 'filters', 'admin'];
foreach ($required_files as $key => $file_name) {
  $file = "app/{$file_name}.php";
  if ( ! \file_exists( \get_template_directory() . '/' . $file ) ) {
    echo \sprintf(__('Error locating <code>%s</code> for inclusion.', 'sc50k'), $file);
    exit;
  }
}

/**
 * Include files from app folder
 */
foreach ( \glob( \get_template_directory() . '/app/*.php' ) as $file ) {
  if ( $file == 'admin.php' ) {
    if ( \is_admin() ){
      require $file;
    }
  } else {
    require $file;
  }
}

/**
 * Change default template hierarchy
 */
$default_templates = ['index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
  'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment'];
foreach ($default_templates as $key => $template) {
  \add_filter("{$template}_template_hierarchy", function($registered_templates){

    foreach ($registered_templates as $registered_template) {
      $templates[] = 'views/layouts/'.$registered_template;
    }

    return $templates;
  }, 9999);
}

function get_header( $name = null ){
  \do_action( 'get_header', $name );

  $templates = array();
  $name      = (string) $name;
  if ( '' !== $name ) {
    $templates[] = "views/partials/header-{$name}.php";
  }

  $templates[] = 'views/partials/header.php';

  \locate_template( $templates, true );
}

function get_footer( $name = null ){
  \do_action( 'get_footer', $name );

  $templates = array();
  $name      = (string) $name;
  if ( '' !== $name ) {
    $templates[] = "views/partials/footer-{$name}.php";
  }

  $templates[] = 'views/partials/footer.php';

  \locate_template( $templates, true );
}

function get_sidebar( $name = null ) {
  \do_action( 'get_sidebar', $name );

  $templates = array();
  $name = (string) $name;
  if ( '' !== $name )
    $templates[] = "views/partials/sidebar-{$name}.php";

  $templates[] = 'views/partials/sidebar.php';

  \locate_template( $templates, true );
}

/**
 * Force WP to load custom pate templates
 */
\add_filter( 'template_include', function ( $original_template ) {
  if ( IS_PAGE ) {
    $template_name = \get_page_template_slug( \get_the_ID() );
    if ( !empty( $template_name ) ){
      $theme_path = \get_template_directory();
      $original_template = $theme_path . '/' . $template_name;
    }
  }

  return $original_template;
}, 99 );
