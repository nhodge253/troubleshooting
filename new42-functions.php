<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri() . '/dist/css/custom.css' );

  // Javascript
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri() . '/assets/js/custom.js' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

/**
 * Remove "Posts" from main WordPress navigation.
 */
function remove_posts_menu_item() {
  remove_menu_page('edit.php');
}
add_action( 'admin_menu', 'remove_posts_menu_item' );

/**
 * Update WordPress media directory structure.
 */
function update_media_directory( $uploads ) {
  $directory = 'media';

  $uploads['path'] = WP_CONTENT_DIR . '/' . $directory;
  $uploads['url'] = WP_CONTENT_URL . '/' . $directory;
  $uploads['basedir'] = WP_CONTENT_DIR . '/' . $directory;
  $uploads['baseurl'] = WP_CONTENT_URL . '/' . $directory;
  $uploads['subdir'] = '';

  return $uploads;
}
add_filter( 'upload_dir', 'update_media_directory' );

/**
 * Disable date-based media directory structure.
 */
function disable_date_base_media_structure() {
  update_option( 'uploads_use_yearmonth_folders', 0 );
}
add_action( 'admin_init', 'disable_date_base_media_structure' );

/**
 * Insert Our Funder section before footer.
 */
function add_our_funder_section( $content ) {
  if ( is_page( 53 ) ):
    $content .= '
      <div id="our-funder" class="py-6 px-10 -mb-20 bg-white md:py-5 md:px-7 sm:py-4 sm:px-6">
        <div class="container">
          <div class="w-full">
            <img src="' . get_field( 'logo', 'option' ) . '" class="block w-full max-w-[240px] mt-6" alt="Funder Statement">

            <div>' . get_field( 'content', 'option' ) . '</div>
          </div>
        </div>
      </div>';
  endif;

  return $content;
}
add_filter( 'the_content', 'add_our_funder_section' );

/**
 * Customize custom post type archive titles.
 */
function customize_archive_title( $title ) {
  if ( is_post_type_archive() ):
    $title = post_type_archive_title( '', false );
  endif;

  return $title;
}
add_filter( 'get_the_archive_title', 'customize_archive_title' );

/**
 * Disable WordPress block editor for Resources custom post type.
 */
function disable_block_editor( $use_block_editor, $post_type ) {
  if ( $post_type === 'resource' ) return false;
  return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'disable_block_editor', 10, 2 );

/**
 * Add additional styles to single Resource view.
 */
function resource_styles() {
  $screen = get_current_screen();
  
  if ( $screen->id === 'resource' ):
    echo '
      <style>
        /* Reorder sidebar panels */
        #side-sortables {
          display: flex;
          flex-direction: column;
        }

        #side-sortables #submitdiv {
          order: -2;
        }

        #side-sortables #postimagediv {
          order: -1;
        }

        /* Hide default taxonomy selection panels */
        #tagsdiv-post_tag,
        #tagsdiv-grade,
        #tagsdiv-art-form,
        #tagsdiv-subject,
        #tagsdiv-audience,
        #tagsdiv-national-arts-standard,
        #tagsdiv-content-type {
          display: none;
        }

        /* Advanced Custom Fields panel styles */
        .acf-postbox .acf-fields .acf-field-true-false .acf-label {
          display: none;
        }

        .acf-postbox .acf-fields .acf-field-true-false .acf-input .acf-true-false .message {
          display: block;
          margin: 8px 0 0 0;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list {
          display: flex;
          flex-direction: column;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li {
          order: 0;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li:nth-of-type(14) {
          order: -2;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li:nth-of-type(13) {
          order: -1;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li:nth-of-type(1) {
          order: 1;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li:nth-of-type(2) {
          order: 2;
        }

        .acf-postbox .acf-fields .acf-field[data-name="grades"] .acf-checkbox-list li:nth-of-type(3) {
          order: 3;
        }
      </style>
    ';
  endif;
}
add_action( 'admin_head', 'resource_styles' );

/**
 * Use production images when developing locally.
 */
if ( $_SERVER['HTTP_HOST'] === 'localhost:8080' ):
  function replace_src_paths( $url ) {
    $local_file_path = ABSPATH . ltrim( parse_url( $url, PHP_URL_PATH ), '/' );
      
    if ( !file_exists( $local_file_path ) ):
      return str_replace( WP_HOME, 'https://edlibrarystage.wpengine.com/', $url );
    endif;

    return $url;
  }
  add_filter( 'wp_get_attachment_url', 'replace_src_paths' );

  function replace_srcset_paths( $sources ) {
    foreach ( $sources as &$source ):
      $local_file_path = ABSPATH . ltrim( parse_url( $source['url'], PHP_URL_PATH ), '/' );
          
      if ( !file_exists( $local_file_path ) ):
        $source['url'] = str_replace( WP_HOME, 'https://edlibrarystage.wpengine.com/', $source['url'] );
      endif;
    endforeach;

    return $sources;
  }
  add_filter( 'wp_calculate_image_srcset', 'replace_srcset_paths' );
endif;

//Hide clean title field from users
function hide_clean_title($field) {
  return false;
}
add_filter("acf/prepare_field/name=clean_title", "hide_clean_title");

// Function to strip HTML from the title and save it in ACF field
function update_clean_title_field($post_id) {
  if (get_post_type($post_id) == 'resource') {
      $post_title = get_the_title($post_id);
      $clean_title = wp_strip_all_tags($post_title);
      // Update the ACF field with the clean title
      update_field('clean_title', $clean_title, $post_id);
  }
}
add_action('save_post', 'update_clean_title_field');


// Function to update clean_title field for all existing resources
function update_clean_title_for_all_resources() {
  // Get all 'resource' posts
  $args = array(
      'post_type' => 'resource',
      'posts_per_page' => -1, // Get all posts
      'post_status' => 'publish', // Only published resources
  );
  
  $resources = new WP_Query($args);
  
  // Loop through each resource and update the clean_title field
  if ($resources->have_posts()) {
      while ($resources->have_posts()) {
          $resources->the_post();
          $post_id = get_the_ID();
          $post_title = get_the_title($post_id);
          // Strip HTML tags from the title
          $clean_title = wp_strip_all_tags($post_title);
          
          // Check if update_field function exists before calling it
          if (function_exists('update_field')) {
              // Update the ACF field 'clean_title'
              update_field('clean_title', $clean_title, $post_id);
          } else {
              // Optional: Add error handling if needed, e.g., logging
              error_log('ACF function update_field not found, unable to update clean_title for post ID: ' . $post_id);
          }
      }
  }
  wp_reset_postdata();
}


// Run this function once to update all existing resources
add_action('init', 'update_clean_title_for_all_resources');

//Remove admin bar styling
function custom_remove_admin_bar_css() {
  wp_deregister_style('admin-bar');
}
add_action('wp_enqueue_scripts', 'custom_remove_admin_bar_css', 11);

?>
