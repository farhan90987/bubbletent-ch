<?php
namespace MWEW\Inc\Elementor\Widgets\Listing_Grid;


class Listing_Grid_Action{

   public function __construct(){
        add_action('wp_ajax_filter_listing_by_tag', [$this, 'handle_filter_listing_by_tag']);
        add_action('wp_ajax_nopriv_filter_listing_by_tag', [$this, 'handle_filter_listing_by_tag']);
   }

    public function handle_filter_listing_by_tag() {
        $term_slug = sanitize_text_field($_GET['term'] ?? 'all');
        $max = absint($_GET['max'] ?? 6);

        $args = [
            'post_type' => 'listing',
            'posts_per_page' => $term_slug === 'all' ? -1 : $max,
            'post_status'    => 'publish',
        ];

        if ( $term_slug !== 'all' ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'listing_location',
                    'field'    => 'slug',
                    'terms'    => $term_slug,
                ],
            ];
        }

        $query = new \WP_Query( $args );

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $img = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                echo '<div class="listing-card">';
                echo '<div class="listing-image" style="background:url(' . esc_url($img) . ')"></div>';
                echo '<div class="listing-card-location">';
                echo '<img src="' . MWEW_PATH_URL . 'assets/images/Location.svg" /> <span class="listing-title">' . esc_html(get_the_title()) . '</span>';
                echo '</div></div>';
            }
        } else {
            echo '<p>No listings found.</p>';
        }

        wp_die();
    }

}