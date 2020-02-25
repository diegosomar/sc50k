<?php

namespace SC50k;

define( 'BLOGINFO_URL', get_bloginfo('url') );
define( 'BLOGINFO_TEMPLATE_URL', get_bloginfo('template_url') );
define( 'BLOGINFO_NAME', get_bloginfo('name') );
define( 'BLOGINFO_DESCRIPTION', get_bloginfo('description') );

add_action( 'template_redirect', function(){
  define( 'IS_HOME', is_home() );
  define( 'IS_FRONT_PAGE', is_front_page() );
  define( 'IS_SINGLE', is_single() );
  define( 'IS_PAGE', is_page() );
  define( 'IS_ARCHIVE', is_archive() );
}, 99 );

/**
 * Check if constant SITE_ENV is defined
 */
if ( ! defined( 'SITE_ENV' ) ){
	echo sprintf(__('Constant <code>%s</code> not defined in wp.config. Possible values: <code>development</code> | <code>production</code>', 'luisasch'), 'SITE_ENV');
	exit;
}
elseif ( defined( 'SITE_ENV' ) ){
	if ( SITE_ENV != 'development' && SITE_ENV != 'production' ){
		echo sprintf(__('Invalid value for constant <code>%s</code>. Must be <code>development</code> or <code>production</code>', 'luisasch'), 'SITE_ENV');
		exit;
	}
}

/**
 * Required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
$required_files = ['helpers', 'setup', 'filters', 'admin'];
foreach ($required_files as $key => $file_name) {
	$file = "app/{$file_name}.php";
	if ( ! file_exists( get_template_directory() . '/' . $file ) ) {
		echo sprintf(__('Error locating <code>%s</code> for inclusion.', 'luisasch'), $file);
		exit;
	}
}

/**
 * Include files from app folder
 */
foreach ( glob( get_template_directory() . '/app/*.php' ) as $file ) {
	if ($file == 'admin.php') {
		if ( is_admin() ){
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
	add_filter("{$template}_template_hierarchy", function($registered_templates){

		foreach ($registered_templates as $key => $registered_template) {
			$templates[] = 'views/layouts/'.$registered_template;
		}
		
		return $templates;
	}, 9999);
}

function get_header( $name = null ){
	do_action( 'get_header', $name );

	$templates = array();
	$name      = (string) $name;
	if ( '' !== $name ) {
		$templates[] = "views/partials/header-{$name}.php";
	}

	$templates[] = 'views/partials/header.php';

	locate_template( $templates, true );
}

function get_footer( $name = null ){
	do_action( 'get_footer', $name );

	$templates = array();
	$name      = (string) $name;
	if ( '' !== $name ) {
		$templates[] = "views/partials/footer-{$name}.php";
	}

	$templates[] = 'views/partials/footer.php';

	locate_template( $templates, true );
}

function get_sidebar( $name = null ) {
	do_action( 'get_sidebar', $name );

	$templates = array();
	$name = (string) $name;
	if ( '' !== $name )
		$templates[] = "views/partials/sidebar-{$name}.php";

	$templates[] = 'views/partials/sidebar.php';

	locate_template( $templates, true );
}
