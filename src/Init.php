<?php

namespace Afca\Themes\TheRateFramework;

class Init {

	private string $theme_path;
	private string $theme_url;
	private string $theme_version;

	public function __construct( $theme_dir_path, $theme_dir_url, $theme_version ) {
		$this->theme_path    = $theme_dir_path;
		$this->theme_url     = $theme_dir_url;
		$this->theme_version = $theme_version;

		$this->set_theme_update();

		$this->include_theme_blocks();
	}

	/**
	 * Schedule a daily task to check for theme updates
	 *
	 * Uses the `wp_schedule_event` function to schedule a daily event that will
	 * call the `check_for_updates_on_hub` method of the `Updates` class. This
	 * method will check for updates of the theme on a GitHub repository.
	 *
	 * @return void
	 */
	private function set_theme_update() {
		$update_class = new Updates( 'https://andreamorim.site/', basename( $this->theme_path ), $this->theme_version );

		// Schedule task for checking updates
		add_action( 'afca_the_rate_framework_updates', [ $update_class, 'check_for_updates_on_hub' ] );
		if ( ! wp_next_scheduled( 'afca_the_rate_framework_updates' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'afca_the_rate_framework_updates' );
		}
	}

	/**
	 * Include the theme blocks from the theme path.
	 *
	 * This method is used to include the theme blocks that are present in the theme
	 * directory. The theme blocks are used to generate the dynamic content for the
	 * blocks in the theme.
	 */
	private function include_theme_blocks() {
		new Blocks\Blocks( $this->theme_path );
	}
}
