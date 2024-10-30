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

class bpSEOCore
{
	/**
	* instance method
	*
	* @since 1.0
	*/
	function __construct()
	{
		// Output all the seo data
		add_filter( 'bp_page_title', array( &$this, 'seo_title' ) );
		add_action( 'bp_head', array( &$this, 'load_seo' ) );
	}

	/**
	* Load all SEO functions
	*
	* @since 1.0
	*/
	function load_seo()
	{
		$this->seo_description();
		$this->seo_keywords();
		$this->seo_auth();
		$this->meta_revised();
 		$this->meta_copyright();
		$this->seo_robots();
		
		do_action( 'bpseo_load_head' );
	}

	/**
	* Appends page number if paged
	*
	* @since 1.0
	* @return string
	*/
	function is_paged()
	{
		global $paged, $cpage;
	
		// When archive pages are pages
		if( is_paged() )
			$is_paged =  sprintf( __( ' - Page: %1$s', 'bpseo' ),  $paged);
	
		// When single post comments are paged
		if( $cpage != 0 && is_single() )
			$is_paged =  sprintf( __( ' - Comments Page: %1$s', 'bpseo' ),  $cpage);
	
		return $is_paged;
	}
	
	/**
	* Meta tag for title
	*
	* @since 1.0
	* @echo string
	*/
	function seo_title()
	{
		global $bp, $bpseo, $post, $wp_query, $current_blog;

		if( is_front_page() || ( is_home() && bp_is_page( 'home' ) ) )
		{
			if( $bpseo->options->seo_home_title )
				$title = $bpseo->options->seo_home_title;
			else
				$title = get_bloginfo( 'description' );
		}
		elseif( bp_is_blog_page() )
		{
			if( is_single() || is_page() )
			{
				// Check for a custom field
				$value = get_post_meta( $post->ID, 'title', true );
				if( $value )
					$title = $value;
				// Otherwise use the post title
				else
					$title = $post->post_title;
			}
			// Year archives
			elseif( is_year() )
				$title = sprintf( __( 'Archives for the year %1$s', 'bpseo' ), get_the_time( __( 'Y', 'bpseo' ) ) );
				
			// Month archives
			elseif( is_month() )
				$title = sprintf( __( 'Archives for the month %1$s', 'bpseo' ), get_the_time( __( 'F, Y', 'bpseo' ) ) );
				
			// Day archives
			elseif( is_day() )
				$title = sprintf( __( 'Archives for %1$s', 'bpseo' ), get_the_time( __( 'F jS, Y', 'bpseo' ) ) );
				
			// Tag archives
			elseif( is_tag() )
				$title = sprintf( __( 'Tag archives for %1$s', 'bpseo' ), single_tag_title( '', false ) );
				
			// Author archives
			elseif( is_author() )
			{
				$auth = get_userdata( get_query_var( 'author' ) );
				$title = sprintf( __( 'Archives for %1$s', 'bpseo' ), $auth->display_name );
			}
			// Search results
			elseif( is_search() )
				$title = sprintf( __( 'Search results for \'%1$s\'', 'bpseo' ), attribute_escape( get_search_query() ) );
				
			// Category archives
			elseif( is_category() )
				$title = sprintf( __( 'Archives for the category %1$s', 'bpseo' ), single_cat_title( '', false ) );
				
			// 404 error
			elseif( is_404() )
				$title = __( 'Error 404 - Nothing found', 'bpseo' );
				
			// use the tagline
			else
				$title = get_bloginfo( 'description' );
		}
		elseif( ! empty( $bp->displayed_user->fullname ) )
		{
			$title = strip_tags( $bp->displayed_user->fullname . ' - ' . ucwords( $bp->current_component ) );
		}
		elseif( $bp->is_single_item )
		{
			$thing = $bp->bp_options_nav[$bp->current_component][$bp->current_action]['name'];
			
			$title = ucwords( $bp->current_component ) . ' - ' . $bp->bp_options_title;
			
			if( $thing )
				$title .= ' - ' . $thing;
		}
		elseif( $bp->is_directory )
		{
			if ( ! $bp->current_component )
				$title = sprintf( __( '%s Directory', 'buddypress' ), ucwords( BP_MEMBERS_SLUG ) );
				
			else
				$title = sprintf( __( '%s Directory', 'buddypress' ), ucwords( $bp->current_component ) );
		}
		elseif( bp_is_register_page() )
		{
			$title = __( 'Create an Account', 'buddypress' );
		}
		elseif( bp_is_activation_page() )
		{
			$title = __( 'Activate your Account', 'buddypress' );
		}
		else
			$title = get_bloginfo( 'description' );

		$title = apply_filters( 'bpseo_title', $title );
	
		// Put it all together and spit it out
		printf( '%1$s%2$s | %3$s', $title, $this->is_paged(), get_bloginfo( 'name' ) ). "\n";
	}
	
