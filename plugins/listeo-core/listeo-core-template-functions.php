<?php

/**
 * Template Functions
 *
 * Template functions for listings
 *
 * @author 		Lukasz Girek
 * @version     1.0
 */


/**
 * Add custom body classes
 */
function listeo_core_body_class($classes)
{
	$classes   = (array) $classes;
	$classes[] = sanitize_html_class(wp_get_theme());

	// Check if current page is a combined taxonomy page
	if (class_exists('Listeo_Core_Post_Types') && Listeo_Core_Post_Types::is_combined_taxonomy_page()) {
		$classes[] = 'listeo-combined-taxonomy-page';
		
		// Get the specific terms for more granular classes
		$terms = Listeo_Core_Post_Types::get_combined_taxonomy_terms();
		if ($terms) {
			$top_layout = get_option('pp_listings_top_layout', 'map');
		
			$classes[] = $top_layout . '-archive-listings-layout';
			if (isset($terms['region'])) {
				$classes[] = 'listeo-region-' . sanitize_html_class($terms['region']->slug);
			}
			if (isset($terms['listing_feature'])) {
				$classes[] = 'listeo-feature-' . sanitize_html_class($terms['listing_feature']->slug);
			}
		}
	}

	// Add top layout classes for listing archives
	$is_listing_taxonomy = false;
	if (is_post_type_archive('listing')) {
		$is_listing_taxonomy = true;
	} else {
		// Check if we're on any listing taxonomy page
		$listing_taxonomies = get_object_taxonomies('listing');
		foreach ($listing_taxonomies as $taxonomy) {
			if (is_tax($taxonomy)) {
				$is_listing_taxonomy = true;
				break;
			}
		}
	}
	
	if ($is_listing_taxonomy) {
		$top_layout = get_term_meta(get_queried_object_id(), 'listeo_taxonomy_top_layout', true);
		
		if (empty($top_layout)) {
			$top_layout = get_option('pp_listings_top_layout', 'map');
		}
		$classes[] = $top_layout . '-archive-listings-layout';
	}

	return array_unique($classes);
}

add_filter('body_class', 'listeo_core_body_class');


/**
 * Outputs the listing offer type
 *
 * @return void
 */
function the_listing_offer_type($post = null)
{
	$type = get_the_listing_offer_type($post);
	$offers = listeo_core_get_offer_types_flat(true);
	if (array_key_exists($type, $offers)) {
		echo '<span class="tag">' . $offers[$type] . '</span>';
	}
}


function listeo_partition($list, $p)
{
	$listlen = count($list);
	$partlen = floor($listlen / $p);
	$partrem = $listlen % $p;
	$partition = array();
	$mark = 0;
	for ($px = 0; $px < $p; $px++) {
		$incr = ($px < $partrem) ? $partlen + 1 : $partlen;
		$partition[$px] = array_slice($list, $mark, $incr);
		$mark += $incr;
	}
	return $partition;
}
/**
 * Gets the listing offer type
 *
 * @return string
 */
function get_the_listing_offer_type($post = null)
{
	$post     = get_post($post);
	if ($post->post_type !== 'listing') {
		return;
	}
	return apply_filters('the_listing_offer_type', $post->_offer_type, $post);
}


function the_listing_type($post = null)
{
	$type = get_the_listing_type($post);
	$types = listeo_core_get_listing_types(true);
	if (array_key_exists($type, $types)) {
		echo '<span class="listing-type-badge listing-type-badge-' . $type . '">' . $types[$type] . '</span>';
	}
}
/**
 * Gets the listing  type
 *
 * @return string
 */
function get_the_listing_type($post = null)
{
	$post     = get_post($post);
	if ($post->post_type !== 'listing') {
		return;
	}
	return apply_filters('the_listing_type', $post->_listing_type, $post);
}

function listeo_get_reviews_criteria()
{
	$criteria = array(
		'service' => array(
			'label' => esc_html__('Service', 'listeo_core'),
			'tooltip' => esc_html__('Quality of customer service and attitude to work with you', 'listeo_core')
		),
		'value-for-money' => array(
			'label' => esc_html__('Value for Money', 'listeo_core'),
			'tooltip' => esc_html__('Overall experience received for the amount spent', 'listeo_core')
		),
		'location' => array(
			'label' => esc_html__('Location', 'listeo_core'),
			'tooltip' => esc_html__('Visibility, commute or nearby parking spots', 'listeo_core')
		),
		'cleanliness' => array(
			'label' => esc_html__('Cleanliness', 'listeo_core'),
			'tooltip' => esc_html__('The physical condition of the business', 'listeo_core')
		),
	);

	return apply_filters('listeo_reviews_criteria', $criteria);
}

/**
 * Outputs the listing location
 *
 * @return void
 */
function the_listing_address($post = null)
{
	echo get_the_listing_address($post);
}

/**
 * get_the_listing_address function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_the_listing_address($post = null)
{
	$post = get_post($post);
	if ($post->post_type !== 'listing') {
		return;
	}

	$friendly_address = get_post_meta($post->ID, '_friendly_address', true);
	$address = get_post_meta($post->ID, '_address', true);
	$output =  (!empty($friendly_address)) ? $friendly_address : $address;
	$disable_address = get_option('listeo_disable_address');
	if ($disable_address) {
		$output = get_post_meta($post->ID, '_friendly_address', true);
	}
	return apply_filters('the_listing_location', $output, $post);
}


function listeo_output_price($price){
	$currency_abbr = get_option('listeo_currency');
	$currency_postion = get_option('listeo_currency_postion');
	$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
	$price = floatval($price);
	if ($currency_postion == 'before') {
		return $currency_symbol . $price;
	} else {
		return $price . $currency_symbol;
	} 
	
	//$price = number_format($price, 2, '.', '');
	
}

/**
 * Outputs the listing price
 *
 * @return void
 */
function the_listing_price($post = null)
{
	echo get_the_listing_price($post);
}

/**
 * get_the_listing_price function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_the_listing_price($post = null)
{
	return Listeo_Core_Listing::get_listing_price($post);
}


function get_the_listing_price_range($post = null)
{
	return Listeo_Core_Listing::get_listing_price_range($post);
}


function listeo_get_saved_icals($post = null)
{
	return Listeo_Core_iCal::get_saved_icals($post);
}

function listeo_ical_export_url($post_id = null)
{
	return Listeo_Core_iCal::get_ical_export_url($post_id);
}

function listeo_get_ical_events($post_id = null)
{
	// $ical = new Listeo_Core_iCal;
	// return $ical -> get_ical_events( $post_id );
	return Listeo_Core_iCal::get_ical_events($post_id);
}





/**
 * Outputs the listing price per scale
 *
 * @return void
 */
function the_listing_price_per_scale($post = null)
{
	echo get_the_listing_price_per_scale($post);
}

function get_the_listing_price_per_scale($post = null)
{
	return Listeo_Core_Listing::get_listing_price_per_scale($post);
}

function has_listing_location($post = null)
{
	$post = get_post($post);
	if ($post->post_type !== 'listing') {
		return false;
	}

	$address = get_post_meta($post->ID, '_address', true);
	// check if has _friendly_address
	$friendly_address = get_post_meta($post->ID, '_friendly_address', true);
	if (!empty($friendly_address)) {
		$address = $friendly_address;
	}
	return !empty($address);
}

function the_listing_location_link($post = null, $map_link = true)
{

	$address =  get_post_meta($post, '_address', true);
	$friendly_address =  get_post_meta($post, '_friendly_address', true);
	$disable_address = get_option('listeo_disable_address');
	if ($disable_address) {
		echo $friendly_address;
	} else {
		if (empty($friendly_address)) {
			$friendly_address = $address;
		}

		if ($address) {
			if ($map_link) {
				// If linking to google maps, we don't want anything but text here
				echo apply_filters('the_listing_map_link', '<a class="listing-address popup-gmaps" href="' . esc_url('https://maps.google.com/maps?q=' . urlencode(strip_tags($address)) . '') . '"><i class="fa fa-map-marker"></i>' . esc_html(strip_tags($friendly_address)) . '</a>', $address, $post);
			} else {
				echo wp_kses_post($friendly_address);
			}
		} else {
			echo esc_html($friendly_address);
		}
	}
}




function listeo_core_check_if_bookmarked($id)
{
	if ($id) {
		$classObj = new Listeo_Core_Bookmarks;
		return $classObj->check_if_added($id);
	} else {
		return false;
	}
}

function listeo_core_is_featured($id)
{
	$featured = get_post_meta($id, '_featured', true);
	if (!empty($featured)) {
		return true;
	} else {
		return false;
	}
}
function listeo_core_is_verified($id)
{
	$author_id 		= get_post_field('post_author', $id);
	$verified = get_user_meta($author_id, 'listeo_verified_user', true);

	if (empty($verified)) {
		$verified = get_post_meta($id, '_verified', true) == 'on';
	}
	if (!empty($verified)) {
		return true;
	} else {
		return false;
	}
}



function listeo_core_is_instant_booking($id)
{
	$featured = apply_filters('listeo_instant_booking', get_post_meta($id, '_instant_booking', true));
	if (!empty($featured)) {
		return true;
	} else {
		return false;
	}
}

// make listeo_instant_booking always on
//add_filter('listeo_instant_booking', '__return_true');
//add_filter('listeo_allow_overbooking', '__return_true');

/**
 * Gets the listing title for the listing.
 *
 * @since 1.27.0
 * @param int|WP_Post $post (default: null)
 * @return string|bool|null
 */
function listeo_core_get_the_listing_title($post = null)
{
	$post = get_post($post);
	if (!$post || 'listing' !== $post->post_type) {
		return;
	}

	$title = esc_html(get_the_title($post));

	/**
	 * Filter for the listing title.
	 *
	 * @since 1.27.0
	 * @param string      $title Title to be filtered.
	 * @param int|WP_Post $post
	 */
	return apply_filters('listeo_core_the_listing_title', $title, $post);
}

function listeo_core_add_tooltip_to_label($field_args, $field)
{
	// Get default label
	$label = $field->label();
	if ($label && $field->options('tooltip')) {
		$label = substr($label, 0, -9);

		// If label and tooltip exists, add it
		$label .= sprintf(' <i class="tip" data-tip-content="%s"></i></label>', $field->options('tooltip'));
	}

	return $label;
}

/**
 * Overrides the default render field method
 * Allows you to add custom HTML before and after a rendered field
 *
 * @param  array             $field_args Array of field parameters
 * @param  CMB2_Field object $field      Field object
 */
function listeo_core_render_as_col_12($field_args, $field)
{

	// If field is requesting to not be shown on the front-end
	if (!is_admin() && !$field->args('on_front')) {
		return;
	}

	// If field is requesting to be conditionally shown
	if (!$field->should_show()) {
		return;
	}

	$field->peform_param_callback('before_row');

	echo '<div class="col-md-12">';

	// Remove the cmb-row class
	printf('<div class="custom-class %s">', $field->row_classes());

	if (!$field->args('show_names')) {

		// If the field is NOT going to show a label output this
		$field->peform_param_callback('label_cb');
	} else {

		// Otherwise output something different
		if ($field->get_param_callback_result('label_cb', false)) {
			echo $field->peform_param_callback('label_cb');
		}
	}

	$field->peform_param_callback('before');

	// The next two lines are key. This is what actually renders the input field
	$field_type = new CMB2_Types($field);
	$field_type->render();

	$field->peform_param_callback('after');

	echo '</div>'; //cmb-row

	echo '</div>';

	$field->peform_param_callback('after_row');

	// For chaining
	return $field;
}
/**
 * Dispays bootstarp column start
 * @param  string $col integer column width
 */
function listeo_core_render_column($col = '', $name = '', $type = '')
{
	echo '<div class="col-md-' . $col . ' form-field-' . $name . '-container form-field-container-type-' . $type . '">';
}

function listeo_archive_buttons($list_style, $list_top_buttons = null)
{
	$template_loader = new Listeo_Core_Template_Loader;
	$data = array('buttons' => $list_top_buttons);
	$template_loader->set_template_data($data)->get_template_part('archive/top-buttons');
}

function listeo_get_categories_for_slider()
{
	$categories = get_terms(array(
		'taxonomy'   => 'listing_category',
		'hide_empty' => true,
	));

	$choices = array();
	foreach ($categories as $category) {
		$choices[$category->term_id] = $category->name;
	}

	return $choices;
}

/**
 * Get categories grouped by listing types for slider
 * Used for customizer option 'show_listing_types_categories'
 * 
 * @return array Flat array for multicheck field with grouped structure
 */
function listeo_get_categories_grouped_by_listing_types()
{
	$choices = array();
	
	// Get custom listing types and their categories (skip global categories)
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		$types = $custom_types->get_listing_types(true); // active only
		
		if ($types) {
			foreach ($types as $type) {
				// Check if this type registers its own taxonomy
				if ($type->register_taxonomy) {
					$taxonomy_slug = $type->slug . '_category';
					
					// Check if taxonomy exists
					if (taxonomy_exists($taxonomy_slug)) {
						$categories = get_terms(array(
							'taxonomy'   => $taxonomy_slug,
							'hide_empty' => false,
						));
						
						if (!empty($categories) && !is_wp_error($categories)) {
							// Add section header with emoji
							$choices['---' . $type->slug . '---'] = '➡️ ' . sprintf(__('%s Categories', 'listeo_core'), $type->name);
							foreach ($categories as $category) {
								$choices[$category->term_id . '_' . $taxonomy_slug] = '   ' . $category->name;
							}
						}
					}
				}
			}
		}
	} else {
		// Fallback for default taxonomies when custom types system not available
		$default_taxonomies = array(
			'service_category' => __('Service Categories', 'listeo_core'),
			'rental_category' => __('Rental Categories', 'listeo_core'),
			'event_category' => __('Event Categories', 'listeo_core'),
			'classifieds_category' => __('Classifieds Categories', 'listeo_core')
		);
		
		foreach ($default_taxonomies as $taxonomy_slug => $label) {
			if (taxonomy_exists($taxonomy_slug)) {
				$categories = get_terms(array(
					'taxonomy'   => $taxonomy_slug,
					'hide_empty' => false,
				));
				
				if (!empty($categories) && !is_wp_error($categories)) {
					// Add section header with emoji
					$choices['---' . $taxonomy_slug . '---'] = '➡️ ' . $label;
					foreach ($categories as $category) {
						$choices[$category->term_id . '_' . $taxonomy_slug] = '   ' . $category->name;
					}
				}
			}
		}
	}
	
	return $choices;
}

/**
 * Get listing types for customizer dropdown
 * Used for customizer option 'show_only_listing_types'
 * 
 * @return array Listing types choices
 */
function listeo_get_listing_types_for_slider()
{
	$choices = array();
	
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		$types = $custom_types->get_listing_types(true); // active only
		
		if ($types) {
			foreach ($types as $type) {
				$choices[$type->slug] = $type->name;
			}
		}
	} else {
		// Fallback to default types
		$choices = array(
			'service' => __('Service', 'listeo_core'),
			'rental' => __('Rental', 'listeo_core'),
			'event' => __('Event', 'listeo_core'),
			'classifieds' => __('Classifieds', 'listeo_core')
		);
	}
	
	return $choices;
}


/* Hooks */
/* Hooks */
//add_action( 'listeo_before_archive', 'listeo_result_sorting', 20 );
add_action('listeo_before_archive', 'listeo_archive_buttons', 25, 2);
//add_action( 'listeo_before_archive', 'listeo_result_layout_switch', 10, 2 );

/**
 * Return type of listings
 *
 */
function listeo_core_get_listing_types()
{
	// Get types from the custom listing types system
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		$types = $custom_types->get_listing_types(true); // active only
		
		$options = array();
		if ($types) {
			foreach ($types as $type) {
				$options[$type->slug] = __($type->name, 'listeo_core');
			}
		}
	} else {
		// Fallback to hardcoded types if custom system not available
		$options = array(
			'service' 		=> __('Service', 'listeo_core'),
			'rental' 	 	=> __('Rental', 'listeo_core'),
			'event' 		=> __('Event', 'listeo_core'),
			'classifieds' 	=> __('Classifieds', 'listeo_core'),
		);
	}
	
	return apply_filters('listeo_core_get_listing_types', $options);
}

/**
 * Get the taxonomy name for a given listing type
 * This maintains backward compatibility while allowing for future custom taxonomies
 */
function listeo_core_get_taxonomy_for_listing_type($listing_type) {
	// Backward compatibility mapping
	$taxonomy_mapping = array(
		'service' => 'service_category',
		'rental' => 'rental_category', 
		'event' => 'event_category',
		'classifieds' => 'classifieds_category',
		'region' => 'region'
	);
	
	// Return mapped taxonomy if it exists, otherwise construct from type
	if (isset($taxonomy_mapping[$listing_type])) {
		return $taxonomy_mapping[$listing_type];
	}
	
	// For custom types, construct taxonomy name (can be customized via filter)
	$taxonomy = $listing_type . '_category';
	return apply_filters('listeo_core_listing_type_taxonomy', $taxonomy, $listing_type);
}

/**
 * Check if a listing type supports a specific feature
 */
function listeo_core_listing_type_supports($listing_type, $feature) {
	// Use new custom listing types system
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		$type_obj = $custom_types->get_listing_type_by_slug($listing_type);
		
		if ($type_obj) {
			switch ($feature) {
				case 'booking':
					// Check if booking is enabled for this type
					return ($type_obj->booking_type && $type_obj->booking_type !== 'none');
				
				case 'opening_hours':
					// Check opening hours support directly
					return (bool) $type_obj->supports_opening_hours;
				
				case 'pricing':
				case 'calendar':  
				case 'time_slots':
				case 'guests':
				case 'services':
				case 'date_range':
				case 'hourly_picker':
				case 'tickets':
					// Use the unified booking features system
					return $custom_types->type_supports_feature($listing_type, $feature);
				
				default:
					// For any other feature, check if it exists in booking_features
					return $custom_types->type_supports_feature($listing_type, $feature);
			}
		}
	}
	
	// Backward compatibility fallback for legacy installations
	switch ($listing_type) {
		case 'service':
			$default_features = ['booking', 'pricing', 'calendar', 'time_slots', 'guests', 'services'];
			break;
		case 'rental':
			$default_features = ['booking', 'pricing', 'calendar', 'date_range', 'hourly_picker', 'guests', 'services'];
			break;
		case 'event':
			$default_features = ['booking', 'pricing', 'tickets', 'guests', 'services'];
			break;
		case 'classifieds':
			$default_features = []; // classifieds don't support booking features
			break;
		default:
			$default_features = [];
			break;
	}
	
	return in_array($feature, $default_features);
}

/**
 * Get all supported features for a listing type
 * 
 * @param string $listing_type The listing type slug
 * @return array Array of supported feature names
 */
function listeo_core_get_listing_type_features($listing_type) {
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		return $custom_types->get_type_features($listing_type);
	}
	
	// Fallback for legacy installations
	switch ($listing_type) {
		case 'service':
			return ['time_slots', 'services', 'calendar'];
		case 'rental':
			return ['date_range', 'hourly_picker', 'services', 'calendar'];
		case 'event':
			return ['tickets', 'services'];
		case 'classifieds':
		default:
			return [];
	}
}

/**
 * Get the booking type for a listing type
 * 
 * @param string $listing_type The listing type slug
 * @return string The booking type (single_day, date_range, tickets, none)
 */
function listeo_core_get_listing_type_booking_type($listing_type) {
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types = listeo_core_custom_listing_types();
		$type_obj = $custom_types->get_listing_type_by_slug($listing_type);
		
		if ($type_obj) {
			return $type_obj->booking_type ?: 'none';
		}
	}
	
	// Fallback for legacy installations
	switch ($listing_type) {
		case 'service':
			return 'single_day';
		case 'rental':
			return 'date_range';
		case 'event':
			return 'tickets';
		case 'classifieds':
		default:
			return 'none';
	}
}


/*add_filter('listeo_core_get_listing_types','add_listing_types_from_option');*/

/**
 * Return type of listings
 *
 */
function listeo_core_get_rental_period()
{
	$options = array(
		'daily' => __('Daily', 'listeo_core'),
		'weekly' 	 => __('Weekly', 'listeo_core'),
		'monthly' => __('Monthly', 'listeo_core'),
		'yearly' 	 => __('Yearly', 'listeo_core'),
	);
	return apply_filters('listeo_core_get_rental_period', $options);
}

