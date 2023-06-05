<?php
/**
 * Comment Edit Lite main file.
 *
 * @package DLXPlugins\comment-edit-lite
 */

/**
 * Plugin Name: Comment Edit Lite
 * Plugin URI: https://dlxplugins.com/plugins/comment-edit-lite/
 * Description: Allow your users to edit their comments.
 * Author: DLX Plugins
 * Version: 2.9.7
 * Requires PHP: 7.2
 * Requires at least: 5.0
 * Author URI: https://dlxplugins.com/
 * Contributors: ronalfy
 * Text Domain: simple-comment-editing
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}
define( 'SCE_SLUG', plugin_basename( __FILE__ ) );
define( 'SCE_VERSION', '2.9.7' );
define( 'SCE_FILE', __FILE__ );
define( 'SCE_SPONSORS_URL', 'https://github.com/sponsors/DLXPlugins' );

require_once 'lib/autoload.php';
require 'simple-comment-editing.php';
