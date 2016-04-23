<?php
 /*
Plugin Name: Mooberry Book Manager Image Fixer
Plugin URI: http://www.mooberrydreams.com/products/mooberry-book-manager/
Description: Fixes broken retailer, format, goodreads, and placeholder book cover images in Mooberry Book Manager.
Author: Mooberry Dreams
Version: 1.3
Author URI: http://www.mooberrydreams.com/
Text Domain: mooberry-book-manager-image-fixer

Copyright 2015  Mooberry Dreams  (email : bookmanager@mooberrydreams.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA	
	
*/

define('MBDBIF_PLUGIN_DIR', plugin_dir_path( __FILE__ )); 
define('MBDBIF_PLUGIN_VERSION_KEY', 'mbdbif_version');
define('MBDBIF_PLUGIN_VERSION', '1.3');

$mbdb_version = get_option('mbdb_version');
$mbdb_url = '';


add_action( 'plugins_loaded', 'mbdbif_check_dependencies' );
function mbdbif_check_dependencies() {
	// requires Book Manager
	if( !function_exists( 'mbdb_activate' ) ) {
		if ( current_user_can( 'activate_plugins' ) ) {
		  add_action( 'admin_init', 'mbdbif_deactivate_image_fixer' );
		  add_action( 'admin_notices', 'mbdbif_dependency_fail' );
		 } 
	}
	

	
	load_plugin_textdomain( 'mooberry-book-manager-image-fixer', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	
	global $mbdb_url;
	if ( ! defined( 'MBDB_PLUGIN_URL' ) ) {
		$mbdb_url =  plugins_url() . '/mooberry-book-manager/' ;
	} else {
		$mbdb_url = MBDB_PLUGIN_URL;
	}

}	

// deactivates the plugin
function mbdbif_deactivate_image_fixer() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
	
function mbdbif_dependency_fail() {
	echo '<div class="updated"><p>' . __('Mooberry Book Manager Image Fixer requires <strong>Mooberry Book Manager</strong>. The plug-in has been <strong>deactivated</strong>.', 'mooberry-book-manager-image-fixer') . '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

add_action( 'admin_head', 'mbdbif_register_admin_styles' );
function mbdbif_register_admin_styles() {
	if (array_key_exists('page', $_GET)) {
		if (is_admin() && ($_GET['page'] == 'mbdbif_settings') ) {
			wp_register_style( 'mbdbif_admin_styles', plugins_url( 'style.css', __FILE__)  );
			wp_enqueue_style( 'mbdbif_admin_styles' );
		}
	}
}

/*
add_action( 'admin_footer', 'mbdbif_register_script');
function mbdbif_register_script() {
	if (array_key_exists('page', $_GET)) {
		if ( is_admin() && ($_GET['page'] == 'mbdbif_settings') ) {
			wp_enqueue_script( 'admin-image-fixer',  plugins_url( 'image-fixer.js', __FILE__)); 
		}
	}
}
*/


add_action( 'init', 'mbdbif_init');
function mbdbif_init() {
	
	$current_version = get_option(MBDBIF_PLUGIN_VERSION_KEY);
		
	if ($current_version == '') {
		$current_version = MBDBIF_PLUGIN_VERSION;
	}
	// do any upgrade routines here
	
	
	// update to current version
	update_option(MBDBIF_PLUGIN_VERSION_KEY, MBDBIF_PLUGIN_VERSION);
	

}

/**************************************
 *  settings menupage
 ****************************************/

 //if MBM v3+ add as a page to options
 if (version_compare($mbdb_version, '3.0', '>=')) {
	add_filter('mbdb_settings_pages', 'mbdbif_add_options_page');
	add_action('mbdb_settings_metabox', 'mbdbif_settings_page_v3', 10, 2);
	
	
} else { 
	add_action( 'admin_menu', 'mbdbif_settings_menu');
}

 function mbdbif_add_options_page( $pages ) {
	$pages['mbdbif_fix_images'] = array ( 
					'page_title'	=>	__('Mooberry Book Manager Image Fixer', 'mooberry-book-manager-image-fixer'),
					'menu_title'	=>	__('Image Fixer', 'mbm-multi-author')
					);
	
	return $pages;
 }

function mbdbif_settings_menu() {
	add_management_page( __('Mooberry Book Manager Image Fixer', 'mooberry-book-manager-image-fixer'), __('Book Manager Image Fixer', 'mooberry-book-manager-image-fixer'), 'manage_options', 'mbdbif_settings', 'mbdbif_settings_page');
}

function mbdbif_settings_page_v3( $metabox, $tab) {
	if ($tab == 'mbdbif_fix_images') {

		
		$metabox->add_field( array(
					'id'	=> 'mbdbif_title',
					'name'	=>	__('REPAIR IMAGES', 'mooberry-book-manager-image-fixer'),
					'type'	=>	'title',
					'desc' =>__('This will restore the original images that were included with Mooberry Book Manager. Images that were deleted will not be restored unless Restore is checked below.', 'mooberry-book-manager-image-fixer'),
				)
			);
			$metabox->add_field( 	 array(
					'id'	=>	'mbdbif_images_to_fix',
					'name'	=>	__('Fix these images', 'mooberry-book-manager-image-fixer'),
					'type'	=>	'multicheck',
					'options'	=>	mbdbif_image_options(),
				)
			);
	}
	return $metabox;
}

	
function mbdbif_settings_page(  ) {
	
	echo '<h1>' . __('Mooberry Book Manager Image Fixer', 'mooberry-book-manager-image-fixer') . '</h1>';
	
	$metabox = apply_filters('mbdbif_settings_options_meta_box', array(
			'id'         => 'mbdbif_option_metabox',
			'show_on'    => array( 'key' => 'options-page', 'value' => 'mbdbif_options', ),
			'show_names' => true,
			'fields'     => array( 
				array(
					'id'	=> 'mbdbif_title',
					'name'	=>	'', //__('REPAIR IMAGES', 'mooberry-book-manager-image-fixer'),
					'type'	=>	'title',
					'desc' =>__('This will restore the original images that were included with Mooberry Book Manager. Images that were deleted will not be restored unless Restore is checked below.', 'mooberry-book-manager-image-fixer') . ' <br><br><b>' . __('Please be patient. The process can take a minute to run.', 'mooberry-book-manager-image-fixer') . '</b>',
				),
				 array(
					'id'	=>	'mbdbif_images_to_fix',
					'name'	=>	__('Fix these images', 'mooberry-book-manager-image-fixer'),
					'type'	=>	'multicheck',
					'options'	=>	mbdbif_image_options(),
				),
		/*		array(
					'id'	=>	'mbdbif_include_deleted',
					'name'	=>	__('Restore deleted images', 'mooberry-book-manager-image-fixer'),
					'type'	=>	'checkbox',
					'options'	=> array('restore' => 'Restore')
				), */
			)
		)
	);
	
	cmb2_metabox_form( $metabox, 'mbdbif_options' );	
	

	
}




function mbdbif_image_options() {
	return array( 				
				'retailers'	=>	__('Retailers',  'mooberry-book-manager-image-fixer'),
				'formats'	=>	__('E-book Formats', 'mooberry-book-manager-image-fixer'),
				'goodreads'	=>	__('Add to Goodreads', 'mooberry-book-manager-image-fixer'),
				'coming-soon'	=>	__('Coming Soon book cover placeholder', 'mooberry-book-manager-image-fixer'),
			);
}

 if (version_compare($mbdb_version, '3.0', '>=')) {
	add_filter('cmb2_override_option_save_mbdb_options', 'mbdbif_override_save_v3', 10, 3);
 }
 
 function mbdbif_override_save_v3( $override, $option, $options_object ) {
	 $screen = get_current_screen();
	 if ($screen->id == 'mooberry-book-manager-settings_page_mbdbif_fix_images') {
	 	 return mbdbif_override_save( $override, $option, $options_object );
	 } else {
		 return $override;
	 }
 }
 
 
add_filter('cmb2_override_option_save_mbdbif_options', 'mbdbif_override_save', 10, 3);
function mbdbif_override_save( $override, $options, $options_object ) {

	$image_options = array_keys(mbdbif_image_options());
	
	if ( is_array($options) && array_key_exists('mbdbif_images_to_fix', $options)) {
		$images = array_intersect($image_options, $options['mbdbif_images_to_fix']);
	}
	
	if ( !is_array($options) || ! array_key_exists('mbdbif_images_to_fix', $options) || empty( $images ) ) {
		add_settings_error( 'mbdbif_options-notices', '', __( 'You must select at least one type of image.', 'mooberry-book-manager-image-fixer' ), 'error' );
		settings_errors( 'mbdbif_options-notices' );
		return '';
	}
	$restore = array_key_exists('mbdbif_include_deleted', $options);
	
	// if image fixer, do the image fixing, report the results, and override the saving
	mbdbif_fix_images($options['mbdbif_images_to_fix'], $restore);
	return '';
}


add_filter('cmb2_override_option_get_mbdbif_options', 'mbdbif_override_get', 10, 3);
function mbdbif_override_get( $override, $default, $options_object ) {
	
			
	// if the image fix tab, there's nothing to retrieve from the database so just
	// return a blank to override
	return '';
		
}

function mbdbma_get_default_formats() {
	$default_formats = array();
	$default_formats[] = array('name' => 'ePub', 'uniqueID' => 1, 'image' => 'epub.png');
	// use pre-MBM 2.4.2 images if necessary
	if (version_compare(MBDB_PLUGIN_VERSION, '2.4.2', '<')) {
		$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 2, 'image' => 'amazon-kindle.jpg');
	} else {
		$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 2, 'image' => 'amazon-kindle.png');
	}
	$default_formats[] = array('name' => 'PDF', 'uniqueID' => 3, 'image' => 'pdficon.png');
	
	return apply_filters('mbdbma_default_formats', $default_formats);
	
}