/**
 * Return type of offers
 *
 */

function listeo_core_get_offer_types()
{
	$options =  array(
		'sale' => array(
			'name' => __('For Sale', 'listeo_core'),
			'front' => '1'
		),
		'rent' => array(
			'name' => __('For Rent', 'listeo_core'),
			'front' => '1'
		),
		'sold' => array(
			'name' => __('Sold', 'listeo_core')
		),
		'rented' => array(
			'name' => __('Rented', 'listeo_core')
		),
	);
	return apply_filters('listeo_core_get_offer_types', $options);
}

function listeo_core_get_offer_types_flat($with_all = false)
{
	$org_offer_types = listeo_core_get_offer_types();

	$options = array();
	foreach ($org_offer_types as $key => $value) {

		if ($with_all == true) {
			$options[$key] = $value['name'];
		} else {
			if (isset($value['front']) && $value['front'] == 1) {
				$options[$key] = $value['name'];
			} elseif (!isset($value['front']) && in_array($key, array('sale', 'rent'))) {
				$options[$key] = $value['name'];
			}
		}
	}
	return $options;
}
function listeo_core_get_options_array($type, $data)
{
	$options = array();
	if ($type == 'taxonomy') {

		$args = array(
			'taxonomy' => $data,
			'hide_empty' => true,
			'orderby' => 'term_order'
		);
		$args = apply_filters('listeo_taxonomy_dropdown_options_args', $args);
		$categories =  get_terms($data, $args);

		$options = array();
		foreach ($categories as $cat) {
			$options[$cat->term_id] = array(
				'name'  => $cat->name,
				'slug'  => $cat->slug,
				'id'	=> $cat->term_id,
			);
		}
	}
	return $options;
}
function listeo_core_get_options_array_hierarchical($terms, $selected, $output = '', $parent_id = 0, $level = 0)
{
	//Out Template

	$outputTemplate = '<option %SELECED% value="%ID%">%PADDING%%NAME%</option>';

	foreach ($terms as $term) {
		if ($parent_id == $term->parent) {
			if (is_array($selected)) {
				$is_selected = in_array($term->slug, $selected) ? ' selected="selected" ' : '';
			} else {
				$is_selected = selected($selected, $term->slug, false);
			}
			//Replacing the template variables
			$itemOutput = str_replace('%SELECED%', $is_selected, $outputTemplate);
			$itemOutput = str_replace('%ID%', $term->slug, $itemOutput);
			$itemOutput = str_replace('%PADDING%', str_pad('', $level * 12, '&nbsp;&nbsp;'), $itemOutput);
			$itemOutput = str_replace('%NAME%', $term->name, $itemOutput);

			$output .= $itemOutput;
			$output = listeo_core_get_options_array_hierarchical($terms, $selected, $output, $term->term_id, $level + 1);
		}
	}
	return $output;
}

/*$terms = get_terms('taxonomy', array('hide_empty' => false));
$output = get_terms_hierarchical($terms);

echo '<select>' . $output . '</select>';  
*/
/**
 * Returns html for select input with options based on type
 *
 *
 * @param  $type taxonomy
 * @param  $data term
 */
// function get_listeo_core_dropdown( $type, $data='', $name, $class='chosen-select-no-single', $placeholder='Any Type'){
// 	$output = '<select name="'.esc_attr($name).'" data-placeholder="'.esc_attr($placeholder).'" class="'.esc_attr($class).'">';
// 	if($type == 'taxonomy'){
// 		$categories =  get_terms( $data, array(
// 		    'hide_empty' => false,
// 		) );	

// 		$output .= '<option>'.esc_html__('Any Type','listeo_core').'</option>';
// 		foreach ($categories as $cat) { 
// 			$output .= '<option value='.$cat->term_id.'>'.$cat->name.'</option>';
// 		}
// 	}
// 	$output .= '</select>';
// 	return $output;
// }

/**
 * Returns html for just options input based on data array
 *
 * @param  $data array
 */
function get_listeo_core_options_dropdown($data, $selected)
{
	$output = '';

	if (is_array($data)) :
		foreach ($data as $id => $value) {
			if (is_array($selected)) {

				$is_selected = in_array($value['slug'], $selected) ? ' selected="selected" ' : '';
			} else {
				$is_selected = selected($selected, $id);
			}
			$output .= '<option ' . $is_selected . ' value="' . esc_attr($value['slug']) . '">' . esc_html($value['name']) . '</option>';
		}
	endif;
	return $output;
}

function get_listeo_core_options_dropdown_by_type($type, $data)
{
	$output = '';
	if (is_array($data)) :
		foreach ($data as $id => $value) {
			$output .= '<option value="' . esc_attr($id) . '">' . esc_html($value) . '</option>';
		}
	endif;
	return $output;
}

function get_listeo_core_numbers_dropdown($number = 10)
{
	$output = '';
	$x = 1;
	while ($x <= $number) {
		$output .= '<option value="' . esc_attr($x) . '">' . esc_html($x) . '</option>';
		$x++;
	}
	return $output;
}

function get_listeo_core_intervals_dropdown($min, $max, $step = 100, $name = false)
{
	$output = '';

	if ($min == 'auto') {
		$min = Listeo_Core_Search::get_min_meta_value($name);
	}
	if ($max == 'auto') {
		$max = Listeo_Core_Search::get_max_meta_value($name);
	}
	$range = range($min, $max, $step);
	if (sizeof($range) > 30) {
		$output = "<option>ADMIN NOTICE: increase your step value in Search Form Editor, having more than 30 steps is not recommended for performence options</option>";
	} else {
		foreach ($range as $number) {
			$output .= '<option value="' . esc_attr($number) . '">' . esc_html(number_format_i18n($number)) . '</option>';
		}
	}
	return $output;
}


/**
 * Gets a number of posts and displays them as options
 * @param  array $query_args Optional. Overrides defaults.
 * @return array             An array of options that matches the CMB2 options array
 */
function listeo_core_get_post_options($query_args)
{

	$args = wp_parse_args($query_args, array(
		'post_type'   => 'post',
		'numberposts' => 399,
		'update_post_meta_cache' => false,
		'cache_results' => false,
		'update_post_term_cache' => false
	));

	$posts = get_posts($args);

	$post_options = array();
	$post_options[0] = esc_html__('--Disabled--', 'listeo_core');
	if ($posts) {
		foreach ($posts as $post) {
			$post_options[$post->ID] = $post->post_title;
		}
	}

	return $post_options;
}


function listeo_core_get_product_options($product_type = false ){
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'update_post_meta_cache' => false,
		'cache_results' => false,
		'update_post_term_cache' => false,
		'tax_query' => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $product_type,
			),
		),
	);

	$posts = get_posts($args);

	$post_options = array();
	$post_options[0] = esc_html__('--Disabled--', 'listeo_core');
	if ($posts) {
		foreach ($posts as $post) {
			$post_options[$post->ID] = $post->post_title;
		}
	}

	return $post_options;
	
}
/**
 * Gets 5 posts for your_post_type and displays them as options
 * @return array An array of options that matches the CMB2 options array
 */
function listeo_core_get_pages_options()
{
	return listeo_core_get_post_options(array('post_type' => 'page',));
}


function listeo_core_get_listing_packages_as_options($include_all = false)
{
	if($include_all){
		$terms = array('listing_package','listing_package_subscription');
	} else {
		$terms = array('listing_package');
	}
	$args =  array(
		'post_type'        => 'product',
		'posts_per_page'   => -1,
		'order'            => 'asc',
		'orderby'          => 'date',
		'suppress_filters' => false,
		'tax_query'        => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $terms,
				'operator' => 'IN',
			),
		),

	);

	$posts = get_posts($args);

	$post_options = array();
	if ($include_all) {
		$post_options[0] = esc_html__('All', 'listeo_core');
	}
	if ($posts) {
		foreach ($posts as $post) {
			$post_options[$post->ID] = $post->post_title;
		}
	}

	return $post_options;
}
{

	$args =  array(
		'post_type'        => 'product',
		'posts_per_page'   => -1,
		'order'            => 'asc',
		'orderby'          => 'date',
		'suppress_filters' => false,
		'tax_query'        => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array('listing_package'),
				'operator' => 'IN',
			),
		),

	);

	$posts = get_posts($args);

	$post_options = array();

	if ($posts) {
		foreach ($posts as $post) {
			$post_options[$post->ID] = $post->post_title;
		}
	}

	return $post_options;
}

function listeo_core_get_listing_taxonomies_as_options()
{
	$taxonomy_objects = get_object_taxonomies('listing', 'objects');

	$_options = array();

	if ($taxonomy_objects) {
		foreach ($taxonomy_objects as $tax) {
			$_options[$tax->name] = $tax->label;
		}
	}

	return $_options;
}

function listeo_core_get_related_listing_taxonomies_as_options()
{
	$taxonomy_objects = get_object_taxonomies('listing', 'objects');

	$_options = array();

	// Add "From the same listing type" option
	$_options['listing_type'] = __('From the same listing type', 'listeo_core');

	if ($taxonomy_objects) {
		foreach ($taxonomy_objects as $tax) {
			$_options[$tax->name] = $tax->label;
		}
	}

	return $_options;
}

function listeo_core_get_nearby_listing_taxonomies_as_options()
{
	$taxonomy_objects = get_object_taxonomies('listing', 'objects');

	$_options = array();

	// Add "Display All" option as default for nearby listings
	$_options['all'] = __('Display All (no taxonomy filter)', 'listeo_core');

	// Add "From the same listing type" option
	$_options['listing_type'] = __('From the same listing type', 'listeo_core');

	if ($taxonomy_objects) {
		foreach ($taxonomy_objects as $tax) {
			$_options[$tax->name] = $tax->label;
		}
	}

	return $_options;
}

function listeo_core_get_product_taxonomies_as_options()
{
	$taxonomy_objects = get_terms(array(
		'taxonomy' => 'product_cat',
		'hide_empty' => false,
	));

	$_options = array();
	if (!empty($taxonomy_objects) && !is_wp_error($taxonomy_objects)) {

		foreach ($taxonomy_objects as $tax) {

			$_options[$tax->term_id] = $tax->name;
		}
	}


	return $_options;
}




function listeo_core_agent_name()
{
	$fname = get_the_author_meta('first_name');
	$lname = get_the_author_meta('last_name');
	$full_name = '';

	if (empty($fname)) {
		$full_name = $lname;
	} elseif (empty($lname)) {
		$full_name = $fname;
	} else {
		//both first name and last name are present
		$full_name = "{$fname} {$lname}";
	}

	echo $full_name;
}


function listeo_core_ajax_pagination($pages = '', $current = false, $range = 2)
{


	if (!empty($current)) {
		$paged = $current;
	} else {
		global $paged;
	}

	$output = false;
	if (empty($paged)) $paged = 1;

	$prev = $paged - 1;
	$next = $paged + 1;
	$showitems = ($range * 2) + 1;
	$range = 2; // change it to show more links

	if ($pages == '') {
		global $wp_query;

		$pages = $wp_query->max_num_pages;
		if (!$pages) {
			$pages = 1;
		}
	}

	if (1 != $pages) {


		$output .= '<nav class="pagination margin-top-30"><ul class="pagination">';
		$output .=  ($paged > 2 && $paged > $range + 1 && $showitems < $pages) ? '<li data-paged="prev"><a href="#"><i class="sl sl-icon-arrow-left"></i></a></li>' : '';
		//$output .=  ( $paged > 1 ) ? '<li><a class="previouspostslink" href="#"">'.__('Previous','listeo_core').'</a></li>' : '';
		for ($i = 1; $i <= $pages; $i++) {

			if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
				if ($paged == $i) {
					$output .=  '<li class="current" data-paged="' . $i . '"><a href="#">' . $i . ' </a></li>';
				} else {
					$output .=  '<li data-paged="' . $i . '"><a href="#">' . $i . '</a></li>';
				}
			}
		}
		// $output .=  ( $paged < $pages ) ? '<li><a class="nextpostslink" href="#">'.__('Next','listeo_core').'</a></li>' : '';
		$output .=  ($paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages) ? '<li data-paged="next"><a  href="#"><i class="sl sl-icon-arrow-right"></i></a></li>' : '';
		$output .=  '</ul></nav>';
	}
	return $output;
}
function listeo_core_pagination($pages = '', $current = false, $range = 2)
{
	if (!empty($current)) {
		$paged = $current;
	} else {
		global $paged;
	}


	if (empty($paged)) $paged = 1;

	$prev = $paged - 1;
	$next = $paged + 1;
	$showitems = ($range * 2) + 1;
	$range = 2; // change it to show more links

	if ($pages == '') {
		global $wp_query;

		$pages = $wp_query->max_num_pages;
		if (!$pages) {
			$pages = 1;
		}
	}

	if (1 != $pages) {


		echo '<ul class="pagination">';
		echo ($paged > 2 && $paged > $range + 1 && $showitems < $pages) ? '<li><a href="' . get_pagenum_link(1) . '"><i class="sl sl-icon-arrow-left"></i></a></li>' : '';
		// echo ( $paged > 1 ) ? '<li><a class="previouspostslink" href="'.get_pagenum_link($prev).'">'.__('Previous','listeo_core').'</a></li>' : '';
		for ($i = 1; $i <= $pages; $i++) {
			if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
				if ($paged == $i) {
					echo '<li class="current" data-paged="' . $i . '"><a href="' . get_pagenum_link($i) . '">' . $i . ' </a></li>';
				} else {
					echo '<li data-paged="' . $i . '"><a href="' . get_pagenum_link($i) . '">' . $i . '</a></li>';
				}
			}
		}
		// echo ( $paged < $pages ) ? '<li><a class="nextpostslink" href="'.get_pagenum_link($next).'">'.__('Next','listeo_core').'</a></li>' : '';
		echo ($paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages) ? '<li><a  href="' . get_pagenum_link($pages) . '"><i class="sl sl-icon-arrow-right"></i></a></li>' : '';
		echo '</ul>';
	}
}

function listeo_core_get_post_status($id)
{
	$status = get_post_status($id);
	switch ($status) {
		case 'publish':
			$friendly_status = esc_html__('Published', 'listeo_core');
			break;
		case 'pending_payment':
			$friendly_status = esc_html__('Pending Payment', 'listeo_core');
			break;
		case 'expired':
			$friendly_status = esc_html__('Expired', 'listeo_core');
			break;
		case 'draft':
		case 'pending':
			$friendly_status = esc_html__('Pending Approval', 'listeo_core');
			break;

		default:
			$friendly_status = $status;
			break;
	}
	return $friendly_status;
}

/**
 * Calculates and returns the listing expiry date.
 *
 * @since 1.22.0
 * @param  int $id
 * @return string
 */
function calculate_listing_expiry($id)
{
	// Get duration from the product if set...
	$duration = get_post_meta($id, '_duration', true);
	$is_from_package = get_post_meta($id, '_package_id',true);

	// ...otherwise use the global option
	if (!$duration) {
		if($is_from_package){
			$duration = 0;
		} else {
		$duration = absint(get_option('listeo_default_duration'));
		}
	}

	if ($duration > 0) {
		$new_date = date_i18n('Y-m-d', strtotime("+{$duration} days", current_time('timestamp')));
		return CMB2_Utils::get_timestamp_from_value($new_date, 'm/d/Y');
	}

	return '';
}

function listeo_core_get_expiration_date($id)
{
	$expires = get_post_meta($id, '_listing_expires', true);

	$package_id = get_post_meta($id, '_user_package_id', true);

	if ($package_id) {
		global $wpdb;
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT order_id FROM {$wpdb->prefix}listeo_core_user_packages WHERE
		    id = %d",
				$package_id
			)
		);

		if ($id && function_exists('wcs_get_subscription')) {

			$subscription_obj = wcs_get_subscription($id);
			if ($subscription_obj) {
				$date_end =  $subscription_obj->get_date('end');

				if (!empty($date_end)) {

					$converted_date = date_i18n(get_option('date_format'), strtotime($date_end));
					return $converted_date;
				} else {

					if (!empty($expires)) {
						if (listeo_core_is_timestamp($expires)) {
							$saved_date = get_option('date_format');
							$new_date = date_i18n($saved_date, $expires);
						} else {
							return $expires;
						}
					}
				}
			}

			// echo $subscription_obj->get_expiration_date( 'next_payment' ); 
		}
	}


	if (!empty($expires)) {
		if (listeo_core_is_timestamp($expires)) {
			$saved_date = get_option('date_format');
			$new_date = date_i18n($saved_date, $expires);
		} else {
			return $expires;
		}
	}
	return (empty($expires)) ? __('Never/not set', 'listeo_core') : $new_date;
}

function listeo_core_is_timestamp($timestamp)
{

	$check = (is_int($timestamp) or is_float($timestamp))
		? $timestamp
		: (string) (int) $timestamp;
	return ($check === $timestamp)
		and ((int) $timestamp <=  PHP_INT_MAX)
		and ((int) $timestamp >= ~PHP_INT_MAX);
}

function listeo_core_get_listing_image($id)
{
	if (has_post_thumbnail($id)) {
		return	wp_get_attachment_image_url(get_post_thumbnail_id($id), 'listeo-listing-grid');
	} else {
		$gallery = (array) get_post_meta($id, '_gallery', true);

		$ids = array_keys($gallery);
		if (!empty($ids[0]) && $ids[0] !== 0) {
			return  wp_get_attachment_image_url($ids[0], 'listeo-listing-grid');
		} else {
			$placeholder = get_listeo_core_placeholder_image();
			return $placeholder;
		}
	}
}

add_action('listeo_page_subtitle', 'listeo_core_my_account_hello');
function listeo_core_my_account_hello()
{
	$my_account_page = get_option('my_account_page');
	if (is_user_logged_in() && !empty($my_account_page) && is_page($my_account_page)) {
		$current_user = wp_get_current_user();
		if (!empty($current_user->user_firstname)) {
			$name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
		} else {
			$name = $current_user->display_name;
		}
		echo "<span>" . esc_html__('Howdy, ', 'listeo_core') . $name . '!</span>';
	} else {
		global $post;
		$subtitle = get_post_meta($post->ID, 'listeo_subtitle', true);
		if ($subtitle) {
			echo "<span>" . esc_html($subtitle) . "</span>";
		}
	}
}



function listeo_core_sort_by_priority($array = array(), $order = SORT_NUMERIC)
{

	if (!is_array($array))
		return;

	// Sort array by priority

	$priority = array();

	foreach ($array as $key => $row) {

		if (isset($row['position'])) {
			$row['priority'] = $row['position'];
			unset($row['position']);
		}

		$priority[$key] = isset($row['priority']) ? absint($row['priority']) : false;
	}

	array_multisort($priority, $order, $array);

	return apply_filters('listeo_sort_by_priority', $array, $order);
}


/**
 * CMB2 Select Multiple Custom Field Type
 * @package CMB2 Select Multiple Field Type
 */

/**
 * Adds a custom field type for select multiples.
 * @param  object $field             The CMB2_Field type object.
 * @param  string $value             The saved (and escaped) value.
 * @param  int    $object_id         The current post ID.
 * @param  string $object_type       The current object type.
 * @param  object $field_type_object The CMB2_Types object.
 * @return void
 */
// if(!function_exists('listeo_cmb2_render_select_multiple_field_type')) {
// 	function listeo_cmb2_render_select_multiple_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

// 		$select_multiple = '<select class="widefat" multiple name="' . $field->args['_name'] . '[]" id="' . $field->args['_id'] . '"';
// 		foreach ( $field->args['attributes'] as $attribute => $value ) {
// 			$select_multiple .= " $attribute=\"$value\"";
// 		}
// 		$select_multiple .= ' />';

// 		foreach ( $field->options() as $value => $name ) {
// 			$selected = ( $escaped_value && in_array( $value, $escaped_value ) ) ? 'selected="selected"' : '';
// 			$select_multiple .= '<option class="cmb2-option" value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
// 		}

// 		$select_multiple .= '</select>';
// 		$select_multiple .= $field_type_object->_desc( true );

// 		echo $select_multiple; // WPCS: XSS ok.
// 	}
// 	add_action( 'cmb2_render_select_multiple', 'listeo_cmb2_render_select_multiple_field_type', 10, 5 );


