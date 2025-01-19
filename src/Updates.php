<?php

namespace Afca\Themes\TheRateFramework;

class Updates {

	private $remote_response;
	private string $update_hub;
	private string $theme_name;
	private string $theme_version;

	public function __construct( $hub, $uid, $version ) {
		$this->remote_response = get_transient( 'afca_the_rate_framework_update_api_response' );
		$this->update_hub      = $hub;
		$this->theme_name      = $uid;
		$this->theme_version   = $version;

		add_filter( 'site_transient_update_themes', [ $this, 'get_theme_updates' ] );
	}

	public function get_theme_updates( $transient ) {
		require_once ABSPATH . 'wp-admin/includes/theme.php';

		// Ensure the remote response contains the required data.
		if ( ! isset( $this->remote_response->version ) ) {
			return $transient;
		}

		// Prepare the theme update data as an array.
		$theme_slug  = $this->theme_name; // The theme's directory name.
		$update_data = [
			'new_version'    => $this->remote_response->version,
			'package'        => $this->remote_response->url, // Download URL.
			'url'            => $this->update_hub . 'afca-software-lib/' . $this->theme_name,
			'requires'       => $this->remote_response->wp_required,
			'tested'         => $this->remote_response->wp_tested,
			'upgrade_notice' => $this->remote_response->released_notes,
		];

		// Compare the current theme version with the remote version.
		if ( version_compare( $this->theme_version, $this->remote_response->version, '<' ) ) {
			// Ensure $transient->response is initialized as an array.
			if ( ! is_array( $transient->response ) ) {
				$transient->response = [];
			}

			// Add the update data for the theme.
			$transient->response[ $theme_slug ] = $update_data;
		}

		return $transient;
	}

	public function check_for_updates_on_hub() {
		$ssl_verify = true; // Initial SSL verification option

		try {
			$this->remote_response = wp_remote_get(
				$this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->theme_name,
				[
					'timeout'   => 30,
					'headers'   => [
						'Accept' => 'application/json',
					],
					'sslverify' => $ssl_verify,
				]
			);
		} catch ( \Exception $ex ) {
			error_log( 'It was not possible to check for updates: ' . $ex->getMessage() );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $this->remote_response );

		// Retry with SSL verification disabled if there is an error
		if ( is_wp_error( $this->remote_response ) || $response_code !== 200 || empty( $response_code ) ) {
			try {
				$ssl_verify            = false; // Disable SSL verification
				$this->remote_response = wp_remote_get(
					$this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->theme_name,
					[
						'timeout'   => 30,
						'headers'   => [
							'Accept' => 'application/json',
						],
						'sslverify' => $ssl_verify,
					]
				);
			} catch ( \Exception $ex ) {
				error_log( 'It was not possible to check for updates: ' . $ex->getMessage() );
				return;
			}
		}

		$response_code = wp_remote_retrieve_response_code( $this->remote_response );

		// Bug Fix When Response is 403 Forbidden
		if ( $response_code == 403 || $response_code == '' ) {
			$this->remote_response = file_get_contents( $this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->theme_name );
		}

		if ( is_wp_error( $this->remote_response ) && ( wp_remote_retrieve_response_code( $this->remote_response ) !== 200 || empty( wp_remote_retrieve_body( $this->remote_response ) ) ) ) {
			error_log( $this->remote_response->get_error_message() );
			return;
		} else {
			if ( is_string( $this->remote_response ) ) {
				$this->remote_response = json_decode( $this->remote_response );
			} else {
				$this->remote_response = json_decode( wp_remote_retrieve_body( $this->remote_response ) );
			}
			set_transient( 'afca_the_rate_framework_update_api_response', $this->remote_response, 86400 );
		}
	}
}
