<?php
namespace MWEW\Inc;

use MWEW\Inc\Admin\Admin_Init;
use MWEW\Inc\Database\Listing_Maps_DB;
use MWEW\Inc\Elementor\Elementor_Init;
use MWEW\Inc\Logger\Logger;
use MWEW\Inc\Orders\Order_Meta_Init;
use MWEW\Inc\Shortcodes\Shortcodes_Init;
use MWEW\Inc\Services\Calendar_Availability;

class Mwew_Init {

    public function __construct() {
        new Elementor_Init();
        new Admin_Init();
        new Shortcodes_Init();
        new Order_Meta_Init();

        $this->load_hooks();
    }

    private function load_hooks(){
        add_action('wp_enqueue_scripts', [$this, 'load_styles']);
        add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
        
        add_action('admin_enqueue_scripts', [$this, 'load_admin_script']);

        add_action('admin_enqueue_scripts', [$this, 'load_admin_style']);

        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

        //add_filter('template_include', [$this, 'listing_archive_template'], 10, 1);
    }

    public function listing_archive_template($template){
        if (is_post_type_archive('listing')) {
            $plugin_template = MWEW_DIR_PATH . '/templates/archive-listing.php';
            if (file_exists($plugin_template)) {
                Logger::debug("$plugin_template");
                return $plugin_template;
            }
        }
        return $template;
    }

    public function load_styles(){
        wp_enqueue_style( 'mw-owl-carousel', MWEW_PATH_URL.'assets/css/owl.carousel.min.css' );
        wp_enqueue_style( 'mw-owl-carousel-theme', MWEW_PATH_URL.'assets/css/owl.theme.default.min.css' );

        wp_register_style( 'mw-hero-slider', MWEW_PATH_URL . 'inc/elementor/widgets/hero-slider/css/mw-slider.css', [], wp_rand(), 'all' );

        
        wp_register_style('mw-country-map', MWEW_PATH_URL . 'inc/elementor/widgets/area-map/css/styles.css', [], wp_rand(), 'all');
        
        wp_register_style('mw-listing-grid', MWEW_PATH_URL . 'inc/elementor/widgets/listing-grid/css/listing-grid.css', [], wp_rand(), 'all');

        
        wp_enqueue_style(
            'mwew-plugin-style', 
            MWEW_PATH_URL . 'assets/css/style.css',
            [],
            wp_rand(),
            'all'
        );
    }


    public function load_scripts() {

        wp_enqueue_script( 'mw-owl-carousel',  MWEW_PATH_URL .'assets/js/owl.carousel.min.js', array('jquery'), '1.0', true );
        
        wp_enqueue_script( 'mw-easepick',  'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.umd.min.js', array(), '1.2.1', true );

        wp_register_script('mw-hero-slider', MWEW_PATH_URL .'inc/elementor/widgets/hero-slider/js/widgets.js', ['jquery'], wp_rand(), false);

        wp_register_script('mw-country-map', MWEW_PATH_URL . 'inc/elementor/widgets/area-map/js/main.js', [], wp_rand());
        
        wp_register_script('mw-listing-grid', MWEW_PATH_URL . 'inc/elementor/widgets/listing-grid/js/listing-grid.js', [], wp_rand());

        wp_enqueue_script(
            'mwew-plugin-script',
            MWEW_PATH_URL . 'assets/js/script.js',
            ['jquery'],
            wp_rand(), 
            true
        );

        $localized_data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('mwew_plugin_nonce'),
            'plugin_url' => MWEW_PATH_URL
        ];

        wp_localize_script('mwew-plugin-script', 'mwewPluginData', $localized_data);
    }

    public function load_admin_script($hook){

        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'region') {
            wp_enqueue_media();
            wp_enqueue_script(
                'mwew-map-uploader',
                MWEW_PATH_URL . 'assets/js/map-image-uploader.js',
                ['jquery'],
                MWEW_VERSION, 
                true
            );
        }

        global $post;

        if ( isset($post->post_type) && $post->post_type === 'shop_order' && isset($_GET['post']) && !empty($_GET['post']) ) {
            
            $order_id = isset($_GET['post']) && !empty($_GET['post']) ? intval($_GET['post']) : 0;
            
            wp_enqueue_script( 'mw-easepick',  'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.umd.min.js', [], '1.2.1', true );

            wp_enqueue_script(
                'mwew-easepick-picker',
                MWEW_PATH_URL . 'assets/js/easepick-picker.js',
                ['jquery','mw-easepick'],
                wp_rand(), 
                true
            );

            wp_enqueue_script(
                'mwew-shop-order',
                MWEW_PATH_URL . 'assets/js/shop-order.js',
                ['jquery'],
                wp_rand(), 
                true
            );

            $localized_data = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mwew_plugin_nonce'),
                'plugin_url' => MWEW_PATH_URL,
                'mw_busy_dates' => Calendar_Availability::get_busy_dates_by_order_id($order_id),
            ];
            wp_localize_script('mwew-shop-order', 'mwewPluginData', $localized_data);
        }

        $allowed_hooks = [
            'toplevel_page_mw-map-builder',
            'mw-map-builder_page_mw-new-map-builder'
        ];

        if (!in_array($hook, $allowed_hooks)) {
            return;
        }

        wp_enqueue_script(
            'mwew-tailwind',
            'https://cdn.tailwindcss.com',
            [],
            MWEW_VERSION, 
            true
        );
        wp_enqueue_script(
            'mwew-map-action',
            MWEW_PATH_URL . 'assets/js/map-builder-action.js',
            [],
            wp_rand(), 
            true
        );
        
        wp_enqueue_script(
            'mwew-map-builder',
            MWEW_PATH_URL . 'assets/js/map-builder.js',
            [],
            wp_rand(), 
            true
        );
        
    }

    public function load_admin_style($hook){
        $allowed_hooks = [
            'toplevel_page_mw-map-builder',
            'mw-map-builder_page_mw-new-map-builder'
        ];

        if (!in_array($hook, $allowed_hooks)) {
            return;
        }

        wp_enqueue_style(
            'mwew-admin-style', 
            MWEW_PATH_URL . 'assets/css/admin-style.css',
            [],
            wp_rand(),
            'all'
        );


    }

    public static function load_textdomain() {
        load_plugin_textdomain(
            "mwew",
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * The activation hook for the plugin.
     * This method will run when the plugin is activated.
     */
    public static function activate() {

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        Listing_Maps_DB::maybe_upgrade();

        add_option( 'mwew_plugin_activated', true );

    }

    /**
     * The deactivation hook for the plugin.
     * This method will run when the plugin is deactivated.
     */
    public static function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        delete_option( 'mwew_plugin_activated' );
    }

    public static function uninstall() {
        // if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        //     die; // Exit if this is not a valid uninstall request
        // }
        Listing_Maps_DB::drop_table();
        
        delete_option( 'mwew_plugin_activated' );
        
    }
}