	/**
	* Meta tag for description
	* Backwards compatible with All In One SEO
	*
	* @since 1.0
	* @echo string
	*/
	function seo_description()
	{
		global $bp, $post, $bpseo;
		
		if( bp_is_blog_page() )
		{
			if( is_home() || is_front_page() )
			{
				// Theme option
				$description = trim( stripcslashes( $bpseo->options->seo_home_desc ) );
				if( ! $description )
					// Look up AIOSEO
					$description = trim( stripcslashes( get_option( 'aiosp_home_description' ) ) );

				if( ! $description )
					// Lets use the tagline then
					$description  = get_bloginfo('description');
			}
			elseif( is_single() || is_page() )
			{
				// This should be backwards compatible with AIOSEO
				$description = trim( stripcslashes( get_post_meta( $post->ID, 'description', true ) ) );
				if( ! $description )
					// Use truncated content
					$description = $this->truncate( $post->post_content );
			}
			elseif( is_author() )
			{
				// Use user description
				$auth = get_userdata( get_query_var( 'author' ) );
				$description = $this->truncate( $auth->description );
			}
			elseif( is_category() )
				// Let's use the category description
				$description = $this->truncate( category_description() );
	
			elseif ( is_tag() )
				$description = $this->truncate( tag_description() );
		
			elseif ( is_tax() )
				$description = $this->truncate( term_description( '', get_query_var( 'taxonomy' ) ) );
		}
		elseif( $bp->is_directory || bp_is_register_page()|| bp_is_activation_page() )
		{
			$description = trim( stripcslashes( get_post_meta( $post->ID, 'description', true ) ) );
		}
		elseif( bp_is_group_single() )
		{
			$description = $this->truncate( bp_get_group_description( $bp->groups->current_group ) );
		}
		elseif( bp_is_user_profile() )
		{
			$description = $this->truncate( bp_get_profile_field_data( 'field='. $bpseo->options->seo_profile_desc ) );
		}
		else
		{
			$description = trim( stripcslashes( $bpseo->options->seo_home_desc ) );
		}
		
		if( ! empty( $description ) )
		{
			$description = apply_filters( 'bpseo_description', $description );
			
			// Put it all together if there is a description
			printf('<meta name="description" content="%1$s%2$s" />', $description, $this->is_paged() ). "\n";

		}
	}
	
	/**
	* Meta tag for keywords
	* Adjusted from Hybrid Theme, keywords for categories and author pages as well
	*
	* @since 1.0
	* @echo string
	*/
	function seo_keywords()
	{
		global $bp, $bpseo, $wp_query, $posts, $post;
		
		if( bp_is_blog_page() )
		{
			if( is_single() && ! is_preview() || is_author() || is_category() )
			{
				if( is_array( $posts ) )
				{
					$keywords = array();
					
					foreach( $posts as $post )
					{
						if( $post )
						{
							// Check the custom field
							$keys = get_post_meta( $post->ID, 'keywords', true );
							if( $keys )
								$keywords[] = $keys;
		
							// Get all the category names
							$cats = get_the_category();
							foreach( $cats as $cat )
								$keywords[] = $cat->name;
		
							// Get the tag names
							$wp_query->in_the_loop = true;
		
							$tags = get_the_tags();
							
							if( $tags )
								foreach( $tags as $tag )
									$keywords[] = $tag->name;
		
							$wp_query->in_the_loop = false;
						}
					}
				}
			}
			elseif( is_page() && ! is_preview() )
			{
				// No tags or categories here, only custom fields
				$keys = get_post_meta( $post->ID, 'keywords', true );
				if( $keys )
					$keywords[] = $keys;
			}
			elseif( is_home() || is_front_page() )
			{
				// Get theme option
				$keywords = trim( stripcslashes( $bpseo->options->seo_home_keywords ) );
				if( ! $keywords )
					// Check for AIOSEO
					$keywords = trim( stripcslashes( get_option( 'aiosp_home_keywords' ) ) );
			}
			else
				$keywords = trim( stripcslashes( $bpseo->options->seo_home_keywords ) );
		}
		elseif( bp_is_user_profile() )
		{
			$keywords = bp_get_profile_field_data( 'field='. $bpseo->options->seo_profile_keys );
		}
		elseif( $bp->is_directory || bp_is_register_page()|| bp_is_activation_page() )
		{
			$keys = get_post_meta( $post->ID, 'keywords', true );
			if( $keys )
				$keywords = $keys;
		}
		else
		{
			$keywords = trim( stripcslashes( $bpseo->options->seo_home_keywords ) );
		}
		
		if( is_array( $keywords ) )
		{
			// Make sure we don't include more of the same keywords
			$keywords = array_unique( $keywords );
			// Join them all together
			$keywords = join( ',', $keywords );
		}
			
		$keywords = apply_filters( 'bp_seo_keywords', $keywords );
		
		// And echo them if there are any keywords
		if( $keywords )
			printf('<meta name="keywords" content="%1$s" />', wp_specialchars( strtolower( $keywords ), 1 ) ). "\n";
	}
	
