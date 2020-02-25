<?php

namespace SC50k\Helpers;

function asset_path( $file = '' ){
	$theme_path = get_template_directory();
	$dist_path = $theme_path . '/dist/';
  $dist_path = $dist_path . $file;
  
  if ( SITE_ENV == 'development' ){
    $dist_path = str_replace( array( '.js', '.css' ), array( '.dev.js', '.dev.css' ), $dist_path );
  } else {
    $dist_path = str_replace( array( '.js', '.css' ), array( '.min.js', '.min.css' ), $dist_path );
  }
	
  return $dist_path;
}

function asset_uri( $file = '' ){
	$dist_uri = BLOGINFO_TEMPLATE_URL . '/dist/';
  $dist_uri = $dist_uri . $file;
  
  if ( SITE_ENV == 'development' ){
    $dist_uri = str_replace( array( '.js', '.css' ), array( '.dev.js', '.dev.css' ), $dist_uri );
  } else {
    $dist_uri = str_replace( array( '.js', '.css' ), array( '.min.js', '.min.css' ), $dist_uri );
  }

  return $dist_uri;
}

function get_svg( $args = array() ) {
  // Make sure $args are an array.
  if ( empty( $args ) ) {
    return '';
  }

  // Define an icon.
  if ( false === array_key_exists( 'icon', $args ) ) {
    return '';
  }

  // Set defaults.
  $defaults = array(
    'icon'     => '',
    'class'    => ''
  );

  // Parse args.
  $args = wp_parse_args( $args, $defaults );

  // Set aria hidden.
  $aria_hidden = ' aria-hidden="true"';

  // Set ARIA.
  $aria_labelledby = '';

  // Begin SVG markup.
  $svg = '<svg class="'.$args["class"].' icon icon-' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

  /*
   * Display the icon.
   *
   * The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
   *
   * See https://core.trac.wordpress.org/ticket/38387.
   */
  $svg .= ' <use href="#icon-' . esc_html( $args['icon'] ) . '" xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use> ';

  $svg .= '</svg>';

  return $svg;
}

/**
 * Altera os atributos da tag <img> de uma string HTML para suportar o lazy load
 * @link https://florianbrinkmann.com/en/responsive-images-and-lazy-loading-in-wordpress-3350/
 */
function lazy_load_responsive_images ( $content ) {

  if ( empty( $content ) ) {
    return $content;
  }
  $dom = new DOMDocument();
  libxml_use_internal_errors( true );
  $dom->loadHTML( mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
  libxml_clear_errors();
  foreach ( $dom->getElementsByTagName( 'img' ) as $img ) {
    $src = $img->getAttribute( 'src' );

    if ( $img->hasAttribute( 'sizes' ) && $img->hasAttribute( 'srcset' ) ) {
      $sizes_attr = $img->getAttribute( 'sizes' );
      $srcset     = $img->getAttribute( 'srcset' );
      
      $img->setAttribute( 'data-sizes', $sizes_attr );
      $img->setAttribute( 'data-srcset', $srcset );
      $img->setAttribute( 'data-src', $src );

      $img->removeAttribute( 'sizes' );
      $img->removeAttribute( 'srcset' );
      $img->removeAttribute( 'src' );
    } else {
      if ( ! $src ) {
        $src = $img->getAttribute( 'data-noscript' );
      }
      $img->setAttribute( 'data-src', $src );
    }
    $classes = $img->getAttribute( 'class' );
    $classes .= " lazyload";
    $img->setAttribute( 'class', $classes );
    $noscript      = $dom->createElement( 'noscript' );
    $noscript_node = $img->parentNode->insertBefore( $noscript, $img );
    $noscript_img  = $dom->createElement( 'IMG' );
    $noscript_img->setAttribute( 'class', $classes );
    $new_img = $noscript_node->appendChild( $noscript_img );
    $new_img->setAttribute( 'src', $src );
    $content = $dom->saveHTML();
  }

  return $content;
}

function pluginize_local_cptui_data( $data = array() ) {
  $theme_dir = get_stylesheet_directory().'/plugins';
  // Create our directory if it doesn't exist.
  if ( ! is_dir( $theme_dir .= '/cptui_data' ) ) {
    mkdir( $theme_dir, 0755 );
  }

  if ( array_key_exists( 'cpt_custom_post_type', $data ) ) {
    // Fetch all of our post types and encode into JSON.
    $cptui_post_types = get_option( 'cptui_post_types', array() );
    $content = json_encode( $cptui_post_types );
    // Save the encoded JSON to a primary file holding all of them.
    file_put_contents( $theme_dir . '/cptui_post_type_data.json', $content );
  }

  if ( array_key_exists( 'cpt_custom_tax', $data ) ) {
    // Fetch all of our taxonomies and encode into JSON.
    $cptui_taxonomies = get_option( 'cptui_taxonomies', array() );
    $content = json_encode( $cptui_taxonomies );
    // Save the encoded JSON to a primary file holding all of them.
    file_put_contents( $theme_dir . '/cptui_taxonomy_data.json', $content );
  }
}
