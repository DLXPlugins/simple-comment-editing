<?php
/*
Plugin Name: Simple Comment Editing
Plugin URI: https://dlxplugins.com/plugins/simple-comment-editing/
Description: Allow your users to edit their comments.
Author: DLX Plugins
Version: 2.7.2
Requires at least: 5.0
Author URI: https://dlxplugins.com/
Contributors: ronalfy
Text Domain: simple-comment-editing
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}
define( 'SCE_SLUG', plugin_basename( __FILE__ ) );
define( 'SCE_VERSION', '2.7.2' );
define( 'SCE_FILE', __FILE__ );
define( 'SCE_SPONSORS_URL', 'https://github.com/sponsors/DLXPlugins' );

require_once 'autoloader.php';
require 'simple-comment-editing.php';
