<?php defined('ABSPATH') || exit;

final class JK_WP_ELEMENTOR_NATIVE_PARALLAX
{

    public function __construct()
    {

        add_action('elementor/element/section/section_layout/after_section_end', array($this, 'register_controls'), 10);

        add_action('elementor/frontend/section/after_render', array($this, 'after_render'), 10);

    }

    private static $_instance = null;

    public static function instance()
    {

        if (is_null(self::$_instance)) :

            self::$_instance = new self();

        endif;

        return self::$_instance;

    }

    public function register_controls($element)
    {

        $element->start_controls_section('parallax_section',
            [
                'label' => __('Parallax', '@@textdomain'),
                'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
            ]
        );

        $element->add_control('parallax_switcher',
            [
                'label' => __('Enable Parallax', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
            ]
        );

        $element->add_control('parallax_type',
            [
                'label' => __('Type', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'scroll' => __('Scroll', '@@textdomain'),
                    'scroll-opacity' => __('Scroll with Fade', '@@textdomain'),
                    'opacity' => __('Fade', '@@textdomain'),
                    'scale' => __('Zoom', '@@textdomain'),
                    'scale-opacity' => __('Zoom with Fade', '@@textdomain'),
                    'multi' => __('Multi-Layered', '@@textdomain')
                ],
                'label_block' => 'true',
                'condition' => [
                    'parallax_switcher' => 'yes'
                ]
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('parallax_layer_image',
            [
                'label' => __('Choose Image', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'label_block' => true
            ]
        );

        $repeater->add_control('parallax_layer_mouse',
            [
                'label' => esc_html__('Hover Interaction', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes'
            ]
        );

        $repeater->add_control('parallax_layer_rate',
            [
                'label' => esc_html__('Intensity', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => -10,
                'min' => -20,
                'max' => 20,
                'step' => 1,
                'condition' => [
                    'parallax_layer_mouse' => 'yes'
                ]
            ]
        );

        $repeater->add_control('parallax_layer_hor_pos',
            [
                'label' => esc_html__('Horizontal Position', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 0,
                'max' => 100
            ]
        );

        $repeater->add_control('parallax_layer_ver_pos',
            [
                'label' => esc_html__('Vertical Position', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 0,
                'max' => 100,
            ]
        );

        $repeater->add_control('parallax_layer_back_size',
            [
                'label' => esc_html__('Image Size', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => [
                    'auto' => esc_html__('Auto', '@@textdomain'),
                    'cover' => esc_html__('Cover', '@@textdomain'),
                    'contain' => esc_html__('Contain', '@@textdomain'),
                ],
            ]
        );

        $repeater->add_control('parallax_layer_z_index',
            [
                'label' => __('z-index', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1
            ]
        );

        $element->add_control('parallax_speed',
            [
                'label' => __('Parallax Speed', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => -1,
                'max' => 2,
                'step' => 0.1,
                'default' => 1.3,
                'condition' => [
                    'parallax_type!' => ['automove', 'multi'],
                    'parallax_switcher' => 'yes'
                ],
            ]
        );

        $element->add_control('parallax_android_support',
            [
                'label' => esc_html__('Parallax on Android Devices', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'condition' => [
                    'parallax_type!' => ['automove', 'multi'],
                    'parallax_switcher' => 'yes'
                ],
            ]
        );

        $element->add_control('parallax_ios_support',
            [
                'label' => esc_html__('Parallax on iOS Devices', '@@textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'condition' => [
                    'parallax_type!' => ['automove', 'multi'],
                    'parallax_switcher' => 'yes'
                ],
            ]
        );

        $element->add_control('parallax_layers_list',
            [
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => array_values($repeater->get_controls()),
                'condition' => [
                    'parallax_switcher' => 'yes',
                    'parallax_type' => 'multi'
                ]
            ]
        );

        $element->end_controls_section();

    }

    public function after_render($element)
    {

        $data = $element->get_data();

        $type = $data['elType'];

        $settings = $data['settings'];

        $parallax = isset($settings['parallax_type']) ? $settings['parallax_type'] : '';

        if ('section' === $type && isset($parallax) && '' !== $parallax && 'yes' === $element->get_settings('parallax_switcher')) :

            $android = (isset($settings['parallax_android_support']) && $settings['parallax_android_support'] == 'yes') ? 0 : 1;

            $ios = (isset($settings['parallax_ios_support']) && $settings['parallax_ios_support'] == 'yes') ? 0 : 1;

            $speed = !empty($settings['parallax_speed']) ? $settings['parallax_speed'] : 0.5;

            ?>

            <script>

                jQuery(document).ready(function ($) {

                    "use strict";

                    let target = $('.elementor-element-<?php echo esc_js($element->get_id()); ?>');

                    <?php if( 'automove' != $parallax && 'multi' != $parallax ) : ?>

                    let ParallaxElement = {

                        init: function () {
                            elementorFrontend.hooks.addAction('frontend/element_ready/global', ParallaxElement.initWidget);
                        },

                        responsiveParallax: function () {
                            let android = <?php echo esc_js($android); ?>,
                                ios = <?php echo esc_js($ios); ?>;
                            switch (true || 1) {
                                case android && ios:
                                    return /iPad|iPhone|iPod|Android/;
                                    break;
                                case android && !ios:
                                    return /Android/;
                                    break;
                                case !android && ios:
                                    return /iPad|iPhone|iPod/;
                                    break;
                                case (!android && !ios):
                                    return null;
                            }
                        },

                        initWidget: function ($scope) {
                            target.jarallax({
                                type: '<?php echo esc_js($parallax); ?>',
                                speed: <?php echo esc_js($speed); ?>,
                                keepImg: true,
                                disableParallax: ParallaxElement.responsiveParallax(),
                            });
                        }

                    };

                    $(window).on('elementor/frontend/init', ParallaxElement.init);

                    <?php endif; ?>

                });

            </script>

        <?php endif; ?>

        <?php

    }

}

JK_WP_ELEMENTOR_NATIVE_PARALLAX::instance();