// 	/**
// 	 * Sanitize the selected value.
// 	 */
// 	function listeo_cmb2_sanitize_select_multiple_callback( $override_value, $value ) {
// 		if ( is_array( $value ) ) {
// 			foreach ( $value as $key => $saved_value ) {
// 				$value[$key] = sanitize_text_field( $saved_value );
// 			}

// 			return $value;
// 		}

// 		return;
// 	}
// 	add_filter( 'cmb2_sanitize_select_multiple', 'listeo_cmb2_sanitize_select_multiple_callback', 10, 2 );
// }




function listeo_core_array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
{
	$sort_col = array();
	foreach ($arr as $key => $row) {
		$sort_col[$key] = $row[$col];
	}

	array_multisort($sort_col, $dir, $arr);
}


function listeo_core_get_nearby_listings($lat, $lng, $distance, $radius_type)
{
	global $wpdb;
	if ($radius_type == 'km') {
		$ratio = 6371;
	} else {
		$ratio = 3959;
	}

	$post_ids =
		$wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT DISTINCT
			 		geolocation_lat.post_id,
			 		geolocation_lat.meta_key,
			 		geolocation_lat.meta_value as listingLat,
			        geolocation_long.meta_value as listingLong,
			        ( %d * acos( cos( radians( %f ) ) * cos( radians( geolocation_lat.meta_value ) ) * cos( radians( geolocation_long.meta_value ) - radians( %f ) ) + sin( radians( %f ) ) * sin( radians( geolocation_lat.meta_value ) ) ) ) AS distance 
		       
			 	FROM 
			 		$wpdb->postmeta AS geolocation_lat
			 		LEFT JOIN $wpdb->postmeta as geolocation_long ON geolocation_lat.post_id = geolocation_long.post_id
					WHERE geolocation_lat.meta_key = '_geolocation_lat' AND geolocation_long.meta_key = '_geolocation_long'
			 		HAVING distance < %d

		 	",
				$ratio,
				$lat,
				$lng,
				$lat,
				$distance
			),
			ARRAY_A
		);

	return $post_ids;
}

function listeo_core_geocode($address)
{
	// error_log('=== GEOCODING DEBUG ===');
	// error_log('Original address: ' . $address);
	
	// url encode the address
	$address = urlencode($address);
	// error_log('Encoded address: ' . $address);

	// Check if we have cached results for this address
	$cache_key = 'geocode_' . md5($address);
	$cached_results = get_transient($cache_key);
	// error_log('Cache key: ' . $cache_key);
	// error_log('Cached result: ' . var_export($cached_results, true));
	
	// TEMPORARY: Clear cache to debug the issue
	delete_transient($cache_key);
	// error_log('Cache cleared for debugging');
	
	// if ($cached_results !== false) {
	// 	error_log('Returning cached result');
	// 	return $cached_results;
	// }

	$geocoding_provider = get_option('listeo_geocoding_provider', 'google');
	if ($geocoding_provider == 'google') {
		$api_key = get_option('listeo_maps_api_server');
		// google map geocode api url
		$url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key={$api_key}";
		// error_log('Geocoding URL: ' . $url);

		// get the json response
		$resp_json = wp_remote_get($url);

		if (is_wp_error($resp_json)) {
			// error_log('wp_remote_get error: ' . $resp_json->get_error_message());
			return false;
		}

		$resp = json_decode(wp_remote_retrieve_body($resp_json), true);
		// error_log('API Response: ' . print_r($resp, true));

		// response status will be 'OK', if able to geocode given address 
		if ($resp['status'] == 'OK') {
			// get the important data
			$lati = $resp['results'][0]['geometry']['location']['lat'];
			$longi = $resp['results'][0]['geometry']['location']['lng'];
			$formatted_address = $resp['results'][0]['formatted_address'];

			// verify if data is complete
			if ($lati && $longi && $formatted_address) {
				// put the data in the array
				$data_arr = array(
					$lati,
					$longi,
					$formatted_address
				);

				// Cache the results
				set_transient('geocode_' . md5($address), $data_arr, 7 * DAY_IN_SECONDS);

				return $data_arr;
			} else {
				// error_log('Data incomplete - lat: ' . $lati . ', lng: ' . $longi . ', formatted_address: ' . $formatted_address);
			}
		} else {
			// error_log('API Status not OK: ' . $resp['status']);
		}
		// error_log('Geocoding failed - returning false');
		return false;
	} else {
		$api_key = get_option('listeo_geoapify_maps_api_server');
		$url = "https://api.geoapify.com/v1/geocode/search?text={$address}&apiKey={$api_key}";

		// get the json response
		$resp_json = wp_remote_get($url);

		if (is_wp_error($resp_json)) {
			return false;
		}

		$resp = json_decode(wp_remote_retrieve_body($resp_json), true);

		// response status will be 'OK', if able to geocode given address 
		if ($resp && isset($resp['features']) && !empty($resp['features'])) {
			// get the important data
			$lati = $resp['features'][0]['geometry']['coordinates'][1];
			$longi = $resp['features'][0]['geometry']['coordinates'][0];
			$formatted_address = $resp['features'][0]['properties']['formatted'];

			// verify if data is complete
			if ($lati && $longi && $formatted_address) {
				// put the data in the array
				$data_arr = array(
					$lati,
					$longi,
					$formatted_address
				);

				// Cache the results
				set_transient('geocode_' . md5($address), $data_arr, 7 * DAY_IN_SECONDS);

				return $data_arr;
			}
		}
		return false;
	}
}

/**
 * Smart location search function - used across all search implementations
 * Handles progressive search with country skipping and combination matching
 * 
 * @param string $location The location string to search for (e.g., "Reduta, Kraków, Poland")
 * @param boolean $search_only_address Whether to restrict search to address fields only
 * @return array Array of post IDs that match the location search
 */
function listeo_core_search_location_smart($location, $search_only_address = null) {
	if (empty($location)) {
		return array(0);
	}
	
	// Check the location search method setting
	$location_search_method = get_option('listeo_location_search_method', 'basic');
	
	// If basic method is selected, use the basic function
	if ($location_search_method === 'basic') {
		return listeo_core_search_location_basic($location, $search_only_address);
	}
	
	// Continue with the broad/smart method below
	global $wpdb;
	
	// Get the search restriction setting if not provided
	if ($search_only_address === null) {
		$search_only_address = (get_option('listeo_search_only_address', 'off') == 'on');
	}
	
	// Smart combination search - try combinations before individual fallback
	$locations = array_map('trim', explode(',', $location));
	$num_parts = count($locations);
	
	// Skip last part (country) if 3+ parts exist
	$search_attempts = array();
	
	if ($num_parts >= 3) {
		// Try without country first (most specific)
		$without_country = implode(', ', array_slice($locations, 0, -1));
		$search_attempts[] = array('type' => 'exact', 'term' => $without_country);
		
		// Try first two parts with AND condition (both must be present)
		if (count($locations) >= 2) {
			$search_attempts[] = array('type' => 'and', 'terms' => array($locations[0], $locations[1]));
		}
		
		// Individual part fallbacks (skip country)
		foreach (array_slice($locations, 0, -1) as $part) {
			$search_attempts[] = array('type' => 'single', 'term' => $part);
		}
	} else {
		// 2 or fewer parts - try each individually
		foreach ($locations as $part) {
			$search_attempts[] = array('type' => 'single', 'term' => $part);
		}
	}
	
	// Execute search attempts in order until results found
	foreach ($search_attempts as $attempt) {
		$current_location_post_ids = array();
		
		if ($attempt['type'] == 'exact') {
			// Search for exact phrase
			$escaped_part = '%' . $wpdb->esc_like($attempt['term']) . '%';
			
			if ($search_only_address) {
				$current_location_post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
					 WHERE (meta_key = '_address' AND meta_value LIKE %s)
						OR (meta_key = '_friendly_address' AND meta_value LIKE %s)",
					$escaped_part,
					$escaped_part
				));
			} else {
				$post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
					 WHERE meta_value LIKE %s",
					$escaped_part
				));
				
				$content_post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts}
					 WHERE (post_title LIKE %s OR post_content LIKE %s)
						AND post_type = 'listing' 
						AND post_status = 'publish'",
					$escaped_part,
					$escaped_part
				));
				
				$current_location_post_ids = array_merge($post_ids, $content_post_ids);
			}
		} 
		elseif ($attempt['type'] == 'and') {
			// Search for listings that contain ALL terms (simplified approach)
			$terms = $attempt['terms'];
			$all_post_ids = array();
			
			// Get posts for each term, then find intersection
			foreach ($terms as $term) {
				$escaped_term = '%' . $wpdb->esc_like($term) . '%';
				
				if ($search_only_address) {
					$term_post_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
						 WHERE (meta_key = '_address' AND meta_value LIKE %s)
							OR (meta_key = '_friendly_address' AND meta_value LIKE %s)",
						$escaped_term,
						$escaped_term
					));
				} else {
					$meta_post_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
						 WHERE meta_value LIKE %s",
						$escaped_term
					));
					
					$content_post_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts}
						 WHERE (post_title LIKE %s OR post_content LIKE %s)
							AND post_type = 'listing' 
							AND post_status = 'publish'",
						$escaped_term,
						$escaped_term
					));
					
					$term_post_ids = array_merge($meta_post_ids, $content_post_ids);
				}
				
				if (empty($all_post_ids)) {
					$all_post_ids = $term_post_ids;
				} else {
					// Find intersection - posts that have ALL terms
					$all_post_ids = array_intersect($all_post_ids, $term_post_ids);
				}
				
				// If no posts have all terms so far, break early
				if (empty($all_post_ids)) {
					break;
				}
			}
			
			$current_location_post_ids = $all_post_ids;
		}
		elseif ($attempt['type'] == 'single') {
			// Search for single term
			$escaped_part = '%' . $wpdb->esc_like($attempt['term']) . '%';
			
			if ($search_only_address) {
				$current_location_post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
					 WHERE (meta_key = '_address' AND meta_value LIKE %s)
						OR (meta_key = '_friendly_address' AND meta_value LIKE %s)",
					$escaped_part,
					$escaped_part
				));
			} else {
				$post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
					 WHERE meta_value LIKE %s",
					$escaped_part
				));
				
				$content_post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts}
					 WHERE (post_title LIKE %s OR post_content LIKE %s)
						AND post_type = 'listing' 
						AND post_status = 'publish'",
					$escaped_part,
					$escaped_part
				));
				
				$current_location_post_ids = array_merge($post_ids, $content_post_ids);
			}
		}
		
		// If we found results with this attempt, use them and stop searching
		if (!empty($current_location_post_ids)) {
			return $current_location_post_ids;
		}
	}
	
	// No results found - return array with 0 for WordPress query compatibility
	return array(0);
}

/**
 * Basic location search function - original simple approach
 * 
 * @param string $location The location string to search for
 * @param boolean $search_only_address Whether to restrict search to address fields only
 * @return array Array of post IDs that match the location search
 */
