<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'includes/email-handler.php'; // Email handler

// // Admin menu page
add_action('admin_menu', function() {
    add_menu_page(
        'Christmas Order',
        'Christmas Order',
        'manage_woocommerce',
        'christmas-order',
        'render_christmas_order',
        'dashicons-printer',
        56
    );
});

function render_christmas_order() {
    ?>
    <div class="wrap">
        <h1>Christmas Printer Email Send</h1>
        <form method="post">
            <?php wp_nonce_field('fetch_orders_action', 'fetch_orders_nonce'); ?>         
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date">
            <input type="submit" name="fetch_orders" class="button button-primary" value="Fetch Orders">
        </form>
        <?php
        if (isset($_POST['fetch_orders']) && check_admin_referer('fetch_orders_action', 'fetch_orders_nonce')) {
            $start = sanitize_text_field($_POST['start_date']);
            $end   = sanitize_text_field($_POST['end_date']);
			fetch_and_display_order($start , $end);
        }
        ?>
    </div>
    <?php
}

function fetch_and_display_order($start_date, $end_date) {
    global $wpdb;
	if( !empty($start_date) && !empty($end_date)){
        $today_ts = strtotime(date('Y-m-d'));
        $statuses = array('wc-processing', 'wc-on-hold', 'wc-completed');

        $start_dt = date('Y-m-d H:i:s', strtotime($start_date));
        $end_dt   = date('Y-m-d H:i:s', strtotime($end_date . ' 23:59:59'));
		// fallback only by date range + status
		$product_ids = array(222556, 222557, 222558);
		$statuses = 'wc-completed';

		$query = $wpdb->prepare("
			SELECT DISTINCT p.ID
			FROM {$wpdb->prefix}posts p
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
			WHERE p.post_type = 'shop_order'
			AND p.post_status IN ('". $statuses . "')
			AND p.post_date BETWEEN %s AND %s
			AND oim.meta_key = '_product_id'
			AND oim.meta_value IN (" . implode(',', $product_ids) . ")
		", $start_dt, $end_dt);

		$order_ids = $wpdb->get_col($query);
        if (empty($order_ids)) {
            echo "<p>No orders found.</p>";
            return;
        }

        echo "<table class='widefat striped'>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Email</th>
                    <th>Customer Name</th>
                    <th>Status</th>
                    <th>Create date</th>
					<th>Voucher code</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;
            $order_email    = $order->get_billing_email();
            $status   = wc_get_order_status_name($order->get_status());
            $arrival_raw  = trim($order->get_meta('smoobu_calendar_start'));
            $create_date  = $order->get_date_created();
            $create_date  = $create_date->date('d-m-Y');
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            $user_name = $first_name . ' ' . $last_name;
			$voucher_code = get_post_meta($order_id , '_voucher_code' , true);
            echo "<tr>
                <td>{$order_id}</td>
                <td>{$order_email}</td>
                <td>{$user_name}</td>
                <td>{$status}</td>
                <td>{$create_date}</td>
				<td>{$voucher_code}</td>
                <td><a href='#' class='button resend-email' data-email='farhan90987@gmail.com' data-voucher='". $voucher_code ."' data-order-id='{$order_id}'>Send Printer Email</a></td>
            </tr>";
        }

        echo "</tbody></table>";

        // Batch send button
        if (!empty($order_ids)) {
            $nonce = wp_create_nonce('send_all_emails_ajax_printer');
            echo "<br><div style='display:none' id='progress-container'><div id='progress-bar'>0%</div></div>";
            echo "<button id='send-all-emails' class='button button-primary' data-nonce='{$nonce}' data-orders='" . esc_attr(json_encode($order_ids)) . "'>Send Printer Emails</button>";
        }
    }else{
        echo "Please select date";
    }
}


