"use strict";

/**
 * Cookie Consent
 *
 * @author Tim Green <github@timgreen.ws>
 */
const Cookie_Consent = function( $, tgwp ) {

	/**
	 * Module Definition
	 *
	 * @type {{}}
	 */
	let module = {

		/**
		 * Settings
		 */
		settings: {
			noCookieMode: false, // Debug Mode: true will disable the cookie, allowing you to debug the banner.
			consentRememberDuration: 30, // Duration in Days: The number of days before the cookie should expire.
			cookieName: "CookieJarConsent", // The name of the cookie.
			cookieActiveValue: 1 // The active value of the cookie.
		},

		/**
		 * Sets the cookie with the active value.
		 */
		setCookie: function() {

			// If no debug mode, set the cookie
			if ( ! module.settings.noCookieMode ) {

				// Set the consent duration into a cookie date string
				var date = new Date();
				date.setTime( date.getTime() + ( module.settings.consentRememberDuration * 24 * 60 * 60 * 1000 ) );

				// Set the actual cookie
				document.cookie = module.settings.cookieName + "=" + module.settings.cookieActiveValue + "; expires=" + date.toGMTString() + "; path=/";

			}

		},

		/**
		 * Create the cookie consent banner and add it
		 * to the DOM.
		 */
		createBanner: function() {

			// Set the contents.
			const consentBlock = "<div class=\"tgwp-cookie-consent-notice js--tgwp-cookie-consent-notice\" id=\"cookie-consent-block\"><div class=\"tgwp-cookie-consent-notice-content\"><p><span>" + tgwp.cookieConsentTitle + "</span>" + tgwp.cookieConsentText + "</p><button class=\"tgwp-cookie-consent-close js--tgwp-cookie-consent-close close-cookie-block\">" + tgwp.acceptText + "</button></div></div>";

			// Get body tag
			const $body = $( "body.has-tgwp-banner" );

			// Append to body
			$body.append( consentBlock );

			// Get the height of the consent block
			const consentBlockHeight = $( ".js--tgwp-cookie-consent-notice" ).innerHeight();

			// Add class to body
			if ( $body.hasClass( "tgwp-style-top" ) ) {
				$body.css( "padding-top", consentBlockHeight + "px" );
			}

		},

		/**
		 * Get the value of a cookie by its name.
		 *
		 * @param {string} name
		 */
		getCookieValue: function( name ) {
			let nameEQ = name + "=";
			let ca     = document.cookie.split( ";" );
			for ( let i = 0; i < ca.length; i++ ) {
				let c = ca[ i ];
				while ( c.charAt( 0 ) == " " ) {
					c = c.substring( 1, c.length );
				}
				if ( c.indexOf( nameEQ ) == 0 ) {
					return c.substring( nameEQ.length, c.length );
				}
			}
			return null;
		},

		/**
		 * Remove the cookie banner from the page.
		 */
		removeBanner: function() {
			$( ".js--tgwp-cookie-consent-notice" ).slideToggle( {
				start: function() {

					let $body = $( "body" );

					if ( $body.hasClass( "tgwp-style-top" ) ) {
						$body.animate( {
							"padding-top": "0px"
						} );
					}

				},
				complete: function() {

					// Remove cookie banner class
					$( "body" )
						.removeClass( "has-tgwp-banner" )
						.removeClass( "tgwp-style-top" )
						.removeClass( "tgwp-style-overlay" )
						.addClass( "has-tgwp-consented" );

					// Remove the cookie banner from the DOM.
					$( this ).remove();

				}
			} );
		}

	};

	/**
	 * When the window loads and the user hasn't already accepted
	 * the cookie terms (ie. we have no cookie), then we
	 * create the banner.
	 */
	$( window ).on("load", function() {
		if ( module.getCookieValue( module.settings.cookieName ) != module.settings.cookieActiveValue ) {
			module.createBanner();
		}
	} );

	/**
	 * If the user clicks on the accept button to close the banner,
	 * we remove it and set the accepted cookie.
	 */
	$( document.body ).on( "click", ".js--tgwp-cookie-consent-close", function() {
		module.removeBanner();
		module.setCookie();
	} );

	// Return the module.
	return module;

}( jQuery, tgwp );
