<?php
/**
 * @package Picasaweb_Embed
 * @author Devon Buchanan
 * @version 1.4
 */
/*
Plugin Name: Picasa Picture Embed
Plugin URI: http://wordpress.org/extend/plugins/picasa-picture-embed/
Description: A plugin that allows you to embed picasa photos with the Wordpress 2.9 embedding feature. Include the url of the photo on a line of its own or between [embed] tags and the photo will be embedded.
Author: Devon Buchanan
Version: 1.3
Author URI: http://www.srcf.ucam.org/~db505/
*/

// Add the ./Library directory to the PHP path
$clientLibraryPath = dirname(__FILE__) . '/Library';
$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);

// I use the Zend Picasa GData classes
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_Photos');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');


/**
* Embed plugin for adding Picasa Web Albums photos. Picasa does not support oEmbed.
*/
class Picasaweb_Embed
{
  var $picasaweb;
  // Available photo sizes taken from http://code.google.com/apis/picasaweb/docs/2.0/reference.html#gphoto_thumbnail
  var $sizes = array(32, 48, 64, 72, 94, 104, 110, 128, 144, 150, 160, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600);
  
  /**
   * PHP4 constructor
   */
  function Picasaweb_Embed() {
    return $this->__construct();
  }
	
  /**
   * PHP5 constructor
   */
  function __construct()
  {
    // After a post is saved, invalidate the oEmbed cache
    add_action( 'save_post', array(&$this, 'delete_picasaweb_caches') );
    wp_embed_register_handler( 'picasaweb', '|http://picasaweb\.google\.(?P<tld>[A-Za-z.]{2,5})/(?P<username>[^/]+)/(?P<albumname>[^#?]+)(\?[^#]*)?#(?P<photoid>\d+)|i', array(&$this, 'handler'));
    $this->picasaweb = new Zend_Gdata_Photos();
    // handler() is dependant on the array being in ascending order
    sort($this->sizes);
  }

  /**
   * The Picasa Web Albums embed handler callback.
   *
   * @see WP_Embed::register_handler()
   * @see WP_Embed::shortcode()
   *
   * @param array $matches The regex matches from the provided regex when calling {@link wp_embed_register_handler()}.
   * @param array $attr Embed attributes.
   * @param string $url The original URL that was matched by the regex.
   * @param array $rawattr The original unmodified attributes.
   * @return string The embed HTML.
   */
  function handler( $matches, $attr, $url, $rawattr ) {
    global $post, $wp_embed;
    $post_ID = ( !empty($post->ID) ) ? $post->ID : null;
    if ( !empty($wp_embed->post_ID) ) // Potentially set by WP_Embed::cache_oembed()
      $post_ID = $wp_embed->post_ID;

    // Create a query for the photo
    $query = $this->picasaweb->newPhotoQuery();
    $query->setUser($matches['username']);
    $query->setAlbumName($matches['albumname']);
    $query->setPhotoId($matches['photoid']);
    
    $html = '';
    if ($post_ID) {
      // Check the cache
      $cachekey = '_picasaweb_' . md5( $url . serialize( $attr ) );
      $html = get_post_meta( $post_ID, $cachekey, true );

      // Failures are cached
      if ( '{{unknown}}' === $html ) {
        return false;
      }
      if (empty($html)) {
        try {
          // Run the query to get the entry
          $photoEntry = $this->picasaweb->getPhotoEntry($query);
	  $contentUrl = "";
          if ($photoEntry->getMediaGroup()->getContent() != null) {
            $mediaContentArray = $photoEntry->getMediaGroup()->getContent();
            $contentUrl = $mediaContentArray[0]->getUrl();
            $contentWidth = $mediaContentArray[0]->getWidth();
            $contentHeight = $mediaContentArray[0]->getHeight();
	  }
      	} catch (Zend_Gdata_App_HttpException $e) {
          error_log( $e->getMessage(), 0);
          return false;
      	} catch (Zend_Gdata_App_Exception $e) {
          error_log( $e->getMessage(), 0);
          update_post_meta( $post_ID, $cachekey, '{{unknown}}' );
          return false;
	}
      	// Expand the image's size to the supplied dimensions
      	if ( !empty($rawattr['width']) && !empty($rawattr['height']) ) {
          list( $width, $height ) = wp_expand_dimensions($contentWidth, $contentHeight, (int) $attr['width'], (int) $attr['height']);
      	} else {
          list( $width, $height ) = wp_expand_dimensions( 425, 344, $attr['width'], $attr['height'] );
        }
	// Pick the largest needed URL based on the new size
        $largestSide = ($width > $height) ? $width : $height;
        $thumbnailSize = 0;
        foreach ($this->sizes as $size) {
	  if ($size > $largestSide) {
            $thumbnailSize = $size;
            break;
          }
        }
        if ($thumbnailSize === 0) {
          // No thumbnail is large enough -- use the full size image
          $url = $contentUrl;
        } else {
          // Use an image smaller than the full size to save bandwidth
          $url = preg_replace('|(https?:/(?:/[^/]+){5})(/.+)|', "$1/s{$thumbnailSize}$2", $contentUrl);
        }
        // Create the embedding HTML, including width and height attributes if they were stated.
        $html = '<img src="' . $url .'" ';
        if ( !empty($rawattr['width']) ) {
          $html .= 'width="' . $width .'" ';
        }
        if ( !empty($rawattr['height']) ) {
          $html .= 'height="' . $height . '" ';
        }
        $html .= '/>';
        // Cache the created HTML
        update_post_meta( $post_ID, $cachekey, $html );
      }
    }
    // If there was a result, return it
    return apply_filters( 'embed_picasaweb', $html, $matches, $attr, $url, $rawattr );
  }

  /**
   * Delete all embed caches.
   *
   * @param int $post_ID Post ID to delete the caches for.
   */
  function delete_picasaweb_caches( $post_ID ) {
    $post_metas = get_post_custom_keys( $post_ID );
      if ( empty($post_metas) )
        return;

      foreach( $post_metas as $post_meta_key ) {
        if ( '_picasaweb_' == substr( $post_meta_key, 0, 11 ) )
	  delete_post_meta( $post_ID, $post_meta_key );
      }
  }
}
$picasaweb_embed = new Picasaweb_Embed();

function  picasaweb_embed_handler( $matches, $attr, $url, $rawattr ) {
  global $picasaweb_embed;
  $picasaweb_embed->handler( $matches, $attr, $url, $rawattr );
}

?>
