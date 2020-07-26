<?php

/**
 * Load alternative custom templates
 */
add_filter( 'theme_page_templates', function ( $post_templates ) {
  // $post_templates['views/layouts/page_info.php'] = 'Info';
  return $post_templates;
} );

/**
 * Remove admin top bar in development
 */
add_filter('show_admin_bar', function(){
  return ( SITE_ENV == 'production' );
});

/**
 * Carrega os JS de forma assíncrona
 * @link https://stackoverflow.com/questions/50289262/how-to-enqueue-jquery-asynchronously-in-wordpress
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ){
  if ( ! is_admin() && $GLOBALS['pagenow'] != 'wp-login.php' ){
    if ( 'jquery-core' != $handle ){
      $tag = str_replace( ' src', ' defer src', $tag );
    }
  }
  return $tag;
}, 999, 3 );

/**
 * Allow SVG through WordPress Media Uploader
 * @link https://css-tricks.com/snippets/wordpress/allow-svg-through-wordpress-media-uploader/
 */
add_filter('upload_mimes', function($file_types){
  $new_filetypes = array();
  $new_filetypes['svg'] = 'image/svg+xml';
  $file_types = array_merge($file_types, $new_filetypes );
  return $file_types;
});

/**
 * Remove Paragraph Tags From Around Images
 * @link https://wordpress.stackexchange.com/questions/7090/stop-wordpress-wrapping-images-in-a-p-tag
 */
add_filter('the_content', 'filter_ptags_on_images');
function filter_ptags_on_images($content){
  return preg_replace('/<p>(\s*)(<img .* \/>)(\s*)<\/p>/iU', '\2', $content);
}

/**
 * Adiciona a classe "is-inner-page" no body
 */
add_filter( 'body_class', 'add_is_inner_page_class_to_body', 10, 2 );
function add_is_inner_page_class_to_body( $classes, $class ){
  if ( !is_front_page() ){
    $classes[] = 'is-inner-page';
  }

  if ( (is_single() || is_page()) && has_post_thumbnail() ){
    $classes[] = 'content-has-post-thumbnail';
  }

  return $classes;
}

/**
 * Wrap iframes within the_content with bootstrap responsive embed component
 * @link https://wordpress.stackexchange.com/questions/61877/how-to-wrap-an-element-around-an-iframe-or-embed-in-content-automatically
 */
add_filter('the_content', 'the_content_iframe_div_wrapper');
function the_content_iframe_div_wrapper($content) {
  $pattern = '~<iframe.*</iframe>|<embed.*</embed>~';
  preg_match_all($pattern, $content, $matches);

  foreach ($matches[0] as $match) {
    $wrappedframe = '<div class="embed-responsive embed-responsive-16by9">' . $match . '</div>';
    $content = str_replace($match, $wrappedframe, $content);
  }

  return $content;
}

/**
 * Altera a tag <link> stylesheet adicionando opções para carregamento assíncrono
 */
add_filter( 'style_loader_tag', 'sc50k_change_stylesheet_tag_to_allow_async', 9999, 4 );
function sc50k_change_stylesheet_tag_to_allow_async( $html, $handle, $href, $media ){
  if ( ! is_admin() && $GLOBALS['pagenow'] != 'wp-login.php' ){
    $html_ori = $html;

    $html_new = str_replace("rel='stylesheet'", "rel='preload'", $html);
    $html_new = str_replace("type='text/css'", "type='text/css' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" ", $html_new);

    $html = $html_new.'<noscript>'.$html_ori.'</noscript>';
  }
  
  return $html;
}

/**
 * Remove “Category:”, “Tag:”, “Author:” from the_archive_title
 * Used: gregrickaby.com/2016/04/customize-archive-title/
 * wordpress.stackexchange.com/questions/179585/remove-category-tag-author-from-the-archive-title
 */
add_filter( 'get_the_archive_title', function ($title) {
  return preg_replace( '#^[\w\d\s]+:\s*#', '', strip_tags( $title ) );
});

/**
 * Remove YOAST META NEXT LINK FROM HOME
 */
add_filter( 'wpseo_next_rel_link', 'wpseo_disable_rel_next_home' );
function wpseo_disable_rel_next_home( $link ) {
  if ( is_home() ) {
    return false;
  }
}

/**
 * Add custom directories to save ACF group fields (to sync)
 * Load custom ACF group fields to sync
 */
add_filter('acf/settings/save_json', function ( $path ) {
  $path = get_stylesheet_directory() . '/plugins/acf-json';
  return $path;
} );
add_filter('acf/settings/load_json',function ( $paths ) {
  unset($paths[0]);
  $paths[] = get_stylesheet_directory() . '/plugins/acf-json';
  return $paths;
} );
