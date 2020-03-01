<?php
/*
 *	Plugin Name: 	Cookie Consent
 *	Plugin URI: 	https://github.com/tim-green/greeny-cookie-consent
 *	Description: 	A simple, dev friendly plugin for WordPress that let visitors know that the site is using cookies.
 *	Author: 		Tim Green
 *	Version: 		1.0.0
 *	Author URI: 	https://www.timgreen.ws/
 *	Text Domain: 	cookie-consent
 *	Domain Path: 	/languages
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TGWP Cookie Consent
 */
class TGWP_Cookie_Consent {

	/**
	 * The Plugin Path
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * The Plugin URL
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * The Plugin Version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The single instance of the class
	 *
	 * @var TGWP_Cookie_Consent|null
	 */
	protected static $_instance = null;

	/**
	 * Instance
	 *
	 * @return TGWP_Cookie_Consent
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Set Developer Mode Constant.
		if ( ! defined( 'TGWP_DEV_MODE' ) ) {
			define( 'TGWP_DEV_MODE', apply_filters( 'tgwp_dev_mode', false ) );
		}

		// Set the plugin path.
		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		// Set the plugin URL.
		$this->plugin_url = untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );

		// Hooks to run on plugin init.
		$this->init_hooks();

		do_action( 'tgwp_loaded' );

	}

	/**
	 * Hooks into various necessary hooks
	 * at the init time.
	 *
	 * @return void
	 */
	public function init_hooks() {

		do_action( 'before_tgwp_init' );

		// Add Scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );

		// Add Styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );

		// Register customizer fields.
		add_action( 'customize_register', [ $this, 'customizer_settings' ] );

		// Add Translation Loading.
		add_action( 'plugins_loaded', [ $this, 'load_languages' ] );

		// Add body class
		add_filter( 'body_class', [ $this, 'banner_body_class' ] );

		do_action( 'tgwp_init' );

	}

	/**
	 * Load translations in the right order.
	 */
	public function load_languages() {

		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'cookie-consent' );

		unload_textdomain( 'cookie-consent' );

		// Start checking in the main language dir.
		load_textdomain( 'cookie-consent', WP_LANG_DIR . '/cookie-consent/cookie-consent-' . $locale . '.mo' );

