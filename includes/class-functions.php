<?php
/**
 * Helper fuctions.
 *
 * @package SCE
 */

namespace SCE\Includes;

/**
 * Class functions
 */
class Functions {

	/**
	 * Check whether site is multisite or not.
	 *
	 * @return bool True if multisite, false if not.
	 */
	public static function is_multisite() {
		$sce = \Simple_Comment_Editing::get_instance();
		return $sce::is_multisite();
	}

	/**
	 * Sanitize an attribute based on type.
	 *
	 * @param array  $attributes Array of attributes.
	 * @param string $attribute  The attribute to sanitize.
	 * @param string $type       The type of sanitization you need (values can be int, text, float, bool, url).
	 *
	 * @return mixed Sanitized attribute. wp_error on failure.
	 */
	public static function sanitize_attribute( $attributes, $attribute, $type = 'text' ) {
		if ( isset( $attributes[ $attribute ] ) ) {
			switch ( $type ) {
				case 'text':
					return sanitize_text_field( $attributes[ $attribute ] );
				case 'bool':
					return filter_var( $attributes[ $attribute ], FILTER_VALIDATE_BOOLEAN );
				case 'int':
					return absint( $attributes[ $attribute ] );
				case 'float':
					if ( is_float( $attributes[ $attribute ] ) ) {
						return $attributes[ $attribute ];
					}
					return 0;
				case 'url':
					return esc_url( $attributes[ $attribute ] );
				case 'default':
					return new \WP_Error( 'sce_unknown_type', __( 'Unknown type.', 'simple-comment-editing' ) );
			}
		}
		return new \WP_Error( 'sce_attribute_not_found', __( 'Attribute not found.', 'simple-comment-editing' ) );
	}

