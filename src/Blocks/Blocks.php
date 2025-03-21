<?php

namespace Afca\Themes\TheRateFramework\Blocks;

class Blocks {

	private string $theme_path;

	public function __construct( $theme_dir_path ) {
		$this->theme_path = $theme_dir_path;

		$this->afca_query_with_template_part_block();
	}

	private function afca_query_with_template_part_block() {
		add_action(
			'init',
			function () {
				register_block_type( $this->theme_path . '/src/Blocks/QueryWithTemplatePart' );
				register_block_type( $this->theme_path . '/src/Blocks/MetaFieldBlock' );
			}
		);

		wp_set_script_translations( 'afca-query-with-template-part-editor-script', 'afca-query-with-template-part', $this->theme_path . '/src/Blocks/QueryWithTemplatePart' );
		wp_set_script_translations( 'afca-meta-field-block-editor-script', 'afca-meta-field-block', $this->theme_path . '/src/Blocks/MetaFieldBlock' );
	}
}
