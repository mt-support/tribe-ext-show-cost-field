<?php
/**
 * Plugin Name:       Event Tickets/Eventbrite Extension: Show Cost Field
 * Plugin URI:        https://theeventscalendar.com/extensions/show-cost-field/
 * Description:       Force displaying The Events Calendar's "Event Cost" field in wp-admin and the Community Events event edit form (if applicable) when Event Tickets or Eventbrite Tickets is activated.
 * Version:           1.0.1
 * Extension Class:   Tribe__Extension__Show_Cost_Field
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-show-cost-field
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-show-cost-field
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( 'Tribe__Extension__Show_Cost_Field' )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Tribe__Extension__Show_Cost_Field extends Tribe__Extension {

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			add_action( 'tribe_plugins_loaded', array( $this, 'required_tribe_classes' ), 0 );
		}

		/**
		 * Check required plugins after all Tribe plugins have loaded.
		 */
		public function required_tribe_classes() {
			// If neither Event Tickets or Eventbrite Tickets, require Event Tickets
			if (
				! Tribe__Dependency::instance()->is_plugin_active( 'Tribe__Tickets__Main' )
				&& ! Tribe__Dependency::instance()->is_plugin_active( 'Tribe__Events__Tickets__Eventbrite__Main' )
			) {
				add_filter( 'gettext_with_context', array( $this, 'reqd_plugins_and_to_or_text' ), 10, 4 );
				$this->add_required_plugin( 'Tribe__Tickets__Main' );
				$this->add_required_plugin( 'Tribe__Events__Tickets__Eventbrite__Main' );
			}

			// Event Tickets
			if ( Tribe__Dependency::instance()->is_plugin_active( 'Tribe__Tickets__Main' ) ) {
				$this->add_required_plugin( 'Tribe__Tickets__Main' );
			}

			// Eventbrite Tickets
			if ( Tribe__Dependency::instance()->is_plugin_active( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {
				$this->add_required_plugin( 'Tribe__Events__Tickets__Eventbrite__Main' );
			}
		}

		/**
		 * We require only one of the required plugins so we change the
		 * conjunction from "and" to "or".
		 *
		 * @see Tribe__Admin__Notice__Plugin_Download::implode_with_grammar()
		 *
		 * @param $translation
		 * @param $text
		 * @param $context
		 * @param $domain
		 *
		 * @return string
		 */
		public function reqd_plugins_and_to_or_text( $translation, $text, $context, $domain ) {
			if (
				'tribe-common' == $domain
				&& ' and ' == $translation
				&& 'the final separator in a list of two or more items' == $context
			) {
				$translation = ' or '; // OR, not AND
			}

			return $translation;
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			// Don't forget to generate the 'languages/tribe-ext-show-cost-field.pot' file
			load_plugin_textdomain( 'tribe-ext-show-cost-field', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			/**
			 * Protect against fatals by specifying the required minimum PHP
			 * version. Make sure to match the readme.txt header.
			 * All extensions require PHP 5.3+
			 *
			 * @link https://secure.php.net/manual/en/migration53.new-features.php
			 * 5.3: Namespaces, Closures, and Shorthand Ternary Operator
			 */
			$php_required_version = '5.3';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if (
					is_admin()
					&& current_user_can( 'activate_plugins' )
				) {
					$message = '<p>';

					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', 'tribe-ext-show-cost-field' ), $this->get_name(), $php_required_version );

					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );

					$message .= '</p>';

					tribe_notice( $this->get_name(), $message, 'type=error' );
				}

				return;
			}

			// This bit is the whole point of this extension. All that for just this :)
			add_filter( 'tribe_events_admin_show_cost_field', '__return_true', 100 );
		}

	} // end class
} // end if class_exists check