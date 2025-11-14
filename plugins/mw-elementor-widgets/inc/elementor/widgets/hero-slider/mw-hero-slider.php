<?php

namespace MWEW\Inc\Elementor\Widgets\Hero_Slider;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Repeater;
use \Elementor\Utils;
use \Elementor\Group_Control_Border;

class MW_Hero_Slider extends Widget_Base
{
	public function get_name(): string {
		return 'book_hero_bg_slider';
	}

	public function get_title(): string {
		return esc_html__('Hero Background Slider', 'mwew');
	}

	public function get_icon(): string {
		return 'eicon-background';
	}

	public function get_categories(): array {
		return ['basic'];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_images',
			[
				'label' => __('Background Images', 'mwew'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => __('Desktop Image', 'mwew'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'mobile_image',
			[
					'label' => __('Mobile Image', 'mwew'),
					'type' => Controls_Manager::MEDIA,
					'default' => [
							'url' => Utils::get_placeholder_image_src(),
					],
			]
		);

		$this->add_control(
			'slides',
			[
				'label' => __('Slides', 'mwew'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => 'Slider Image',
			]
		);

		$this->add_control(
			'line_1_text',
			[
				'label' => __('Text 1', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Discover the extraordinary', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_2_text',
			[
				'label' => __('Text 2', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Side of', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_3_text',
			[
				'label' => __('Text 3', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Switzerland', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_4_text',
			[
				'label' => __('Text 4', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('with', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_5_text',
			[
				'label' =>__('Text 5', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Book a Bubble', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_6_text',
			[
				'label' => __('Small Info Text 1', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Romantically', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);

		$this->add_control(
			'line_7_text',
			[
				'label' => __('Small Info Text 2', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Unique', 'mwew'),
				'dynamic' => ['active' => true],
			]
		);


		$this->add_control(
			'country_image',
			[
				'label'       => __( 'Country Flag', 'mwew' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => [
				'url' => Utils::get_placeholder_image_src(),
				],
			]
		);


		$this->add_control(
			'secondary_image',
			[
				'label'       => __( 'Secondary Image', 'mwew' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => [
				'url' => Utils::get_placeholder_image_src(),
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'labels_section',
			[
				'label' => esc_html__( 'Labels & Placeholders', 'mwew' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		// Check-in label and placeholder
		$this->add_control(
			'check_in_label',
			[
				'label' => esc_html__( 'Check-in Label', 'mwew' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Check in date', 'mwew' ),
			]
		);

		$this->add_control(
			'check_in_placeholder',
			[
				'label' => esc_html__( 'Check-in Placeholder', 'mwew' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Arrival', 'mwew' ),
			]
		);

		// Check-out label and placeholder
		$this->add_control(
			'check_out_label',
			[
				'label' => esc_html__( 'Check-out Label', 'mwew' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Check out date', 'mwew' ),
			]
		);

		$this->add_control(
			'check_out_placeholder',
			[
				'label' => esc_html__( 'Check-out Placeholder', 'mwew' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Departure', 'mwew' ),
			]
		);

		// Search button text
		$this->add_control(
			'search_button_text',
			[
				'label' => esc_html__( 'Search Button Text', 'mwew' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'View available bubble tents', 'mwew' ),
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_left_style',
			[
				'label' => __('Left Content Style', 'mwew'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'left_text_color',
			[
				'label' => __('Text Color', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .slider-left' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'left_typography',
				'label' => __('Typography', 'mwew'),
				'selector' => '{{WRAPPER}} .slider-left',
			]
		);

		$this->add_responsive_control(
			'left_text_align',
			[
				'label' => __('Text Alignment', 'mwew'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => ['title' => __('Left'), 'icon' => 'eicon-text-align-left'],
					'center' => ['title' => __('Center'), 'icon' => 'eicon-text-align-center'],
					'right' => ['title' => __('Right'), 'icon' => 'eicon-text-align-right'],
				],
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .slider-left' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'left_padding',
			[
				'label' => __('Padding'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .slider-left' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_mobile_buttons',
			[
				'label' => __('Mobile Buttons', 'mwew'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'button_icon',
			[
				'label' => __('Button Icon', 'mwew'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => '',
					'library' => 'solid',
				],
			]
		);


		$repeater->add_control(
			'button_text',
			[
				'label' => __('Button Text', 'mwew'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Click Me', 'mwew'),
				'dynamic' => ['active' => true]
			]
		);

		$repeater->add_control(
			'button_link',
			[
				'label' => __('Button Link', 'mwew'),
				'type' => Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'default' => [
					'url' => '#0',
					'is_external' => false,
					'nofollow' => false,
				],
			]
		);

		$this->add_control(
			'mobile_buttons',
			[
				'label' => __('Buttons', 'mwew'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'button_text' => __('Standorte entdecken', 'mwew'),
						'button_link' => ['url' => '#0']
					],
					[
						'button_text' => __('Gutschein bestellen', 'mwew'),
						'button_link' => ['url' => '#0']
					]
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'style_mobile_buttons',
			[
				'label' => __('Mobile Button Style', 'mwew'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// === Tabs: Normal and Hover ===
		$this->start_controls_tabs('tabs_mobile_button_style');

		// Normal Tab
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __('Normal', 'mwew'),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __('Text Color', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a, {{WRAPPER}} .book-hero-bg-slider .mobile-btn a svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => __('Background Color', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'default' => '#3D6B50',
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .book-hero-bg-slider .mobile-btn a',
			]
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __('Hover', 'mwew'),
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => __('Text Color (Hover)', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label' => __('Background Color (Hover)', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_hover_border',
				'selector' => '{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Shared Typography and Spacing
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .book-hero-bg-slider .mobile-btn a',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => __('Padding', 'mwew'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => __('Border Radius', 'mwew'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// === First Button Specific Style ===
		$this->add_control(
			'first_button_style_heading',
			[
				'label' => __('First Button Override', 'mwew'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'first_button_text_color',
			[
				'label' => __('First Button Text Color', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:nth-child(1)' => 'color: {{VALUE}};',
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:nth-child(1) svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'first_button_bg_color',
			[
				'label' => __('First Button Background', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:nth-child(1)' => 'background-color: {{VALUE}};',
				],
			]
		);

		// === Other Buttons Specific Style ===
		$this->add_control(
			'rest_button_style_heading',
			[
				'label' => __('Other Buttons Override', 'mwew'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'rest_button_text_color',
			[
				'label' => __('Text Color (Others)', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:not(:nth-child(1))' => 'color: {{VALUE}};',
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:not(:nth-child(1)) svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'rest_button_bg_color',
			[
				'label' => __('Background (Others)', 'mwew'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .book-hero-bg-slider .mobile-btn a:not(:nth-child(1))' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();



	}

	public function get_style_depends() {
	    return ['mw-hero-slider'];
	}

	public function get_script_depends() {
	    return ['mw-hero-slider'];
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		if (empty($settings['slides'])) return;

		$image_urls = array_map(fn($slide) => esc_url($slide['image']['url']), $settings['slides']);
		$mobile_image_urls = array_map(fn($slide) => esc_url($slide['mobile_image']['url']), $settings['slides']);
		$data_src = htmlspecialchars(json_encode($image_urls), ENT_QUOTES, 'UTF-8');
		$mobile_data_src = htmlspecialchars(json_encode($mobile_image_urls), ENT_QUOTES, 'UTF-8');
		$country_img_url = esc_url( $settings['country_image']['url'] );
		$secondary_image_url = esc_url( $settings['secondary_image']['url'] );
		

		$first_image = $settings['slides'][0]['image']['url']; // Keep this raw

		$unique_id = 'hero-bg-slider-' . $this->get_id();


		$calendar_icon = esc_url(MWEW_PATH_URL . '/assets/images/calendar.png');
		$search_icon = esc_url(MWEW_PATH_URL . '/assets/images/search_icon.png');

		if(!empty($country_img_url))
			$map_image = '<img width="40" decoding="async" src="' . esc_url($country_img_url) . '" alt="' . esc_attr__('Country image', 'mwew') . '">';
		else 
			$map_image = '';
		
		if(!empty($secondary_image_url))
			$secondary_image = '<img width="15" decoding="async" src="' . esc_url($secondary_image_url) . '" alt="' . esc_attr__('Seperator', 'mwew') . '">';
		else 
			$secondary_image = '';

		echo '<div id="' . esc_attr($unique_id) . '" class="book-hero-bg-slider" width="auto" height="auto"  style="background-image: url(' . esc_url($first_image) . ');" data-src="' . $data_src . '" data-mobile-src="'. $mobile_data_src .'">
			<div class="slider-inner">
				<div class="slider-left">
					<div class="left-inner">
						<span class="uppercase main-title">' . $settings['line_1_text'] . '</span>
						<span class="uppercase">' . esc_html($settings['line_2_text']) . ' <span class="font-aclonica-logo"><strong>' . esc_html($settings['line_3_text']) . '</strong>'. $map_image .'</span></span>
						<span>' . esc_html($settings['line_4_text']) . ' <span class="font-aclonica-logo"><strong>' . esc_html($settings['line_5_text']) . '</strong></span></span>
						<span class="small-info">' . esc_html($settings['line_6_text']) . ' ' .$secondary_image.' ' . esc_html($settings['line_7_text']) . '</span>
					</div>
					<div class="slider-dots"></div>
				</div>
				<div class="slider-right">
					<div class="search-container">
						<div class="search-row">
							<div class="search-field">
								<span class="label">' . esc_html( $settings['check_in_label'] ) . '</span>
								<div class="input-wrapper" id="checkin-wrapper">
									<input type="text"
										class="date-input checkin-date"
										id="mwew-checkin-date"
										placeholder="' . esc_attr( $settings['check_in_placeholder'] ) . '"
										readonly>
									<img src="' . esc_url( $calendar_icon ) . '" alt="' . esc_attr__( 'calendar icon', 'mwew' ) . '" class="icon">
								</div>
							</div>

							<div class="search-field">
								<span class="label">' . esc_html( $settings['check_out_label'] ) . '</span>
								<div class="input-wrapper" id="checkout-wrapper">
									<input type="text"
										class="date-input checkout-date"
										id="mwew-checkout-date"
										placeholder="' . esc_attr( $settings['check_out_placeholder'] ) . '"
										readonly>
									<img src="' . esc_url( $calendar_icon ) . '" alt="' . esc_attr__( 'calendar icon', 'mwew' ) . '" class="icon">
								</div>
							</div>
						</div>

						<div class="search-button" data-home-url="' . esc_url( home_url() ) . '">
							<img src="' . esc_url( $search_icon ) . '" alt="' . esc_attr__( 'search icon', 'mwew' ) . '" class="icon">
							<span>' . esc_html( $settings['search_button_text'] ) . '</span>
						</div>
					</div>
					
				</div>
			</div>';

			echo '<div class="mobile-btn">';
				if ( ! empty( $settings['mobile_buttons'] ) ) {
					foreach ( $settings['mobile_buttons'] as $button ) {
						$link = ! empty( $button['button_link']['url'] ) ? $button['button_link']['url'] : '#';
						$is_external = ! empty( $button['button_link']['is_external'] ) ? ' target="_blank"' : '';
						$nofollow = ! empty( $button['button_link']['nofollow'] ) ? ' rel="nofollow"' : '';

						echo '<a href="' . esc_url( $link ) . '"' . $is_external . $nofollow . '>';
						if ( ! empty( $button['button_icon']['value'] ) ) {
							\Elementor\Icons_Manager::render_icon( $button['button_icon'], [ 'aria-hidden' => 'true' ] );
						}
						echo esc_html( $button['button_text'] );
						echo '</a>';
					}
				}
			echo '</div>';

		echo '</div>';

	}

	
}