function mbdbma_get_default_retailers() {
	$default_retailers = array();
	$default_retailers[] = array('name' => 'Amazon', 'uniqueID' => 1, 'image' => 'amazon.png');
	
	$default_retailers[] = array('name' => 'Kobo', 'uniqueID' => 3, 'image' => 'kobo.png');
	$default_retailers[] = array('name' => 'iBooks', 'uniqueID' => 4, 'image' => 'ibooks.png');
	$default_retailers[] = array('name' => 'Smashwords', 'uniqueID' => 5, 'image' => 'smashwords.png');
	$default_retailers[] = array('name' => 'Audible', 'uniqueID' => 6, 'image' => 'audible.png' );

	$default_retailers[] = array('name' => 'Books A Million', 'uniqueID' => 8, 'image' => 'bam.png' );
	$default_retailers[] = array('name' => 'Create Space', 'uniqueID' => 9, 'image' => 'createspace.png' );
	
	$default_retailers[] = array('name' => 'Scribd', 'uniqueID' => 12, 'image' => 'scribd.png' );
	$default_retailers[] = array('name' => 'Amazon Kindle', 'uniqueID' => 13, 'image' => 'kindle.png' );
	$default_retailers[] = array('name' => 'Barnes and Noble Nook', 'uniqueID' => 14, 'image' => 'nook.png' );
	
	// use pre-MBM 2.4.2 images if necessary
	if (version_compare(MBDB_PLUGIN_VERSION, '2.4.2', '<')) {
		$default_retailers[] = array('name' => 'Barnes and Noble', 'uniqueID' => 2, 'image' => 'bn.jpg ');
		$default_retailers[] = array('name' => 'Book Baby', 'uniqueID' => 7, 'image' => 'bookbaby.gif' );
		$default_retailers[] = array('name' => 'Indie Bound', 'uniqueID' => 10, 'image' => 'indiebound.gif' );
		$default_retailers[] = array('name' => 'Powells', 'uniqueID' => 11, 'image' => 'powells.jpg' );
	} else {
		$default_retailers[] = array('name' => 'Barnes and Noble', 'uniqueID' => 2, 'image' => 'bn.png ');
		$default_retailers[] = array('name' => 'Book Baby', 'uniqueID' => 7, 'image' => 'bookbaby.png' );
		$default_retailers[] = array('name' => 'Indie Bound', 'uniqueID' => 10, 'image' => 'indiebound.png' );
		$default_retailers[] = array('name' => 'Powells', 'uniqueID' => 11, 'image' => 'powells.png' );
	}
	
	return apply_filters('mbdbma_default_retailers', $default_retailers);
}


