<?php

namespace Afca\Themes\TheRateFramework;

class ThirdParties {

	private array $required_plugins;

	/**
	 * Constructor for the ThirdParties class.
	 *
	 * Initializes the required plugins list and checks for any missing plugins.
	 *
	 * @param array $plugins_list List of required plugin slugs.
	 */

	public function __construct( array $plugins_list ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->required_plugins = $plugins_list;
		$this->check_missing_plugins();
	}

	/**
	 * Checks if any of the required plugins are missing.
	 *
	 * Iterates through the list of required plugins and checks if each one is active.
	 * If a plugin is not active and the current user is an administrator, add an
	 * admin notice listing the required plugins.
	 */
	private function check_missing_plugins() {
		$missing_list = '';
		foreach ( $this->required_plugins as $plugin ) {
			if ( ! is_plugin_active( $plugin ) && current_user_can( 'administrator' ) ) {
				$missing_list .= sprintf(
					'<b>%1$s</b>, ',
					$plugin
				);
			}
		}

		if ( ! empty( $missing_list ) ) {
			add_action(
				'current_screen',
				function ( $screen ) use ( $missing_list ) {
					if ( $screen->id == 'themes' ) {
						add_action(
							'admin_notices',
							function () use ( $missing_list ) {
								printf(
									'<div class="notice notice-info is-dismissible">
										<p>%1$s %2$s</p>
									</div>',
									__( 'This framework advices you to install the following plugins for extra functionalities:', 'the-rate-framework' ),
									rtrim( $missing_list, ', ' ) . '.'
								);
							}
						);
					}
				}
			);
		}
	}
}
