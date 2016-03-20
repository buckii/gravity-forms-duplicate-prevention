<?php
/**
 * Plugin Name: Gravity Forms Duplicate Prevention
 * Plugin URI: http://wordpress.org/extend/plugins/gravity-forms-duplicate-prevention/
 * Description: Prevent duplicate form submissions on both the client- and server-sides. Requires Gravity Forms.
 * Author: Buckeye Interactive
 * Version: 0.1.5
 * Author URI: http://www.buckeyeinteractive.com
 * License: GPL2
 */

class GravityFormsDuplicatePrevention {

  /**
   * Plugin release version
   */
  const PLUGIN_VERSION = '0.1.5';

  /**
   * Class constructor
   * @uses add_filter()
   */
  public function __construct() {

    // Hosts like Pantheon throw a 500 when session_start() is called and
    // non-file based session handling is in place. This filter allows us
    // to only control the usage of this plugin when an applicable session
    // handler is in place

    if ( ! apply_filters( 'gform_duplicate_prevention_execute', true ) ) {
        return;
    }
    // Attempt to start the PHP session
    $this->start_session();

    // Support GF Logging
    add_filter( 'gform_logging_supported', array( $this, 'set_logging_supported' ) );

    // Apply our server-side filtering
    add_filter( 'gform_validation', array( &$this, 'duplicate_detection' ), 10, 1 );

    // Enqueue our client-side script
    $this->load_script();
  }

  /**
   * Send a message to the log files
   * @param str $message The message to write
   * @param bool $debug Should this message only be written if WP_DEBUG is true?
   * @return void
   */
  protected function log( $message, $debug=false ) {
    if ( ! $debug || WP_DEBUG ) {
      error_log( sprintf( 'GFDP: %s', $message ) );
    }
    if ( class_exists( 'GFLogging' ) ) {
      GFLogging::include_logger();
      $level = $debug ? KLogger::ERROR : KLogger::DEBUG; // Treat messages shown with WP_DEBUG on as errors
      GFLogging::log_message( 'gfdp', $message, $level );
    }
  }

  public function set_logging_supported( $plugins ) {
    $plugins['gfdp'] = 'Duplicate Prevention';
    return $plugins;
  }

  /**
   * Since WordPress doesn't use sessions we'll need to manually start one
   * @return bool
   */
  protected function start_session() {
    if ( ! session_id() ) {
      session_start();
    }
    return true;
  }

  /**
   * Verify that a form submission is unique by creating and checking a md5 hash of the values against the session
   * If the generated hash matches what's in the session then the user has most likely double-clicked the submit
   * button, causing a duplicate entry. In these instances we'll simulate a non-empty honeypot form field, tricking
   * Gravity Forms into pretending the submission was successful while actually disregarding the submission.
   *
   * Site owners:
   * If you need to access the $validation_result variable after a failed hash check you can do so by listening for
   * the gform_duplicate_prevention_duplicate_entry action hook.
   *
   * @param array $validation_result The array passed to us by the gform_validation filter
   * @return bool
   * @see http://www.gravityhelp.com/documentation/page/Gform_validation
   */
  public function duplicate_detection( $validation_result ) {
    $hash = $this->hash_array( $_POST );

    // If this unique ID is already in our session the form is likely a double submission
    if ( isset( $_SESSION['gform_hash'] ) && $_SESSION['gform_hash'] == $hash ) {

      // Make Gravity Forms think there's a honeypot mismatch
      $form = $validation_result;
      $validation_result['form']['enableHoneypot'] = true;
      $_POST[ sprintf( 'input_%d', self::get_max_field_id( $validation_result['form'] ) + 1 ) ] = 'duplicate';
      $this->log( $this->format_log( $validation_result ), false );
      do_action( 'gform_duplicate_prevention_duplicate_entry', $form );

    } else {
      // Store $uid in the session - this is either the first or only time they've submitted this UID
      $_SESSION['gform_hash'] = $hash;
    }
    return $validation_result;
  }

  protected function format_log( $validation_result ) {
      $input = $_POST; // Make a copy.

      foreach ( $validation_result['form']['fields'] as $field ) {
          // Filter credit card numbers.
          if ( $field instanceof GF_Field_CreditCard ) {
              foreach ( range( 1, 5 ) as $input_id ) {
                  $input_name = "input_{$field->id}_{$input_id}";

                  if ( isset( $input[$input_name] ) ) {
                      $input[$input_name] = $field->get_value_save_entry( $input[$input_name], NULL, $input_name, NULL, NULL ); // $form, $lead_id, and $lead params not used for credit card.
                  }
              }
          }
      }
      return sprintf( 'Blocking duplicate submission for form ID %d: %s', $validation_result['form']['id'], print_r( $input, true ) );
  }

  /**
   * Create a cryptographic hash for an array
   * @param array $array The array to hash
   * @return str
   */
  protected static function hash_array( $array=array() ) {
    return md5( print_r( $array, true ) );
  }

  /**
   * Return the maximum field ID
   * This is taken directly from GFFormDisplay::get_max_field_id() with only formatting changes - were the original not a
   * private class method we'd be using that.
   * @param array $form The GF Form Object
   * @return int
   */
  protected static function get_max_field_id( $form ) {
    $max = 0;
    foreach ( $form['fields'] as $field ) {
      $val = floatval( $field['id'] );
      if ( $val > $max ) {
        $max = $val;
      }
    }
    return $max;
  }

  /**
   * Register our client script file and, unless told otherwise, set it up to be enqueued on gform_enqueue_scripts.
   *
   * Site owners:
   * To prevent loading the client-side scripting, add this to your theme:
   * add_filter( 'gform_duplicate_prevention_load_script', '__return_false' );
   *
   * @return void
   * @uses add_action()
   * @uses apply_filters()
   * @uses wp_enqueue_script()
   * @uses wp_register_script()
   */
  public function load_script() {
    wp_register_script( 'gform-duplicateprevention', plugins_url( 'gravityforms-duplicateprevention.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );

    // Unless the site owner tells us not to load our script with the other Gravity Forms scripting
    if ( apply_filters( 'gform_duplicate_prevention_load_script', true ) ) {
      add_action( 'gform_enqueue_scripts', array( &$this, 'enqueue_script' ) );
    }
  }

  /**
   * Enqueue our client-side scripting
   * To reduce unnecessary loading this should only be enqueued when the gform_enqueue_scripts action is called
   * @return void
   * @uses wp_enqueue_script()
   */
  public function enqueue_script() {
    wp_enqueue_script( 'gform-duplicateprevention' );
  }

}

/**
 * Bootstrap the GravityFormsDuplicatePrevention class
 * @global $gform_duplicateprevention
 * @return void
 */
function gform_duplicateprevention_init() {
  global $gform_duplicateprevention;
  $gform_duplicateprevention = new GravityFormsDuplicatePrevention;
  return;
}
add_action( 'init', 'gform_duplicateprevention_init' );