function mbdbif_fix_images( $images, $restore ) {
	
	$errors = array();
	foreach( $images as $image ) {
		$error = array();
		switch ($image) {
			case 'coming-soon':
			case 'goodreads':
				$error = mbdbif_fix_single_image($image, $restore);
				break;
			case 'retailers':
				if (function_exists('mbdb_get_default_retailers')) {
					$defaults = mbdb_get_default_retailers();
				} else {
					$defaults = mbdbma_get_default_retailers();
				}
				$error = mbdbif_fix_multiple_images( $image, $defaults, $restore );
				break;
			case 'formats':
				if (function_exists('mbdb_get_default_formats')) {
					$defaults = mbdb_get_default_formats();
				} else {
					$defaults = mbdbma_get_default_formats();
				}
				$error = mbdbif_fix_multiple_images( $image, $defaults, $restore );
				break;
		}
	
		$errors = array_merge($errors, $error);
		
	}
	if (count($errors)>0) {
		$message =  __('The following images were not able to be fixed:', 'mooberry-book-manager-image-fixer');
		$message .=  '<ul>';
		foreach( $errors as $error ) {
			$message .= '<li>' . $error . '</li>';
		}
		$message .= '</ul>';
		$class = 'error';
	} else {
		$class = 'updated';
		$message = __('Images updated!', 'mooberry-book-manager-image-fixer');
	}
		add_settings_error( 'mbdbif_options-notices', '', $message, $class );
		settings_errors( 'mbdbif_options-notices' );
}

