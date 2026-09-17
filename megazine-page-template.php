<?php
/**
 * Template Name: Megazine flipbook template
 *
 * Renders a page's block content as a Turn.js flipbook. The magazine's
 * structure is the block structure — see mixtape_build_flipbook() in
 * functions.php for the section/page contract.
 *
 * @package Mixtape
 */

$mixtape_flipbook = mixtape_build_flipbook( parse_blocks( get_the_content() ) );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="mobile-web-app-capable" content="yes" />

	<style type="text/css">
		#flipbook .wp-block-group {
			margin: 0;
			padding: 0;
		}

		#flipbook .wp-block-group.page > .wp-block-cover {
			margin: 0;
			min-height: 100%;
		}
	</style>

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'megazine' ); ?>>

<div class="megazine-app">
	<div id="viewer">
		<div id="flipbook" class="ui-flipbook">

			<?php echo $mixtape_flipbook['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() output. ?>

			<a ignore="1" class="ui-arrow-control ui-arrow-next-page"></a>
			<a ignore="1" class="ui-arrow-control ui-arrow-previous-page"></a>
		</div>
	</div>

	<!-- controls -->
	<div id="controls">
		<div class="all">
			<div class="ui-slider" id="page-slider">
				<div class="bar">
					<div class="progress-width">
						<div class="progress">
							<div class="handler"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="ui-options" id="options">
				<a class="ui-icon" id="ui-icon-table-contents" title="<?php esc_attr_e( 'Table of contents', 'mixtape' ); ?>">
					<i class="fa fa-bars"></i>
				</a>
				<a class="ui-icon show-hint" id="ui-icon-miniature" title="<?php esc_attr_e( 'Thumbnails', 'mixtape' ); ?>">
					<i class="fa fa-th"></i>
				</a>
				<a class="ui-icon" id="ui-icon-zoom" title="<?php esc_attr_e( 'Zoom', 'mixtape' ); ?>">
					<i class="fa fa-file-o"></i>
				</a>
				<a class="ui-icon show-hint" id="ui-icon-share" title="<?php esc_attr_e( 'Share', 'mixtape' ); ?>">
					<i class="fa fa-share"></i>
				</a>
				<a class="ui-icon show-hint" id="ui-icon-full-screen" title="<?php esc_attr_e( 'Full screen', 'mixtape' ); ?>">
					<i class="fa fa-expand"></i>
				</a>
				<a class="ui-icon show-hint" id="ui-icon-toggle" title="<?php esc_attr_e( 'More options', 'mixtape' ); ?>">
					<i class="fa fa-ellipsis-h"></i>
				</a>
			</div>

			<!-- zoom slider -->
			<div id="zoom-slider-view" class="zoom-slider">
				<div class="bg">
					<div class="ui-slider" id="zoom-slider">
						<div class="bar">
							<div class="progress-width">
								<div class="progress">
									<div class="handler"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- / zoom slider -->
		</div>

		<div id="ui-icon-expand-options">
			<a class="ui-icon show-hint" title="<?php esc_attr_e( 'More options', 'mixtape' ); ?>">
				<i class="fa fa-ellipsis-h"></i>
			</a>
		</div>
	</div>
	<!-- / controls -->

	<!-- miniatures -->
	<div id="miniatures" class="ui-miniatures-slider"></div>
	<!-- / miniatures -->

	<script type="text/javascript">
		var FlipbookSettings = {
			options: {
				width: 1280,
				height: 920
			},

			shareMessage: <?php echo wp_json_encode( __( 'Check this out', 'mixtape' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>,

			// Table of contents, generated from the block tree at render time.
			table: <?php echo wp_json_encode( $mixtape_flipbook['toc'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>,

			pageFolder: <?php echo wp_json_encode( get_stylesheet_directory_uri() . '/content', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>
		};

		// Animate in the first page once assets have loaded.
		jQuery( window ).on( 'load', function () {
			jQuery( '#flipbook .page-1' ).addClass( 'animation-on' );
		} );
	</script>
</div>

<?php wp_footer(); ?>
</body>
</html>
