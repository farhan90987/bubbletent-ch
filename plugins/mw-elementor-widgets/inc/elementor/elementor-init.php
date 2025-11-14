<?php
namespace MWEW\Inc\Elementor;

use MWEW\Inc\Elementor\Widgets\Listing_Grid\Listing_Grid_Widget;
use MWEW\Inc\Elementor\Widgets\Listing_Grid\Listing_Grid_Action;
use MWEW\Inc\Elementor\Widgets\Area_Map\Country_Map;
use MWEW\Inc\Elementor\Widgets\Loop_Carousel\Template_Loop_Carousel;
use MWEW\Inc\Elementor\Widgets\Hero_Slider\MW_Hero_Slider;
use MWEW\Inc\Logger\Logger;
class Elementor_Init{
    public function __construct(){
        
        add_action( 'elementor/widgets/register', [$this, 'register_widget'] );
        
        add_filter( 'wpml_elementor_widgets_to_translate', [$this, 'widget_translate'] );

        new Listing_Grid_Action();
    }

    public function register_widget( $widgets_manager ) {
        $widgets_manager->register( new Template_Loop_Carousel() );
        $widgets_manager->register( new Country_Map() );
        $widgets_manager->register( new MW_Hero_Slider() );
        $widgets_manager->register( new Listing_Grid_Widget() );
    }


    public function widget_translate( $widgets_to_translate ) {
        Logger::debug("in translate");

        $widgets_to_translate['book_hero_bg_slider'] = [
            'fields' => [
                [
                    'field'       => 'line_1_text',
                    'type'        => 'Text 1',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'line_2_text',
                    'type'        => 'Text 2',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'line_3_text',
                    'type'        => 'Text 3',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'line_4_text',
                    'type'        => 'Text 4',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'line_5_text',
                    'type'        => 'Text 5',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'line_6_text',
                    'type'        => 'Text 6',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'check_in_label',
                    'type'        => 'Check-in Label',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'check_in_placeholder',
                    'type'        => 'Check-in Placeholder',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'check_out_label',
                    'type'        => 'Check-out Label',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'check_out_placeholder',
                    'type'        => 'Check-out Placeholder',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'search_button_text',
                    'type'        => 'Search Button Text',
                    'editor_type' => 'LINE',
                ],
            ],
            'fields_in_item' => [
                'slides' => [
                    [
                        'field'       => 'image_alt',
                        'type'        => 'Slide Image Alt Text',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'mobile_image_alt',
                        'type'        => 'Slide Mobile Image Alt Text',
                        'editor_type' => 'LINE',
                    ],
                ],
                'mobile_buttons' => [
                    [
                        'field'       => 'button_text',
                        'type'        => 'Mobile Button Text',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'button_link.url',
                        'type'        => 'Mobile Button Link URL',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'button_icon_label',
                        'type'        => 'Mobile Button Icon Label',
                        'editor_type' => 'LINE',
                    ],
                ],
            ],
        ];

        $widgets_to_translate['mw_country_map'] = [
            'fields_in_item' => [
                'tabs_list' => [
                    [
                        'field'       => 'tab_title',
                        'type'        => 'Map Tab Title',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'title',
                        'type'        => 'Map Title',
                        'editor_type' => 'LINE',
                    ],
                    [
                        'field'       => 'description',
                        'type'        => 'Map Description',
                        'editor_type' => 'AREA',
                    ],
                ],
            ],
        ];

        $widgets_to_translate['mw_loop_carousel'] = [
            'fields' => [
                [
                    'field'       => 'section_title',
                    'type'        => 'Section Title',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'button_text',
                    'type'        => 'Button Text',
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'button_link.url',
                    'type'        => 'Button Link URL',
                    'editor_type' => 'LINE',
                ],
            ],
        ];


        $widgets_to_translate['listing_grid_widget'] = [
            'fields' => [
                [
                    'field'       => 'title_text',
                    'type'        => 'Section Title',
                    'editor_type' => 'LINE',
                ],
            ],
        ];

        return $widgets_to_translate;
    }


}