function listeo_core_search_location_basic($location, $search_only_address = null) {
	if (empty($location)) {
		return array(0);
	}
	
	global $wpdb;
	
	// Get the search restriction setting if not provided
	if ($search_only_address === null) {
		$search_only_address = (get_option('listeo_search_only_address', 'off') == 'on');
	}
	
	$locations = array_map('trim', explode(',', $location));

	// Setup SQL
	$posts_locations_sql    = array();
	$postmeta_locations_sql = array();

	if ($search_only_address) {
		$postmeta_locations_sql[] = " meta_value LIKE '%" . esc_sql($locations[0]) . "%'  AND meta_key = '_address'";
		$postmeta_locations_sql[] = " meta_value LIKE '%" . esc_sql($locations[0]) . "%'  AND meta_key = '_friendly_address'";
	} else {
		$postmeta_locations_sql[] = " meta_value LIKE '%" . esc_sql($locations[0]) . "%' ";
		// Create post title and content SQL
		$posts_locations_sql[]    = " post_title LIKE '%" . esc_sql($locations[0]) . "%' OR post_content LIKE '%" . esc_sql($locations[0]) . "%' ";
	}

	// Get post IDs from post meta search
	$post_ids = $wpdb->get_col("
		SELECT DISTINCT post_id FROM {$wpdb->postmeta}
		WHERE " . implode(' OR ', $postmeta_locations_sql) . "
	");

	// Merge with post IDs from post title and content search
	if ($search_only_address) {
		$location_post_ids = array_merge($post_ids, array(0));
	} else {
		$location_post_ids = array_merge($post_ids, $wpdb->get_col("
			SELECT ID FROM {$wpdb->posts}
			WHERE ( " . implode(' OR ', $posts_locations_sql) . " )
			AND post_type = 'listing'
			AND post_status = 'publish'
		"), array(0));
	}
	
	return array_unique($location_post_ids);
}

function listeo_core_get_place_id($post)
{
	// url encode the address


	$address = urlencode(get_post_meta($post->ID, '_address', true));
	$api_key = get_option('listeo_maps_api_server');
	// google map geocode api url
	$url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key={$api_key}";

	// get the json response
	$resp_json = wp_remote_get($url);

	$resp = json_decode(wp_remote_retrieve_body($resp_json), true);

	// response status will be 'OK', if able to geocode given address 
	if ($resp['status'] == 'OK') {

		// get the important data

		if (isset($resp['results'][0]['place_id'])) {

			return $resp['results'][0]['place_id'];
		} else {

			return false;
		}
	} else {
		return false;
	}
}


function check_comment_hash_part($comment, $status = 'approved')
{
	$name = isset($comment->comment_author) ? $comment->comment_author : '';
	$email = isset($comment->comment_author_email) ? $comment->comment_author_email : '';
	$date = isset($comment->comment_date_gmt) ? $comment->comment_date_gmt : '';

	return wp_hash(
		implode(
			'|',
			array_filter(
				array($name, $email, $date, $status)
			)
		)
	);
}

function listeo_get_google_reviews($post)
{
	$reviews = false;

	// Debug: Allow cache clearing via URL parameter
	if (isset($_GET['clear_google_cache']) && current_user_can('manage_options')) {
		delete_transient('listeo_reviews_' . $post->ID);
	}

	if (get_option('listeo_google_reviews')) {

		$place_id = get_post_meta($post->ID, '_place_id', true);


		// Check for stale Google data - if no place ID but Google data exists, clean it up
		if (empty($place_id)) {
			// Check if we have stale Google review data
			$google_rating = get_post_meta($post->ID, '_google_rating', true);
			$google_count = get_post_meta($post->ID, '_google_review_count', true);

			if (!empty($google_rating) || !empty($google_count)) {
				// Clean up stale Google data
				delete_post_meta($post->ID, '_google_rating');
				delete_post_meta($post->ID, '_google_review_count');
				delete_post_meta($post->ID, '_google_last_updated');
				delete_transient('listeo_reviews_' . $post->ID);

				// Force recalculate combined rating without Google data
				// Clear existing combined rating to ensure fresh calculation
				delete_post_meta($post->ID, '_combined_rating');
				delete_post_meta($post->ID, '_combined_review_count');

				$reviews_instance = Listeo_Core_Reviews::instance();
				if (method_exists($reviews_instance, 'get_combined_rating')) {
					$new_combined_rating = $reviews_instance->get_combined_rating($post->ID);
				}


			}

			return false;
		}

		// Initialize gateway if available
		$gateway = null;
		if (class_exists('Listeo_Core_Google_Reviews_Gateway')) {
			$gateway = listeo_google_reviews_gateway();
		}

		// Check transient cache first
		$cached_reviews = get_transient('listeo_reviews_' . $post->ID);
		if ($cached_reviews) {
			$reviews = $cached_reviews;


			// Log cache hit if gateway is enabled
			if ($gateway && $gateway->is_enabled()) {
				$gateway->log_api_call($post->ID, $place_id, true, 'cache_hit');
			}

			// Ensure permanent storage is updated even when using cached data
			if (isset($reviews['result']['rating'])) {
				$current_google_rating = get_post_meta($post->ID, '_google_rating', true);
				$current_google_count = get_post_meta($post->ID, '_google_review_count', true);

				// Update permanent storage if it's missing or different
				if (empty($current_google_rating) || $current_google_rating != $reviews['result']['rating']) {
					update_post_meta($post->ID, '_google_rating', $reviews['result']['rating']);
				}
				if (isset($reviews['result']['user_ratings_total']) &&
					(empty($current_google_count) || $current_google_count != $reviews['result']['user_ratings_total'])) {
					update_post_meta($post->ID, '_google_review_count', $reviews['result']['user_ratings_total']);
				}

				// Trigger combined rating recalculation if needed
				$combined_rating = get_post_meta($post->ID, '_combined_rating', true);
				if (empty($combined_rating)) {
					$reviews_instance = Listeo_Core_Reviews::instance();
					if (method_exists($reviews_instance, 'get_combined_rating')) {
						$reviews_instance->get_combined_rating($post->ID);
					}
				}
			}
		} else {
			// Check rate limiting gateway if enabled
			if ($gateway && $gateway->is_enabled()) {
				if (!$gateway->should_allow_api_call($post->ID, $place_id)) {

					// Rate limited - try to return cached/stored data
					$google_rating = get_post_meta($post->ID, '_google_rating', true);
					$google_count = get_post_meta($post->ID, '_google_review_count', true);

					if ($google_rating) {

						// Return stored data in expected format
						$reviews = array(
							'result' => array(
								'rating' => $google_rating,
								'user_ratings_total' => $google_count,
								'from_cache' => true,
								'rate_limited' => true,
							),
							'status' => 'OK',
						);

						// Extend cache time when rate limited
						$extended_cache = 12; // Cache for 12 hours when rate limited
						set_transient('listeo_reviews_' . $post->ID, $reviews, $extended_cache * HOUR_IN_SECONDS);

						return $reviews;
					}

					return false; // No cached data available
				}
			}

			// Proceed with API call
			// Use dedicated Google Reviews API key if available, fallback to geocoding API key
			$api_key = get_option('listeo_google_reviews_api_key');
			if (empty($api_key)) {
				$api_key = get_option('listeo_maps_api_server');
			}
			$language = get_option('listeo_google_reviews_lang', 'en');

			// Build URL with proper review parameters
			$url = "https://maps.googleapis.com/maps/api/place/details/json?placeid={$place_id}&fields=name%2Crating%2Creviews%2Cbusiness_status%2Cformatted_phone_number%2Copening_hours/periods%2Cuser_ratings_total&key={$api_key}&language={$language}&reviews_sort=newest&reviews_no_translations=false";

			$resp_json = wp_remote_get($url);

			$reviews_raw = wp_remote_retrieve_body($resp_json);

			$reviews_clean = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $reviews_raw);  //remove emojis
			$reviews = json_decode($reviews_clean, true);

			// Log successful API call if gateway is enabled
			if ($gateway && $gateway->is_enabled()) {
				$status = (isset($reviews['status']) && $reviews['status'] === 'OK') ? 'success' : 'error';
				$gateway->log_api_call($post->ID, $place_id, false, $status);
			}

			// Get cache duration - use smart caching if gateway is enabled
			if ($gateway && $gateway->is_enabled()) {
				$cache_time = $gateway->get_smart_cache_duration($post->ID);
			} else {
				$cache_time = get_option('listeo_google_reviews_cache_days', 1);
			}


			set_transient('listeo_reviews_' . $post->ID, $reviews, (int) $cache_time * 24 * HOUR_IN_SECONDS);

			// Always store permanent meta data when fetching fresh data
			if (isset($reviews['result']['rating'])) {
				update_post_meta($post->ID, '_google_rating', $reviews['result']['rating']);
				// Store Google review count
				if (isset($reviews['result']['user_ratings_total'])) {
					update_post_meta($post->ID, '_google_review_count', $reviews['result']['user_ratings_total']);
				}
				// Store timestamp of last update
				update_post_meta($post->ID, '_google_last_updated', current_time('mysql'));

				// Trigger combined rating recalculation
				$reviews_instance = Listeo_Core_Reviews::instance();
				if (method_exists($reviews_instance, 'get_combined_rating')) {
					$reviews_instance->get_combined_rating($post->ID);
				}
			}
		}
	}


	return $reviews;
}

/**
 * Get combined rating display data for a listing
 * Returns combined rating from Google and Listeo reviews, with fallback to existing logic
 * 
 * @param int $post_id The post ID
 * @return array Array with 'rating' and 'count' keys
 */
function listeo_get_rating_display($post_id) {
	// Check for stale Google data and clean up if needed (max once per 24h per listing)
	$stale_check_key = 'stale_check_' . $post_id;
	$last_stale_check = get_transient($stale_check_key);
	
	if (!$last_stale_check) {
		$place_id = get_post_meta($post_id, '_place_id', true);
		if (empty($place_id)) {
			// Check if we have stale Google review data
			$google_rating = get_post_meta($post_id, '_google_rating', true);
			$google_count = get_post_meta($post_id, '_google_review_count', true);
			
			if (!empty($google_rating) || !empty($google_count)) {
				// Clean up stale Google data
				delete_post_meta($post_id, '_google_rating');
				delete_post_meta($post_id, '_google_review_count');
				delete_post_meta($post_id, '_google_last_updated');
				delete_transient('listeo_reviews_' . $post_id);
				
				// Force recalculate combined rating without Google data
				// Clear existing combined rating to ensure fresh calculation
				delete_post_meta($post_id, '_combined_rating');
				delete_post_meta($post_id, '_combined_review_count');
				
				$reviews_instance = Listeo_Core_Reviews::instance();
				if (method_exists($reviews_instance, 'get_combined_rating')) {
					$new_combined_rating = $reviews_instance->get_combined_rating($post_id);
				}
				
				// Log the cleanup for debugging
				
			}
		}
		
		// Set 24h transient to prevent frequent stale data checks
		set_transient($stale_check_key, time(), 24 * HOUR_IN_SECONDS);
	}
	
	// Try to get cached combined rating first
	$combined_rating = get_post_meta($post_id, '_combined_rating', true);
	$combined_count = get_post_meta($post_id, '_combined_review_count', true);
	
	// If combined rating exists (even if 0), use it
	if ($combined_rating !== '' && $combined_count !== '') {
		return array(
			'rating' => floatval($combined_rating),
			'count' => intval($combined_count)
		);
	}
	
	// Fallback: Calculate combined rating on the fly
	$reviews_instance = Listeo_Core_Reviews::instance();
	if (method_exists($reviews_instance, 'get_combined_rating')) {
		$rating = $reviews_instance->get_combined_rating($post_id);
		$count = intval(get_post_meta($post_id, '_combined_review_count', true));
		
		return array(
			'rating' => $rating,
			'count' => $count
		);
	}
	
	// Final fallback: Use existing Listeo rating logic
	$rating = get_post_meta($post_id, 'listeo-avg-rating', true);
	$comments_count = wp_count_comments($post_id);
	$count = intval($comments_count->approved);
	
	// If no local reviews but Google reviews are enabled, try Google rating
	if (empty($rating) && get_option('listeo_google_reviews_instead')) {
		$google_rating = get_post_meta($post_id, '_google_rating', true);
		$google_count = get_post_meta($post_id, '_google_review_count', true);
		if (!empty($google_rating)) {
			$rating = $google_rating;
			$count = intval($google_count);
		}
	}
	
	return array(
		'rating' => floatval($rating),
		'count' => $count
	);
}

/**
 * Checks if the user can edit a listing.
 */
function listeo_core_if_can_edit_listing($listing_id)
{
	$can_edit = true;

	if (!is_user_logged_in() || !$listing_id) {
		$can_edit = false;
	} else {
		$listing      = get_post($listing_id);

		if (!$listing || (absint($listing->post_author) !== get_current_user_id())) {
			$can_edit = false;
		}
	}

	return apply_filters('listeo_core_if_can_edit_listing', $can_edit, $listing_id);
}



//&& ! current_user_can( 'edit_post', $listing_id )


add_filter('submit_listing_form_submit_button_text', 'listeo_core_rename_button_no_preview');

function listeo_core_rename_button_no_preview()
{
	if (get_option('listeo_new_listing_preview')) {
		return  __('Submit', 'listeo_core');
	} else {
		return  __('Preview', 'listeo_core');
	}
}

function get_listeo_core_placeholder_image()
{
	$image_id = get_option('listeo_placeholder_id');

	if ($image_id) {
		//$placeholder = wp_get_attachment_image_src($image_id,'listeo-listing-grid');
		return $image_id;
	} else {
		return  plugin_dir_url(__FILE__) . "templates/images/listeo_placeholder.png";
	}
}


function listeo_is_rated()
{
	return true;
}




function listeo_count_user_comments($args = array())
{
	global $wpdb;
	$default_args = array(
		'author_id' => 1,
		'approved' => 1,
		'author_email' => '',
	);

	$param = wp_parse_args($args, $default_args);

	$sql = $wpdb->prepare(
		"SELECT COUNT(comments.comment_ID) 
            FROM {$wpdb->comments} AS comments 
            LEFT JOIN {$wpdb->posts} AS posts
            ON comments.comment_post_ID = posts.ID
            WHERE posts.post_author = %d
            AND comment_approved = %d
            AND comment_author_email NOT IN (%s)
            AND comment_type IN ('comment', '')",
		$param
	);

	return $wpdb->get_var($sql);
}





	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @since astrum 1.0
	 */
	function listeo_comment_review($comment, $args, $depth)
	{
		$GLOBALS['comment'] = $comment;
		global $post;

		switch ($comment->comment_type):
			case 'pingback':
			case 'trackback':
?>
				<li class="post pingback">
					<p><?php esc_html_e('Pingback:', 'listeo_core'); ?> <?php comment_author_link(); ?><?php edit_comment_link(esc_html__('(Edit)', 'listeo'), ' '); ?></p>
				<?php
				break;
			default:
				$allowed_tags = wp_kses_allowed_html('post');
				$rating  = get_comment_meta(get_comment_ID(), 'listeo-rating', true);
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
					<div class="avatar"><?php echo get_avatar($comment, 70); ?></div>
					<div class="comment-content">
						<div class="arrow-comment"></div>

						<div class="comment-by">
						
							<?php if ($comment->user_id === $post->post_author) { ?>
								<h5><?php esc_html_e('Owner', 'listeo_core') ?></h5>
							<?php } else {
								printf('<h5>%s</h5>', get_comment_author_link());
							} ?>
							<span class="date"> <?php printf(esc_html__('%1$s at %2$s', 'listeo_core'), get_comment_date(), get_comment_time()); ?>

							</span>

							<div class="star-rating" data-rating="<?php echo esc_attr($rating); ?>"></div>
						</div>
						<?php comment_text(); ?>
						<?php
						$photos = get_comment_meta(get_comment_ID(), 'listeo-attachment-id', false);

						if ($photos) : ?>
							<div class="review-images mfp-gallery-container">
								<?php foreach ($photos as $key => $attachment_id) {

									$image = wp_get_attachment_image_src($attachment_id, 'listeo-gallery');
									$image_thumb = wp_get_attachment_image_src($attachment_id, 'thumbnail');

								?>
									<a href="<?php echo esc_attr($image[0]); ?>" class="mfp-gallery"><img src="<?php echo esc_attr($image_thumb[0]); ?>" alt=""></a>
								<?php } ?>
							</div>
						<?php endif; ?>
						<?php $review_rating = get_comment_meta(get_comment_ID(), 'listeo-review-rating', true); ?>
						<a href="#" id="review-<?php comment_ID(); ?>" data-comment="<?php comment_ID(); ?>" class="rate-review listeo_core-rate-review"><i class="sl sl-icon-like"></i> <?php esc_html_e('Helpful Review ', 'listeo_core'); ?><?php if ($review_rating) {
																																																													echo "<span>" . $review_rating . "</span>";
																																																												} ?></a>
					</div>

		<?php
				break;
		endswitch;
	}


function listeo_get_days()
{
	$start_of_week = intval(get_option('start_of_week')); // 0 - sunday, 1- monday

	$days = array(
		'monday'	=> __('Monday', 'listeo_core'),
		'tuesday' 	=> __('Tuesday', 'listeo_core'),
		'wednesday' => __('Wednesday', 'listeo_core'),
		'thursday' 	=> __('Thursday', 'listeo_core'),
		'friday' 	=> __('Friday', 'listeo_core'),
		'saturday' 	=> __('Saturday', 'listeo_core'),
		'sunday' 	=> __('Sunday', 'listeo_core'),
	);

	if ($start_of_week == 0) {

		$sun['sunday'] = __('Sunday', 'listeo_core');
		$days = $sun + $days;
	}
	return apply_filters('listeo_days_array', $days);
}

function listeo_top_comments_only($clauses)
{
	$clauses['where'] .= ' AND comment_parent = 0';
	return $clauses;
}

function listeo_check_if_review_replied($comment_id, $user_id)
{

	$author_replies_args = array(
		'user_id' => $user_id,
		'parent'  => $comment_id
	);
	$author_replies = get_comments($author_replies_args);
	return (empty($author_replies)) ? false : true;
}
function listeo_get_review_reply($comment_id, $user_id)
{

	$author_replies_args = array(
		'user_id' => $user_id,
		'parent'  => $comment_id
	);
	$author_replies = get_comments($author_replies_args);
	return $author_replies;
}


function listeo_check_if_open($post = '')
{

	$status = false;
	$has_hours = false;
	if (empty($post)) {
		global $post;
	}



	

	$days = listeo_get_days();
	$storeSchedule = array();
	foreach ($days as $d_key => $value) {
		$open_val = get_post_meta($post->ID, '_' . $d_key . '_opening_hour', true);

		$opening = ($open_val) ? $open_val : '';
		$clos_val = get_post_meta($post->ID, '_' . $d_key . '_closing_hour', true);
		$closing = ($clos_val) ? $clos_val : '';



		$storeSchedule[$d_key] = array(
			'opens' => $opening,
			'closes' => $closing
		);
	}

	$clock_format = get_option('listeo_clock_format');

	//get current  time
	$meta_timezone =  get_post_meta($post->ID, '_listing_timezone', true);


	// $timezone = (!empty($meta_timezone)) ? $meta_timezone : listeo_get_timezone() ;
	// $timeObject = new DateTime(null, $timezone);
	// echo date("H:i:s l").'<br>'; 
	// echo current_time("H:i:s l").'<br>'; 

	if (empty($meta_timezone)) {

		$timeObject = new DateTime('now', listeo_get_timezone());
		$timestamp 		= $timeObject->getTimeStamp();
		$currentTime 	= $timeObject->setTimestamp($timestamp)->format('Hi');
	} else {

		if (substr($meta_timezone, 0, 3) == "UTC") {
			$offset =  substr($meta_timezone, 3);
			$meta_timezone = str_replace('UTC', 'Etc/GMT', $meta_timezone);
			if (0 == $offset) {
			} elseif ($offset < 0) {
				$meta_timezone = str_replace('-', '+', $meta_timezone);
			} else {
				$meta_timezone = str_replace('+', '-', $meta_timezone);
			}
		}


		date_default_timezone_set($meta_timezone);
		$timeObject = new DateTime();
		$timestamp 		= $timeObject->getTimeStamp();
		$currentTime 	= $timeObject->setTimestamp($timestamp)->format('Hi');
		// echo $currentTime;
	}

	// $now = new DateTime(null, new DateTimeZone('Europe/Warsaw'));
	// echo $now->format("H:i O");  echo "<br/>";


	if (isset($storeSchedule[lcfirst(date('l', $timestamp))])) :


		$day = ($storeSchedule[lcfirst(date('l', $timestamp))]);

		$startTime = $day['opens'];
		$endTime = $day['closes'];
		if (is_array($startTime)) {
			foreach ($startTime as $key => $start_time) {
				# code...
				$end_time = $endTime[$key];
				if (!empty($start_time) && is_numeric(substr($start_time, 0, 1))) {
					if (substr($start_time, -1) == 'M') {


						$start_time = DateTime::createFromFormat('h:i A', $start_time);
						if ($start_time) {
							$start_time = $start_time->format('Hi');
						}

						//
					} else {
						$start_time = DateTime::createFromFormat('H:i', $start_time);
						if ($start_time) {
							$start_time = $start_time->format('Hi');
						}
					}
				}
				//create time objects from start/end times and format as string (24hr AM/PM)
				if (!empty($end_time)  && is_numeric(substr($end_time, 0, 1))) {
					if (substr($end_time, -1) == 'M') {
						$end_time = DateTime::createFromFormat('h:i A', $end_time);
						if ($end_time) {
							$end_time = $end_time->format('Hi');
						}
					} else {
						$end_time = DateTime::createFromFormat('H:i', $end_time);
						if ($end_time) {
							$end_time = $end_time->format('Hi');
						}
					}
				}

				if ($end_time == '0000') {
					$end_time = 2400;
				}

				if ((int)$start_time > (int)$end_time) {
					// midnight situation
					$end_time = 2400 + (int)$end_time;
				}


				// check if current time is within the range
				if (((int)$start_time < (int)$currentTime) && ((int)$currentTime < (int)$end_time)) {
					return TRUE;
				}
			}
		} else {

			//backward compatibilty
			if (!empty($startTime) && is_numeric(substr($startTime, 0, 1))) {
				if (substr($startTime, -1) == 'M') {
					$startTime = DateTime::createFromFormat('h:i A', $startTime)->format('Hi');
				} else {
					$startTime = DateTime::createFromFormat('H:i', $startTime)->format('Hi');
				}
			}
			//create time objects from start/end times and format as string (24hr AM/PM)
			if (!empty($endTime)  && is_numeric(substr($endTime, 0, 1))) {
				if (substr($endTime, -1) == 'M') {
					$endTime = DateTime::createFromFormat('h:i A', $endTime)->format('Hi');
				} else {
					$endTime = DateTime::createFromFormat('H:i', $endTime)->format('Hi');
				}
			}
			if ($endTime == '0000') {
				$endTime = 2400;
			}

			if ((int)$startTime > (int)$endTime) {
				// midnight situation
				$endTime = 2400 + (int)$endTime;
			}

			// check if current time is within the range
			if (((int)$startTime < (int)$currentTime) && ((int)$currentTime < (int)$endTime)) {
				return TRUE;
			}
		}


	endif;

	if ($status == false) {

		if (isset($storeSchedule[lcfirst(date('l', strtotime('-1 day', $timestamp)))])) :

			$day = ($storeSchedule[lcfirst(date('l', (strtotime('-1 day', $timestamp))))]);

			$startTime = $day['opens'];
			$endTime = $day['closes'];

			if (is_array($startTime)) {
				foreach ($startTime as $key => $start_time) {

					# code...
					$end_time = $endTime[$key];
					//backward
					if (!empty($start_time) && is_numeric(substr($start_time, 0, 1))) {
						
						if (substr($start_time, -1) == 'M') {
							$start_time = DateTime::createFromFormat('h:i A', $start_time);
							if ($start_time) {
								$start_time = $start_time->format('Hi');
							}
						} else {
							$start_time = DateTime::createFromFormat('H:i', $start_time);

							if ($start_time) {
								$start_time = $start_time->format('Hi');
							}
						}
					}
					//create time objects from start/end times and format as string (24hr AM/PM)
					if (!empty($end_time)  && is_numeric(substr($end_time, 0, 1))) {
						if (substr($end_time, -1) == 'M') {
							$end_time = DateTime::createFromFormat('h:i A', $end_time);
							if ($end_time) {
								$end_time = $end_time->format('Hi');
							}
						} else {
							$end_time = DateTime::createFromFormat('H:i', $end_time);
							if ($end_time) {
								$end_time = $end_time->format('Hi');
							}
						}
					}


					if (((int)$start_time > (int)$end_time) && (int)$currentTime < (int)$end_time) {
						return TRUE;
					}
				}
			} else {

				//backward
				if (!empty($startTime) && !is_array($startTime) && is_numeric(substr($startTime, 0, 1))) {
					if (substr($startTime, -1) == 'M') {
						$startTime = DateTime::createFromFormat('h:i A', $startTime)->format('Hi');
					} else {
						$startTime = DateTime::createFromFormat('H:i', $startTime)->format('Hi');
					}
				}
				//create time objects from start/end times and format as string (24hr AM/PM)
				if (!empty($endTime) && !is_array($endTime) && is_numeric(substr($endTime, 0, 1))) {
					if (substr($endTime, -1) == 'M') {
						$endTime = DateTime::createFromFormat('h:i A', $endTime)->format('Hi');
					} else {
						$endTime = DateTime::createFromFormat('H:i', $endTime)->format('Hi');
					}
				}
				if (((int)$startTime > (int)$endTime) && (int)$currentTime < (int)$endTime) {
					$status = TRUE;
				}
			}



		endif;
	}
	return $status;
}


function listeo_get_timezone()
{

	$tzstring = get_option('timezone_string');
	$offset   = get_option('gmt_offset');

	//Manual offset...
	//@see http://us.php.net/manual/en/timezones.others.php
	//@see https://bugs.php.net/bug.php?id=45543
	//@see https://bugs.php.net/bug.php?id=45528
	//IANA timezone database that provides PHP's timezone support uses POSIX (i.e. reversed) style signs
	if (empty($tzstring) && 0 != $offset && floor($offset) == $offset) {
		$offset_st = $offset > 0 ? "-$offset" : '+' . absint($offset);
		$tzstring  = 'Etc/GMT' . $offset_st;
	}

	//Issue with the timezone selected, set to 'UTC'
	if (empty($tzstring)) {
		$tzstring = 'UTC';
	}

	$timezone = new DateTimeZone($tzstring);
	return $timezone;
}


function listeo_check_if_has_hours()
{
	$status = false;
	$has_hours = false;
	global $post;
	$days = listeo_get_days();
	$storeSchedule = array();
	foreach ($days as $d_key => $value) {
		$open_val = get_post_meta($post->ID, '_' . $d_key . '_opening_hour', true);
		if (is_array($open_val)) {

			if (!empty($open_val)) {
				$has_hours = true;
			}
		} else {

			$opening = ($open_val) ? $open_val : '';
			if (is_numeric(substr($opening, 0, 1))) {
				$has_hours = true;
			}
		}

		// $clos_val = get_post_meta($post->ID, '_'.$d_key.'_closing_hour', true);
		// $closing = ($clos_val) ? $clos_val : '';

		// if(is_numeric(substr($opening, 0, 1))) {
		// 	$has_hours = true;
		// }
		// $storeSchedule[$d_key] = array(
		// 	'opens' => $opening,
		// 	'closes' => $closing
		// );
	}

	return $has_hours;
}

// function listeo_check_if_open(){

// 	$status = false;
// 	$has_hours = false;
// 	global $post;
// 	$days = listeo_get_days();
// 	$storeSchedule = array();
// 	foreach ($days as $d_key => $value) {
// 		$open_val = get_post_meta($post->ID, '_'.$d_key.'_opening_hour', true);
// 		$opening = ($open_val) ? $open_val : '' ;
// 		$clos_val = get_post_meta($post->ID, '_'.$d_key.'_closing_hour', true);
// 		$closing = ($clos_val) ? $clos_val : '';
// 		if(is_numeric(substr($opening, 0, 1))) {
// 			$has_hours = true;
// 		}
// 		$storeSchedule[$d_key] = array(
// 			'opens' => $opening,
// 			'closes' => $closing
// 		);
// 	}

// 	if(!$has_hours){
// 		return;
// 	}

//     //get current East Coast US time
//     $timeObject = new DateTime();
//     $timestamp 		= $timeObject->getTimeStamp();
//     $currentTime 	= $timeObject->setTimestamp($timestamp)->format('H:i A');
//     $timezone		= get_option('timezone_string');

// 	if(isset($storeSchedule[lcfirst(date('l', $timestamp))])) :
// 		$day = ($storeSchedule[lcfirst(date('l', $timestamp))]);
// 		$startTime = $day['opens'];
// 		$endTime = $day['closes'];

// 		if(!empty($startTime) && is_numeric(substr($startTime, 0, 1)) ) {
// 	 			$startTime = DateTime::createFromFormat('h:i A', $startTime)->format('H:i A');	

// 	 	} 
// 	        //create time objects from start/end times and format as string (24hr AM/PM)
//         if(!empty($endTime)  && is_numeric(substr($endTime, 0, 1))){
//          	$endTime = DateTime::createFromFormat('h:i A', $endTime)->format('H:i A');	
//         }

//         // check if current time is within the range
//         if (($startTime < $currentTime) && ($currentTime < $endTime)) {
//             $status = TRUE;

//         }
// 	endif;
//    return $status;

// }


function listeo_get_geo_data($post)
{
	$terms = get_the_terms($post->ID, 'listing_category');

	if ($terms) {
		$term = array_pop($terms);

		$t_id = $term->term_id;
		// retrieve the existing value(s) for this meta field. This returns an array
		$icon = get_term_meta($t_id, 'icon', true);
		if ($icon) {
			$icon = '<i class="' . $icon . '"></i>';
		}
	}
	if (is_tax('listing-category')) {
		$term = get_queried_object();
		$t_id = $term->term_id;
		// retrieve the existing value(s) for this meta field. This returns an array
		$icon = get_term_meta($t_id, 'icon', true);
		if ($icon) {
			$icon = '<i class="' . $icon . '"></i>';
		}
	}
	if (isset($t_id)) {
		$_icon_svg = get_term_meta($t_id, '_icon_svg', true);
		$_icon_svg_image = wp_get_attachment_image_src($_icon_svg, 'medium');
	}
	if (isset($_icon_svg_image) && !empty($_icon_svg_image)) {
		$icon = listeo_render_svg_icon($_icon_svg);
		//$icon = '<img class="listeo-map-svg-icon" src="'.$_icon_svg_image[0].'"/>';


	} else {

		if (empty($icon)) {
			$icon = get_post_meta($post->ID, '_icon', true);
		}

		if (empty($icon)) {
			$icon = '<i class="sl sl-icon-location"></i>';
		}
	}

	$listing_type = get_post_meta($post->ID, '_listing_type', true);

	$disable_address = get_option('listeo_disable_address');
	$latitude = get_post_meta($post->ID, '_geolocation_lat', true);
	$longitude = get_post_meta($post->ID, '_geolocation_long', true);
	if (!empty($latitude) && $disable_address) {
		$dither = 0.001;
		$latitude = (float) $latitude + (rand(5, 15) - 0.5) * $dither;
	}

	// Use the new combined rating display function
	$rating_data = listeo_get_rating_display($post->ID);
	$rating = esc_attr($rating_data['rating']);
	$reviews = $rating_data['count'];
	
		$currency_abbr = get_option('listeo_currency');
		$currency_postion = get_option('listeo_currency_postion');
		$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
	ob_start(); ?>

		data-title="<?php the_title(); ?>"
		data-listing-type="<?php echo esc_attr($listing_type); ?>"
		data-classifieds-price="<?php if ($currency_postion == "before") {
									echo $currency_symbol;
								} echo esc_attr(get_post_meta($post->ID, '_classifieds_price', true));
								if ($currency_postion == "after") {
									echo $currency_symbol;
								} ?>"
		data-friendly-address="<?php echo esc_attr(get_post_meta($post->ID, '_friendly_address', true)); ?>"
		data-address="<?php the_listing_address(); ?>"
		data-image="<?php echo listeo_core_get_listing_image($post->ID); ?>"
		data-longitude="<?php echo esc_attr($latitude); ?>"
		data-latitude="<?php echo esc_attr($longitude); ?>"
		<?php if (!get_option('listeo_disable_reviews')) { ?>
			data-rating="<?php echo $rating ?>"
			data-reviews="<?php echo esc_attr($reviews); ?>"
		<?php } ?>
		data-icon="<?php echo esc_attr($icon); ?>"

	<?php
	return ob_get_clean();
}

function listeo_get_unread_counter()
{
	$user_id = get_current_user_id();
	global $wpdb;

	$result_1  = $wpdb->get_var("
        SELECT COUNT(*) FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  user_1 = '$user_id' AND read_user_1 = 0  AND user_1 != user_2
        ");
	$result_2  = $wpdb->get_var("
        SELECT COUNT(*) FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  user_2 = '$user_id' AND read_user_2 = 0  AND user_1 != user_2
        ");
	return $result_1 + $result_2;
}


function listeo_count_posts_by_user($post_author = null, $post_type = array(), $post_status = array())
{
	global $wpdb;

	if (empty($post_author))
		return 0;

	$post_status = (array) $post_status;
	$post_type = (array) $post_type;

	$sql = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = %d AND ", $post_author);

	//Post status
	if (!empty($post_status)) {
		$argtype = array_fill(0, count($post_status), '%s');
		$where = "(post_status=" . implode(" OR post_status=", $argtype) . ') AND ';
		$sql .= $wpdb->prepare($where, $post_status);
	}

	//Post type
	if (!empty($post_type)) {
		$argtype = array_fill(0, count($post_type), '%s');
		$where = "(post_type=" . implode(" OR post_type=", $argtype) . ') AND ';
		$sql .= $wpdb->prepare($where, $post_type);
	}

	$sql .= '1=1';
	$count = $wpdb->get_var($sql);
	return $count;
}

function listeo_count_gallery_items($post_id)
{
	if (!$post_id) {
		return;
	}

	$gallery = get_post_meta($post_id, '_gallery', true);

	if (is_array($gallery)) {
		return sizeof($gallery);
	} else {
		return 0;
	}
}

function listeo_get_reviews_number($post_id = 0)
{

	global $wpdb, $post;

	$post_id = $post_id ? $post_id : $post->ID;

	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_parent = 0 AND comment_post_ID = $post_id AND comment_approved = 1");
}

function listeo_count_bookings($user_id, $status, $bookings_author = '')
{
	global $wpdb;
	if ($status == 'approved') {
		$status_sql = "AND status IN ('confirmed','paid')";
	} else if ($status == 'waiting') {
		$status_sql = "AND status IN ('waiting','pay_to_confirm')";
	} else {
		$status_sql = $wpdb->prepare("AND status = %s", sanitize_text_field($status));
	}
	if (!empty($bookings_author)) {
		$status_sql .= $wpdb->prepare("AND bookings_author = %s", sanitize_text_field($bookings_author));
	}
	$sql = "
		SELECT * FROM `{$wpdb->prefix}bookings_calendar`
		WHERE owner_id = %d
		{$status_sql}
	";

	$result = $wpdb->get_results(
		$wpdb->prepare($sql, $user_id),
		"ARRAY_A"
	);
	return $wpdb->num_rows;
}

function listeo_count_my_bookings($user_id)
{
	global $wpdb;
	$user_id = (int) $user_id;
	$sql = "
    SELECT * FROM `{$wpdb->prefix}bookings_calendar`
    WHERE NOT comment = 'owner reservations'
    AND bookings_author = %d
    AND type = 'reservation'
";

	$result = $wpdb->get_results(
		$wpdb->prepare($sql, $user_id),
		"ARRAY_A"
	);
	return $wpdb->num_rows;
}

function listeo_get_bookings_author($user_id)
{
	global $wpdb;

	$sql = $wpdb->prepare(
		"SELECT DISTINCT `bookings_author` 
		 FROM `{$wpdb->prefix}bookings_calendar` 
		 WHERE `owner_id` = %d",
		(int) $user_id
	);

	$result = $wpdb->get_results($sql, "ARRAY_N");
	return $result;
}


	function listeo_write_log($log)
	{
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}




	function listeo_get_bookable_services($post_id)
	{

		$services = array();

		$_menu = get_post_meta($post_id, '_menu', 1);
		if ($_menu) {
			foreach ($_menu as $menu) {

				if (isset($menu['menu_elements']) && !empty($menu['menu_elements'])) :
					foreach ($menu['menu_elements'] as $item) {
						if (isset($item['bookable'])) {

							$services[] = $item;
						}
					}
				endif;
			}
		}

		return $services;
	}



	/**
	 * Prepares files for upload by standardizing them into an array. This adds support for multiple file upload fields.
	 *
	 * @since 1.21.0
	 * @param  array $file_data
	 * @return array
	 */
	function listeo_prepare_uploaded_files($file_data)
	{
		$files_to_upload = array();

		if (is_array($file_data['name'])) {
			foreach ($file_data['name'] as $file_data_key => $file_data_value) {
				if ($file_data['name'][$file_data_key]) {
					$type              = wp_check_filetype($file_data['name'][$file_data_key]); // Map mime type to one WordPress recognises
					$files_to_upload[] = array(
						'name'     => $file_data['name'][$file_data_key],
						'type'     => $type['type'],
						'tmp_name' => $file_data['tmp_name'][$file_data_key],
						'error'    => $file_data['error'][$file_data_key],
						'size'     => $file_data['size'][$file_data_key]
					);
				}
			}
		} else {
			$type              = wp_check_filetype($file_data['name']); // Map mime type to one WordPress recognises
			$file_data['type'] = $type['type'];
			$files_to_upload[] = $file_data;
		}

		return apply_filters('listeo_prepare_uploaded_files', $files_to_upload);
	}


	/**
	 * Uploads a file using WordPress file API.
	 *
	 * @since 1.21.0
	 * @param  array|WP_Error      $file Array of $_FILE data to upload.
	 * @param  string|array|object $args Optional arguments
	 * @return stdClass|WP_Error Object containing file information, or error
	 */
	function listeo_upload_file($file, $args = array())
	{
		global $listeo_upload, $listeo_uploading_file;

		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/media.php');

		$args = wp_parse_args($args, array(
			'file_key'           => '',
			'file_label'         => '',
			'allowed_mime_types' => '',
		));

		$listeo_upload         = true;
		$listeo_uploading_file = $args['file_key'];
		$uploaded_file              = new stdClass();

		$allowed_mime_types = $args['allowed_mime_types'];


		/**
		 * Filter file configuration before upload
		 *
		 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
		 * object to prevent the file from being uploaded, and return the error.
		 *
		 * @since 1.25.2
		 *
		 * @param array $file               Array of $_FILE data to upload.
		 * @param array $args               Optional file arguments
		 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults
		 */
		$file = apply_filters('listeo_upload_file_pre_upload', $file, $args, $allowed_mime_types);

		if (is_wp_error($file)) {
			return $file;
		}

		if (!in_array($file['type'], $allowed_mime_types)) {
			if ($args['file_label']) {
				return new WP_Error('upload', sprintf(__('"%s" (filetype %s) needs to be one of the following file types: %s', 'listeo_core'), $args['file_label'], $file['type'], implode(', ', array_keys($allowed_mime_types))));
			} else {
				return new WP_Error('upload', sprintf(__('Uploaded files need to be one of the following file types: %s', 'listeo_core'), implode(', ', array_keys($allowed_mime_types))));
			}
		} else {
			$upload = wp_handle_upload($file, apply_filters('submit_property_wp_handle_upload_overrides', array('test_form' => false)));
			if (!empty($upload['error'])) {
				return new WP_Error('upload', $upload['error']);
			} else {
				$uploaded_file->url       = $upload['url'];
				$uploaded_file->file      = $upload['file'];
				$uploaded_file->name      = basename($upload['file']);
				$uploaded_file->type      = $upload['type'];
				$uploaded_file->size      = $file['size'];
				$uploaded_file->extension = substr(strrchr($uploaded_file->name, '.'), 1);
			}
		}

		$listeo_upload         = false;
		$listeo_uploading_file = '';

		return $uploaded_file;
	}



	/**
	 * Returns mime types specifically for WPJM.
	 *
	 * @since 1.25.1
	 * @param   string $field Field used.
	 * @return  array  Array of allowed mime types
	 */
	function listeo_get_allowed_mime_types($field = '')
	{

		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'pdf'          => 'application/pdf',
			'doc'          => 'application/msword',
			'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'mp4'          => 'video/mp4',
			'avi'          => 'video/avi',
			'mov'          => 'video/quicktime',
		);


		/**
		 * Mime types to accept in uploaded files.
		 *
		 * Default is image, pdf, and doc(x) files.
		 *
		 * @since 1.25.1
		 *
		 * @param array  {
		 *     Array of allowed file extensions and mime types.
		 *     Key is pipe-separated file extensions. Value is mime type.
		 * }
		 * @param string $field The field key for the upload.
		 */
		return apply_filters('listeo_mime_types', $allowed_mime_types, $field);
	}


	//listeo_fields_for_cmb2


	
		function listeo_date_to_cal($timestamp)
		{
			return date('Ymd\THis\Z', $timestamp);
		}
	

	
		function listeo_escape_string($string)
		{
			return preg_replace('/([\,;])/', '\\\$1', $string);
		}
	

	function listeo_calculate_service_price($service, $adults, $children,  $children_discount, $days, $countable)
	{
	
		if (isset($service['bookable_options'])) {
			switch ($service['bookable_options']) {
				case 'onetime':
					$price = $service['price'];
					break;
				case 'byguest':
					$price_adults = $service['price'] * (int) $adults;
					$price_children =  $service['price'] * (1 - ((int)$children_discount/100));

					$price = $price_adults + ($price_children * (int) $children);
					break;
				case 'bydays':
					$price = $service['price'] * (int) $days;
					break;
				case 'byguestanddays':
					$price_adults = $service['price'] * (int) $days * (int) $adults;
					$price_children =  $service['price'] * (1 - ((int)$children_discount/100));
					$price = $price_adults + ($price_children * (int) $days * (int) $children);
					break;
				default:
					$price = $service['price'];
					break;
			}

			return (float) $price * (int)$countable;
		} else {
			return (float) $service['price'] * (int)$countable;
		}
	}

	function listeo_get_extra_services_html($arr)
	{
		$output = '';
		if (is_array($arr)) {
			$output .= '<ul class="listeo_booked_services_list">';
			$currency_abbr = get_option('listeo_currency');
			$currency_postion = get_option('listeo_currency_postion');
			$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

			foreach ($arr as $key => $booked_service) {

				$price = esc_html__('Free', 'listeo_core');
				if (isset($booked_service->price)) {
					if ($booked_service->price == 0) {
						$price = esc_html__('Free', 'listeo_core');
					} else {
						$price = '';
						if ($currency_postion == 'before') {
							$price .= $currency_symbol . ' ';
						}
						$price .= $booked_service->price;
						if ($currency_postion == 'after') {
							$price .= ' ' . $currency_symbol;
						}
					}
				}

				$output .= '<li>' . $booked_service->service->name;
				if (isset($booked_service->countable) && $booked_service->countable > 1) {
					$output .= 	' <em>(*' . $booked_service->countable . ')</em>';
				}

				$output .=  ' <span class="services-list-price-tag">' . $price . '</span></li>';

				# code...
			}
			$output .= '</ul>';
			return $output;
		} else {
			return wpautop($arr);
		}
	}

	function listeo_get_users_name($user_id = null)
	{

		$user_info = $user_id ? new WP_User($user_id) : wp_get_current_user();
		if (!empty($user_info->display_name)) {
			return $user_info->display_name;
		}
		if ($user_info->first_name) {

			if ($user_info->last_name) {
				return $user_info->first_name . ' ' . $user_info->last_name;
			}

			return $user_info->first_name;
		}
		if (!empty($user_info->display_name)) {
			return $user_info->display_name;
		} else {
			return $user_info->user_login;
		}
	}

	/**
	 * @param mixed $role 
	 * @return string|false 
	 */
	function listeo_get_extra_registration_fields($role)
	{
		if ($role == 'owner' || $role == 'vendor') {
			$fields = get_option('listeo_owner_registration_form');
		} else {
			$fields = get_option('listeo_guest_registration_form');
		}
		if (!empty($fields)) {

			ob_start();
		?>
			<div id="listeo-core-registration-<?php echo esc_attr($role); ?>-fields">
				<?php
				foreach ($fields as $key => $field) :

					if ($field['type'] == 'header') { ?>
						<h4 class="listeo_core-registration-separator"><?php esc_html_e($field['placeholder']) ?></h4>
					<?php }
					$field['value'] = false;
					if ($field['type'] == 'file') { ?>
						<h4 class="listeo_core-registration-file_label"><?php esc_html_e($field['placeholder']) ?></h4>
					<?php }

					$template_loader = new Listeo_Core_Template_Loader;

					// fix the name/id mistmatch
					if (isset($field['id'])) {
						$field['name'] = $field['id'];
					}
					// $field['label'] = $field['placeholder'];
					$field['form_type'] = 'registration';

					if ($field['type'] == 'select_multiple') {

						$field['type'] = 'select';
						$field['multi'] = 'on';
						$field['placeholder'] = '';
					}
					if ($field['type'] == 'multicheck_split') {

						$field['type'] = 'checkboxes';
					}
					if ($field['type'] == 'wp-editor') {
						$field['type'] = 'textarea';
					}


					$has_icon = false;
					if (!in_array($field['type'], array('checkbox', 'select', 'select_multiple')) && isset($field['icon']) && $field['icon'] != 'empty') {
						$has_icon = true;
					}
					?>
					<label class="<?php if (!$has_icon) {
										echo "field-no-icon";
									} ?> listeo-registration-custom-<?php echo esc_attr($field['type']); ?>" id="listeo-registration-custom-<?php echo esc_attr($key); ?>" for="<?php echo esc_attr($key); ?>">

						<?php

						if ($has_icon) { ?>

							<i class="<?php echo esc_attr($field['icon']); ?>"></i><?php
																				}

																				$template_loader->set_template_data(array('key' => $key, 'field' => $field,))->get_template_part('form-fields/' . $field['type']);
																				$has_icon = false;
																					?>

					</label>
				<?php
				endforeach; ?>
			</div>
		<?php return ob_get_clean();
		} else {
			return false;
		}
	}

	function listeo_get_extra_booking_fields($type)
	{

		$fields = get_option("listeo_{$type}_booking_fields");
		if (!empty($fields)) {

			ob_start();
		?>
			<div id="listeo-core-booking-fields-<?php echo esc_attr($type); ?>-fields">
				<?php
				foreach ($fields as $key => $field) :

					if ($field['type'] == 'header') {
				?>
						<div class="col-md-12">
							<h3 class="margin-top-20 margin-bottom-20"><?php esc_html_e($field['label']) ?></h3>
						</div>
					<?php } else {

						$field['value'] = false;


						$template_loader = new Listeo_Core_Template_Loader;

						// fix the name/id mistmatch
						if (isset($field['id'])) {
							$field['name'] = $field['id'];
						}

						if ($field['type'] == 'select_multiple') {

							$field['type'] = 'select';
							$field['multi'] = 'on';
							$field['placeholder'] = '';
						}
						if ($field['type'] == 'multicheck_split') {

							$field['type'] = 'checkboxes';
						}
						if ($field['type'] == 'wp-editor') {
							$field['type'] = 'textarea';
						}


						$has_icon = false;
						if (!in_array($field['type'], array('checkbox', 'select', 'select_multiple')) && isset($field['icon']) && $field['icon'] != 'empty') {
							$has_icon = true;
						}
						$width = (!empty($field['width'])) ? $field['width'] : 'col-md-6';
						$css_class = (!empty($field['css'])) ? $field['css'] : '';
					?>
						<div class="<?php echo $width . ' ' . $css_class; ?>">
							<?php if ($has_icon) { ?><div class="input-with-icon medium-icons"><?php } ?>
								<label class="listeo-booking-custom-<?php echo esc_attr($field['type']); ?>" id="listeo-booking-custom-<?php echo esc_attr($key); ?>" for="<?php echo esc_attr($key); ?>">
									<?php
									// remove slash before appostrophe
									echo stripslashes($field['label']);
									
									if(isset($field['required']) &&  !empty($field['required']))  {
										echo '<i class="fas fa-asterisk"></i>';
									}
									
									?></label><?php
												$template_loader->set_template_data(array('key' => $key, 'field' => $field,))->get_template_part('form-fields/' . $field['type']);
												if ($has_icon) { ?>
									<i class="<?php echo esc_attr($field['icon']); ?>"></i><?php } ?>

								<?php if ($has_icon) { ?>
								</div><?php } ?>
						</div>
				<?php
					}


				endforeach; ?>
			</div>
			<?php return ob_get_clean();
		} else {
			return false;
		}
	}

	/** @return void  */
	function workscout_b472b0_admin_notice()
	{

		$activation_date = get_option('listeo_activation_date');

		$db_option = get_option('listeo_core_db_version');


		if (empty($activation_date)) {
			if ($db_option && version_compare($db_option, '1.5.18', '<=')) {
				update_option('listeo_activation_date', time());
				$activation_date = time();
				update_option('listeo_core_db_version', '1.5.19');
			}
		}
		$current_time = time();
		$time_diff = ($current_time - $activation_date) / 86400;

		if ($time_diff > 4) {



			$licenseKey   = get_option("Listeo_lic_Key", "");
			$liceEmail    = get_option("Listeo_lic_email", "");

			$templateDir  = get_template_directory(); //or dirname(__FILE__);

			$show_message = false;

			if (class_exists("b472b0Base") && empty($licenseKey) && b472b0Base::CheckWPPlugin($licenseKey, $liceEmail, $licenseMessage, $responseObj, $templateDir . "/style.css")) {

				ob_start();

			?>
				<div class="license-validation-popup license-nulled">
					<p>Hi, it seems you are using unlicensed version of Listeo!</p>
					<ul>
						<li>Nulled software may contain malware.</li>
						<li>Malicious code can steal informations from your website.</li>
						<li>A nulled version can add spammy links and malicious redirects to your websites. Search engines penalize this kind of activity.</li>
						<li>Denied udpates. You can't update a nulled Listeo.</li>
						<li>No Support. You won't get support from us if you run in any problems with your site. And <a class="link" href="https://themeforest.net/item/listeo-directory-listings-wordpress-theme/reviews/23239259?utf8=%E2%9C%93&reviews_controls%5Bsort%5D=ratings_descending">our Support is awesome</a>.</li>
						<li>Legal issues. Nulled plugins may involve the distribtuion of illegal material or data theft, leading to legal proceedings</li>
					</ul>
					<a style="zoom:1.3" href="https://bit.ly/3LyA4cp" class="nav-tab">Buy Legal License (One time Payment) &#8594;</a><br>
					<small>Buy legal version and get clean and tested code directly from the developer, your purchase will support ongoing improvements of Listeo</small>
				</div>

			<?php $html = ob_get_clean();
				echo $html;
			}
		}
	}
	//add_action('admin_notices', 'workscout_b472b0_admin_notice');



	function listeo_get_term_post_count($taxonomy = 'category', $term = '', $args = [])
	{
		// Lets first validate and sanitize our parameters, on failure, just return false
		if (!$term)
			return false;

		if ($term !== 'all') {
			if (!is_array($term)) {
				$term = filter_var($term, FILTER_VALIDATE_INT);
			} else {
				$term = filter_var_array($term, FILTER_VALIDATE_INT);
			}
		}

		if ($taxonomy !== 'category') {
			//$taxonomy = filter_var($taxonomy, FILTER_SANITIZE_STRING);
			if (!taxonomy_exists($taxonomy))
				return false;
		}

		if ($args) {
			if (!is_array)
				return false;
		}

		// Now that we have come this far, lets continue and wrap it up
		// Set our default args
		$defaults = [
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status' => 'publish',
			'post_type' => array('listing')
		];

		if ($term !== 'all') {
			$defaults['tax_query'] = [
				[
					'taxonomy' => $taxonomy,
					'terms'    => $term
				]
			];
		}

		$combined_args = wp_parse_args($args, $defaults);
		$q = new WP_Query($combined_args);

		// Return the post count
		return $q->found_posts;
	}


	if (!function_exists('dokan_store_category_menu')) :

		/**
		 * Store category menu for a store
		 *
		 * @param  int $seller_id
		 *
		 * @since 3.2.11 rewritten whole function
		 *
		 * @return void
		 */
		function dokan_store_category_menu($seller_id, $title = '')
		{
			?>
			<div id="cat-drop-stack" class="store-cat-stack-dokan">
				<?php
				$seller_id = empty($seller_id) ? get_query_var('author') : $seller_id;
				$vendor    = dokan()->vendor->get($seller_id);
				if ($vendor instanceof \WeDevs\Dokan\Vendor\Vendor) {
					$categories = $vendor->get_store_categories();
					$walker = new \WeDevs\Dokan\Walkers\StoreCategory($seller_id);
					echo '<ul>';
					echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, array())); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
					echo '</ul>';
				}
				?>
			</div>
	<?php
		}

	endif;



	// Booking meta


	/**
	 * Adds metadata for the specified object.
	 *
	 * @since 2.9.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $meta_type  Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
	 *                           or any other object type with an associated meta table.
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param bool   $unique     Optional. Whether the specified metadata key should be unique for the object.
	 *                           If true, and the object already has a value for the specified metadata key,
	 *                           no change will be made. Default false.
	 * @return int|false The meta ID on success, false on failure.
	 */
	function add_booking_meta($object_id, $meta_key, $meta_value, $unique = false)
	{
		global $wpdb;

		if (!$meta_key || !is_numeric($object_id)) {
			return false;
		}
		$meta_type = 'booking';
		$object_id = absint($object_id);
		if (!$object_id) {
			return false;
		}

		$table = $wpdb->prefix . 'bookings_meta';

		// expected_slashed ($meta_key)
		$meta_key   = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);
		//	$meta_value = sanitize_meta($meta_key, $meta_value);



		if ($unique && $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE meta_key = %s AND 'booking_id' = %d",
				$meta_key,
				$object_id
			)
		)) {
			return false;
		}

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize($meta_value);

		$result = $wpdb->insert(
			$table,
			array(
				'booking_id'      => $object_id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			)
		);

		if (!$result) {
			return false;
		}

		$mid = (int) $wpdb->insert_id;

		wp_cache_delete($object_id, $meta_type . '_meta');

		return $mid;
	}


	function update_booking_meta($object_id, $meta_key, $meta_value, $prev_value = '')
	{
		global $wpdb;

		if (!$meta_key || !is_numeric($object_id)) {
			return false;
		}
		$meta_type = 'booking';

		$object_id = absint($object_id);
		if (!$object_id) {
			return false;
		}
		$table = $wpdb->prefix . 'bookings_meta';


		$column    = sanitize_key($meta_type . '_id');
		$id_column =  'meta_id';

		// expected_slashed ($meta_key)
		$raw_meta_key = $meta_key;
		$meta_key     = wp_unslash($meta_key);
		$passed_value = $meta_value;
		$meta_value   = wp_unslash($meta_value);
		//$meta_value   = sanitize_meta($meta_key, $meta_value);



		// Compare existing value to new value if no prev value given and the key exists only once.
		if (empty($prev_value)) {
			$old_value = get_metadata_raw($meta_type, $object_id, $meta_key);
			if (is_countable($old_value) && count($old_value) === 1) {
				if ($old_value[0] === $meta_value) {
					return false;
				}
			}
		}

		$meta_ids = $wpdb->get_col($wpdb->prepare("SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id));
		if (empty($meta_ids)) {
			return add_metadata($meta_type, $object_id, $raw_meta_key, $passed_value);
		}

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize($meta_value);

		$data  = compact('meta_value');
		$where = array(
			$column    => $object_id,
			'meta_key' => $meta_key,
		);

		if (!empty($prev_value)) {
			$prev_value          = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		foreach ($meta_ids as $meta_id) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook name, `$meta_type`, refers to the meta object type
			 * (post, comment, term, user, or any other type with an associated meta table).
			 *
			 * Possible hook names include:
			 *
			 *  - `update_post_meta`
			 *  - `update_comment_meta`
			 *  - `update_term_meta`
			 *  - `update_user_meta`
			 *
			 * @since 2.9.0
			 *
			 * @param int    $meta_id     ID of the metadata entry to update.
			 * @param int    $object_id   ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value.
			 */
			do_action("update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value);

			if ('post' === $meta_type) {
				/**
				 * Fires immediately before updating a post's metadata.
				 *
				 * @since 2.9.0
				 *
				 * @param int    $meta_id    ID of metadata entry to update.
				 * @param int    $object_id  Post ID.
				 * @param string $meta_key   Metadata key.
				 * @param mixed  $meta_value Metadata value. This will be a PHP-serialized string representation of the value
				 *                           if the value is an array, an object, or itself a PHP-serialized string.
				 */
				do_action('update_postmeta', $meta_id, $object_id, $meta_key, $meta_value);
			}
		}

		$result = $wpdb->update($table, $data, $where);
		if (!$result) {
			return false;
		}

		wp_cache_delete($object_id, $meta_type . '_meta');

		foreach ($meta_ids as $meta_id) {
			/**
			 * Fires immediately after updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook name, `$meta_type`, refers to the meta object type
			 * (post, comment, term, user, or any other type with an associated meta table).
			 *
			 * Possible hook names include:
			 *
			 *  - `updated_post_meta`
			 *  - `updated_comment_meta`
			 *  - `updated_term_meta`
			 *  - `updated_user_meta`
			 *
			 * @since 2.9.0
			 *
			 * @param int    $meta_id     ID of updated metadata entry.
			 * @param int    $object_id   ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value.
			 */
			do_action("updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value);

			if ('post' === $meta_type) {
				/**
				 * Fires immediately after updating a post's metadata.
				 *
				 * @since 2.9.0
				 *
				 * @param int    $meta_id    ID of updated metadata entry.
				 * @param int    $object_id  Post ID.
				 * @param string $meta_key   Metadata key.
				 * @param mixed  $meta_value Metadata value. This will be a PHP-serialized string representation of the value
				 *                           if the value is an array, an object, or itself a PHP-serialized string.
				 */
				do_action('updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value);
			}
		}

		return true;
	}
	function get_booking_meta($booking_id, $meta_key = '')
	{
		$booking_id = (int) $booking_id;
		if ($booking_id <= 0) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'bookings_meta';
		$meta = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table WHERE booking_id = %d AND meta_key = %s", $booking_id, $meta_key));

		if (empty($meta)) {
			return false;
		}

		$meta = maybe_unserialize($meta);

		return $meta;
	}


	/**
	 * Check if WooCommerce is activated
	 */
	if (!function_exists('is_woocommerce_activated')) {
		function is_woocommerce_activated()
		{
			if (class_exists('woocommerce')) {
				return true;
			} else {
				return false;
			}
		}
	}

	function listeo_get_default_search_forms()
	{
		return array(
			'search_on_home_page' => array(
				'id' => 'search_on_home_page',
				'type' => 'fullwidth',
				'title' => 'Home Search Form Default'
			),
			'search_on_homebox_page' => array(
				'id' => 'search_on_homebox_page',
				'type' => 'boxed',
				'title' => 'Home Search Form Boxed'
			),
			'sidebar_search' => array(
				'id' => 'sidebar_search',
				'type' => 'sidebar',
				'title' => 'Sidebar Search'
			),
			'search_on_half_map' => array(
				'id' => 'search_on_half_map',
				'type' => 'split',
				'title' => 'Search on Half Map Layout'
			),
			'search_in_header' => array(
				'id' => 'search_in_header',
				'type' => 'fullwidth',
				'title' => 'Search in Header'
			),
		);
	}
	function listeo_get_search_forms()
	{
		$default_search_forms = listeo_get_default_search_forms();
		$forms = get_option('listeo_search_forms', array());

		return array_merge($default_search_forms, $forms);
	}
	function listeo_get_search_forms_dropdown($type = 'all')
	{
		$forms = listeo_get_search_forms();
		
		$dropdown = array();

		foreach ($forms as $key => $value) {
			if ($type == 'all') {
				$dropdown[$key] = $value['title'];
			} else {
				if ($type == $value['type']) {
					$dropdown[$key] = $value['title'];
				}
			}
		}
		return $dropdown;
	}

function listeo_get_search_form_metabox_cb($field){
	// get term layout setting
	$layout = get_term_meta($field->object_id, 'listeo_taxonomy_top_layout', true);
	$forms = listeo_get_search_forms();
	
	switch ($layout) {
		case 'search':
		case 'map_searchform':
			$search_forms = listeo_get_search_forms_dropdown('fullwidth');
			break;

		case 'halfsidebar':
		case 'half':
		case 'split':
			$search_forms = listeo_get_search_forms_dropdown('split');
			break;

		default:
			$search_forms = listeo_get_search_forms_dropdown('all');
			break;
	}
	
	
	return $search_forms;
}


	function listeo_get_compatible_search_form_for_layout($search_form_key, $layout) {
		if (empty($layout)) {
			return $search_form_key;
		}
		
		$forms = listeo_get_search_forms();
		
		// If the current form exists and is compatible, return it
		if (!empty($search_form_key) && isset($forms[$search_form_key])) {
			$form_type = $forms[$search_form_key]['type'];
	
			$is_compatible = false;
			switch ($layout) {
				case 'search':
				case 'map_searchform':
				
					$is_compatible = ($form_type === 'fullwidth');
		
					break;
				case 'half':
				case 'split':
			
					$is_compatible = ($form_type === 'split');
				
					break;
				
				case 'halfsidebar':
				
					$is_compatible = ($form_type === 'sidebar');
				
					break;
				default:
				
					$is_compatible = true;
					break;
			}
		
			if ($is_compatible) {
				return $search_form_key;
			}
		}

	
		
		// Current form is not compatible, find first available compatible form
		$required_type = '';
		switch ($layout) {
			case 'search':
			case 'map_searchform':
				$required_type = 'fullwidth';
				break;
				
			
			case 'half':
			case 'split':
				$required_type = 'split';
				break;
				
			case 'halfsidebar':
				$required_type = 'sidebar';
			break;	
			default:
				// For other layouts, return the original form or first available
				return !empty($search_form_key) ? $search_form_key : key($forms);
		}
		
		// Find first form of the required type
		foreach ($forms as $form_key => $form_data) {
			if (isset($form_data['type']) && $form_data['type'] === $required_type) {
				return $form_key;
			}
		}
		
		// If no compatible form found, return original or first available
		return !empty($search_form_key) ? $search_form_key : key($forms);
	}


function listeo_create_product($listing_id){

	$listing = get_post($listing_id);
	$post_title = $listing->post_title;
	$post_content = $listing->post_content;
	$product = array(
		'post_author' => get_current_user_id(),
		'post_content' => $post_content,
		'post_status' => 'publish',
		'post_title' => $post_title,
		'post_parent' => '',
		'post_type' => 'product',
	);

	// set product as virtual
	
	// add product if not exist
	

	// insert listing as WooCommerce product
	$product_id = wp_insert_post($product);
	wp_set_object_terms($product_id, 'listing_booking', 'product_type');

	wp_set_object_terms($product_id, 'exclude-from-catalog', 'product_visibility');
	wp_set_object_terms($product_id, 'exclude-from-search', 'product_visibility');

	// Set as virtual product
	update_post_meta($product_id, '_virtual', 'yes');
	update_post_meta($product_id, '_stock_status', 'instock');
	update_post_meta($product_id, '_manage_stock', 'no');
	update_post_meta($product_id, '_sold_individually', 'yes');
	// set product category
	$term = get_term_by('name', apply_filters('listeo_default_product_category', 'Listeo booking'), 'product_cat', ARRAY_A);

	if (!$term) $term = wp_insert_term(
		apply_filters('listeo_default_product_category', 'Listeo booking'),
		'product_cat',
		array(
			'description' => __('Listings category', 'listeo-core'),
			'slug' => str_replace(' ', '-', apply_filters('listeo_default_product_category', 'Listeo booking'))
		)
	);
	update_post_meta($listing_id, 'product_id', $product_id);
	wp_set_object_terms($product_id, $term['term_id'], 'product_cat');

	return $product_id;
}


function searchForPostedValue($id, $array)
{
	foreach ($array as $key => $val) {
		if ($key === $id) {
			return $val;
		}

		if (is_array($val)) {
			$result = searchForPostedValue($id, $val);
			if ($result !== false) {
				return $result;
			}
		}
	}
	return false;
}

function listeo_custom_posts_orderby($orderby, $query)
{
	// Only apply custom ordering if our flag is set
	if ($query->get('listeo_custom_event_order')) {
		global $wpdb;

		$current_timestamp = current_time('timestamp');

		// Modify the ORDER BY clause
		$orderby = $wpdb->prepare("
            MAX(CASE 
                WHEN {$wpdb->postmeta}.meta_key = '_event_date_timestamp' 
                THEN ABS(CAST({$wpdb->postmeta}.meta_value AS SIGNED) - %d)
                ELSE 9999999999 
            END) ASC,
            {$wpdb->posts}.post_date DESC
        ", $current_timestamp);

		// Ensure GROUP BY is set
		add_filter('posts_groupby', function ($groupby) use ($wpdb) {
			if (empty($groupby)) {
				return "{$wpdb->posts}.ID";
			}
			return $groupby;
		});

		// Remove the filters after use to prevent affecting other queries
		add_action('posts_selection', function () {
			remove_all_filters('posts_groupby');
			remove_filter('posts_orderby', 'listeo_custom_posts_orderby', 10);
		});
	}
	return $orderby;
}


function listeo_get_ids_listings_for_ads($ad_placement,$ad_filters = array()){

	// get filters 
	$listing_category = isset($ad_filters['listing_category']) ? $ad_filters['listing_category'] : '';
	// if is array, convert to string
	if(is_array($listing_category)){
		$listing_category = implode(',', $listing_category);
	}
	$region = isset($ad_filters['region']) ? $ad_filters['region'] : '';
	// if is array, convert to string
	if(is_array($region)){
		$region = implode(',', $region);
	}
	$address = isset($ad_filters['address']) ? $ad_filters['address'] : '';
	// instead of listings, query all "ad" post type that match the filters and take the listing_id meta field from each ad 
	// then query the listings with the listing_id in the meta field
	
	$args = array(
		'post_type' => 'listeoad',
		'posts_per_page' => -1,
		'fields' => 'ids',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'ad_status',
				'value' => 'active',
				'compare' => '='
			),
			array(
				'key' => 'placement',
				'value' => array($ad_placement), // You can adjust this array as needed
				'compare' => 'IN'
			)
		)
	);

	
	// how would that above like in SQL query
	$logged_status = is_user_logged_in();
	//if ad has meta field 'only_loggedin' set to 1 show it only to logged in users
	if($ad_placement == 'search'){

		
		// search by address
		if($address){
			
				
				global $wpdb;
				
				$radius =  get_option('listeo_maps_default_radius');
				
				$radius_type = get_option('listeo_radius_unit', 'km');
				$radius_api_key = get_option('listeo_maps_api_server');
				$geocoding_provider = get_option('listeo_geocoding_provider', 'google');
				if ($geocoding_provider == 'google') {
					$radius_api_key = get_option('listeo_maps_api_server');
				} else {
					$radius_api_key = get_option('listeo_geoapify_maps_api_server');
				}

				if (!empty($address) && !empty($radius) && !empty($radius_api_key)) {
					//search by google

					$latlng = listeo_core_geocode($address);

					$nearbyposts = listeo_core_get_nearby_listings($latlng[0], $latlng[1], $radius, $radius_type);

					listeo_core_array_sort_by_column($nearbyposts, 'distance');
					$location_post_ids = array_unique(array_column($nearbyposts, 'post_id'));

					if (empty($location_post_ids)) {
						$location_post_ids = array(0);
					}
				} else {

					// Smart location search - use centralized helper function
					$location_post_ids = listeo_core_search_location_smart($address);
				}
				if (sizeof($location_post_ids) != 0) {
					$args['post__in'] = $location_post_ids;
				}
			
		}



		// // add filters
		if($listing_category){
			// $args['meta_query'][] = array(
			// 	'key' => 'taxonomy-listing_category',
			// 	'value' => $listing_category,
			// 	'compare' => 'LIKE'
			// );

			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'taxonomy-listing_category',
					'value' => $listing_category,
					'compare' => 'LIKE'
				),
				array(
					'key' => 'taxonomy-listing_category',
					'compare' => 'NOT EXISTS'
				)
			);
			
		}
		// if $listing_category is empty, show ads that don't have listing_category meta field
		else {
			// $args['meta_query'][] = array(
			// 	'key' => 'taxonomy-listing_category',
			// 	'compare' => 'NOT EXISTS'
			// );
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'taxonomy-listing_category',
					'value' => $region,
					'compare' => 'LIKE'
				),
				array(
					'key' => 'taxonomy-listing_category',
					'value' => '0',
					'compare' => '='
				),
				array(
					'key' => 'taxonomy-listing_category',
					'compare' => 'NOT EXISTS'
				)
			);
		}

		if($region){
		
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'taxonomy-region',
					'value' => $region,
					'compare' => 'LIKE'
				),
				array(
					'key' => 'taxonomy-region',
					'compare' => 'NOT EXISTS'
				)
			);
			
		}
		// if $region is empty, show ads that are not filtered by region
		else{
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'taxonomy-region',
					'value' => $region,
					'compare' => 'LIKE'
				),
				array(
					'key' => 'taxonomy-region',
					'value' => '0',
					'compare' => '='
				),
				array(
					'key' => 'taxonomy-region',
					'compare' => 'NOT EXISTS'
				)
			);
		}
	}
	
	$query = new WP_Query($args);
	
	// if there are no ads, return empty array
	if(!$query->have_posts()){
		return array();
	}
	

	$listing_ids = array();
	
	if ($query->have_posts()) {
		
		foreach ($query->posts as $ad_id) {
			$listing_id = get_post_meta($ad_id, 'listing_id', true);
			// if ad has only_loggedin set to 1 and user is not logged in, skip this ad
			if(get_post_meta($ad_id, 'only_loggedin', true) == 1 && !$logged_status){
				continue;
			}
			// if ad has address set, and there's no address in the search query, skip this ad
			if(get_post_meta($ad_id, '_address', true) && !$address){
				continue;
			}
			if ($listing_id) {
				$listing_ids[] = $listing_id;
			}
		}
	}
	// if there are no listing ids, return empty array
	if(empty($listing_ids)){
		return array();
	}
	
	wp_reset_postdata();
	return $listing_ids;




	// $args = array(
	// 	'post_type' => 'listing',
	// 	'posts_per_page' => -1,
	// 	'fields' => 'ids',
	// 	'meta_query' => array(
	// 		'relation' => 'AND',
	// 		array(
	// 			'key' => 'ad_status',
	// 			'value' => 'active',
	// 			'compare' => '='
	// 		),
	// 		array(
	// 			'key' => 'ad_placement',
	// 			'value' => $ad_type,
	// 			'compare' => 'LIKE'
	// 		)
	// 	)
	// );

	// $query = new WP_Query($args);
	// wp_reset_postdata();
	// return $query->posts;
}



    function listeo_get_category_drilldown_data($taxonomy = 'category', $args = []) {
        // Default arguments
        $default_args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'parent' => 0
        );
        $args = wp_parse_args($args, $default_args);
        
        // Get top level terms
        $terms = get_terms($args);
        
        if (is_wp_error($terms)) {
            return [];
        }
        
        $categories = array();
        
        foreach ($terms as $term) {
            $category = array(
                'label' => $term->name,
                'id' => $term->term_id,
                'slug' => $term->slug
            );
            
            // Check for children
            $children = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'parent' => $term->term_id
            ));
            
            if (!is_wp_error($children) && !empty($children)) {
                $category['children'] = array();
                foreach ($children as $child) {
                    $category['children'][] = array(
                        'label' => $child->name,
                        'id' => $child->term_id,
                        'slug' => $child->slug
                    );
                }
            }
            
            $categories[] = $category;
        }
        
        return $categories;
    }


