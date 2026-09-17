<?php
/**
 * Mixtape child theme.
 *
 * @package Mixtape
 */

define( 'MIXTAPE_VERSION', '1.1.0' );

/**
 * Parent and child stylesheets.
 */
function mixtape_enqueue_styles() {
	wp_enqueue_style(
		'hamilton-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		MIXTAPE_VERSION
	);

	wp_enqueue_style(
		'mixtape-style',
		get_stylesheet_uri(),
		array( 'hamilton-parent-style' ),
		MIXTAPE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'mixtape_enqueue_styles' );

/**
 * Flipbook assets, loaded only on the Megazine template.
 *
 * jQuery is pinned to the bundled 2.0.3 on this template only. Turn.js 5.0.0
 * calls jQuery's .size(), which was removed in jQuery 3.0, so core's jQuery
 * cannot drive the flipbook without jQuery Migrate. The override is scoped to
 * this one template so the rest of the site keeps core jQuery.
 */
function mixtape_enqueue_flipbook_assets() {
	if ( ! is_page_template( 'megazine-page-template.php' ) ) {
		return;
	}

	$uri = get_stylesheet_directory_uri();

	// Swap in the pinned jQuery under the core handle so dependents resolve.
	wp_deregister_script( 'jquery' );
	wp_deregister_script( 'jquery-core' );
	wp_deregister_script( 'jquery-migrate' );
	wp_register_script( 'jquery', $uri . '/assets/js/jquery-2.0.3.min.js', array(), '2.0.3', false );

	wp_enqueue_script( 'mixtape-underscore', $uri . '/assets/js/underscore-min.js', array(), MIXTAPE_VERSION, false );
	wp_enqueue_script( 'mixtape-backbone', $uri . '/assets/js/backbone-min.js', array( 'jquery', 'mixtape-underscore' ), MIXTAPE_VERSION, false );
	wp_enqueue_script( 'mixtape-turn', $uri . '/assets/js/turn.min.js', array( 'jquery' ), '5.0.0', false );
	wp_enqueue_script( 'mixtape-flipbook', $uri . '/assets/js/app.js', array( 'jquery', 'mixtape-backbone', 'mixtape-turn' ), MIXTAPE_VERSION, false );

	wp_enqueue_style( 'font-awesome', $uri . '/assets/css/font-awesome.min.css', array(), '4.7.0' );
	wp_enqueue_style( 'mixtape-flipbook', $uri . '/assets/css/app.css', array(), MIXTAPE_VERSION );
}
add_action( 'wp_enqueue_scripts', 'mixtape_enqueue_flipbook_assets' );

/**
 * Walk a page's block tree and build the flipbook's section/page map.
 *
 * Structure authored in the block editor:
 *   - A top-level core/group with a flex layout is a SECTION. Its List View
 *     name (stored in attrs.metadata.name) becomes a table-of-contents entry.
 *   - Each core/group nested inside a section is one PAGE of the magazine.
 *
 * @param array $blocks Parsed blocks from parse_blocks().
 * @return array {
 *     @type string $html     Rendered markup for every page, in order.
 *     @type array  $toc      List of array( 'text' => string, 'page' => int ).
 *     @type int    $pages    Total page count.
 * }
 */
function mixtape_build_flipbook( array $blocks ) {
	$html        = '';
	$toc         = array();
	$page_num    = 0;
	$section_num = 0;

	foreach ( $blocks as $block ) {
		if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
			continue;
		}

		// Sections are flex-layout groups at the top level.
		if ( 'flex' !== ( $block['attrs']['layout']['type'] ?? '' ) ) {
			continue;
		}

		if ( empty( $block['innerBlocks'] ) ) {
			continue;
		}

		$section_num++;
		$section_name  = $block['attrs']['metadata']['name'] ?? '';
		$section_start = 0;

		foreach ( $block['innerBlocks'] as $inner_block ) {
			if ( 'core/group' !== ( $inner_block['blockName'] ?? '' ) ) {
				continue;
			}

			$page_num++;

			if ( 0 === $section_start ) {
				$section_start = $page_num;
			}

			$html .= render_block( $inner_block );
		}

		// A section with no page groups contributes nothing to the reader.
		if ( 0 === $section_start ) {
			$section_num--;
			continue;
		}

		$toc[] = array(
			/* translators: %d: section number, used when a section is unnamed. */
			'text' => '' !== $section_name ? $section_name : sprintf( __( 'Section %d', 'mixtape' ), $section_num ),
			'page' => $section_start,
		);
	}

	return array(
		'html'  => $html,
		'toc'   => $toc,
		'pages' => $page_num,
	);
}
