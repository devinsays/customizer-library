<?php
/**
 * Custom control for arbitarty text, extend the WP customizer
 */

if ( ! class_exists( 'WP_Customize_Control' ) ) {
    return NULL;
}

class Customizer_Library_Help_Text extends WP_Customize_Control {

    /**
     * Render the control's content.
     */
    public function render_content() {

        echo '<span class="customize-control-title help-text-title">' . esc_html( $this->label ) . '</span>';
        echo '<p class="description help-text-description">' . $this->description . '</p>';
        echo '<hr />';

    }

}