function mbdbif_fix_single_image( $image, $restore ) {
	$mbdb_options = get_option('mbdb_options');
	$filenames = array( 'coming-soon' => 'coming_soon_blue.jpg',
						'goodreads' => 'goodreads.png');
	$message = array('coming-soon'	=>	__('Coming Soon Book Cover', 'mooberry-book-manager-image-fixer'),
					'goodreads'	=>	__('Add to Goodreads', 'mooberry-book-manager-image-fixer'));
					
	$error = array();
	global $mbdb_url;
	$path = $mbdb_url . 'includes/assets/';
	
	$filename = $filenames[$image];
	$mbdb_options[$image] = $path . $filename;
	$mbdb_options[$image . '_id'] = 0;
	/*
	$attachID = mbdb_upload_image($filename);
	$mbdb_options[$image . '-id'] = $attachID;
	if ($attachID != 0) {
		$img = wp_get_attachment_url( $attachID );
		$mbdb_options[$image] = $img;
	} else {
		$mbdb_options[$image] = '';
		$error[] = $message[$image];
	}*/
	
	update_option('mbdb_options', $mbdb_options);
	
	return $error;
}



function mbdbif_fix_multiple_images( $setting, $defaults, $restore ) {
	
	
	$mbdb_options = get_option('mbdb_options');
	
	if ( array_key_exists($setting, $mbdb_options) ) {
		$existing_ids = array_column( $mbdb_options[$setting], 'uniqueID');
	} else {
		$existing_ids = array(); //array_column( $defaults, 'uniqueID');
	}
	
	$error = array();
	foreach( $defaults as $default ) {
		// if this default is in the existing settings OR we're restoring deleted ones
		//if (in_array($default['uniqueID'], $existing_ids) || $restore ) {
			//$attachID = mbdb_upload_image( $default['image'] );
			global $mbdb_url;
			$path = $mbdb_url . 'includes/assets/' . $default['image']; 
			
			if (in_array($default['uniqueID'], $existing_ids) ) {
				$index = array_search($default['uniqueID'], $existing_ids );
				$mbdb_options[$setting][$index]['name'] = $default['name'];
				$mbdb_options[$setting][$index]['image'] = $path;
				$mbdb_options[$setting][$index]['image_id'] = 0;
			} else {
				$mbdb_options[$setting][] = array( 'name' => $default['name'],
													'image'	=> $path,
													'uniqueID'	=> $default['uniqueID'],
													'image_id'	=> 0,
												);
			}
			/* if ( $attachID != 0 ) {
				// if it wasn't found that means we're restoring a deleted one
				if ( $index === false ) {
					$mbdb_options[$setting][] = array( 'imageID' => $attachID,
														'image'	=>	wp_get_attachment_url( $attachID ),
														'name'	=> $default['name'],
														'uniqueID'	=> $default['uniqueID']
													);
				} else {
					$mbdb_options[$setting][$index]['imageID'] = $attachID;
					$mbdb_options[$setting][$index]['image'] = wp_get_attachment_url( $attachID );
				}
				
			} else {
				$error[] = $mbdb_options[$setting][$index]['name'] . ' (' . $setting . ')';
			}
			*/
		//}
	}
	
	update_option('mbdb_options', $mbdb_options);
	return $error;
}

// for users with PHP <5.5
if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}
