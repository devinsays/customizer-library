<?php
/**
 * Customizer Library
 *
 * @package        Customizer_Library
 * @author         Devin Price, The Theme Foundry
 * @license        GPL-2.0+
 * @version        1.3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Continue if the Customizer_Library isn't already in use.
if ( ! class_exists( 'Customizer_Library' ) ) :

	// Helper functions to output the customizer controls.
	require plugin_dir_path( __FILE__ ) . 'extensions/interface.php';

	// Helper functions for customizer sanitization.
	require plugin_dir_path( __FILE__ ) . 'extensions/sanitization.php';

	// Helper functions to build the inline CSS.
	require plugin_dir_path( __FILE__ ) . 'extensions/style-builder.php';

	// Helper functions for fonts.
	require plugin_dir_path( __FILE__ ) . 'extensions/fonts.php';

	// Utility functions for the customizer.
	require plugin_dir_path( __FILE__ ) . 'extensions/utilities.php';

	// Customizer preview functions.
	require plugin_dir_path( __FILE__ ) . 'extensions/preview.php';

	// Textarea control
	if ( version_compare( $GLOBALS['wp_version'], '4.0', '<' ) ) {
		require plugin_dir_path( __FILE__ ) . 'custom-controls/textarea.php';
	}

	// Arbitrary content controls
	require plugin_dir_path( __FILE__ ) . 'custom-controls/content.php';

	/**
	 * Class wrapper with useful methods for interacting with the theme customizer.
	 */
	class Customizer_Library {

		/**
		 * The one instance of Customizer_Library.
		 *
		 * @since 1.0.0.
		 *
		 * @var   Customizer_Library_Styles    The one instance for the singleton.
		 */
		private static $instance;

		/**
		 * The array for storing $options.
		 *
		 * @since 1.0.0.
		 *
		 * @var   array    Holds the options array.
		 */

		public $options = array();

		/**
		 * Instantiate or return the one Customizer_Library instance.
		 *
		 * @since  1.0.0.
		 *
		 * @return Customizer_Library
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function add_options( $options = array() ) {
			$this->options = array_merge( $options, $this->options );
		}

		public function get_options() {
			return $this->options;
		}

	}

endif;