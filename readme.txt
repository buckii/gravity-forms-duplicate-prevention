=== Gravity Forms Duplicate Prevention ===
Contributors: BuckeyeInteractive, stevegrunwell
Tags: Forms, Gravity Forms
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 0.1
License: GPLv2 or later

Silently prevent duplicate Gravity Form submissions.


== Description ==

This plugin adds silent duplicate detection to the popular <a href="http://www.gravityforms.com/" rel="external">Gravity Forms</a> WordPress plugin. The goal is to prevent Gravity Forms from creating multiple entries when an impatient user double-, triple, or full-on secret-combo-move-clicks his/her mouse when submitting a form.

= How does it work? =

The plugin prevents duplicate submissions in two ways: first, a small bit of JavaScript is loaded into the page that disables the submit button(s) on form submit. Second, the plugin creates a cryptographic hash of the form data upon submission. This unique hash is compared to a hash stored in a PHP session (if available) and, if a matching hash is found, the form data is altered to simulate a failing honeypot condition. Like with all failed honeypots, Gravity Forms will skip saving the data or sending any notifications but the form will appear (to the user) to have been submitted successfully.

Plugin development can be tracked on the project's Github Page: https://github.com/buckii/gravityforms-duplicateprevention


== Installation ==

1. Upload the gravity-forms-duplicate-prevention plugin to your WordPress plugins directory
2. Activate the plugin


== Frequently Asked Questions ==

= What versions of Gravity Forms has this plugin been tested against? =

The plugin was developed against Gravity Forms version 1.6.11, the most current at the time. If you find issues with newer versions please file a bug report at https://github.com/buckii/gravityforms-duplicateprevention.

= Can I prevent loading the client-side scripting? =

Yes! The JavaScript file is a very simple jQuery-powered event listener. If you'd prefer to move it to your own script file (or exclude it entirely), you can add the following to your theme's functions.php:

    add_filter( 'gform_duplicate_prevention_load_script', '__return_false' );

= How can I contribute to the further development of this plugin? =

The plugin's source is hosted on Github: https://github.com/buckii/gravityforms-duplicateprevention. If you'd like to contribute, please feel free to send us a pull request or contact us there.


== Changelog ==

= 0.1 =
* First public version of the plugin