<?php

/* Template Name: Megazine flipbook template */

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?> class="no-js">

  <head>
    <meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>" charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width = device-width, minimum-scale=1, maximum-scale=1, user-scalable = no" />
    <meta name="mobile-web-app-capable" content="yes">
       
    <!-- External JS dependencies -->
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/underscore-min.js"></script>
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/backbone-min.js"></script>
    
    <!-- Turn.js UI Kit -->
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/turn.min.js"></script>
    
    <!-- App dependencies -->
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/app.js"></script>

     <!-- External CSS dependencies -->
    <link type="text/css" rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/font-awesome.min.css"></link>

    <!-- App CSS dependencies -->
    <link type="text/css" rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/app.css"></link>

    <style type="text/css">
    
		/* 
		.page.page-even {
			border-right: 1px dotted #DDD;
		}
    */

    #flipbook .wp-block-group {
			margin: 0;
			padding: 0;      
		}
		#flipbook .wp-block-group.page > .wp-block-cover {
			margin: 0;
			min-height: 100%:
		}
    
    </style>
    
    <?php wp_head(); ?>
  </head>

  <body <?php body_class('megazine'); ?> >



<?php

// get the post content and parse gutenberg blocks to determine sections and pages    
$post_content = get_the_content(); 
$blocks = parse_blocks( $post_content );
 
?>
	
	
<div class="megazine-app">
  <div id="viewer">
    <div id="flipbook" class="ui-flipbook">


<?php #the_content(); ?>
	  
<?php

// init array for table of contents
$sections = array();
$section_num = 0;
$page_num = 0;

// Loop through each block
if ( ! empty( $blocks ) ) : foreach ( $blocks as $block ) :
		
	// Check if the current block is a STACK
    if ( ( 'core/group' === $block['blockName'] ) && ( 'flex' === $block['attrs']['layout']['type'] ) ) :
    	
    	// increment section count
    	$section_num++;
    	
    	// reset this section's page count
    	$pages_per_section = 0;
    	
     	$sections[$section_num]['name'] = $block['attrs']['metadata']['name'];
     	$sections[$section_num]['section'] = $section_num;

     	// Check for inner blocks/pages within this section
        if ( ! empty( $block['innerBlocks'] ) ) : foreach ( $block['innerBlocks'] as $inner_block ) :
            
            // Check if the current block is a PAGE
        	if ( 'core/group' === $inner_block['blockName'] ) {
				
				// increment page counts
				$page_num++;
				$pages_per_section++;
				
				// remember the first page number of this section
				if( $pages_per_section == 1 ) $sections[$section_num]['page'] = $page_num;
			
				// process inner blocks here
                echo render_block( $inner_block );	
			}
			
        endforeach; endif;	
     	
    endif;
	    
endforeach; endif;


// VAR DUMP
#echo '<pre>';
#print_r($sections);
#echo '</pre>';

?>	  
      
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
        <a class="ui-icon" id="ui-icon-table-contents">
          <i class="fa fa-bars"></i>
        </a>
        <a class="ui-icon show-hint" title="Miniatures" id="ui-icon-miniature">
          <i class="fa fa-th"></i>
        </a>
        <a class="ui-icon" id="ui-icon-zoom">
          <i class="fa fa-file-o"></i>
        </a>
        <a class="ui-icon show-hint" title="Share" id="ui-icon-share">
          <i class="fa fa-share"></i>
        </a>
        <a class="ui-icon show-hint" title="Full Screen" id="ui-icon-full-screen">
          <i class="fa fa-expand"></i>
        </a>
        <a class="ui-icon show-hint" id="ui-icon-toggle">
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
      <a class="ui-icon show-hint">
        <i class="fa fa-ellipsis-h"></i>
      </a>
    </div>

  </div>
  <!-- / controls -->

  <!-- miniatures -->
  <div id="miniatures" class="ui-miniatures-slider">  
  </div>
  <!-- / miniatures -->
  
  
  

<script type="text/javascript">

  // Change these settings
  FlipbookSettings = {
    options: {
      width: 1280,
      height: 920
    },

    shareMessage: 'Check this out',

	// Construct table of contents
  
    table: [ 
    	<?php $t = 0; ?>
    	<?php foreach($sections as $section) { $t++; ?>
			<?php if($t > 1) { ?>,<?php } ?>{text: '<?php echo $section['name']; ?>', page: <?php echo $section['page']; ?>}
		<?php } ?>
    ],
	
	// thumbnails?
    pageFolder: '<?php echo get_stylesheet_directory_uri(); ?>/content' // removed trailing slash
  };

  // animate in first page
  $(window).load(function(event) {
    $("#flipbook .page-1").addClass('animation-on');
  });
  </script>
  
  
 
 <!-- 
 
       {text: 'Front Cover', page: 1},
      {text: 'Introduction', page: 2},
      {text: 'Main Article', page: 4},
      {text: 'Big Creative Focus', page: 6},
      {text: 'Activism', page: 10},
      {text: 'Womens Health', page: 12},
      {text: 'Mom Life', page: 14},
      {text: 'Spotlight', page: 16},
      {text: 'From The Vault', page: 18},
      {text: 'Thank You', page: 20},
      {text: 'Back Cover', page: 22}
      
      -->
  

		<?php wp_footer(); ?>
	</body>
</html>