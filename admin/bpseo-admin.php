<?php
/**
 * @package WordPress
 * @subpackage BuddyPress
 * @sub-subpackage BP Advanced SEO
 * @author Boris Glumpler
 * @copyright 2010, ShabuShabu Webdesign
 * @link http://shabushabu.eu/bp-advanced-seo
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */
 
class BPSEO_Admin_Loader
{
	/**
	 * Constructor
	 * @since 1.0
	 */
	function __construct()
	{
		add_action( 'admin_menu', array( &$this, 'add_menu' ), 20 );
		add_action( 'admin_menu', array( &$this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( &$this, 'save_meta' ) );
		add_action( 'admin_print_scripts', array( &$this, 'load_scripts' ) );
		add_action( 'admin_print_styles', array( &$this, 'load_styles' ) );
		add_filter( 'contextual_help', array( &$this, 'show_help' ), 10, 2 );
	}

	/**
	 * Add the options page
	 * @since 1.0
	 */
	function add_menu()
	{
		add_submenu_page( 'bp-general-settings', __( 'BP Advanced SEO', 'bpseo' ), __( 'BP Advanced SEO', 'bpseo' ), 'manage_options', BPSEO_FOLDER, array( &$this, 'show_menu' ) );
	}

	/**
	 * Add the meta boxes
	 * @since 1.0
	 */
	function add_meta_boxes()
	{
		add_meta_box( 'bpseo_post_meta_boxes' , __( 'SEO Options', 'bpseo' ), array( &$this, 'meta_boxes' ), 'post', 'advanced', 'high' );
		add_meta_box( 'bpseo_page_meta_boxes' , __( 'SEO Options', 'bpseo' ), array( &$this, 'meta_boxes' ), 'page', 'advanced', 'high' );
	}

	/**
	 * Content meta boxes
	 * @since 1.0
	 */
	function meta_values()
	{
		$boxes = array(
			 'title' => array(	
					'id' => 'title',
					'title' => __( 'Title', 'bpseo' ),
					'description' => __( 'Enter a title to use for SEO purposes. Only needed if you want to use a different title than the post/page title.', 'bpseo' ),
					'default' => '',
					'type' => 'text'
					),
			 'keywords' => array(	
					'id' => 'keywords',
					'title' => __( 'Keywords', 'bpseo' ),
					'description' => __( 'Enter some keywords for SEO purposes.', 'bpseo' ),
					'default' => '',
					'type' => 'text'
					),
			 'description' => array(
					'id' => 'description',
					'title' => __( 'Description', 'bpseo' ),
					'description' => __( 'Enter a description for SEO purposes. Not neccesary, but a custom description is better than the automatically generated one. Search engines usually do not show more than 160 chars.', 'bpseo' ),
					'default' => '',
					'type' => 'textarea'
					),
		);
		return $boxes;
	}

	/**
	 * Router for the meta boxes
	 * @since 1.0
	 */
	function meta_boxes()
	{
		global $post;
		$boxes = $this->meta_values();
		foreach( $boxes as $box )
		{
			$value = stripslashes( get_post_meta( $post->ID, $box['id'], true ) );
			switch ( $box['type'] )
			{
				case "text":
					$this->meta_text( $box, $value );
					break;
				case "textarea":
					$this->meta_textarea( $box, $value );
					break;
				case "checkbox":
					$this->meta_checkbox( $box, $value );
					break;
				case "select":
					$this->meta_select( $box, $value );
					break;
			}
		}
	}

	/**
	 * Normal text field
	 * @since 1.0
	 */
	function meta_text( $args = array(), $value = false )
	{
		global $bpseo;
		
		extract( $args );
		?>
		<p><label for="<?php echo $id; ?>"><strong><?php echo $title; ?></strong>
			<input id="<?php echo $id; ?>" name="<?php echo $id; ?>" type="text" value="<?php echo wp_specialchars( $value, 1 ); ?>" size="30" tabindex="30" style="width:100%;" /><br />
			<input type="hidden" name="<?php echo $id; ?>_noncename" id="<?php echo $id; ?>_noncename" value="<?php echo wp_create_nonce( $bpseo->plugin_name ); ?>" />
			<?php echo $description; ?>
		</label></p>
		<?php
	}
	
	/**
	 * Multiline text field
	 * @since 1.0
	 */
	function meta_textarea( $args = array(), $value = false )
	{
		global $bpseo;
		
		extract( $args );
		?>
        <script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.char_count').each(function(){
				var length = jQuery(this).val().length;
				jQuery(this).parent().find('.counter').html( length + ' <?php _e( 'chars', 'bpseo' ) ?>');
				jQuery(this).keyup(function(){
					var new_length = jQuery(this).val().length;
					jQuery(this).parent().find('.counter').html( new_length + ' <?php _e( 'chars', 'bpseo' ) ?>');
				});
			});
		});
        </script>

		<p><label for="<?php echo $id; ?>"><strong><?php echo $title; ?></strong>
			<textarea id="<?php echo $id; ?>" class="char_count" name="<?php echo $id; ?>" tabindex="30" cols="60" rows="4" style="width:100%;"><?php echo wp_specialchars( $value, 1 ); ?></textarea><br />
			<input type="hidden" name="<?php echo $id; ?>_noncename" id="<?php echo $id; ?>_noncename" value="<?php echo wp_create_nonce( $bpseo->plugin_name ); ?>" />
            <strong><span class="counter"></span></strong><br />
			<?php echo $description; ?>
		</label></p>
		<?php
	}
	
	/**
	 * Checkbox field
	 * @since 1.0
	 */
	function meta_checkbox( $args = array(), $value = false )
	{
		global $bpseo;
		
		extract( $args );
		?>
		<p><label for="<?php echo $id; ?>">
			<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" <?php if( $value == 'on' ) echo 'checked="checked"'; ?> />
			<input type="hidden" name="<?php echo $id; ?>_noncename" id="<?php echo $id; ?>_noncename" value="<?php echo wp_create_nonce( $bpseo->plugin_name ); ?>" />
			<?php echo $description; ?>
		</label></p>
		<?php
	}
	
	/**
	 * Option select field
	 * @since 1.0
	 */
	function meta_select( $args = array(), $value = false )
	{
		global $bpseo;
		
		extract( $args );
		?>
		<p><label for="<?php echo $id; ?>"><strong><?php echo $title; ?></strong>
			<select name="<?php echo $id; ?>" id="<?php echo $id; ?>">
			<?php foreach( $options as $option => $value ) { ?>
				<option value="<?php echo $option; ?>"<?php if( htmlentities( $value, ENT_QUOTES ) == $option ) echo ' selected="selected"'; ?>>
					<?php echo $value; ?>
				</option>
			<?php } ?>
			</select>
			<?php echo $description; ?>
			<input type="hidden" name="<?php echo $id; ?>_noncename" id="<?php echo $id; ?>_noncename" value="<?php echo wp_create_nonce( $bpseo->plugin_name ); ?>" />
		</label></p>
		<?php
	}

	/**
	 * Save the seo options
	 * @since 1.0
	 */
	function save_meta( $post_id )
	{
		global $post, $bpseo;
		
		$boxes = array_merge( $this->meta_values() );
			
		foreach( $boxes as $box )
		{
			if( ! wp_verify_nonce( $_POST[$box['id'] . '_noncename'], $bpseo->plugin_name ) )
				return $post_id;
				
			if( 'page' == $_POST['post_type'] )
			{
				if( ! current_user_can( 'edit_page', $post_id ) )
					return $post_id;
			}
			else
			{
				if( ! current_user_can( 'edit_post', $post_id ) )
					return $post_id;
			}
			
			$data = stripslashes( $_POST[$box['id']] );
			
			if( get_post_meta( $post_id, $box['id'] ) == '' )
				add_post_meta( $post_id, $box['id'], $data, true );
				
			elseif( $data != get_post_meta( $post_id, $box['id'], true ) )
				update_post_meta( $post_id, $box['id'], $data );
				
			elseif( $data == '' )
				delete_post_meta( $post_id, $box['id'], get_post_meta( $post_id, $box['id'], true ) );
		}
	}

	/**
	 * Display the options page
	 * @since 1.0
	 */
	function show_menu()
	{
		global $bpseo;
		
		include_once( dirname( __FILE__ ). '/bpseo-settings.php' );
		$bpseo->options_page = new BPSEO_Options();
		$bpseo->options_page->controller();
	}

	/**
	 * Load necessary scripts
	 * @since 1.0
	 */
	function load_scripts()
	{
		// no need to go on if it's not a plugin page
		if( ! isset( $_GET['page'] ) )
			return;

		if( $_GET['page'] == BPSEO_FOLDER ) 
		{
			wp_enqueue_script( 'jquery-ui-tabs' );
		}
	}		
	
	/**
	 * Load necessary styles
	 * @since 1.0
	 */
	function load_styles()
	{
		// no need to go on if it's not a plugin page
		if( ! isset( $_GET['page'] ) )
			return;

		if( $_GET['page'] == BPSEO_FOLDER ) 
		{
			wp_enqueue_style( 'bpseotabs', BPSEO_URLPATH .'admin/css/jquery.ui.tabs.css', false, '1.0', 'screen' );
			wp_enqueue_style( 'bpseoadmin', BPSEO_URLPATH .'admin/css/bpseo-admin.css', false, '1.0', 'screen' );
		}
	}
	
	/**
	 * Add some helpful links
	 * @since 1.0
	 */
	function show_help( $help, $screen )
	{
		global $bpseo;
		
		if( $screen->id == 'buddypress_page_'. BPSEO_FOLDER )
		{
			$help  = '<h5>' . __( 'Get help for BP SEO', 'bpseo' ) . '</h5>';
			$help .= '<div class="metabox-prefs">';
			$help .= '<a href="'. $bpseo->home_url .'forums/">' . __( 'Support Forums', 'bpseo' ) . '</a><br />';
			$help .= '<a href="'. $bpseo->home_url .'donation/">' . __( 'Donate', 'bpseo' ) . '</a><br />';
			$help .= '</div>';
			
			return $help;
		}
	}

	/**
	 * Show a success message
	 * @since 1.0
	 */
	function show_message( $message )
	{
		echo '<div class="wrap"><h2></h2><div class="updated fade" id="message"><p>' . $message . '</p></div></div>' . "\n";
	}

	/**
	 * Show an error message
	 * @since 1.0
	 */
	function show_error( $error )
	{
		echo '<div class="wrap"><h2></h2><div class="error" id="error"><p>' . $error . '</p></div></div>' . "\n";
	}
}
?>