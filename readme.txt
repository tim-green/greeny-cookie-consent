=== Cookie Consent ===
Contributors: Tim Green
Tags: cookies, cookie notice, eu cookie law, cookie compliance, cookie banner, cookie consent
Requires at least: 5.0
Tested up to: 5.3.2
Requires PHP: 7.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and friendly WordPress plugin for developer's that let visitors know that the site is using cookies.

== Description ==

There are many WordPress plugins out there which does a lot of fancy things with the cookie consent. I just wanted to developed a WordPress plugin, and it seems like a Cookie based plugin is a good category to start with, this plugin is really lightweight and developer-friendly, and so I created my own.

This plugin isn't meant for the crowds who want tons of configurable options in the admin, many use this plugin with the default styling because it is so lightweight and matches a lot of websites who wants a notification looking style

For the developer who wants the functionality and being able to override the styles in the theme without bloat... here is a plugin for you. You have filters and actions available to you at every step of the process.

See the installation section for more information on how to install. The FAQ section has important information on how to customise the plugin.

== Installation ==

I highly recommend using the built-in plugin installer in WordPress, but if you want to be one of those wild ones you are more than welcome to install the plugin manually:

1. Upload 'cookie-consent' to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Insert a link to your cookie policy in the 'Settings > Reading' page.

== Frequently Asked Questions ==

= How do I set the cookie policy link? =
You can set the URL to the cookie policy page in the customizer under the "Cookie Banner" section, or use the filter `tgwp_policy_url` to return your own link.

= How do I customise the style or disable it altogether? =
Out of the box, the plugin includes a lightweight stylesheet with two placement options (top & overlay). If you don't want to use our default colouring, you can easily prevent us from including the styles.

Just define the following filter somewhere in your code, such as the theme functions.php file:

    add_filter( 'tgwp_load_stylesheet', '__return_false' );

Additionally, for quick theming to your theme's custom colours, I support a series of CSS variables set on `body.has-tgwp-banner` like so:

    body.has-tgwp-banner {
        --tgwp-background-color: #282b2d;
        --tgwp-text-color: #ccc;
        --tgwp-link-color: #ccc;
        --tgwp-link-color-hover: #fff;
        --tgwp-banner-spacing: 1.4rem 0;
        --tgwp-close-button: #474d50;
        --tgwp-close-button-hover: #666;
        --tgwp-close-button-text: white;
        --tgwp-close-button-hover-text: white;
        --tgwp-button-radius: 4px;
    }

If you would like to add your own style in addition to the two offered, you can override the style setting with the `tgwp_style` filter. This would let you style outside the two core positions.

= Can I change the texts and/or button label? =
You can change the two lines of text and the button label from the customizer under the "Cookie Banner" section. Alternatively, you can use a set of filters to return values before rendering.

Modiyfing the title: `tgwp_consent_title`
Modiyfing the text info: `tgwp_consent_text`
Modiyfing the accept button label: `tgwp_accept_text`

Just set their value somewhere in your code, such as in the functions.php file of your theme:

    function tgwp_modify_consent_text( $text ) {
        $text = __( 'This is my custom text about how we use cookies.', 'YOURTEXTDOMAIN' );
        return $text;
    }

    add_filter( 'tgwp_consent_text', 'tgwp_modify_consent_text' );

    function tgwp_modify_accept_text( $text ) {
        $text = __( 'I Accept', 'YOURTEXTDOMAIN' );
        return $text;
    }

    add_filter( 'tgwp_accept_text', 'tgwp_modify_accept_text' );

 = What actions are available? =

`tgwp_loaded` - Runs on constructor.
`before_tgwp_init` - Runs before we have run any init actions.
`tgwp_init` - Runs when all init hooks have run.

= What filters are available? =

`tgwp_has_user_consented` - Specifiy if the user has accepted or not. True or false value. Has arguments $cookie_name and $cookie_value.

`tgwp_cookie_active_value` - Set which value is "active" for the cookie, ie. consented. Defaults to 1.

`tgwp_cookie_name` - Set the name of the cookie. Defaults to 'EUConsentCookie'.

`tgwp_accept_text` - Set the accept button text.

`tgwp_consent_text` - Set the consent text. Has $policy_url as argument.

`tgwp_policy_url` - Allows you to modify the Policy URL. Has the url from the options as argument.

`tgwp_style` - Allows you to set your own style name.

`tgwp_edit_text_capability` - Allows you to modify which capability is required for editing the cookie banner text (below the title) in the customizer. Defaults to `edit_theme_options`.

`tgwp_edit_title_capability` - Allows you to modify which capability is required for editing the cookie banner title in the customizer. Defaults to `edit_theme_options`.

`tgwp_edit_button_capability` - Allows you to modify which capability is required for editing the cookie banner button label in the customizer. Defaults to `edit_theme_options`.

`tgwp_edit_policy_url_capability` - Allows you to modify which capability is required for editing the policy URL in the customizer. Defaults to `edit_theme_options`.

`tgwp_edit_style_capability` - Allows you to modify which capability is required for editing the cookie banner style in the customizer. Defaults to `edit_theme_options`.

`tgwp_load_stylesheets` - (bool) Set if you want the stylesheets to be loaded or not. Defaults to true.

`tgwp_enable_customizer` - Return false to disable all the customizer settings, if you'd like to prevent any user from changing any of the settings.

== Screenshots ==

1. The "top" style design of the cookie consent box out of the box.
2. The "overlay" style design of the cookie consent box, enabled in the customizer.
3. Customiser controls are available for all texts and URL.

== Changelog ==

= Version 1.0.0 =
- First plugin version.