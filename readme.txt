=== Gravity Forms Duplicate Prevention ===
Contributors: BuckeyeInteractive, stevegrunwell
Tags: Forms, Gravity Forms
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 0.1.1
License: GPLv2 or later

Silently prevent duplicate Gravity Form submissions.


== Description ==

This plugin adds silent duplicate detection to the popular [Gravity Forms](http://www.gravityforms.com/) WordPress plugin. The goal is to prevent Gravity Forms from creating multiple entries when an impatient user double-, triple, or full-on secret-combo-move-clicks his/her mouse when submitting a form. No modifications to your forms is necessary, it just works.

= How does it work? =

The plugin prevents duplicate submissions in two ways: first, a small bit of JavaScript is loaded into the page that disables the submit button(s) on form submit. Second, the plugin creates a cryptographic hash of the form data upon submission. This unique hash is compared to a hash stored in a PHP session (if available) and, if a matching hash is found, the form data is altered to simulate a failing honeypot condition. Like with all failed honeypots, Gravity Forms will skip saving the data or sending any notifications but the form will appear (to the user) to have been submitted successfully.

Plugin development can be tracked on the project's Github Page: [https://github.com/buckii/gravity-forms-duplicate-prevention](https://github.com/buckii/gravity-forms-duplicate-prevention)


== Installation ==

1. Upload the gravity-forms-duplicate-prevention plugin to your WordPress plugins directory
2. Activate the plugin


== Frequently Asked Questions ==

= What versions of Gravity Forms has this plugin been tested against? =

The plugin was developed against Gravity Forms version 1.6.11, the most current at the time. If you find issues with newer versions please file a bug report at [https://github.com/buckii/gravity-forms-duplicate-prevention](https://github.com/buckii/gravity-forms-duplicate-prevention).

= Can I prevent loading the client-side scripting? =

Yes! The JavaScript file is a very simple jQuery-powered event listener. If you'd prefer to move it to your own script file (or exclude it entirely), you can add the following to your theme's functions.php:

    add_filter( 'gform_duplicate_prevention_load_script', '__return_false' );

= I find your lack of paranoia disturbing. What if your plugin accidentally honeypots a valid submission? =

We'd by lying if I said that didn't happen to us in testing. As a result we've ensured that Gravity Forms Duplicate Prevention will log the raw HTTP POST data upon detection of a duplicate entry (sent through PHP's system logger using [`error_log`](http://php.net/manual/en/function.error-log.php)). If something goes wrong your data should be recoverable.

As of version 0.1.1 you can also latch onto the `gform_duplicate_prevention_duplicate_entry` action hook if you want to do anything else with the duplicate data; Your function will receive the `$validation_result` array as it was passed to the plugin by Gravity Forms' [`gform_validation`](http://www.gravityhelp.com/documentation/page/Gform_validation) filter.

**Example:**

    function log_duplicate_entries( $validation_result ) {
      // send an email, log it, and/or add points to the user's double-click combo score here
    }
    add_action( 'gform_duplicate_prevention_duplicate_entry', 'log_duplicate_enties' );

= How can I contribute to the further development of this plugin? =

The plugin's source is hosted on Github: [https://github.com/buckii/gravity-forms-duplicate-prevention](https://github.com/buckii/gravity-forms-duplicate-prevention). If you'd like to contribute, please feel free to send us a pull request or contact us there.


== Changelog ==

= 0.1.1 =
* Added `gform_duplicate_prevention_duplicate_entry` action hook that fires when a duplicate entry is detected
* Documentation updates

= 0.1 =
* First public version of the plugin

== Upgrade Notice ==

= 0.1.1 =
Added a new action hook that allows access to duplicate submission data.