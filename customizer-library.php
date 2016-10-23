<?php
    namespace Upages_Objects;

    /**
     * Class Customizer_Library
     * @package Upages_Objects
     */
    class Customizer_Library
    {
        public $wp_customize;
        public $option = [];

        /**
         * Customizer_Library constructor.
         *
         * @param array|null $options
         */
        public function __construct(array $options = null)
        {
            add_action('customize_register', [$this, 'setCustomize']);
            $this->setOption($options);
        }

        /**
         * @param array $option
         */
        public function setOption($option)
        {
            $this->option = $option;
        }

        /**
         * @param $wp_customize
         */
        public function setCustomize($wp_customize)
        {
            $options = $this->getOptions();
            if (isset($options['sections'])) {
                $this->setSections($options['sections'], $wp_customize);
            }
            if (isset($options['panels'])) {
                $this->setPanels($options['panels'], $wp_customize);
            }
            $loop = 0;
            foreach ($options as $option) {
                if ( ! isset($option['description'])) {
                    $option['description'] = '';
                }
                if (isset($option['type'])) {
                    ++$loop;
                    if ( ! isset($option['sanitize_callback'])) {
                        $option['sanitize_callback'] = $this->getSanitization($option['type']);
                    }
                    if ( ! isset($option['active_callback'])) {
                        $option['active_callback'] = '';
                    }
                    $this->setSetting($option, $wp_customize);
                    if ( ! isset($option['priority'])) {
                        $option['priority'] = $loop;
                    }
                    switch ($option['type']) {
                        case 'text':
                        case 'url':
                        case 'select':
                        case 'radio':
                        case 'checkbox':
                        case 'dropdown-pages':
                            $wp_customize->add_control($option['id'], $option);
                            break;
                        case 'color':
                            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, $option['id'],
                                $option));
                            break;
                        case 'image':
                            $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, $option['id'], [
                                'label'             => $option['label'],
                                'section'           => $option['section'],
                                'sanitize_callback' => $option['sanitize_callback'],
                                'priority'          => $option['priority'],
                                'active_callback'   => $option['active_callback'],
                                'description'       => $option['description']
                            ]));
                            break;
                        case 'upload':
                            $wp_customize->add_control(new \WP_Customize_Upload_Control($wp_customize, $option['id'], [
                                'label'             => $option['label'],
                                'section'           => $option['section'],
                                'sanitize_callback' => $option['sanitize_callback'],
                                'priority'          => $option['priority'],
                                'active_callback'   => $option['active_callback'],
                                'description'       => $option['description']
                            ]));
                            break;
                        case 'textarea':
                            $wp_customize->add_control($option['id'], $option);
                            break;

                    }
                }
            }
        }

        /**
         * @param $sections
         * @param $wp_customize
         */
        public function setSections(array $sections = null, $wp_customize)
        {
            foreach ($sections as $section) {
                if ( ! isset($section['description'])) {
                    $section['description'] = false;
                }
                $wp_customize->add_section($section['id'], $section);
            }
        }

        /**
         * @param array|null $panels
         * @param            $wp_customize
         */
        public function setPanels(array $panels = null, $wp_customize)
        {
            foreach ($panels as $panel) {
                if ( ! isset($panel['description'])) {
                    $panel['description'] = false;
                }
                $wp_customize->add_panel($panel['id'], $panel);
            }
        }

        /**
         * @param $type
         *
         * @return array|bool|string
         */
        public function getSanitization($type)
        {
            switch ($this) {
                case 'select' === $type || 'radio' === $type:
                    return [$this, 'sanitizeChoices'];
                    break;
                case 'checkbox' === $type:
                    return [$this, 'sanitizeCheckbox'];
                    break;
                case 'color' === $type:
                    return [$this, 'sanitizeHexColor'];
                    break;
                case 'upload' === $type || 'image' === $type:
                    return [$this, 'sanitizeFileUrl'];
                    break;
                case 'text' === $type || 'textarea' === $type:
                    return [$this, 'sanitizeText'];
                    break;
                case 'url' === $type:
                    return 'esc_url';
                    break;
                case 'dropdown-pages' === $type:
                    return 'absint';
                    break;
                default:
                    return false;
            }
        }

        /**
         * @param array|null $option
         * @param            $wp_customize
         */
        public function setSetting(array $option = null, $wp_customize)
        {
            $settings_default = [
                'default'              => null,
                'option_type'          => 'theme_mod',
                'capability'           => 'edit_theme_options',
                'theme_supports'       => null,
                'transport'            => null,
                'sanitize_callback'    => 'wp_kses_post',
                'sanitize_js_callback' => null
            ];
            $settings         = array_merge($settings_default, $option);
            $wp_customize->add_setting($option['id'], [
                'default'              => $settings['default'],
                'type'                 => $settings['option_type'],
                'capability'           => $settings['capability'],
                'theme_supports'       => $settings['theme_supports'],
                'transport'            => $settings['transport'],
                'sanitize_callback'    => $settings['sanitize_callback'],
                'sanitize_js_callback' => $settings['sanitize_js_callback']
            ]);
        }

        /**
         * @param $value
         * @param $setting
         *
         * @return mixed
         */
        public function sanitizeChoices($value, $setting)
        {
            if (is_object($setting)) {
                $setting = $setting->id;
            }
            $choices         = $this->getChoices($setting);
            $allowed_choices = array_keys($choices);
            if ( ! in_array($value, $allowed_choices)) {
                $value = $this->getDefault($setting);
            }

            return $value;
        }

        /**
         * @param $setting
         *
         * @return string
         */
        public function getChoices($setting)
        {
            $options = $this->getOptions();

            return $options[$setting]['choices'] ?? '';
        }

        /**
         * @return array
         */
        public function getOptions()
        {
            return $this->option;
        }

        /**
         * @param $setting
         *
         * @return string
         */
        public function getDefault($setting)
        {
            $options = $options = $this->getOptions();

            return $options[$setting]['default'] ?? '';
        }

        /**
         * @param $string
         *
         * @return string
         */
        public function sanitizeText($string)
        {
            global $allowedtags;

            return wp_kses($string, $allowedtags);
        }

        /**
         * @param $value
         *
         * @return int
         */
        public function sanitizeCheckbox($value)
        {
            return $value === 1 ? 1 : 0;
        }

        /**
         * @param $url
         *
         * @return string
         */
        public function sanitizeFileUrl($url)
        {
            $output   = '';
            $filetype = wp_check_filetype($url);
            if ($filetype['ext']) {
                $output = esc_url_raw($url);
            }

            return $output;
        }

        /**
         * @param $color
         *
         * @return string
         */
        public function sanitizeHexColor($color)
        {
            $colors = '';
            if ('' === $color) {
                $colors = '';
            }
            if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
                $colors = $color;
            }

            return $colors;
        }
    }