	/**
	 * Convert Hex to RGBA
	 *
	 * @param string $color   The color to convert.
	 * @param int    $opacity The opacity.
	 *
	 * @return string rgba attribute.
	 */
	public static function hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided.
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided.
		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values.
		if ( strlen( $color ) === 6 ) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
				return $default;
		}

		// Convert hexadec to rgb.
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb).
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string.
		return $output;
	}

	/**
	 * Return the URL to the admin screen
	 *
	 * @param string $tab     Tab path to load.
	 * @param string $sub_tab Subtab path to load.
	 *
	 * @return string URL to admin screen. Output is not escaped.
	 */
	public static function get_settings_url( $tab = '', $sub_tab = '' ) {
		$options_url = admin_url( 'options-general.php?page=comment-edit-lite' );
		if ( ! empty( $tab ) ) {
			$options_url = add_query_arg( array( 'tab' => sanitize_title( $tab ) ), $options_url );
			if ( ! empty( $sub_tab ) ) {
				$options_url = add_query_arg( array( 'subtab' => sanitize_title( $sub_tab ) ), $options_url );
			}
		}
		return $options_url;
	}

	/**
	 * Get the current admin tab.
	 *
	 * @return null|string Current admin tab.
	 */
	public static function get_admin_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_DEFAULT );
		if ( $tab && is_string( $tab ) ) {
			return sanitize_text_field( sanitize_title( $tab ) );
		}
		return null;
	}

	/**
	 * Get the current admin sub-tab.
	 *
	 * @return null|string Current admin sub-tab.
	 */
	public static function get_admin_sub_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_DEFAULT );
		if ( $tab && is_string( $tab ) ) {
			$subtab = filter_input( INPUT_GET, 'subtab', FILTER_DEFAULT );
			if ( $subtab && is_string( $subtab ) ) {
				return sanitize_text_field( sanitize_title( $subtab ) );
			}
		}
		return null;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return string plugin slug.
	 */
	public static function get_plugin_slug() {
		return dirname( plugin_basename( SCE_FILE ) );
	}

	/**
	 * Return the plugin path.
	 *
	 * @return string plugin path.
	 */
	public static function get_plugin_path() {
		return plugin_basename( SCE_FILE );
	}

	/**
	 * Return the basefile for the plugin.
	 *
	 * @return string base file for the plugin.
	 */
	public static function get_plugin_file() {
		return plugin_basename( SCE_FILE );
	}

	/**
	 * Return the version for the plugin.
	 *
	 * @return float version for the plugin.
	 */
	public static function get_plugin_version() {
		return SCE_VERSION;
	}

	/**
	 * Get the Plugin Logo.
	 */
	public static function get_plugin_logo() {
		/**
		 * Filer the output of the plugin logo.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string URL to the plugin logo.
		 */
		return apply_filters( 'sce_plugin_logo_full', self::get_plugin_url( '/images/logo.png' ) );
	}

	/**
	 * Get the plugin author name.
	 */
	public static function get_plugin_author() {
		/**
		 * Filer the output of the plugin Author.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin Author name.
		 */
		$plugin_author = apply_filters( 'sce_plugin_author', 'MediaRon LLC' );
		return $plugin_author;
	}

	/**
	 * Return the Plugin author URI.
	 */
	public static function get_plugin_author_uri() {
		/**
		 * Filer the output of the plugin Author URI.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin Author URI.
		 */
		$plugin_author = apply_filters( 'sce_plugin_author_uri', 'https://mediaron.com' );
		return $plugin_author;
	}

	/**
	 * Get the Plugin Icon.
	 */
	public static function get_plugin_icon() {
		/**
		 * Filer the output of the plugin icon.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string URL to the plugin icon.
		 */
		return apply_filters( 'sce_plugin_icon', self::get_plugin_url( '/images/logo.png' ) );
	}

	/**
	 * Return the plugin name for the plugin.
	 *
	 * @return string Plugin name.
	 */
	public static function get_plugin_name() {
		/**
		 * Filer the output of the plugin name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin name.
		 */
		return apply_filters( 'sce_plugin_name', __( 'Simple Comment Editing', 'simple-comment-editing' ) );
	}

	/**
	 * Return the plugin description for the plugin.
	 *
	 * @return string plugin description.
	 */
	public static function get_plugin_description() {
		/**
		 * Filer the output of the plugin name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin description.
		 */
		return apply_filters( 'sce_plugin_description', __( 'Allow your users to edit comments.', 'simple-comment-editing' ) );
	}

	/**
	 * Retrieve the plugin URI.
	 */
	public static function get_plugin_uri() {
		/**
		 * Filer the output of the plugin URI.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin URI.
		 */
		return apply_filters( 'sce_plugin_uri', 'https://mediaron.com/simple-comment-editing/' );
	}

	/**
	 * Retrieve the plugin Menu Name.
	 */
	public static function get_plugin_menu_name() {
		/**
		 * Filer the output of the plugin menu name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin Menu Name.
		 */
		return apply_filters( 'sce_plugin_menu_name', __( 'Simple Comment Editing', 'simple-comment-editing' ) );
	}

	/**
	 * Retrieve the plugin title.
	 */
	public static function get_plugin_title() {
		/**
		 * Filer the output of the plugin title.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 3.0.0
		 *
		 * @param string Plugin Menu Name.
		 */
		return apply_filters( 'sce_plugin_menu_title', self::get_plugin_name() );
	}

	/**
	 * Returns appropriate html for KSES.
	 *
	 * @param bool $svg Whether to add SVG data to KSES.
	 */
	public static function get_kses_allowed_html( $svg = true ) {
		$allowed_tags = wp_kses_allowed_html();

		$allowed_tags['nav']        = array(
			'class' => array(),
		);
		$allowed_tags['a']['class'] = array();

		if ( ! $svg ) {
			return $allowed_tags;
		}
		$allowed_tags['svg'] = array(
			'xmlns'       => array(),
			'fill'        => array(),
			'viewbox'     => array(),
			'role'        => array(),
			'aria-hidden' => array(),
			'focusable'   => array(),
			'class'       => array(),
		);

		$allowed_tags['path'] = array(
			'd'       => array(),
			'fill'    => array(),
			'opacity' => array(),
		);

		$allowed_tags['g'] = array();

		$allowed_tags['use'] = array(
			'xlink:href' => array(),
		);

		$allowed_tags['symbol'] = array(
			'aria-hidden' => array(),
			'viewBox'     => array(),
			'id'          => array(),
			'xmls'        => array(),
		);

		return $allowed_tags;
	}

	/**
	 * Get the plugin directory for a path.
	 *
	 * @param string $path The path to the file.
	 *
	 * @return string The new path.
	 */
	public static function get_plugin_dir( $path = '' ) {
		$dir = rtrim( plugin_dir_path( SCE_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Return a plugin URL path.
	 *
	 * @param string $path Path to the file.
	 *
	 * @return string URL to to the file.
	 */
	public static function get_plugin_url( $path = '' ) {
		$dir = rtrim( plugin_dir_url( SCE_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Gets the highest priority for a filter.
	 *
	 * @param int $subtract The amount to subtract from the high priority.
	 *
	 * @return int priority.
	 */
	public static function get_highest_priority( $subtract = 0 ) {
		$highest_priority = PHP_INT_MAX;
		$subtract         = absint( $subtract );
		if ( 0 === $subtract ) {
			--$highest_priority;
		} else {
			$highest_priority = absint( $highest_priority - $subtract );
		}
		return $highest_priority;
	}
}
