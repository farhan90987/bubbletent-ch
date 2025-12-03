<?php
namespace MWEW\Inc\Google_Tags;

use MWEW\Inc\Logger\Logger;

class Event_Tracking {

    public function __construct() {
        add_action( 'wp_head', [ $this, 'inject_gtm_script' ], 1 );
        add_action( 'wp_body_open', [ $this, 'inject_gtm_noscript' ] );
        add_action( 'wp_footer', [ $this, 'inject_tracking_events' ], 100 );
       // add_action( 'wp', [$this, 'identify_post_type'] );
    }

    public function identify_post_type(){

        global $post;

        if ( isset( $post ) && is_object( $post ) ) {
            $post_type = get_post_type( $post );
        } else {
            $post_type = null;
        }

        Logger::debug("Identifying post type: $post_type - {$post->ID}");
    }

    public function inject_gtm_script() {
        if ( ! $this->is_tracking_enabled() ) {
            return;
        }

        $settings = get_option( 'mwew_gtm_ga4_settings', [] );
        $gtm_id = $settings['gtm_container_id'] ?? '';

        if ( ! empty( $gtm_id ) ) :
            ?>
            <!-- Google Tag Manager -->
            <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');
            </script>
            <!-- End Google Tag Manager -->
            <?php
        endif;
    }

    public function inject_gtm_noscript() {
        if ( ! $this->is_tracking_enabled() ) {
            return;
        }

        $settings = get_option( 'mwew_gtm_ga4_settings', [] );
        $gtm_id = $settings['gtm_container_id'] ?? '';

        if ( ! empty( $gtm_id ) ) :
            ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript>
                <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
            </noscript>
            <!-- End Google Tag Manager (noscript) -->
            <?php
        endif;
    }

    public function inject_tracking_events() {
        if ( ! $this->is_tracking_enabled() ) {
            return;
        }

        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        <?php

        if ( $this->is_thank_you_page() ) {
            $booking = $this->get_booking_data();
            ?>
            sessionStorage.setItem('mwew_booking_completed', true)
            sessionStorage.setItem('mwew_reservation_data', []);

            dataLayer.push({
                event: 'booking_completed',
                booking_value: <?php echo esc_js( $booking['value'] ); ?>,
                checkin_date: '<?php echo esc_js( $booking['checkin_date'] ); ?>',
                checkout_date: '<?php echo esc_js( $booking['checkout_date'] ); ?>',
                num_nights: <?php echo esc_js( $booking['num_nights'] ); ?>,
                num_guests: <?php echo esc_js( $booking['num_guests'] ); ?>,
                bubble_id: '<?php echo esc_js( $booking['bubble_id'] ); ?>',
                bubble_name: '<?php echo esc_js( $booking['bubble_name'] ); ?>'
            });
            <?php
        }

        ?>

        if (sessionStorage.getItem('mwew_booking_completed') !== 'true') {
            window.addEventListener('beforeunload', function() {
                var reservationData = sessionStorage.getItem('mwew_reservation_data');
                if (reservationData) {
                    try {
                        var data = JSON.parse(reservationData);
                        if (Array.isArray(data) && data.length > 0) {
                            dataLayer.push({
                                event: 'date_selected',
                                reservations: data
                            });
                            //console.log('Abandonment tracked - all reservations:', data);
                            sessionStorage.setItem('mwew_reservation_data', []);
                        }
                    } catch (e) {
                        console.warn('Invalid reservation data in sessionStorage');
                    }
                }
            });
        }

        <?php

        if ( is_singular( 'listing' ) ) {

            $bubble = $this->get_bubble_data();

            if ( ! empty( $bubble ) ) :
                ?>
                dataLayer.push({
                    event: 'accommodation_viewed',
                    bubble_id: '<?php echo esc_js( $bubble['id'] ); ?>',
                    bubble_name: '<?php echo esc_js( $bubble['name'] ); ?>',
                    location: '<?php echo esc_js( $bubble['location'] ); ?>',
                    price_per_night: <?php echo esc_js( $bubble['price'] ); ?>
                });

                (function() {
                    var viewedRaw = sessionStorage.getItem('mwew_browsing_titles');
                    var viewed = [];

                    if (viewedRaw) {
                        try {
                            viewed = JSON.parse(viewedRaw);
                        } catch (e) {
                            console.warn('Invalid sessionStorage JSON, resetting.');
                            viewed = [];
                        }
                    }

                    console.log('Before update: ', viewed);

                    var currentTitle = <?php echo json_encode(get_the_title($bubble['id'])); ?>;

                    if (!viewed.includes(currentTitle)) {
                        viewed.push(currentTitle);
                    }

                    var uniqueViewed = Array.from(new Set(viewed));

                    sessionStorage.setItem('mwew_browsing_titles', JSON.stringify(uniqueViewed));

                    console.log('After update: ', uniqueViewed);

                    if (uniqueViewed.length >= 3 && !sessionStorage.getItem('mwew_fired_browsing_depth')) {
                        dataLayer.push({
                            event: 'listing_browsing_depth',
                            bubble_titles: uniqueViewed
                        });
                        sessionStorage.setItem('mwew_fired_browsing_depth', '1');
                    }
                })();

                <?php
            endif;
        }

        if ( $this->should_fire_long_session() ) {
            ?>
            setTimeout(function() {
                dataLayer.push({
                    event: 'long_session_no_conversion'
                });
            }, 120000);
            <?php
        }

        if ( $this->is_voucher_viewed() ) {
            $voucher = $this->get_voucher_data();
            ?>
            dataLayer.push({
                event: 'voucher_viewed',
                voucher_type: '<?php echo esc_js( $voucher['type'] ); ?>',
                bubble_name: '<?php echo esc_js( $voucher['bubble_name'] ); ?>',
                current_page: '<?php echo esc_url( $voucher['page'] ); ?>'
            });
            <?php
        }
        ?>
        </script>
        <?php
    }

