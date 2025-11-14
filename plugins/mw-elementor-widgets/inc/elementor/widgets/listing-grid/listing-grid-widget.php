<?php
namespace MWEW\Inc\Elementor\Widgets\Listing_Grid;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
class Listing_Grid_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'listing_grid_widget';
    }

    public function get_title() {
        return __('Listing Grid', 'mwew');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        // === Content Controls ===
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'mwew'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title_text',
            [
                'label' => __('Section Title', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Bubble Tent With Sauna', 'mwew'),
            ]
        );

        $this->add_control(
            'max_posts',
            [
                'label' => __('Maximum Listings', 'mwew'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'step' => 1,
            ]
        );

        $this->add_control(
            'max_feature',
            [
                'label' => __('Maximum Features', 'mwew'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'step' => 1,
            ]
        );

        $this->add_control(
            'desktop_columns',
            [
                'label' => __('Grid Columns', 'mwew'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns',
                ],
            ]
        );

        $this->add_control(
            'tab_columns',
            [
                'label' => __('Grid Columns', 'mwew'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns',
                ],
            ]
        );

        $this->add_control(
            'mobile_columns',
            [
                'label' => __('Grid Columns', 'mwew'),
                'type' => Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns',
                ],
            ]
        );

        $this->end_controls_section();

        // === Style: Section Title ===
        $this->start_controls_section(
            'style_section_title',
            [
                'label' => __('Section Title Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_title_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .listing-grid-title, {{WRAPPER}} .mw-loading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'selector' => '{{WRAPPER}} .listing-grid-title',
            ]
        );

        $this->add_responsive_control(
            'section_title_margin',
            [
                'label' => __('Margin', 'mwew'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .listing-grid-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // === Style: Grid Area ===
        $this->start_controls_section(
            'style_grid_section',
            [
                'label' => __('Grid Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'grid_gap',
            [
                'label' => __('Grid Gap', 'mwew'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                ],
                'default' => ['size' => 12],
                'selectors' => [
                    '{{WRAPPER}} .listing-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'grid_background',
                'selector' => '{{WRAPPER}} .listing-grid-wrapper',
            ]
        );

        $this->add_responsive_control(
            'grid_padding',
            [
                'label' => __('Grid Padding', 'mwew'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .listing-grid-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __( 'Image Height', 'mwew' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();

        // === Style: Tag Buttons ===
        $this->start_controls_section(
            'style_tag_buttons',
            [
                'label' => __('Tag Button Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tag_button_style_tabs');

        // Normal
        $this->start_controls_tab(
            'tag_button_normal',
            [
                'label' => __('Normal', 'mwew'),
            ]
        );

        $this->add_control(
            'tag_normal_text_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tag_normal_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tag_normal_border',
                'selector' => '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag',
            ]
        );

        $this->end_controls_tab();

        // Hover
        $this->start_controls_tab(
            'tag_button_hover',
            [
                'label' => __('Hover', 'mwew'),
            ]
        );

        $this->add_control(
            'tag_hover_text_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tag_hover_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tag_hover_border',
                'selector' => '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag:hover',
            ]
        );

        $this->end_controls_tab();

        // Active
        $this->start_controls_tab(
            'tag_button_active',
            [
                'label' => __('Active', 'mwew'),
            ]
        );

        $this->add_control(
            'tag_active_text_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tag_active_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tag_active_border',
                'selector' => '{{WRAPPER}} .mwew-listing-grid-wrapper .listing-tag.active',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();

    }

    public function get_style_depends() {
        return [ 'mw-listing-grid' ];
    }

    public function get_script_depends() {
        return [ 'mw-listing-grid' ];
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $title = $settings['title_text'];
        $max_posts = $settings['max_posts'] ?? 6;
        $max_feature = $settings['max_feature'] ?? 6;
        $desktop_columns = $settings['desktop_columns'] ?? '3';
        $tab_columns = $settings['tab_columns'] ?? '3';
        $mobile_columns = $settings['mobile_columns'] ?? '2';

        ?>

        <div class="mwew-listing-grid-wrapper">
            <div class="listing-grid-title"><?php echo esc_html($title); ?></div>

            <div class="listing-tags">
                <?php
                    $terms = get_terms([
                        'taxonomy' => 'listing_location',
                        'hide_empty' => false,
                        'number' => $max_feature,
                    ]);

                    if (!empty($terms) && !is_wp_error($terms)) {
                        $first = true;
                        foreach ($terms as $term) {
                            $active_class = $first ? 'active' : '';
                            echo '<span class="listing-tag ' . $active_class . '" data-slug="' . esc_attr($term->slug) . '">' . esc_html(ucfirst($term->name)) . '</span>';
                            $first = false;
                        }
                        echo '<span class="listing-tag" data-slug="all">'.__('All', 'mwew').'</span>';
                    } else {
                        echo '<span>'.esc_html__('No feature found', 'mwew').'</span>';
                    }
                ?>
            </div>

            <div class="listing-grid" data-loading="<?php echo __("Loading Listing", "mwew"); ?>" data-max="<?php echo esc_attr($max_posts); ?>" style="--desktop-grid:<?php echo esc_attr($desktop_columns); ?>; --tab-grid: <?php echo esc_attr($tab_columns); ?>; --mobile-grid: <?php echo esc_attr($mobile_columns); ?>;">
                <p class="mw-loading"><?php echo esc_html__('Select a tag to view listings.', 'mwew'); ?></p>
            </div>
        </div>

        <?php
    }

}