add_action('wp_ajax_send_all_emails_printer', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'send_all_emails_ajax_printer')) {
        wp_send_json_error(['message' => 'Invalid security token.']);
    }

    if (empty($_POST['order_ids']) || !is_array($_POST['order_ids'])) {
        wp_send_json_error(['message' => 'No orders received.']);
    }

    $order_ids = array_map('intval', $_POST['order_ids']);
    $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
    $batch_size = 50;

    $batch_orders = array_slice($order_ids, $batch * $batch_size, $batch_size);
    $sent_count = 0;

    foreach ($batch_orders as $order_id) {
        if (wc_get_order($order_id)) {
			$voucher_code = get_post_meta($order_id , '_voucher_code' , true);
            send_email_to_printer(wc_get_order($order_id) , $voucher_code , 'farhan90987@gmail.com'); 
            $sent_count++;
        }
    }

    $has_more = (($batch + 1) * $batch_size) < count($order_ids);
    wp_send_json_success([
        'message'   => "Batch {$batch} - {$sent_count} emails sent.",
        'has_more'  => $has_more,
        'processed' => ($batch + 1) * $batch_size > count($order_ids) ? count($order_ids) : ($batch + 1) * $batch_size,
        'total'     => count($order_ids)
    ]);
});

// Resend single email
add_action('wp_ajax_resend_order_email_printer', function () {
    if (!isset($_POST['order_id']) || !check_ajax_referer('resend_email_nonce_printer', 'nonce', false)) {
        wp_send_json_error('Invalid request' . $_POST['order_id']);
    }

    $order_id = intval($_POST['order_id']);
	$voucher = $_POST['voucher'];
	$email = $_POST['email'];

    if (wc_get_order($order_id)) {
        send_email_to_printer(wc_get_order($order_id) , $voucher , $email);
        wp_send_json_success('Email sent');
    } else {
        wp_send_json_error('Order not found');
    }
});

// Admin footer JS
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'christmas-order') {
        ?>
        <style>
            #progress-container {
                width: 100%;
                background: #eee;
                margin-top: 15px;
                display: none;
                margin-bottom:10px;
            }
            #progress-bar {
                width: 0%;
                height: 20px;
                background: #0073aa;
                color: #fff;
                text-align: center;
                line-height: 20px;
                font-size: 12px;
            }
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Batch send emails
            $('#send-all-emails').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let nonce = button.data('nonce');
                let orders = button.data('orders');
                let total = orders.length;
                let batch = 0;

                // Show progress bar
                $('#progress-container').show();
                $('#progress-bar').css('width', '0%').text('0%');

                button.prop('disabled', true).text('Sending batch 1...');

                function sendBatch() {
                    $.post(ajaxurl, {
                        action: 'send_all_emails_printer',
                        nonce: nonce,
                        order_ids: orders,
                        batch: batch
                    }, function(response) {
                        if (response.success) {
                            console.log(response.data.message);

                            // Update progress
                            let processed = response.data.processed;
                            let percent = Math.round((processed / total) * 100);
                            $('#progress-bar').css('width', percent + '%').text(percent + '%');

                            if (response.data.has_more) {
                                batch++;
                                button.text('Sending batch ' + (batch + 1) + '...');
                                sendBatch();
                            } else {
                                $('#progress-bar').css('width', '100%').text('100%');
                                alert('All emails sent successfully!');
                                button.prop('disabled', false).text('Send All Emails in Batches');
                            }
                        } else {
                            alert('Error: ' + response.data.message);
                            button.prop('disabled', false).text('Send All Emails in Batches');
                        }
                    });
                }

                sendBatch();
            });

            // Resend single email
            $('.resend-email').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let orderId = button.data('order-id');
				let voucher = button.data('voucher');
				let email = button.data('email');
                button.text('Sending...');

                $.post(ajaxurl, {
                    action: 'resend_order_email_printer',
                    order_id: orderId,
					voucher : voucher,
					email : email,
                    nonce: '<?php echo wp_create_nonce("resend_email_nonce_printer"); ?>'
                }, function(response) {
                    alert(response.data || 'Something went wrong');
                    button.text('Send Printer Email');
                });
            });
        });
        </script>
        <?php
    }
});