// Function to render the drilldown menu

    function listeo_render_category_drilldown($taxonomy = 'category', $args = [], $button_text = 'Select Category') {
        $categories = listeo_get_category_drilldown_data($taxonomy, $args);
        ?>
        <div class="drilldown-menu" data-categories='<?php echo esc_attr(json_encode($categories)); ?>'>
            <div class="menu-toggle">
                <span class="menu-label"><?php echo esc_html($button_text); ?></span>
                <span class="reset-button" style="display:none;">&times;</span>
	</div>
            <div class="menu-panel">
                <div class="menu-search-wrapper">
                    <input type="text" class="menu-search" placeholder="Search...">
                </div>
                <div class="menu-levels-container"></div>
            </div>
        </div>
        <?php
    }


function listeo_get_nested_categories($taxonomy = 'listing_category')
{
	// Get all music genre terms
	$terms = get_terms(array(
		'taxonomy' => $taxonomy, // Replace with your taxonomy name
		'hide_empty' => false,
		'parent' => 0 // Get top level terms first
	));

	$nested_categories = array();

	foreach ($terms as $term) {
		$category = array(
			'label' => $term->name,
			'id' => $term->term_id,
			'value' => $term->slug // Adding the value field
		);

		// Check for children
		$children = get_child_terms($term->term_id, $taxonomy, $term);

		// Only add children if they are different from the parent
		if (is_array($children) && !empty($children)) {
			$has_different_children = false;
			foreach ($children as $child) {
				// Check if child is different from parent
				if ($child['value'] !== $category['value']) {
					$has_different_children = true;
					break;
				}
			}

			if ($has_different_children) {
				$category['children'] = $children;
			}
		}
	
		$nested_categories[] = $category;
	}
	
	return $nested_categories;
}

