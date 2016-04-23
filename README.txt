=== Mooberry Book Manager Image Fixer ===
Contributors: mooberrydreams
Donate link: http://www.mooberrydreams.com/
Tags: mooberry book manager
Requires at least: 3.8.0
Tested up to: 4.5
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fixes broken retailer, format, Add to Goodreads, and "Coming Soon" placeholder book cover images in Mooberry Book Manager.

== Description ==

[Mooberry Book Manager](https://wordpress.org/plugins/mooberry-book-manager/) comes with several images pre-installed. These include buttons for purchasing or downloading books, an "Add to Goodreads" button, and a placeholder book cover.

Sometimes, these images need to be reset. Possible causes could be:

* Changing domain names

* Accidentally deleting one

* Error during activation.

* Using Mooberry Book Manager version prior to 3.1 on Multi-Site

This plugin will fix these images.

To use, go to **Mooberry Book Manager Settings** -> **Image Fixer** and select the image(s) you want to fix. 

(NOTE: If using Mooberry Book Manager version prior to 3.1, go to **Tools** -> ** Book Manager Image Fixer**)


**NOTE:** Mooberry Book Manager Image Fixer is translatable. If you'd like to volunteer a translation for your language, the template file (.pot) is available in the `languages` folder. [Contact us](http://www.mooberrydreams.com/contact/) when your translation is ready or if you need assistance.


**Want regular updates?**  

* Subscribe to Mooberry Dreams' mailing list: http://www.mooberrydreams.com/products/mooberry-book-manager/  

* Like Mooberry Dreams on Facebook: https://www.facebook.com/MooberryDreams  


== Installation ==

1. Upload the entire `mooberry-book-manager` directory to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Go to **Tools** -> **Book Manager Image Fixer**

== Frequently Asked Questions ==

= I get an error about no such function called array_column. =

You webhost is using a version of PHP prior to 5.5.  Update Mooberry Book Manager Image Fixer to version 1.1 to resolve this issue.

== Screenshots ==

1. Example

== Changelog ==

= 1.3 =
* Fixed: conflicts with MBM 3.0
* Improved: Now links direct to the images instead of uploading to media directory
* Improved: with MBM 3.0, this is now a page under "Mooberry Book Manager Settings" (pre MBM 3.0 this remains in the Tools menu)
* Removed: Restore Images option (no longer necessary as images aren't uploaded to media dir)
* Wordpress 4.5 compliance

= 1.2 = 
* Updated: keep in sync with MBM 2.4.2's new images

= 1.1 =
* Fixed: uses column_exists() wrapper function in case PHP <5.5 is used

= 1.0 =
* Initial
