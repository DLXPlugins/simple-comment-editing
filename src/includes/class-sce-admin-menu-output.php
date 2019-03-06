<?php
if (!defined('ABSPATH')) die('No direct access.');
class SCE_Plugin_Admin_Menu_Output {

	public function __construct() {
		$this->output_options();
	}

	/**
	 * Output options
	 *
	 * @since 2.3.7
	 * @access public
	 * @see __construct
	 */
	public function output_options() {

		if ( isset( $_POST['submit'] ) && isset( $_POST['options'] ) ) {
			check_admin_referer( 'save_sce_options' );
			$this->update_options( $_POST['options'] );
			printf( '<div class="updated"><p><strong>%s</strong></p></div>', __( 'Your options have been saved.', 'simple-comment-editing' ) );
		}
		// Get options and defaults
		$options = get_site_option( 'sce_options', false );
		if ( false === $options ) {
			$options = $this->get_defaults();
		} elseif( is_array( $options ) ) {
			$options = wp_parse_args( $options, $this->get_defaults() );
		} else {
			$options = $this->get_defaults();
		}
		?>
		<div class="wrap">
			<form action="" method="POST">
				<?php wp_nonce_field('save_sce_options'); ?>
				<h1><?php esc_html_e( 'Simple Comment Editing', 'simple-comment-editing' ); ?></h1>
				<p><?php esc_html_e( 'Welcome to Simple Commment Editing!', 'simple-comment-editing' ); ?></p>
				<p><?php esc_html_e( 'For more options and configuration, please try: ', 'simple-comment-editing' ); ?><a target="_blank" href="https://mediaron.com/simple-comment-editing-options/"><?php esc_html_e( 'Simple Comment Editing Options', 'simple-comment-editing' ); ?></a></p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="sce-timer"><?php esc_html_e( 'Edit Timer in Minutes', 'simple-comment-editing' ); ?></label></th>
							<td>
								<input id="sce-timer" class="regular-text" type="number" value="<?php echo esc_attr( absint( $options['timer'] ) ); ?>" name="options[timer]" />
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Options', 'simple-comment-editing' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Update options via sanitization
	 *
	 * @since 2.3.7
	 * @access public
	 * @param array $options array of options to save
	 * @return void
	 */
	private function update_options( $options ) {
		foreach( $options as $key => &$option ) {
			switch( $key ) {
				case 'timer':
					$timer = absint( $options[$key] );
					if( 0 === $timer ) {
						$timer = 5;
					}
					$option = $timer;
					break;
				default:
					$option = sanitize_text_field( $options[$key] );
					break;
			}
		}
		update_site_option( 'sce_options', $options );
	}

	/**
	 * Get defaults for SCE options
	 *
	 * @since 2.3.7
	 * @access public
	 *
	 * @return array default options
	 */
	private function get_defaults() {
		$defaults = array(
			'timer'                     => 5,
		);
		return $defaults;
	}

}