function get_child_terms($parent_id,$taxonomy = 'listing_category', $parent_term = null)
{
	$terms = get_terms(array(
		'taxonomy' => $taxonomy, // Replace with your taxonomy name
		'hide_empty' => false,
		'parent' => $parent_id
	));

	$children = array();
	// Add parent as first item in children array
	if ($parent_term) {
		$children[] = array(
			'label' => esc_html__('All in ','listeo_core'). $parent_term->name,
			'value' => $taxonomy . ':' . $parent_term->slug, // Include taxonomy name
			'id' => $parent_term->term_id
		);
	}
	foreach ($terms as $term) {
		$child = array(
			'label' => $term->name,
			'value' => $taxonomy . ':' . $term->slug, // Include taxonomy name to avoid conflicts
			'id' => $term->term_id
		);

		// Recursively check for grandchildren
		$grandchildren = get_child_terms($term->term_id, $taxonomy, $term);
		if (!empty($grandchildren)) {
			$child['children'] = $grandchildren;
		}

		$children[] = $child;
	}

	return $children;
}

/**
 * Get the correct taxonomy for a listing based on its type
 * Handles both default listing types and custom listing types
 *
 * @param int|WP_Post $listing Listing ID or post object
 * @return string The taxonomy name to use for categories
 */
function listeo_get_listing_taxonomy($listing = null) {
	// Get the listing post
	if (is_numeric($listing)) {
		$listing = get_post($listing);
	} elseif (is_null($listing)) {
		$listing = get_post();
	}

	if (!$listing) {
		return 'listing_category'; // Default fallback
	}

	// Get the listing type
	$listing_type = get_post_meta($listing->ID, '_listing_type', true);

	if (empty($listing_type)) {
		return 'listing_category'; // Default if no type is set
	}

	// Check if this is a custom listing type
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types_manager = listeo_core_custom_listing_types();
		$type_config = $custom_types_manager->get_listing_type_by_slug($listing_type);

		if ($type_config && $type_config->register_taxonomy) {
			// Custom types use {slug}_category pattern
			return $listing_type . '_category';
		}
	}

	// Handle default listing types
	switch ($listing_type) {
		case 'service':
			return 'service_category';
		case 'rental':
			return 'rental_category';
		case 'event':
			return 'event_category';
		case 'classifieds':
			return 'classifieds_category';
		default:
			// For any other type, assume it might be custom
			// Check if the taxonomy exists
			$taxonomy_name = $listing_type . '_category';
			if (taxonomy_exists($taxonomy_name)) {
				return $taxonomy_name;
			}
			// Final fallback
			return 'listing_category';
	}
}

