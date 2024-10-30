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

/**
 * Setup the default options
 * @since 1.0
 */
function bpseo_install()
{
	global $bpseo;

	$bpseo->options = new stdClass;
	$bpseo->options->seo_home_title = '';
	$bpseo->options->seo_home_desc = '';
	$bpseo->options->seo_home_keywords = '';
	$bpseo->options->seo_profile_desc = '';
	$bpseo->options->seo_profile_keys = '';
	$bpseo->options->seo_noindex_author = 0;
	$bpseo->options->seo_noindex_tags = 0;
	$bpseo->options->seo_noindex_archives = 0;
	$bpseo->options->seo_noindex_categories = 0;
	$bpseo->options->seo_noindex_tax = 0;
	$bpseo->options->seo_noindex_activate = 0;
	$bpseo->options->seo_noindex_register = 0;
	$bpseo->options->seo_noindex_directory = 0;

	update_option( 'bpseo_options', $bpseo->options );
}

/**
 * Delete all options and database tables
 * @since 1.0
 */
function bpseo_uninstall()
{
	delete_option( 'bpseo_options' );
}
?>