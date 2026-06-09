/* global ajaxurl, mcWraAdminI18n */
/**
 * Admin scripts for MC-Woo Remote Automations.
 *
 * Handles password-field visibility toggling and the AJAX Save & Test
 * Connection workflow. All PHP-generated data (nonce, post ID, i18n strings)
 * is injected through wp_localize_script (mcWraAdminI18n) and HTML data
 * attributes to avoid inline JavaScript on admin pages.
 *
 * @package MC_Woo_Remote_Automations
 */
(function () {
	'use strict';

	/** i18n strings populated by wp_localize_script. */
	var i18n = ( typeof mcWraAdminI18n !== 'undefined' ) ? mcWraAdminI18n : {};

	// -------------------------------------------------------------------------
	// Password-field visibility toggle.
	// Any <button data-mc-wra-toggle="field-id"> acts as a show/hide toggle.
	// This runs on every page where the script is enqueued (connection edit
	// screens and the settings page).
	// -------------------------------------------------------------------------
	document.querySelectorAll( '[data-mc-wra-toggle]' ).forEach( function ( toggle ) {
		toggle.addEventListener( 'click', function () {
			var targetId = toggle.getAttribute( 'data-mc-wra-toggle' );
			var field    = document.getElementById( targetId );
			if ( ! field ) {
				return;
			}
			var show     = ( 'password' === field.type );
			field.type   = show ? 'text' : 'password';
			toggle.textContent = show
				? ( i18n.hide || 'Hide' )
				: ( i18n.show || 'Show' );
		} );
	} );

	// -------------------------------------------------------------------------
	// Save & Test Connection button.
	// Only present on the Connection edit/create screen; guarded by existence
	// check so the password toggle above is unaffected when button is absent.
	// The nonce and post ID are read from data-* attributes on the button.
	// -------------------------------------------------------------------------
	var button    = document.getElementById( 'mc-wra-save-test-button' );
	var spinner   = document.getElementById( 'mc-wra-save-test-spinner' );
	var resultBox = document.getElementById( 'mc-wra-save-test-result' );

	if ( button && resultBox ) {
		var nonce  = button.getAttribute( 'data-nonce' )   || '';
		var postId = button.getAttribute( 'data-post-id' ) || '';

		/**
		 * Returns the value of a form element by its ID.
		 *
		 * @param {string} id Element ID.
		 * @return {string}
		 */
		function getValue( id ) {
			var el = document.getElementById( id );
			return el ? el.value : '';
		}

		/**
		 * Returns 'yes' when a checkbox element is checked, 'no' otherwise.
		 *
		 * @param {string} id Element ID.
		 * @return {string}
		 */
		function isChecked( id ) {
			var el = document.getElementById( id );
			return ( el && el.checked ) ? 'yes' : 'no';
		}

		/**
		 * Shows a notice inside the result container.
		 *
		 * Uses textContent (not innerHTML) to prevent XSS from server messages.
		 *
		 * @param {boolean} success Whether the operation succeeded.
		 * @param {string}  message Human-readable message.
		 */
		function showMessage( success, message ) {
			resultBox.style.display = 'block';
			resultBox.className = success
				? 'notice notice-success inline'
				: 'notice notice-error inline';

			while ( resultBox.firstChild ) {
				resultBox.removeChild( resultBox.firstChild );
			}

			var p = document.createElement( 'p' );
			p.textContent = String( message || '' );
			resultBox.appendChild( p );
		}

		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			button.disabled = true;
			if ( spinner ) {
				spinner.classList.add( 'is-active' );
			}
			showMessage( true, i18n.saving || 'Saving and testing connection...' );

			var data = new FormData();
			data.append( 'action',             'mc_wra_save_test_connection' );
			data.append( 'nonce',              nonce );
			data.append( 'post_id',            postId );
			data.append( 'post_title',         getValue( 'title' ) );
			data.append( 'mc_enabled',         'yes' === isChecked( 'mc_enabled' ) ? 'yes' : isChecked( 'mc_connection_enabled' ) );
			data.append( 'mc_base_url',        getValue( 'mc_base_url' ) );
			data.append( 'mc_create_endpoint', getValue( 'mc_create_endpoint' ) );
			data.append( 'mc_role_endpoint',   getValue( 'mc_role_endpoint' ) );
			data.append( 'mc_ping_endpoint',   getValue( 'mc_ping_endpoint' ) );
			data.append( 'mc_remote_secret',   getValue( 'mc_remote_secret' ) );

			fetch( ajaxurl, {
				method:      'POST',
				credentials: 'same-origin',
				body:        data,
			} )
			.then( function ( response ) {
				return response.text().then( function ( text ) {
					try {
						return JSON.parse( text );
					} catch ( err ) {
						return {
							success: false,
							data: {
								message: text
									? text.replace( /<[^>]*>/g, ' ' ).replace( /\s+/g, ' ' ).trim()
									: ( i18n.invalidResponse || 'The server returned an invalid response.' ),
							},
						};
					}
				} );
			} )
			.then( function ( payload ) {
				var ok      = payload && payload.success;
				var message = ( payload && payload.data && payload.data.message )
					? payload.data.message
					: ( i18n.unexpectedResponse || 'Unexpected response from WordPress.' );

				showMessage( ok, message );

				if ( payload && payload.data && payload.data.edit_url
						&& window.history && window.history.replaceState ) {
					window.history.replaceState( {}, document.title, payload.data.edit_url );
				}
			} )
			.catch( function ( error ) {
				showMessage(
					false,
					( error && error.message )
						? error.message
						: ( i18n.networkError || 'Connection test failed due to a browser or server error.' )
				);
			} )
			.finally( function () {
				button.disabled = false;
				if ( spinner ) {
					spinner.classList.remove( 'is-active' );
				}
			} );
		} );
	}
}() );
