<?php

namespace MWEW\Inc\Shortcodes;

use MWEW\Inc\Logger\Logger;
use MWEW\Inc\Services\Calendar_Availability;

class MW_Search_Action{

    public function __construct(){
        add_action( 'wp_ajax_nopriv_mwew_get_listings', array( $this, 'ajax_get_listings' ) );
		add_action( 'wp_ajax_mwew_get_listings', array( $this, 'ajax_get_listings' ) );
    }


    public function ajax_get_listings() {

		check_ajax_referer('mwew_plugin_nonce', 'security');

		$availability_api = new \Smoobu_Api_Availability();
		$availability_api->fetch_availability();

		$template_loader = new \Listeo_Core_Template_Loader;

		$radius   	= (isset($_REQUEST['search_radius'])) ?  sanitize_text_field( stripslashes( $_REQUEST['search_radius'] ) ) : '';

		$style   	= 'list';
		$grid_columns  = 2;
		$check_in   	= (isset($_REQUEST['check_in'])) ?  sanitize_text_field(  $_REQUEST['check_in']  ) : '';
		$check_out   	= (isset($_REQUEST['check_out'])) ?  sanitize_text_field(  $_REQUEST['check_out']  ) : '';
		

		$query_args = array(
		    'search_radius'       => $radius,
		    'check_in'            => $check_in,
		    'check_out'           => $check_out,
		);

		$taxonomy_objects = get_object_taxonomies( 'listing', 'objects' );
		foreach ($taxonomy_objects as $tax) {
			if(isset($_REQUEST[ 'tax-'.$tax->name ] )) {
				$query_args[ 'tax-'.$tax->name ] = $_REQUEST[ 'tax-'.$tax->name ];
			}
        }
		
		$available_query_vars = $this->build_available_query_vars();
		foreach ($available_query_vars as $key => $meta_key) {

			if( isset($_REQUEST[ $meta_key ]) && $_REQUEST[ $meta_key ] != -1){

				$query_args[ $meta_key ] = $_REQUEST[ $meta_key ];	
				
			}
			
		}

	
		$listings = \Listeo_Core_Listing::get_real_listings( $query_args);

        $result = array(
			'max_num_pages' => $listings->max_num_pages,
		);

        $listing_count = 0;

		ob_start();
		if ( $listings->have_posts() ) {
			$style_data = array(
				'style' 		=> $style,  
				'grid_columns' 	=> $grid_columns,
				'max_num_pages'	=> $listings->max_num_pages, 
				'counter'		=> $listings->found_posts 
			);
			?>
			<div class="loader-ajax-container"> <div class="loader-ajax"></div> </div>
				<?php
				while ( $listings->have_posts() ) {
					$listings->the_post();
					$post_id = get_the_ID();
                        // Filter here
                        if (!Calendar_Availability::is_available($post_id, $check_in, $check_out)) {
                            continue;
                        }
                        $listing_count++;

                       // Logger::debug("Listing ID: " . $post_id . " Check in " . $check_in . " Check out " . $check_out . " count: " . $listing_count);
                                
					    $template_loader->set_template_data( $style_data )->get_template_part( 'content-listing', $style ); 	
					}
				?>
				<div class="clearfix"></div>
			</div>
			<?php
		}

        if($listing_count == 0) {
			?>
			<div class="loader-ajax-container"> <div class="loader-ajax"></div></div>
			<div id="listeo-listings-container">
				<div class="loader-ajax-container"> <div class="loader-ajax"></div> </div>
					<section id="listings-not-found" class="margin-bottom-50 col-md-12">
						<h2><?php esc_html_e('Nothing Found','mwew'); ?></h2>
						<p><?php _e( 'Unfortunately, we didn\'t find any results matching your search. Please try changing your search settings.', 'mwew' ); ?></p>
					</section>
				</div>
			<div class="clearfix"></div>
			<?php
		}
        
		$result['found_listings'] = $listing_count > 0 ? true : false;
		$max_pages = ceil($listing_count / 10);

		$result['html'] = ob_get_clean();
		$result['pagination'] = $listing_count > 0 ? listeo_core_ajax_pagination( $max_pages, absint( $_REQUEST['page'] ) ) : '';
	
		wp_send_json($result);
		
	}

    public static function build_available_query_vars(){
		$query_vars = array();
		$taxonomy_objects = get_object_taxonomies( 'listing', 'objects' );
        foreach ($taxonomy_objects as $tax) {
        	array_push($query_vars, 'tax-'.$tax->name);
        }
        
     
      
        $service = \Listeo_Core_Meta_Boxes::meta_boxes_service();
            foreach ($service['fields'] as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        } 
        $location = \Listeo_Core_Meta_Boxes::meta_boxes_location();
        
            foreach ($location['fields'] as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        }
        $event = \Listeo_Core_Meta_Boxes::meta_boxes_event();
            foreach ($event['fields']  as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        }  
        $prices = \Listeo_Core_Meta_Boxes::meta_boxes_prices();
            foreach ($prices['fields']  as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        }  
        $contact = \Listeo_Core_Meta_Boxes::meta_boxes_contact();
        
            foreach ($contact['fields']  as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        } 
        $rental = \Listeo_Core_Meta_Boxes::meta_boxes_rental();
            foreach ($rental['fields']  as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        }  
        $custom = \Listeo_Core_Meta_Boxes::meta_boxes_custom();
            foreach ($custom['fields']  as $key => $field) {
              	array_push($query_vars, $field['id']);
              	
        } 
        array_push($query_vars, '_price_range');
        array_push($query_vars, '_listing_type');
        //array_push($query_vars, '_verified');
        array_push($query_vars, '_price');
        array_push($query_vars, '_max_guests');
        array_push($query_vars, '_min_guests');
        array_push($query_vars, '_instant_booking');
		return $query_vars;
	}
}