    private function is_thank_you_page() {
        return is_page( 'thank-you' );
    }

    private function is_voucher_viewed() {
        return $this->is_voucher_page_is_product() || $this->is_voucher_page_is_page();
    }

    private function is_voucher_page_is_product(){
        global $post;

        $settings = get_option( 'mwew_gtm_ga4_settings', [] );
        $voucher_category_ids = $settings['voucher_category_ids'] ?? [];

        if ( is_singular( 'product' ) && ! empty( $voucher_category_ids ) ) {
            $terms = wp_get_post_terms( $post->ID, 'product_cat', [ 'fields' => 'ids' ] );
            if ( ! empty( $terms ) ) {
                foreach ( $terms as $term_id ) {
                    if ( in_array( $term_id, $voucher_category_ids ) ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function is_voucher_page_is_page() {
        return is_page() 
            && isset($_GET['listing_id']) 
            && !empty(intval($_GET['listing_id'])) 
            && intval($_GET['listing_id']) > 0;
    }


    private function is_booking_completed() {
        return isset( $_SESSION['mwew_booking_completed'] );
    }

    private function should_fire_long_session() {
        return ! $this->is_booking_completed();
    }

    private function get_booking_data() {
        if ( ! function_exists( 'wc_get_order' ) || ! is_order_received_page() ) {
            return [];
        }

        $order_id  = absint( get_query_var( 'order-received' ) );
        $order     = wc_get_order( $order_id );

        if ( ! $order ) {
            return [];
        }

        $booking_value = (float) $order->get_total();
        $num_guests    = 0;
        $num_nights    = 0;
        $checkin_date  = '';
        $checkout_date = '';
        $bubble_id     = '';
        $bubble_name   = '';

        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();
            if ( $product ) {
                $bubble_id   = $product->get_id();
                $bubble_name = $product->get_name();

                $checkin_date  = $item->get_meta( 'checkin_date' );
                $checkout_date = $item->get_meta( 'checkout_date' );
                $num_guests    = (int) $item->get_meta( 'num_guests' );
                $num_nights    = (int) $item->get_meta( 'num_nights' );
            }
        }

        return [
            'value'         => $booking_value,
            'checkin_date'  => $checkin_date,
            'checkout_date' => $checkout_date,
            'num_nights'    => $num_nights,
            'num_guests'    => $num_guests,
            'bubble_id'     => $bubble_id,
            'bubble_name'   => $bubble_name,
        ];
    }

    private function get_bubble_data() {
        global $post;

        $woo_product_id = get_post_meta( $post->ID, 'product_id', true );
        $base_price = get_post_meta( $woo_product_id, 'sa_cfw_cog_amount', true );

        return [
            'id'       => $post->ID,
            'name'     => get_the_title( $post ),
            'location' => get_post_meta( $post->ID, '_friendly_address', true ) ?: 'Unknown',
            'price'    => $base_price ?: 0
        ];
    }

    private function get_voucher_data() {
        if($this->is_voucher_page_is_page()){
            $listing_id = $_GET['listing_id'];
            $listing_type = 'listing-specific';
        }else if($this->is_voucher_page_is_product()){
            $listing_id = get_the_ID();
            $listing_type = 'global';
        }

        $name = get_the_title( $listing_id );

        $page_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return [
            'type'         => $listing_type,
            'bubble_name'  => $name ?? '',
            'page'         => $page_url,
        ];
    }

    private function is_tracking_enabled() {
        $settings = get_option( 'mwew_gtm_ga4_settings', [] );
        return ! empty( $settings['enable_event_tracking'] );
    }
}