	/**
	* Meta tag for author on is_single and is_page
	*
	* @since 1.0
	* @echo string
	*/
	function seo_auth()
	{
		if( bp_is_blog_page() )
		{
			if( is_single() || is_page() )
			{
				global $wp_query, $post;
		
				$curauth = get_userdata($wp_query->post->post_author);
				// First full name
				if( $curauth->first_name && $curauth->last_name )
					$seoauth = $curauth->first_name .' '. $curauth->last_name;
				// Then display name
				elseif( $curauth->display_name )
					$seoauth = $curauth->display_name;
				// Then nice name
				elseif( $curauth->user_nicename )
					$seoauth = $curauth->user_nicename;
				// Then nickname
				elseif( $curauth->nickname )
					$seoauth = $curauth->nickname;
				// and if all fails then login
				else
					$seoauth = $curauth->user_login;
				// echo only on single and page view
				$seoauth = apply_filters( 'bp_seo_author', $seoauth );
	
				echo '<meta name="author" content="' . $seoauth . '" />'. "\n";
			}
		}
	}

	/**
	* Meta tag for revised timestamp
	*
	* @since 1.0
	* @echo string
	*/
	function meta_revised()
	{
		if ( is_single() && bp_is_blog_page() || is_page() && bp_is_blog_page() )
		{
			$date = get_the_modified_time( __('l, F jS, Y, g:i a', 'bpseo' ) );
			printf(__('<meta name="revised" content="%1$s by %2$s" />'), $date, get_the_modified_author() ) . "\n";
		}
	}

	/**
	* Copyright meta tag
	*
	* @since 1.0
	* @echo string
	*/
	function meta_copyright()
	{
		if( bp_is_blog_page() )
		{
			if ( is_single() || is_page() )
				$date = get_the_time( __('F Y', 'bpseo' ) );
			else
				$date = date( __('Y', 'bpseo' ) );
		
			printf( __('<meta name="copyright" content="Copyright (c) %1$s" />', 'bpseo' ), $date );
		}
	}
	
	/**
	* Meta tag for robots, index, noindex
	*
	* @since 1.0
	* @echo string
	*/
	function seo_robots()
	{
		global $bp, $bpseo;
		
		$noindex = '<meta name="robots" content="noindex,follow" />'. "\n";
		
		if( bp_is_blog_page() )
		{
			// Don't do anything if blog is not public
			if( ! get_option( 'blog_public' ) )
				return;
			
			if( is_author() && $bpseo->options->seo_noindex_author || is_404() || is_search() ||	is_tag() && $bpseo->options->seo_noindex_tags ||
			! is_category() && is_archive() && ! is_tag() && $bpseo->options->seo_noindex_archives || is_category() && $bpseo->options->seo_noindex_categories
			|| is_tax() && $bpseo->options->seo_noindex_tax )
				$meta_string = $noindex;
			else
				$meta_string = '<meta name="robots" content="index,follow" />'. "\n";
		}
		elseif( $bp->is_directory )
		{
			if( $bpseo->options->seo_noindex_directory )
				$meta_string = $noindex;
			else
				$meta_string = '<meta name="robots" content="index,follow" />'. "\n";
		}
		elseif( bp_is_register_page() )
		{
			if( $bpseo->options->seo_noindex_register )
				$meta_string = $noindex;
			else
				$meta_string = '<meta name="robots" content="index,follow" />'. "\n";
		}
		elseif( bp_is_activation_page() )
		{
			if( $bpseo->options->seo_noindex_activate )
				$meta_string = $noindex;
			else
				$meta_string = '<meta name="robots" content="index,follow" />'. "\n";
		}
		
		$meta_string = apply_filters( 'bpseo_noindex', $meta_string );
		
		echo $meta_string;
	}
	
	/**
	* Creates an excerpt
	*
	* @since 1.0
	* @echo string
	*/
	function truncate( $string, $limit = 160, $break = ' ', $pad = '...' )
	{
		$string = preg_replace( "/<h[23456][^>]*>(.*)<\/h[23456][^>]*>/", "", $string );
		$string = strip_tags( $string );
		$string = ereg_replace( "<img[^>]*>", "", $string );
		$string = strip_shortcodes( $string );
		
		if(strlen( $string) <= $limit ) 
			return $string;
			
		if( false !== ( $breakpoint = strpos( $string, $break, $limit ) ) )
		{
			if( $breakpoint < strlen( $string ) - 1 )
				$string = substr( $string, 0, $breakpoint ) . $pad; 
		}
		
		return $string; 
	}
	
} // End class
$bpseo_core = new bpSEOCore();
?>