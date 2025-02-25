<?php
$meta_key            = $attributes['metaKey'];
$render_type         = $attributes['renderType'];
$show_text_adjacency = $attributes['showTextAdjacency'];
$before_text         = $show_text_adjacency ? sprintf( '<p class="before-text">%1$s</p>', $attributes['beforeText'] ) : '';
$after_text          = $show_text_adjacency ? sprintf( '<p class="after-text">%1$s</p>', $attributes['afterText'] ) : '';
$alt_text            = $attributes['altText'];
$open_link_new_tab   = $attributes['openLinkNewTab'];
$text_link           = $attributes['textLink'];
$img_alt_text        = $attributes['imgAltText'];

$meta_value = null;
if ( $meta_key ) {
	$the_post = get_post();
	if ( $the_post instanceof \WP_Post ) {
		$meta_value = get_post_meta( $the_post->ID, $meta_key, true );
	}
}

$html = '';
if ( $meta_value ) {
	switch ( $render_type ) {
		case 'text':
			$html .= sprintf(
				'<p>%1$s</p>',
				$meta_value
			);

			break;
		case 'url':
			$html .= sprintf(
				'<a href="%1$s" target="%2$s">%3$s</a>',
				$meta_value,
				( $open_link_new_tab ? '_blank' : '' ),
				( empty( $text_link ) ? $meta_value : $text_link )
			);

			break;
		case 'img':
			$img_src = wp_http_validate_url( $meta_value ) ? $meta_value : wp_get_attachment_image_url( $meta_value, 'full' );
			$html   .= sprintf(
				'<img src="%1$s" alt="%2$s" />',
				$img_src,
				$img_alt_text
			);

			break;
		case 'list':
			if ( is_array( $meta_value ) ) {
				$html .= sprintf(
					'<ul>%1$s</ul>',
					implode(
						'',
						array_map(
							function ( $item ) {
								return sprintf(
									'<li>%1$s</li>',
									$item
								);
							},
							$meta_value
						)
					)
				);
			} else {
				$html .= sprintf(
					'<p>%1$s</p>',
					$meta_value
				);
			}
			break;
		default:
			break;
	}
} else {
	$html .= sprintf(
		'<p>%1$s</p>',
		$alt_text
	);
}

echo wp_kses(
	sprintf( '<div class="afca-blocks-meta-field">%1$s</div>', $before_text . $html . $after_text ),
	[
		'div' => [ 'class' => [] ],
		'p'   => [ 'class' => [] ],
		'a'   => [
			'href'   => [],
			'target' => [],
		],
		'img' => [
			'src' => [],
			'alt' => [],
		],
		'ul'  => [],
		'li'  => [],
	]
);
