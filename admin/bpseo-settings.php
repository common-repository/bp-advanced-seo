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
 
class BPSEO_Options
{
	var $price_url;
	var $filepath;
	
	/**
	 * Constructor
	 * @since 1.0
	 */
    function __construct()
	{
		global $bpseo;
		
	    $this->filepath = admin_url() . 'admin.php?page=' . $_GET['page'];
		$this->price_url = $bpseo->home_url . 'prices.php';
        
		// only process if $_POST vars are available
		if( ! empty( $_POST ) )
			$this->processor();
    }

	/**
	 * Process any $_POST variables
	 * @since 1.0
	 */
	function processor()
	{
		global $bpseo;
		
		if ( isset( $_POST['updateoption'] ) )
		{	
			check_admin_referer( 'bpseo_settings' );
			
			$error = false;
			$message = '';


			// proceed if there is no error
			if( ! $error )
			{
				if( $_POST['page_options'] )	
					$options = explode( ',', stripslashes( $_POST['page_options'] ) );
					
				if( $options )
				{
					foreach( $options as $option )
					{
						$option = trim( $option );
						$value = trim( $_POST[$option] );
						
						$bpseo->options->{$option} = $value;
					}
				}
				// Save options
				update_option( 'bpseo_options', $bpseo->options );
				
				BPSEO_Admin_Loader::show_message( __( 'Update Successfully', 'bpseo' ) );
			}
			// or show any errors
			else
			{
				BPSEO_Admin_Loader::show_error( $message );
			}
		}
		do_action( 'bpseo_update_options_page' );
	}
	