		// Otherwise, load from the plugin.
		load_plugin_textdomain( 'cookie-consent', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Load necessary scripts for the plugin.
	 */
	public function scripts() {

		/**
		 * Don't load anything if the user has
		 * already consented to cookies.
		 */
		if ( $this->has_user_consented() ) {
			return;
		}

		/**
		 * We need jQuery for this plugin.
		 */
		wp_enqueue_script( 'jquery' );

		wp_register_script( 'cookie-consent', $this->plugin_url . '/assets/js/cookie-banner.js', [ 'jquery' ], $this->version, true );

		/**
		 * We localize the script to add our texts.
		 * These are changeable by filters. See the functions
		 * that get the texts below.
		 */
		wp_localize_script( 'cookie-consent', 'tgwp', [
			'cookieConsentTitle' => $this->get_consent_title(),
			'cookieConsentText'  => $this->get_consent_text(),
			'acceptText'         => $this->get_accept_text(),
			'style'              => $this->get_style(),
		] );

		// Finally, enqueue!
		wp_enqueue_script( 'cookie-consent' );

	}

	/**
	 * Load the built-in styles for the plugin.
	 */
	public function styles() {

		/**
		 * Don't load anything if the user has
		 * already consented to cookies.
		 */
		if ( $this->has_user_consented() ) {
			return;
		}

		/**
		 * Don't load anything if we are asked not
		 * to load the stylesheet.
		 */
		if ( false === apply_filters( 'tgwp_load_stylesheet', true ) || true === TGWP_DEV_MODE ) {
			return;
		}

		/**
		 * Register the main stylesheet.
		 */
		wp_register_style( 'cookie-consent', $this->plugin_url . '/assets/css/cookie-banner.min.css', false, $this->version, 'all' );

		// Finally, enqueue!
		wp_enqueue_style( 'cookie-consent' );

	}

	/**
	 * Add settings in the customer.
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	public function customizer_settings( $wp_customize ) {

		/**
		 * Set the filter to false to prevent the customizer
		 * settings from showing up.
		 */
		if ( ! apply_filters( 'tgwp_enable_customizer', true ) ) {
			return;
		}

		$wp_customize->add_section( 'tgwp_cookie_banner', [
			'title'       => __( 'Cookie Banner', 'cookie-consent' ),
			'description' => __( 'Customise the appearance and texts in the cookie banner, used for EU cookie compliance.', 'cookie-consent' ),
			'priority'    => 120,
		] );

		/**
		 * Title
		 */
		$wp_customize->add_setting( 'tgwp_title', [
			'default'    => __( 'This website uses cookies to enhance the browsing experience', 'cookie-consent' ),
			'type'       => 'option',
			'capability' => apply_filters( 'tgwp_edit_title_capability', 'edit_theme_options' ),
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'tgwp_title', [
			'label'       => __( 'Title', 'cookie-consent' ),
			'description' => __( 'Keep the title short. It is styled prominently.', 'cookie-consent' ),
			'settings'    => 'tgwp_title',
			'section'     => 'tgwp_cookie_banner',
			'priority'    => 80,
		] ) );

		/**
		 * Text with link
		 */
		$wp_customize->add_setting( 'tgwp_text', [
			'default'    => __( 'By continuing you give us permission to deploy cookies as per our %linkstart% privacy and cookies policy %linkend%.', 'cookie-consent' ),
			'type'       => 'option',
			'capability' => apply_filters( 'tgwp_edit_text_capability', 'edit_theme_options' ),
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'tgwp_text', [
			'label'       => __( 'Text', 'cookie-consent' ),
			'description' => __( 'A secondary line of info about your cookie usage. Remember to link to the policy by using the %linkstart% and %linkend% placeholders.', 'cookie-consent' ),
			'settings'    => 'tgwp_text',
			'section'     => 'tgwp_cookie_banner',
			'priority'    => 80,
		] ) );

		/**
		 * URL setting
		 */
		$wp_customize->add_setting( 'tgwp_policy_url', [
			'default'    => get_privacy_policy_url(),
			'type'       => 'option',
			'capability' => apply_filters( 'tgwp_edit_policy_url_capability', 'edit_theme_options' ),
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'tgwp_policy_url', [
			'label'       => __( 'Cookie Policy Link', 'cookie-consent' ),
			'description' => __( 'Enter a link to your privacy and cookie policy where you outline the use of cookies.', 'cookie-consent' ),
			'settings'    => 'tgwp_policy_url',
			'section'     => 'tgwp_cookie_banner',
			'priority'    => 80,
		] ) );

		/**
		 * Button
		 */
		$wp_customize->add_setting( 'tgwp_button', [
			'default'    => __( 'I Understand', 'cookie-consent' ),
			'type'       => 'option',
			'capability' => apply_filters( 'tgwp_edit_button_capability', 'edit_theme_options' ),
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'tgwp_button', [
			'label'       => __( 'Button Text', 'cookie-consent' ),
			'description' => __( 'Displays the message on the action button that closes the consent banner and assumes consent.', 'cookie-consent' ),
			'settings'    => 'tgwp_button',
			'section'     => 'tgwp_cookie_banner',
			'priority'    => 80,
		] ) );

		/**
		 * Style
		 */
		$wp_customize->add_setting( 'tgwp_style', [
			'default'    => 'top',
			'type'       => 'option',
			'capability' => apply_filters( 'tgwp_edit_style_capability', 'edit_theme_options' ),
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'tgwp_style', [
			'label'       => __( 'Style', 'cookie-consent' ),
			'description' => __( 'The banner can appear both at the top, or overlaid at the bottom of the page.', 'cookie-consent' ),
			'settings'    => 'tgwp_style',
			'section'     => 'tgwp_cookie_banner',
			'priority'    => 80,
			'type'        => 'radio',
			'choices'     => [
				'top'     => __( 'Top', 'cookie-consent' ),
				'overlay' => __( 'Overlay', 'cookie-consent' ),
			],
		] ) );

	}

	/**
	 * Get the Policy URL from the settings.
	 *
	 * @return string
	 */
	public function get_policy_url() {
		$wp_policy_url_page_id = get_option( 'wp_page_for_privacy_policy');
		$default_url = '#';
		if( $wp_policy_url_page_id ){
			$default_url = get_permalink( $wp_policy_url_page_id );
		}
		return apply_filters( 'tgwp_policy_url', get_option( 'tgwp_policy_url', $default_url ) );
	}

