<?php
/**
 * Builds out customizer options
 *
 * @package 	Customizer_Library
 * @author		Devin Price
 */

if ( ! function_exists( 'customizer_library_register' ) ) :
/**
 * Configure settings and controls for the theme customizer
 *
 * @since  1.0.0.
 *
 * @param  object $wp_customize The global customizer object.
 * @return void
 */
function customizer_library_register( $wp_customize ) {

	$customizer_library = Customizer_Library::Instance();

	$options  = $customizer_library->get_options();

	//* Bail early if we don't have any options.
	if ( empty( $options ) ) {
		return;
	}

	$sections = $options['sections'];

	if ( isset( $sections ) ) {
		foreach( $sections as $section ) {

			if ( !isset( $section['description'] ) ) {
				$section['description'] = false;
			}

			$wp_customize->add_section( $section['id'], $section );
		}
	}

	$loop = 0;

	foreach( $options as $option ) {

		if ( isset( $option['type'] ) ) {

			$loop++;

			// Default for setting
			if ( isset( $option['default'] ) ) {
				$default = array( 'default' => $option['default'] );
			}

			// Priority for control
			if ( ! isset( $option['priority'] ) ) {
				$option['priority'] = $loop;
			}

			$wp_customize->add_setting( $option['id'], $default );

			switch ( $option['type'] ) {

				case 'select':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_choices';
					}

					$wp_customize->add_control(
						$option['id'], $option
					);

				break;

				/**
				 * @todo combine with 'select'
				 */
				case 'radio':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_choices';
					}

					$wp_customize->add_control(
						$option['id'], $option
					);

				break;

				case 'checkbox':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_checkbox';
					}

					$wp_customize->add_control(
						$option['id'], $option
					);

				break;

				case 'color':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'sanitize_hex_color';
					}

					$wp_customize->add_control(
						new WP_Customize_Color_Control(
							$wp_customize, $option['id'], $option
						)
					);

				break;

				case 'image':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_file_url';
					}

					$wp_customize->add_control(
						new WP_Customize_Image_Control(
							$wp_customize,
							$option['id'], array(
								'label'             => $option['label'],
								'section'           => $option['section'],
								'sanitize_callback' => $option['sanitize_callback'],
								'priority'          => $option['priority']
							)
						)
					);

				break;

				case 'upload':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_file_url';
					}

					$wp_customize->add_control(
						new WP_Customize_Upload_Control(
							$wp_customize,
							$option['id'], array(
								'label'             => $option['label'],
								'section'           => $option['section'],
								'sanitize_callback' => $option['sanitize_callback'],
								'priority'          => $option['priority']
							)
						)
					);

				break;

				case 'textarea':

					if ( ! isset( $option['sanitize_callback'] ) ) {
						$option['sanitize_callback'] = 'customizer_library_sanitize_text';
					}

					// Custom control required before WordPress 4.0
					if ( version_compare( $GLOBALS['wp_version'], '3.9.2', '<=' ) ) :

						$wp_customize->add_control(
							new Customizer_Library_Textarea(
								$wp_customize, $option['id'], $option
							)
						);

					else :

						$wp_customize->add_control( 'setting_id', array(
							$wp_customize->add_control(
								$option['id'], $option
							)
						) );

					endif;

				break;

			}
		}
	}
}

endif;

add_action( 'customize_register', 'customizer_library_register', 100 );