	/**
	 * Render the page content
	 * @since 1.0
	 */
	function controller()
	{
        // get list of tabs
        $tabs = $this->tabs_order();
		?>
        
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });
		});
        </script>
	
        <div id="slider" class="wrap">
        
            <ul id="tabs">
			<?php    
            foreach( $tabs as $tab_key => $tab_name )
               echo "\n\t\t<li><a href='#$tab_key'>$tab_name</a></li>";
            ?>
            </ul>
            
            <?php    
            foreach( $tabs as $tab_key => $tab_name )
			{
                echo "\n\t<div id='$tab_key'>\n";
				
                // Looks for the internal class function, otherwise enable a hook for plugins
                if( method_exists( $this, "tab_$tab_key" ) )
                    call_user_func( array( &$this , "tab_$tab_key" ) );
                else
                    do_action( 'bpseo_tab_content_' . $tab_key );
					
                echo "\n\t</div>";
            } 
            ?>
        </div>
        <?php
	}
	
	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 * @since 1.0
	 */
    function tabs_order()
	{     
    	$tabs = array();
    	
    	$tabs['settings'] = __( 'Settings', 'bpseo' );
    	$tabs['help'] 	  = __( 'Help', 'bpseo' );
    	$tabs['donate']   = __( 'Donate', 'bpseo' );
    	
    	$tabs = apply_filters( 'bpseo_settings_tabs', $tabs );
    
    	return $tabs;
    }

	/**
	 * Content of the General Options tab
	 * @since 1.0
	 */
    function tab_settings()
	{
        global $bpseo;
    	?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.postbox.close-me').addClass('closed');			
        	jQuery('.postbox h3').click( function() {
				jQuery(this).parent('.postbox').toggleClass('closed');
			});
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
        <h2><?php _e( 'Settings','bpseo' ); ?></h2>

        <form name="general" method="post" action="<?php echo $this->filepath ?>" >
        
            <?php wp_nonce_field( 'bpseo_settings' ) ?>
            
            <input type="hidden" name="page_options" value="seo_profile_keys,seo_profile_desc,seo_noindex_activate,seo_noindex_register,seo_noindex_directory,seo_home_title,seo_home_desc,seo_home_keywords,seo_noindex_archives,seo_noindex_tags,seo_noindex_tax,seo_noindex_categories,seo_noindex_author" />
           
            <table id="bpseo-tos" class="form-table">
            <tr>
                <th><label for="seo_home_title"><?php _e( 'Home Title', 'bpseo' ); ?></label></th>
                <td>
                    <input class="long-text" type="text" id="seo_home_title" name="seo_home_title" value="<?php echo stripcslashes( $bpseo->options->seo_home_title ) ?>" /><br />
                    <?php _e( 'Enter a title for the home page if you don\'t want to use your tagline.', 'bpseo' ); ?>
                </td>
            </tr>
            <tr>
                <th><label for="seo_home_desc"><?php _e( 'Home Description', 'bpseo' ); ?></label></th>
                <td>
                    <textarea class="long-text char_count" id="seo_home_desc" name="seo_home_desc"><?php echo str_replace( '<', '&lt;', stripcslashes( $bpseo->options->seo_home_desc ) ); ?></textarea><br />
            		<strong><span class="counter"></span></strong><br />
					<?php _e( 'Description for your home page/front page. Shouldn\'t be more than 160 letters.', 'bpseo' ); ?>
                </td>
            </tr>
            <tr>
                <th><label for="seo_home_keywords"><?php _e( 'Home Keywords', 'bpseo' ); ?></label></th>
                <td>
                    <input class="long-text" type="text" id="seo_home_keywords" name="seo_home_keywords" value="<?php echo stripcslashes( $bpseo->options->seo_home_keywords ) ?>" /><br />
                    <?php _e( 'Enter a comma seperated list of keywords that describe your home page/front page', 'bpseo' ); ?>
                </td>
            </tr>
            <tr>
                <th><label for="seo_profile_desc"><?php _e( 'Profile Field (Description)', 'bpseo' ); ?></label></th>
                <td>
                    <input class="long-text" type="text" id="seo_profile_desc" name="seo_profile_desc" value="<?php echo stripcslashes( $bpseo->options->seo_profile_desc ) ?>" /><br />
                    <?php _e( 'Enter the name of a profile field, e.g. \'About Me\'. This will be the meta description for profile pages.', 'bpseo' ); ?>
                </td>
            </tr>
            <tr>
                <th><label for="seo_profile_keys"><?php _e( 'Profile Field (Keywords)', 'bpseo' ); ?></label></th>
                <td>
                    <input class="long-text" type="text" id="seo_profile_keys" name="seo_profile_keys" value="<?php echo stripcslashes( $bpseo->options->seo_profile_keys ) ?>" /><br />
                    <?php _e( 'Enter the name of a profile field, e.g. \'Tags\'. This will be the meta keywords for profile pages.', 'bpseo' ); ?>
                </td>
            </tr>
            <tr>
                <th><label for="seo_noindex_archives"><?php _e( 'No Index Options', 'bpseo' ); ?></label></th>
                <td>
                    <input id="seo_noindex_archives" name="seo_noindex_archives" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_archives ); ?> /> 
                    <label for="seo_noindex_archives">
                        <?php _e( 'Check this to use noindex on archive pages.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_tags" name="seo_noindex_tags" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_tags ); ?>/> 
                    <label for="seo_noindex_tags">
                        <?php _e( 'Check this to use noindex on tag archives.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_tax" name="seo_noindex_tax" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_tax ); ?>/> 
                    <label for="seo_noindex_tax">
                        <?php _e( 'Check this to use noindex on taxonomy archives.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_categories" name="seo_noindex_categories" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_categories ); ?> /> 
                    <label for="seo_noindex_categories">
                        <?php _e( 'Check this to use noindex on category pages.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_author" name="seo_noindex_author" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_author ); ?> /> 
                    <label for="seo_noindex_author">
                        <?php _e( 'Check this to use noindex on author archives.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_directory" name="seo_noindex_directory" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_directory ); ?> /> 
                    <label for="seo_noindex_directory">
                        <?php _e( 'Check this to use noindex on directory pages.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_register" name="seo_noindex_register" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_register ); ?> /> 
                    <label for="seo_noindex_register">
                        <?php _e( 'Check this to use noindex on the registration page.', 'bpseo' ); ?>
                    </label><br />
                    <input id="seo_noindex_activate" name="seo_noindex_activate" type="checkbox" value="1" <?php checked( 1, $bpseo->options->seo_noindex_activate ); ?> /> 
                    <label for="seo_noindex_activate">
                        <?php _e( 'Check this to use noindex on the activation page.', 'bpseo' ); ?>
                    </label>
                </td>
            </tr>
            </table>
            <div class="submit"><input type="submit" name="updateoption" value="<?php _e( 'Update' ) ;?> &raquo;"/></div>
        </form>
        <?php	
	}


	/**
	 * Content of the Help tab
	 * @since 1.0
	 */
    function tab_help()
	{
		global $bpseo;

		$shabu = get_option( 'shabu_software' );
			
		$diff = strtotime( date( 'Y-m-d H:i:s' ) ) - strtotime( $shabu['time'] );
		// one ping per day
		$api_time_seconds = 86400;
	
		if( ! $shabu || $diff >= $api_time_seconds )
		{
			if( ! class_exists( 'WP_Http' ) )
				include_once( ABSPATH . WPINC. '/class-http.php' );
			
			$request = new WP_Http;
			$http = $request->request( $this->price_url );
			$result = unserialize( $http['body'] );
			
			$shabu_array = array( 'time' => date( 'Y-m-d H:i:s' ), 'info' => $result );
			
			update_option( 'shabu_software', $shabu_array );
		}
		else
			$result = $shabu['info'];

		?>
        
        <h2><?php _e( 'Help', 'bpseo' ); ?></h2>
        
        <p><?php printf( __( 'To receive support for this plugin, please <a href="%s">register</a> on <a href="%s">ShabuShabu.eu</a>.', 'bpseo' ), $bpseo->home_url . 'membership-options/', $bpseo->home_url ); ?></p>
        <p><?php _e( 'Registration is free. Support, however, will only be provided within the support group of this plugin, for which we charge a small monthly subscription fee.', 'bpseo' ); ?></p>

        <table id="bpseo-prices" class="widefat">
            <thead>
            <tr>
                <th class="manage-column" scope="col"><?php _e('Description', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Price', 'bpseo' ); ?></th>
            </tr>
            </thead>
        	<?php foreach( $result['prices'] as $desc => $price )
			{
                 $alt = $this->alternate( 'odd', 'even' );
				?>
                <tr class="<?php echo $alt; ?>">
                    <td><?php echo $desc ?></td>
                    <td>EUR <?php echo $price ?></td>
                </tr>
                <?php
			}
			?>
        </table>
        
        <p><?php _e( 'Below you find a list of all our available and planned plugins and themes. Items that have a price tag attached come with 3 months free support.', 'bpseo' ); ?></p>

		<h3><?php _e( 'Plugins', 'bpseo' ) ?></h3>      
        <table class="widefat" cellspacing="0">
            <thead>
            <tr>
                <th class="manage-column" scope="col"><?php _e('Name', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Description', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Release Date', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Price', 'bpseo' ); ?></th>
            </tr>
            </thead>
        
        	<?php foreach( $result['plugins'] as $plugin )
			{
                 $alt = $this->alternate( 'odd', 'even' );
				?>
                <tr class="<?php echo $alt; ?>">
                    <td>
                    <?php if( ! empty( $plugin['url'] ) ) { ?>
                    	<a href="<?php echo $plugin['url']; ?>"><?php echo $plugin['name'] . ' ' . $plugin['version'] ; ?></a>
                    <?php } else { ?>
                    	<?php echo $plugin['name'] . ' ' . $plugin['version'] ; ?>
                    <?php } ?>
                    </td>
                    <td><?php echo $plugin['desc']; ?></td>
                    <td><?php echo $plugin['release']; ?></td>
                    <td><?php echo $plugin['cost']; ?></td>
                </tr>
            <?php } ?>
        </table>

		<h3><?php _e( 'Themes', 'bpseo' ) ?></h3>      
        <table class="widefat" cellspacing="0">
            <thead>
            <tr>
                <th class="manage-column" scope="col"><?php _e('Name', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Description', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Release Date', 'bpseo' ); ?></th>
                <th class="manage-column" scope="col"><?php _e('Price', 'bpseo' ); ?></th>
            </tr>
            </thead>
        
        	<?php foreach( $result['themes'] as $theme )
			{
                 $alt = $this->alternate( 'odd', 'even' );
				?>
                <tr class="<?php echo $alt; ?>">
                    <td>
                    <?php if( ! empty( $theme['url'] ) ) { ?>
                    	<a href="<?php echo $theme['url']; ?>"><?php echo $theme['name'] . ' ' . $theme['version'] ; ?></a>
                    <?php } else { ?>
                    	<?php echo $theme['name'] . ' ' . $theme['version'] ; ?>
                    <?php } ?>
                    </td>
                    <td><?php echo $theme['desc']; ?></td>
                    <td><?php echo $theme['release']; ?></td>
                    <td><?php echo $theme['cost']; ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
	}

	/**
	 * Content of the Donate tab
	 * @since 1.0
	 */
    function tab_donate()
	{
		?>
        <h2><?php _e( 'Donate', 'bpseo' ); ?></h2>
        
        <p><?php _e( 'We spend a lot of time and effort on implementing new features and on the maintenance of this plugin, so if you feel generous and have a few bucks to spare, then please consider to donate.', 'bpseo' ); ?></p>
        <p><?php _e( 'Click on the button below and you will be redirected to the PayPal site where you can make a safe donation', 'bpseo' ); ?></p>
        <p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" >
                <input type="hidden" name="cmd" value="_xclick"/><input type="hidden" name="business" value="mail@shabushabu-webbdesign.com"/>
                <input type="hidden" name="item_name" value="<?php _e( 'BP Advanced SEO @ http://shabushabu.eu', 'bpseo' ); ?>"/>
                <input type="hidden" name="no_shipping" value="1"/><input type="hidden" name="return" value="http://shabushabu.eu/" />
                <input type="hidden" name="cancel_return" value="http://shabushabu.eu/"/>
                <input type="hidden" name="lc" value="US" /> 
                <input type="hidden" name="currency_code" value="USD"/>
                <input type="hidden" name="tax" value="0"/>
                <input type="hidden" name="bn" value="PP-DonationsBF"/>
                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" name="submit" alt="<?php _e( 'Make payments with PayPal - it\'s fast, free and secure!', 'bpseo' ); ?>" style="border: none;"/>
            </form>
        </p>
        <p><?php _e( 'Thank you and all the best!', 'bpseo' ); ?><br />ShabuShabu Webdesign Team</p>
        <?php
	}
	
	/**
	 * alternate between anything
	 * @since 1.0
	 */
	function alternate()
	{
		static $i = 0;
		$args = func_get_args();
	
		return $args[$i++ % (func_num_args())];
	}
}
?>