/**
 * Get listing types with their associated taxonomies for drilldown menu
 *
 * @return array Hierarchical data structure for drilldown menu
 */
function listeo_get_listing_types_with_taxonomies() {
	$listing_types_data = array();
	
	// Get all active listing types
	if (function_exists('listeo_core_custom_listing_types')) {
		$custom_types_manager = listeo_core_custom_listing_types();
		$listing_types = $custom_types_manager->get_listing_types(true);
	} else {
		// Fallback to default types if custom types system not available
		$listing_types = array(
			(object) array('slug' => 'service', 'name' => __('Services', 'listeo_core')),
			(object) array('slug' => 'rental', 'name' => __('Rentals', 'listeo_core')),
			(object) array('slug' => 'event', 'name' => __('Events', 'listeo_core')),
			(object) array('slug' => 'classifieds', 'name' => __('Classifieds', 'listeo_core'))
		);
	}
	
	foreach ($listing_types as $listing_type) {
		$type_data = array(
			'label' => $listing_type->name,
			'value' => 'listing_type_' . $listing_type->slug, // Prefix to distinguish from taxonomy terms
			'id' => 'type_' . $listing_type->slug,
			'children' => array()
		);
		
		// Get associated taxonomies for this listing type
		$taxonomy_name = $listing_type->slug . '_category';
		
		// Check if this taxonomy exists
		if (taxonomy_exists($taxonomy_name)) {
			// Add "All in [Type]" option first
			$type_data['children'][] = array(
				'label' => sprintf(__('All in %s', 'listeo_core'), $listing_type->name),
				'value' => 'listing_type_' . $listing_type->slug,
				'id' => 'all_' . $listing_type->slug
			);
			
			// Get taxonomy terms
			$terms = get_terms(array(
				'taxonomy' => $taxonomy_name,
				'hide_empty' => false,
				'parent' => 0 // Get top level terms
			));
			
			foreach ($terms as $term) {
				$term_data = array(
					'label' => $term->name,
					'value' => $taxonomy_name . ':' . $term->slug, // Include taxonomy name to avoid conflicts
					'id' => $term->term_id
				);
				
				// Get child terms recursively
				$children = get_child_terms($term->term_id, $taxonomy_name, $term);
				if (!empty($children)) {
					$term_data['children'] = $children;
				}
				
				$type_data['children'][] = $term_data;
			}
		} else {
			// If no specific taxonomy, add just the "All [Type]" option
			$type_data['children'][] = array(
				'label' => sprintf(__('All %s', 'listeo_core'), $listing_type->name),
				'value' => 'listing_type_' . $listing_type->slug,
				'id' => 'all_' . $listing_type->slug
			);
		}
		
		$listing_types_data[] = $type_data;
	}
	
	return $listing_types_data;
}

function listeo_get_slider_split_categories_json($current_term)
{
	$slider_status = get_option('pp_listings_split-categories-slider-options', 'show_all');
	$categories = [];

	// Add "All" item if no current term
	if(empty($current_term)){
		$categories[] = [
			'name' => esc_html__('All', 'listeo_core'),
			'icon' => '<i class="sl sl-icon-grid"></i>',
			'id' => '0',
			'slug' => 'all',
		];
	}

	// Handle new "Show only Listing Types" option
	if ($slider_status == 'show_listing_types') {
		$selected_types = get_option('pp_listings_split-listing-types-slider', []);
		
		if (empty($selected_types)) {
			// If no types selected, show all available types
			$selected_types = array_keys(listeo_get_listing_types_for_slider());
		}

		// Get listing types and create slider items
		if (function_exists('listeo_core_custom_listing_types')) {
			$custom_types = listeo_core_custom_listing_types();
			$all_types = $custom_types->get_listing_types(true);
			
			foreach ($all_types as $type) {
				if (in_array($type->slug, $selected_types)) {
					// Get icon for listing type
					$icon = '<i class="fa fa-folder"></i>'; // Default icon for listing types
					
					if (!empty($type->icon_id)) {
						// Get SVG icon from media library
						$_icon_svg_image = wp_get_attachment_image_src($type->icon_id, 'medium');
						if (!empty($_icon_svg_image)) {
							$icon = '<i class="listeo-svg-icon-box-grid">'. listeo_render_svg_icon($type->icon_id).'</i>';
						}
					}
					
					$categories[] = [
						'name' => $type->name,
						'icon' => $icon,
						'id' => $type->slug, // Use slug as ID for listing types
						'slug' => $type->slug,
						'type' => 'listing_type', // Mark as listing type
					];
				}
			}
		} else {
			// Fallback to default types with old option system icons
			$default_types = array(
				'service' => array(
					'name' => __('Service', 'listeo_core'),
					'icon_option' => 'listeo_service_type_icon'
				),
				'rental' => array(
					'name' => __('Rental', 'listeo_core'),
					'icon_option' => 'listeo_rental_type_icon'
				),
				'event' => array(
					'name' => __('Event', 'listeo_core'),
					'icon_option' => 'listeo_event_type_icon'
				),
				'classifieds' => array(
					'name' => __('Classifieds', 'listeo_core'),
					'icon_option' => 'listeo_classifieds_type_icon'
				)
			);
			
			foreach ($default_types as $slug => $type_data) {
				if (in_array($slug, $selected_types)) {
					// Get icon from old option system
					$icon = '<i class="fa fa-folder"></i>'; // Default
					$icon_id = get_option($type_data['icon_option'], '');
					
					if (!empty($icon_id)) {
						$_icon_svg_image = wp_get_attachment_image_src($icon_id, 'medium');
						if (!empty($_icon_svg_image)) {
							$icon = '<i class="listeo-svg-icon-box-grid">'. listeo_render_svg_icon($icon_id).'</i>';
						}
					}
					
					$categories[] = [
						'name' => $type_data['name'],
						'icon' => $icon,
						'id' => $slug,
						'slug' => $slug,
						'type' => 'listing_type',
					];
				}
			}
		}
		
		return wp_json_encode($categories);
	}

	// Handle categories (existing logic with new filtering)
	$taxonomy = 'listing_category';
	$hide_empty = ($slider_status == 'show_nonempty');
	
	// Handle "Show preselected categories" with new grouped selection
	if ($slider_status == 'show_preselected') {
		// Check for new grouped selection setting first
		$selected_categories = get_option('pp_listings_split-categories-grouped-selection', []);
		
		if (!empty($selected_categories)) {
			// Process selected category IDs from grouped selection
			foreach ($selected_categories as $category_key) {
				// Parse the category key (format: term_id or term_id_taxonomy)
				if (strpos($category_key, '_') !== false && !is_numeric($category_key)) {
					// New format: term_id_taxonomy
					$parts = explode('_', $category_key);
					$term_id = intval($parts[0]);
					$tax = str_replace($term_id . '_', '', $category_key);
				} else {
					// Simple term ID (global categories)
					$term_id = intval($category_key);
					$tax = 'listing_category';
				}
				
				$term_obj = get_term($term_id, $tax);
				
				if ($term_obj && !is_wp_error($term_obj)) {
					// Get icon
					$icon = get_term_meta($term_id, 'icon', true);
					$_icon_svg = get_term_meta($term_id, '_icon_svg', true);
					$_icon_svg_image = wp_get_attachment_image_src($_icon_svg, 'medium');
					
					if (empty($icon)) {
						$icon = 'fa fa-globe';
					}

					if (!empty($_icon_svg_image)) {
						$icon = '<i class="listeo-svg-icon-box-grid">'. listeo_render_svg_icon($_icon_svg).'</i>';
					} else {
						if ($icon != 'emtpy') {
							$check_if_im = substr($icon, 0, 3);
							if ($check_if_im == 'im ') {
								$icon = ' <i class="' . esc_attr($icon) . '"></i>';
							} else {
								$icon =  ' <i class="fa ' . esc_attr($icon) . '"></i>';
							}
						}
					}
					
					$categories[] = [
						'name' => $term_obj->name,
						'icon' => $icon,
						'id' => $term_obj->term_id,
						'slug' => $term_obj->slug,
						'taxonomy' => $tax,
					];
				}
			}
		} else {
			// Fallback to old preselected terms system for backward compatibility
			$old_preselected = get_option('pp_listings_split-categories-slider', []);
			if (!empty($old_preselected)) {
				$args = array(
					'taxonomy' => 'listing_category',
					'hide_empty' => $hide_empty,
					'include' => $old_preselected,
					'parent' => 0,
				);
				
				$terms = get_terms($args);
				
				if (!empty($terms) && !is_wp_error($terms)) {
					foreach ($terms as $term_obj) {
						$t_id = $term_obj->term_id;
						
						// Get icon
						$icon = get_term_meta($t_id, 'icon', true);
						$_icon_svg = get_term_meta($t_id, '_icon_svg', true);
						$_icon_svg_image = wp_get_attachment_image_src($_icon_svg, 'medium');
						
						if (empty($icon)) {
							$icon = 'fa fa-globe';
						}

						if (!empty($_icon_svg_image)) {
							$icon = '<i class="listeo-svg-icon-box-grid">'. listeo_render_svg_icon($_icon_svg).'</i>';
						} else {
							if ($icon != 'emtpy') {
								$check_if_im = substr($icon, 0, 3);
								if ($check_if_im == 'im ') {
									$icon = ' <i class="' . esc_attr($icon) . '"></i>';
								} else {
									$icon =  ' <i class="fa ' . esc_attr($icon) . '"></i>';
								}
							}
						}
						
						$categories[] = [
							'name' => $term_obj->name,
							'icon' => $icon,
							'id' => $term_obj->term_id,
							'slug' => $term_obj->slug,
							'taxonomy' => 'listing_category',
						];
					}
				}
			}
		}
	} else {
		// Handle other statuses (show_all, show_nonempty)
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => $hide_empty,
			'parent' => 0,
		);

		$terms = get_terms($args);
		
		if (!empty($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term_obj) {
				$t_id = $term_obj->term_id;
				
				// Get icon
				$icon = get_term_meta($t_id, 'icon', true);
				$_icon_svg = get_term_meta($t_id, '_icon_svg', true);
				$_icon_svg_image = wp_get_attachment_image_src($_icon_svg, 'medium');
				
				if (empty($icon)) {
					$icon = 'fa fa-globe';
				}

				if (!empty($_icon_svg_image)) {
					$icon = '<i class="listeo-svg-icon-box-grid">'. listeo_render_svg_icon($_icon_svg).'</i>';
				} else {
					if ($icon != 'emtpy') {
						$check_if_im = substr($icon, 0, 3);
						if ($check_if_im == 'im ') {
							$icon = ' <i class="' . esc_attr($icon) . '"></i>';
						} else {
							$icon =  ' <i class="fa ' . esc_attr($icon) . '"></i>';
						}
					}
				}
				
				$categories[] = [
					'name' => $term_obj->name,
					'icon' => $icon,
					'id' => $term_obj->term_id,
					'slug' => $term_obj->slug,
					'taxonomy' => $taxonomy,
				];
			}
		}
	}

	return wp_json_encode($categories);
}

function get_custom_fields_for_list($post, $with_labels = true) {

	// limit to only 3 fields
	$details = listeo_get_listing_details($post);
	
	$class = (isset($data->class)) ? $data->class : 'listing-details';

	if (!empty($details)) : ?>
		<div class="listing-features-nl">
			<?php $count = 0;
	foreach ($details as $detail) :
		
		if(!isset($detail['display_type']) || $detail['display_type'] === 'header') {
			continue; // Skip headers
		}
		
		if (isset($detail['config']['is_taxonomy_field']) && $detail['config']['is_taxonomy_field']) {
			if( isset($detail['config']['showonfront']) && $detail['config']['showonfront']) {
				// Show taxonomy fields that are set to show on front
			} else {
				continue; // Skip taxonomy fields that are not set to show on front
			}
		}
		$count++; ?>
				<?php if ($detail['display_type'] === 'checkbox') : ?>
					<!-- Checkbox Field Template -->
					<div class="feature-tag-nl <?php echo esc_attr(implode(' ', $detail['css_classes'])); ?>">
						<div class="single-property-detail-label-<?php echo esc_attr($detail['config']['id']); ?>">
							<?php echo esc_html($detail['config']['name']); ?>: <?php echo listeo_render_detail_value($detail); ?>
						</div>
						<div class="tooltip-nl"><?php echo esc_html($detail['config']['name']); ?></div>
					</div>
	
				<?php elseif ($detail['display_type'] === 'area') : ?>
					<!-- Area Field Template -->
					<?php $area_data = $detail['processed_value']; ?>
					<div class="feature-tag-nl" <?php echo esc_attr(implode(' ', $detail['css_classes'])); ?>">
						<i class="<?php echo esc_attr($detail['icon']); ?>"></i>
						<?php if ($detail['is_inverted']) : ?>
							<?php echo esc_html($area_data['scale']); ?>
							<span><?php echo listeo_render_detail_value($detail); ?></span>
						<?php else : ?>
							<span><?php echo listeo_render_detail_value($detail); ?></span>
							<?php echo esc_html($area_data['scale']); ?>
						<?php endif; ?>
					</div>
	
				<?php elseif ($detail['display_type'] === 'file') : ?>
					<!-- File Field Template -->
					<div class="feature-tag-nl <?php echo esc_attr(implode(' ', $detail['css_classes'])); ?>">
						<i class="<?php echo esc_attr($detail['icon']); ?>"></i>
						<?php echo listeo_render_detail_value($detail); ?>
					</div>
	
				<?php else : ?>
					<!-- Regular Field Template -->
					<div class="feature-tag-nl <?php echo esc_attr(implode(' ', $detail['css_classes'])); ?>">
						<i class="<?php echo esc_attr($detail['icon']); ?>"></i>
						<?php if ($detail['is_inverted']) : ?>
							<span><?php echo listeo_render_detail_value($detail); ?></span>
							<?php if ($with_labels) : ?>
								<div class="single-property-detail-label-<?php echo esc_attr($detail['config']['id']); ?>">
									<?php echo esc_html($detail['config']['name']); ?>
								</div>
							<?php endif; ?>

						<?php else : ?>
							<?php if ($with_labels) : ?>
								<div class="single-property-detail-label-<?php echo esc_attr($detail['config']['id']); ?>">
									<?php echo esc_html($detail['config']['name']); ?>
								</div>
							<?php endif; ?>
							<span><?php echo listeo_render_detail_value($detail); ?></span>
						<?php endif; ?>
						<div class="tooltip-nl"><?php echo esc_html($detail['config']['name']); ?></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
<?php 
	endif; // End of details check
	// Return the class for further use if needed
}