	/**
	 * Get the informational consent title.
	 *
	 * @return string
	 */
	public function get_consent_title() {

		$title = __( 'This website uses cookies to enhance the browsing experience', 'cookie-consent' );

		if ( get_option( 'tgwp_title' ) ) {
			$title = get_option( 'tgwp_title' );
		}

		return apply_filters( 'tgwp_consent_title', $title );
	}

	/**
	 * Get the informational consent text.
	 *
	 * @return string
	 */
	public function get_consent_text() {

		$policy_url = $this->get_policy_url();

		/* translators: 1. Policy URL */
		$text = sprintf( __( 'By continuing you give us permission to deploy cookies as per our <a href="%s" rel="nofollow">privacy and cookies policy</a>.', 'cookie-consent' ), $policy_url );

		if ( get_option( 'tgwp_text' ) ) {

			$text = get_option( 'tgwp_text' );

			// check if we have linkstart and linkend, replace with link
			if ( strpos( $text, '%linkstart%' ) !== false && strpos( $text, '%linkend%' ) !== false ) {
				$text = str_replace( '%linkstart%', '<a href="' . $policy_url . '" rel="nofollow">', $text );
				$text = str_replace( '%linkend%', '</a>', $text );
			} // if we only have linkstart but no linked, add linkend
			elseif ( strpos( $text, '%linkstart%' ) !== false && strpos( $text, '%linkend%' ) === false ) {
				$text = str_replace( '%linkstart%', '<a href="' . $policy_url . '" rel="nofollow">', $text );
				$text = $text . '</a>';
			} // if we have linkend, but no linkstart, remove linkend
			elseif ( strpos( $text, '%linkstart%' ) === false && strpos( $text, '%linkend%' ) !== false ) {
				$text = str_replace( '%linkend%', '', $text );
			} // if we have a start a-tag but no end, add the end
			elseif ( strpos( $text, '<a' ) !== false && strpos( $text, '</a' ) === false ) {
				$text = $text . '</a>';
			} // if we only have an end a-tag, remove the endtag.
			elseif ( strpos( $text, '<a' ) === false && strpos( $text, '</a' ) !== false ) {
				$text = str_replace( '</a>', '', $text );
			}
		}


		return apply_filters( 'tgwp_consent_text', $text, $policy_url );
	}

	/**
	 * Get the text for the accept button.
	 *
	 * @return string
	 */
	public function get_accept_text() {
		$accept = __( 'I Understand', 'cookie-consent' );

		if ( get_option( 'tgwp_button' ) ) {
			$accept = get_option( 'tgwp_button' );
		}

		return apply_filters( 'tgwp_accept_text', $accept );
	}

	/**
	 * Get the style for the banner.
	 *
	 * @return string
	 */
	public function get_style() {
		$style = 'top';

		if ( get_option( 'tgwp_style' ) ) {
			$style = get_option( 'tgwp_style' );
		}

		return apply_filters( 'tgwp_style', $style );
	}

	/**
	 * Get the name of the cookie.
	 *
	 * @return string
	 */
	public function get_cookie_name() {
		return apply_filters( 'tgwp_cookie_name', 'EUConsentCookie' );
	}

	/**
	 * Check if the user has consented
	 * to cookies or not.
	 *
	 * @return boolean
	 */
	public function has_user_consented() {

		// Default to false.
		$has_consented = false;

		// Get the cookie name.
		$cookie_name = $this->get_cookie_name();

		// Get which value is considered consented.
		$active_value = apply_filters( 'tgwp_cookie_active_value', '1' );

		if ( isset( $_COOKIE[ $cookie_name ] ) && $active_value === $_COOKIE[ $cookie_name ] ) {
			$has_consented = true;
		}

		return apply_filters( 'tgwp_has_user_consented', $has_consented, $cookie_name, $active_value );

	}

	/**
	 * Add body classes
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function banner_body_class( $classes ) {

		if ( $this->has_user_consented() ) {
			$classes[] = 'has-tgwp-consented';
		} else {
			$classes[] = 'has-tgwp-banner';
			$classes[] = 'tgwp-style-' . $this->get_style();
		}

		return $classes;
	}

}

/**
 * Returns an instance of the plugin class.
 *
 * @return TGWP_Cookie_Consent
 */
function tgwp_cookie_consent() {
	return TGWP_Cookie_Consent::instance();
}

// Initialize the class instance only once
tgwp_cookie_consent();
