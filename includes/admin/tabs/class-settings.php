<?php
/**
 * Register the Settings tab and any sub-tabs.
 *
 * @package SCE
 */

namespace SCE\Includes\Admin\Tabs;

use SCE\Includes\Functions as Functions;
use SCE\Includes\Admin\Options as Options;

/**
 * Output the settings tab and content.
 */
class Settings extends Tabs {

	/**
	 * Tab to run actions against.
	 *
	 * @var $tab Settings tab.
	 */
	private $tab = 'settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'sce_admin_tabs', array( $this, 'add_tab' ), 1, 1 );
		add_filter( 'sce_admin_sub_tabs', array( $this, 'add_sub_tab' ), 1, 3 );
		add_action( 'sce_output_' . $this->tab, array( $this, 'output_settings' ), 1, 3 );
	}

	/**
	 * Add the settings tab and callback actions.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array of tabs.
	 */
	public function add_tab( $tabs ) {
		$tabs[] = array(
			'get'    => $this->tab,
			'action' => 'sce_output_' . $this->tab,
			'url'    => Functions::get_settings_url( $this->tab ),
			'label'  => _x( 'Settings', 'Tab label as settings', 'simple-comment-editing' ),
			'icon'   => 'home-heart',
		);
		return $tabs;
	}

	/**
	 * Add the settings main tab and callback actions.
	 *
	 * @param array  $tabs        Array of tabs.
	 * @param string $current_tab The current tab selected.
	 * @param string $sub_tab     The current sub-tab selected.
	 *
	 * @return array of tabs.
	 */
	public function add_sub_tab( $tabs, $current_tab, $sub_tab ) {
		if ( ( ! empty( $current_tab ) || ! empty( $sub_tab ) ) && $this->tab !== $current_tab ) {
			return $tabs;
		}
		return $tabs;
	}

	/**
	 * Begin settings routing for the various outputs.
	 *
	 * @param string $tab     Current tab.
	 * @param string $sub_tab Current sub tab.
	 */
	public function output_settings( $tab, $sub_tab = '' ) {
		if ( $this->tab === $tab ) {
			if ( empty( $sub_tab ) || $this->tab === $sub_tab ) {
				if ( isset( $_POST['submit'] ) && isset( $_POST['options'] ) ) {
					check_admin_referer( 'save_sce_options' );
					Options::update_options( $_POST['options'] ); // phpcs:ignore
					printf( '<div class="updated"><p><strong>%s</strong></p></div>', esc_html__( 'Your options have been saved.', 'simple-comment-editing' ) );
				}
				// Get options and defaults.
				$options = Options::get_options();
				?>
				<div class="sce-admin-panel-area">
					<div class="sce-panel-row">
						<form action="" method="POST">
							<?php wp_nonce_field( 'save_sce_options' ); ?>
							<h1><?php esc_html_e( 'Welcome to Comment Edit Lite!', 'simple-comment-editing' ); ?></h1>
							<p><?php esc_html_e( 'For more options, stats, restoration of edited comments, and more configuration, please try: ', 'simple-comment-editing' ); ?><a target="_blank" href="https://dlxplugins.com/plugins/comment-edit-pro/"><?php esc_html_e( 'Comment Edit Pro', 'simple-comment-editing' ); ?></a></p>
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="sce-timer"><?php esc_html_e( 'Edit Timer in Minutes', 'simple-comment-editing' ); ?></label></th>
										<td>
											<input id="sce-timer" class="regular-text" type="number" value="<?php echo esc_attr( absint( $options['timer'] ) ); ?>" name="options[timer]" />
										</td>
									</tr>
									<tr>
									<th scope="row"><label for="sce-timer-appearance"><?php esc_html_e( 'Timer Appearance', 'simple-comment-editing' ); ?></label></th>
									<td>
										<select name="options[timer_appearance]">
											<option value="words" <?php selected( 'words', $options['timer_appearance'] ); ?>><?php esc_html_e( 'Words', 'simple-comment-editing' ); ?></option>
											<option value="compact" <?php selected( 'compact', $options['timer_appearance'] ); ?>><?php esc_html_e( 'Compact', 'simple-comment-editing' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
								<th scope="row"><label for="sce-button-theme"><?php esc_html_e( 'Button Theme', 'simple-comment-editing-options' ); ?></label></th>
								<td>
									<select name="options[button_theme]">
										<option value="default" <?php selected( 'default', $options['button_theme'] ); ?>><?php esc_html_e( 'None', 'simple-comment-editing-options' ); ?></option>
										<option value="regular" <?php selected( 'regular', $options['button_theme'] ); ?>><?php esc_html_e( 'Regular', 'simple-comment-editing-options' ); ?></option>
										<option value="dark" <?php selected( 'dark', $options['button_theme'] ); ?> ><?php esc_html_e( 'Dark', 'simple-comment-editing-options' ); ?></option>
										<option value="light" <?php selected( 'light', $options['button_theme'] ); ?>><?php esc_html_e( 'Light', 'simple-comment-editing-options' ); ?></option>
									</select>
									<input type="hidden" value="false" name="options[show_icons]" />
									<p><input id="sce-allow-icons" type="checkbox" value="true" name="options[show_icons]" <?php checked( true, $options['show_icons'] ); ?> /> <label for="sce-allow-icons"><?php esc_html_e( 'Allow icons for the buttons. Recommended if you have selected a button theme.', 'simple-comment-editing-options' ); ?></label></p>
									<p class="sce-theme-preview">
										<strong>
										<?php
											esc_html_e( 'Button Theme Preview:', 'simple-comment-editing' );
										?>
										</strong>
										<a data-animation-effect="zoom" data-animation-duration="1000" data-fancybox data-src="#sce-screenshot-default" data-caption="SCE Default Theme" href="javascript:;"><?php esc_html_e( 'Default Theme', 'simple-comment-editing' ); ?></a> | <a data-animation-effect="zoom" data-animation-duration="1000" data-fancybox data-src="#sce-screenshot-dark" data-caption="SCE Dark Theme" href="javascript:;"><?php esc_html_e( 'Dark Theme', 'simple-comment-editing' ); ?></a> | <a data-animation-effect="zoom" data-animation-duration="1000" data-fancybox data-src="#sce-screenshot-light" data-caption="SCE Light Theme" href="javascript:;"><?php esc_html_e( 'Light Theme', 'simple-comment-editing' ); ?></a>
									</p>
								</td>
							</tr>
								</tbody>
							</table>
							<div id="sce-screenshot-default" style="display: none;">
								<img src="<?php echo esc_url( Functions::get_plugin_url( '/images/screenshot-theme-default.png' ) ); ?>" alt="SCE Default Theme Screenshot" />
							</div>
							<div id="sce-screenshot-dark" style="display: none;">
								<img src="<?php echo esc_url( Functions::get_plugin_url( '/images/screenshot-theme-dark.png' ) ); ?>" alt="SCE Dark Theme Screenshot" />
							</div>
							<div id="sce-screenshot-light" style="display: none;">
								<img src="<?php echo esc_url( Functions::get_plugin_url( '/images/screenshot-theme-light.png' ) ); ?>" alt="SCE Light Theme Screenshot" />
							</div>
							
							<?php submit_button( __( 'Save Options', 'simple-comment-editing' ), 'sce-button sce-button-info', 'submit', true ); ?>
						</form>
					</div>
				</div>
				<div class="sce-admin-panel-area">
					<h3 class="sce-panel-heading">
						<a href="https://dlxplugins.com/plugins/comment-edit-pro"><img id="sce-options-logo" src="<?php echo esc_url( Functions::get_plugin_url( 'images/comment-edit-base.png' ) ); ?>" alt="Comment Edit Pro logo" /></a>
					</h3>
					<div class="sce-panel-row">
						<p class="description">
							<?php echo wp_kses_post( '<strong>Go Pro</strong> with <strong>Comment Edit Pro</strong> and its useful comment utilities.', 'simple-comment-editing' ); ?>
						</p>
						<ul>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/comment-editing">Additional Comment Editing Features</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/comment-avatars">Comment Avatars</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/gravatar-privacy-protection">Gravatar Privacy Protection</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/integrations/recaptcha-3-support">reCAPTCHA 3 Support</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/integrations/akismet-spam-protection">Akismet Spam Protection Integration</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/spam-protection/cloudflare-turnstile">Cloudflare Turnstile Support</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/comment-character-control">Comment Character Control</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/frontend-editing">Front-end Comment Editing</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/integrations/slack-integration">Slack Integration</a></li>
							<li><a href="https://docs.dlxplugins.com/v/comment-edit-pro/features-overview/webhooks">Webhooks</a></li>
						</ul>
					</div>
					<div class="sce-panel-row">
						<a class="sce-button sce-button-info" href="https://dlxplugins.com/plugins/comment-edit-pro" target="_blank"> <?php esc_html_e( 'Find out More About Comment Edit Pro', 'simple-comment-editing' ); ?></a>
					</div>
				</div>
				<?php
			}
		}
	}
}