function has_visible_fields_after($details, $start_index, $current_taxonomy = null)
{
	for ($i = $start_index + 1; $i < count($details); $i++) {
		$next = $details[$i];

		if (!isset($next['display_type'])) continue;
		if ($next['display_type'] === 'header') break; // Stop at next header

		if ($current_taxonomy) {
			if (!empty($next['config']['taxonomy']) && $next['config']['taxonomy'] === $current_taxonomy) {
				return true;
			}
		} else {
			if (empty($next['config']['is_taxonomy_field'])) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Calculate distance between two coordinates using Haversine formula
 *
 * @param float $lat1 Latitude of first point
 * @param float $lng1 Longitude of first point  
 * @param float $lat2 Latitude of second point
 * @param float $lng2 Longitude of second point
 * @param string $unit Unit of measurement ('km' or 'miles')
 * @return float Distance in the specified unit
 */
function listeo_calculate_distance($lat1, $lng1, $lat2, $lng2, $unit = 'km') {
    if (($lat1 == $lat2) && ($lng1 == $lng2)) {
        return 0;
    }
    
    $theta = $lng1 - $lng2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    
    switch($unit) {
        case 'miles':
            return round($miles, 2);
        case 'km':
        default:
            return round($miles * 1.609344, 2);
    }
}

/**
 * Format distance for display
 *
 * @param float $distance Distance value
 * @param string $unit Unit ('km' or 'miles')
 * @return string Formatted distance string
 */
function listeo_format_distance($distance, $unit = 'km') {
    $unit_label = ($unit === 'miles') ? __('mi', 'listeo_core') : __('km', 'listeo_core');
    return sprintf('%.1f %s', $distance, $unit_label);
}

/**
 * Get nearby listings based on geolocation
 *
 * @param int $listing_id ID of the current listing
 * @param float $radius Search radius
 * @param string $unit Distance unit
 * @param array $args Additional WP_Query arguments
 * @return array Array of nearby listings with distances
 */
function listeo_get_nearby_listings_with_distance($listing_id, $radius = 50, $unit = 'km', $args = array()) {
    // Get current listing coordinates
    $current_lat = get_post_meta($listing_id, '_geolocation_lat', true);
    $current_lng = get_post_meta($listing_id, '_geolocation_long', true);
    
    if (empty($current_lat) || empty($current_lng)) {
        return array();
    }
    
    // Performance optimization: Use database-level spatial filtering for large datasets
    $total_listings = wp_count_posts('listing')->publish;
    
    if ($total_listings > 5000) {
        // For large datasets, use optimized database query
        return listeo_get_nearby_listings_optimized($listing_id, $current_lat, $current_lng, $radius, $unit, $args);
    }
    
    // Original method for smaller datasets
    return listeo_get_nearby_listings_standard($listing_id, $current_lat, $current_lng, $radius, $unit, $args);
}

/**
 * Standard method for smaller datasets (< 5000 listings)
 */
function listeo_get_nearby_listings_standard($listing_id, $current_lat, $current_lng, $radius, $unit, $args) {
    // Base query arguments
    $default_args = array(
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 100, // Increased limit for better results
        'post__not_in' => array($listing_id),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_geolocation_lat',
                'value' => '',
                'compare' => '!='
            ),
            array(
                'key' => '_geolocation_long',
                'value' => '',
                'compare' => '!='
            )
        )
    );
    
    $query_args = wp_parse_args($args, $default_args);
    $listings = get_posts($query_args);
    
    $nearby_listings = array();
    
    foreach ($listings as $listing) {
        $listing_lat = get_post_meta($listing->ID, '_geolocation_lat', true);
        $listing_lng = get_post_meta($listing->ID, '_geolocation_long', true);
        
        if (empty($listing_lat) || empty($listing_lng)) {
            continue;
        }
        
        $distance = listeo_calculate_distance($current_lat, $current_lng, $listing_lat, $listing_lng, $unit);
        
        if ($distance <= $radius) {
            $nearby_listings[] = array(
                'post' => $listing,
                'distance' => $distance
            );
        }
    }
    
    // Sort by distance and limit results
    usort($nearby_listings, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    
    return array_slice($nearby_listings, 0, 20); // Return top 20 nearest
}

/**
 * Optimized method for large datasets (5000+ listings)
 * Uses database-level spatial calculations and bounding box filtering
 */
function listeo_get_nearby_listings_optimized($listing_id, $current_lat, $current_lng, $radius, $unit, $args) {
    global $wpdb;
    
    // Calculate bounding box to pre-filter results
    $earth_radius = ($unit === 'miles') ? 3959 : 6371; // Earth radius in km or miles
    $lat_range = $radius / $earth_radius * (180 / M_PI);
    $lng_range = $radius / $earth_radius * (180 / M_PI) / cos($current_lat * M_PI / 180);
    
    $min_lat = $current_lat - $lat_range;
    $max_lat = $current_lat + $lat_range;
    $min_lng = $current_lng - $lng_range;
    $max_lng = $current_lng + $lng_range;
    
    // Build additional WHERE conditions from args
    $additional_where = '';
    $additional_joins = '';
    
    // Handle taxonomy filtering if present in args
    if (isset($args['tax_query']) && !empty($args['tax_query'])) {
        $tax_query = $args['tax_query'];
        if (isset($tax_query[0]) && is_array($tax_query[0])) {
            $taxonomy = $tax_query[0]['taxonomy'];
            $terms = $tax_query[0]['terms'];
            $operator = isset($tax_query[0]['operator']) ? $tax_query[0]['operator'] : 'IN';
            
            if (!empty($terms)) {
                $terms_placeholders = implode(',', array_fill(0, count($terms), '%d'));
                $additional_joins .= "
                    INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                ";
                
                if ($operator === 'IN') {
                    $additional_where .= $wpdb->prepare(" AND tt.taxonomy = %s AND tt.term_id IN ($terms_placeholders)", 
                        array_merge(array($taxonomy), $terms));
                }
            }
        }
    }
    
    // Handle author filtering
    if (isset($args['author']) && !empty($args['author'])) {
        $additional_where .= $wpdb->prepare(" AND p.post_author = %d", $args['author']);
    }
    
    // Optimized query with spatial filtering
    $sql = $wpdb->prepare("
        SELECT DISTINCT
            p.ID,
            lat_meta.meta_value as latitude,
            lng_meta.meta_value as longitude,
            (%f * ACOS(
                COS(RADIANS(%f)) * 
                COS(RADIANS(lat_meta.meta_value)) * 
                COS(RADIANS(lng_meta.meta_value) - RADIANS(%f)) + 
                SIN(RADIANS(%f)) * 
                SIN(RADIANS(lat_meta.meta_value))
            )) as distance
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} lat_meta ON p.ID = lat_meta.post_id 
            AND lat_meta.meta_key = '_geolocation_lat'
        INNER JOIN {$wpdb->postmeta} lng_meta ON p.ID = lng_meta.post_id 
            AND lng_meta.meta_key = '_geolocation_long'
        $additional_joins
        WHERE p.post_type = 'listing'
            AND p.post_status = 'publish'
            AND p.ID != %d
            AND lat_meta.meta_value BETWEEN %f AND %f
            AND lng_meta.meta_value BETWEEN %f AND %f
            AND lat_meta.meta_value IS NOT NULL
            AND lng_meta.meta_value IS NOT NULL
            AND lat_meta.meta_value != ''
            AND lng_meta.meta_value != ''
            $additional_where
        HAVING distance <= %f
        ORDER BY distance ASC
        LIMIT 20
    ", 
        $earth_radius, $current_lat, $current_lng, $current_lat,
        $listing_id, $min_lat, $max_lat, $min_lng, $max_lng,
        $radius
    );
    
    $results = $wpdb->get_results($sql);
    
    $nearby_listings = array();
    
    foreach ($results as $result) {
        $post = get_post($result->ID);
        if ($post) {
            $nearby_listings[] = array(
                'post' => $post,
                'distance' => round((float)$result->distance, 2)
            );
        }
    }
    
    return $nearby_listings;
}

/**
 * Get cached nearby listings
 *
 * @param int $listing_id ID of the current listing
 * @param float $radius Search radius
 * @param string $unit Distance unit
 * @param array $args Additional WP_Query arguments
 * @return array Array of nearby listings with distances
 */
function listeo_get_cached_nearby_listings($listing_id, $radius = 50, $unit = 'km', $args = array()) {
    $start_time = microtime(true);
    
    // Generate cache key
    $cache_key = 'listeo_nearby_listings_' . $listing_id . '_' . $radius . '_' . $unit . '_' . md5(serialize($args));
    
    // Try to get from cache
    $cached_results = get_transient($cache_key);
    if ($cached_results !== false) {
        // Track cache hit
        listeo_track_cache_usage(true);
        
        // Log performance if debug enabled
        listeo_log_performance_metric('nearby_listings_cached', $start_time, array(
            'listing_id' => $listing_id,
            'radius' => $radius,
            'unit' => $unit,
            'result_count' => count($cached_results)
        ));
        
        return $cached_results;
    }
    
    // Track cache miss
    listeo_track_cache_usage(false);
    
    // Get fresh results
    $nearby_listings = listeo_get_nearby_listings_with_distance($listing_id, $radius, $unit, $args);
    
    // Cache the results
    $cache_days = get_option('listeo_nearby_listings_cache_days', 30); // Default 30 days
    set_transient($cache_key, $nearby_listings, $cache_days * DAY_IN_SECONDS);
    
    // Log performance if debug enabled
    listeo_log_performance_metric('nearby_listings_fresh', $start_time, array(
        'listing_id' => $listing_id,
        'radius' => $radius,
        'unit' => $unit,
        'result_count' => count($nearby_listings)
    ));
    
    return $nearby_listings;
}

/**
 * Clear nearby listings cache for a specific listing
 *
 * @param int $listing_id ID of the listing
 */
function listeo_clear_nearby_listings_cache($listing_id) {
    global $wpdb;
    
    // Delete all transients that contain this listing ID
    $cache_prefix = 'listeo_nearby_listings_' . $listing_id . '_';
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE %s 
            OR option_name LIKE %s",
            '_transient_' . $cache_prefix . '%',
            '_transient_timeout_' . $cache_prefix . '%'
        )
    );
}

/**
 * Clear all nearby listings cache
 */
function listeo_clear_all_nearby_listings_cache() {
    global $wpdb;
    
    // Delete all nearby listings transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_listeo_nearby_listings_%' 
        OR option_name LIKE '_transient_timeout_listeo_nearby_listings_%'"
    );
}

// Hook to selectively clear nearby listings cache when a listing is updated
add_action('save_post', 'listeo_clear_cache_on_listing_save', 10, 3);
function listeo_clear_cache_on_listing_save($post_id, $post, $update) {
    // Only process listing post type
    if ($post->post_type !== 'listing') {
        return;
    }
    
    // For new listings, don't clear any cache
    if (!$update) {
        return;
    }
    
    // Get old and new geolocation data to check if location changed
    static $old_locations = array();
    
    // Store old location on first call (before save)
    if (!isset($old_locations[$post_id])) {
        $old_locations[$post_id] = array(
            'lat' => get_post_meta($post_id, '_geolocation_lat', true),
            'lng' => get_post_meta($post_id, '_geolocation_long', true)
        );
    }
    
    $new_lat = get_post_meta($post_id, '_geolocation_lat', true);
    $new_lng = get_post_meta($post_id, '_geolocation_long', true);
    
    $old_lat = $old_locations[$post_id]['lat'] ?? '';
    $old_lng = $old_locations[$post_id]['lng'] ?? '';
    
    // Only clear cache if geolocation actually changed significantly (>100m difference)
    $location_changed = false;
    if (!empty($new_lat) && !empty($new_lng) && !empty($old_lat) && !empty($old_lng)) {
        $distance_moved = listeo_calculate_distance($old_lat, $old_lng, $new_lat, $new_lng, 'km');
        $location_changed = $distance_moved > 0.1; // 100 meters threshold
    } elseif ((!empty($new_lat) && !empty($new_lng)) && (empty($old_lat) || empty($old_lng))) {
        // Location was added
        $location_changed = true;
    } elseif ((empty($new_lat) || empty($new_lng)) && (!empty($old_lat) && !empty($old_lng))) {
        // Location was removed
        $location_changed = true;
    }
    
    // Only clear cache if location actually changed or listing status changed
    if ($location_changed || $post->post_status !== get_post_status($post_id)) {
        // Clear cache for this specific listing only
        listeo_clear_nearby_listings_cache($post_id);
        
        // Clean up the stored old location
        unset($old_locations[$post_id]);
    }
}

// Hook to clear cache when listing is deleted
add_action('delete_post', 'listeo_clear_cache_on_listing_delete');
function listeo_clear_cache_on_listing_delete($post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'listing') {
        // Only clear cache for the deleted listing itself
        // Other listings' nearby cache will naturally expire and refresh
        listeo_clear_nearby_listings_cache($post_id);
    }
}

/**
 * Add database indexes for geolocation fields to improve query performance
 * 
 * @return bool True if indexes were added successfully
 */
function listeo_add_geolocation_indexes() {
    global $wpdb;
    
    // Check if indexes already exist
    $existing_indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name LIKE 'idx_geolocation%'");
    if (!empty($existing_indexes)) {
        return true; // Indexes already exist
    }
    
    $success = true;
    
    // Add composite index for latitude
    $result = $wpdb->query("ALTER TABLE {$wpdb->postmeta} ADD INDEX idx_geolocation_lat (meta_key, meta_value) WHERE meta_key = '_geolocation_lat'");
    if ($result === false) {
        error_log('Listeo: Failed to add latitude index');
        $success = false;
    }
    
    // Add composite index for longitude  
    $result = $wpdb->query("ALTER TABLE {$wpdb->postmeta} ADD INDEX idx_geolocation_lng (meta_key, meta_value) WHERE meta_key = '_geolocation_long'");
    if ($result === false) {
        error_log('Listeo: Failed to add longitude index');
        $success = false;
    }
    
    // Add composite index for post_id and geolocation fields
    $result = $wpdb->query("ALTER TABLE {$wpdb->postmeta} ADD INDEX idx_post_geolocation (post_id, meta_key, meta_value) WHERE meta_key IN ('_geolocation_lat', '_geolocation_long')");
    if ($result === false) {
        error_log('Listeo: Failed to add post geolocation index');
        $success = false;
    }
    
    if ($success) {
        // Mark indexes as created
        update_option('listeo_geolocation_indexes_created', true);
        error_log('Listeo: Geolocation database indexes created successfully');
    }
    
    return $success;
}

/**
 * Check and create geolocation indexes if needed
 * Called during plugin activation or when needed
 *
 * @return void
 */
function listeo_maybe_add_geolocation_indexes() {
    $indexes_created = get_option('listeo_geolocation_indexes_created', false);
    
    if (!$indexes_created) {
        // Only attempt if we have a reasonable number of listings
        $listing_count = wp_count_posts('listing')->publish;
        
        if ($listing_count > 1000) {
            listeo_add_geolocation_indexes();
        }
    }
}

// Hook to automatically check and add indexes when needed
add_action('init', 'listeo_maybe_add_geolocation_indexes');

/**
 * Background cache warming for nearby listings
 * Scheduled via wp-cron to pre-populate cache for popular listings
 *
 * @return void
 */
function listeo_warm_nearby_listings_cache() {
    // Get popular listings (most viewed in last 30 days)
    $popular_listings = get_posts(array(
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'meta_key' => '_listing_views_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'date_query' => array(
            array(
                'after' => '30 days ago'
            )
        )
    ));
    
    $default_radius = get_option('listeo_nearby_listings_radius', 50);
    $default_unit = get_option('listeo_nearby_listings_unit', 'km');
    
    foreach ($popular_listings as $listing) {
        // Check if listing has geolocation data
        $lat = get_post_meta($listing->ID, '_geolocation_lat', true);
        $lng = get_post_meta($listing->ID, '_geolocation_long', true);
        
        if (!empty($lat) && !empty($lng)) {
            // Pre-warm cache by calling the function
            listeo_get_cached_nearby_listings($listing->ID, $default_radius, $default_unit);
            
            // Small delay to prevent overwhelming the server
            usleep(100000); // 0.1 second delay
        }
    }
    
    error_log('Listeo: Background cache warming completed for ' . count($popular_listings) . ' listings');
}

// Schedule background cache warming
add_action('wp', 'listeo_schedule_cache_warming');
function listeo_schedule_cache_warming() {
    if (!wp_next_scheduled('listeo_warm_nearby_cache')) {
        wp_schedule_event(time(), 'daily', 'listeo_warm_nearby_cache');
    }
}
add_action('listeo_warm_nearby_cache', 'listeo_warm_nearby_listings_cache');

/**
 * Performance monitoring for nearby listings feature
 *
 * @param string $operation Operation name
 * @param float $start_time Operation start time
 * @param array $context Additional context data
 * @return void
 */
function listeo_log_performance_metric($operation, $start_time, $context = array()) {
    if (!defined('LISTEO_DEBUG') || !LISTEO_DEBUG) {
        return;
    }
    
    $execution_time = microtime(true) - $start_time;
    
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'operation' => $operation,
        'execution_time' => round($execution_time, 4),
        'context' => $context
    );
    
    error_log('Listeo Performance: ' . wp_json_encode($log_entry));
    
    // Store in transient for admin dashboard (keep last 100 entries)
    $performance_log = get_transient('listeo_performance_log') ?: array();
    array_unshift($performance_log, $log_entry);
    $performance_log = array_slice($performance_log, 0, 100);
    set_transient('listeo_performance_log', $performance_log, WEEK_IN_SECONDS);
}

/**
 * Get performance metrics for admin dashboard
 *
 * @return array Performance metrics
 */
function listeo_get_performance_metrics() {
    global $wpdb;
    
    $metrics = array();
    
    // Get cache hit rate
    $cache_stats = get_transient('listeo_nearby_cache_stats') ?: array('hits' => 0, 'misses' => 0);
    $total_requests = $cache_stats['hits'] + $cache_stats['misses'];
    $metrics['cache_hit_rate'] = $total_requests > 0 ? round(($cache_stats['hits'] / $total_requests) * 100, 2) : 0;
    
    // Get database performance indicators
    $listing_count = wp_count_posts('listing')->publish;
    $metrics['total_listings'] = $listing_count;
    
    // Check if indexes exist
    $indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name LIKE 'idx_geolocation%'");
    $metrics['geolocation_indexes'] = count($indexes);
    
    // Get cache size estimate
    $cache_entries = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_listeo_nearby_listings_%'"
    );
    $metrics['cached_entries'] = $cache_entries;
    
    // Performance log
    $metrics['recent_performance'] = get_transient('listeo_performance_log') ?: array();
    
    return $metrics;
}

/**
 * Track cache usage statistics
 *
 * @param bool $is_hit Whether this was a cache hit
 * @return void
 */
function listeo_track_cache_usage($is_hit) {
    $stats = get_transient('listeo_nearby_cache_stats') ?: array('hits' => 0, 'misses' => 0);
    
    if ($is_hit) {
        $stats['hits']++;
    } else {
        $stats['misses']++;
    }
    
    set_transient('listeo_nearby_cache_stats', $stats, DAY_IN_SECONDS);
}

