<?php 
/*
Plugin Name: Cherry Hill SkipBooking
Description: Booking plugin for Cherry Hill Waste
Author: Ruel
Version: 1.0
*/

function ch_booking_enqueue_style() {
	wp_enqueue_style( 'ch-booking-style', plugins_url() . '/cherry_hill_booking/booking-style.css', false );
	// if (is_page('Book a Skip')) {
		// wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');//for dev
		wp_register_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
	    wp_enqueue_style( 'jquery-ui' ); 
	// }
}

function ch_booking_enqueue_script() {
	// if (is_page('Book a Skip')) {
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'ch-booking-script', plugins_url() . '/cherry_hill_booking/booking.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),'2.7' );
		wp_localize_script( 'ch-booking-script', 'ajax_ch_booking_object', array( 
	        'ajaxurl' 			=> admin_url('admin-ajax.php'),
	        'redirecturl' 		=> $_SERVER['REQUEST_URI'],
	        'loadingmessage' 	=> __('Loading...')
	    ));
	// }
}

add_action( 'wp_enqueue_scripts', 'ch_booking_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'ch_booking_enqueue_script' );

function get_skip_price($location, $size) {
	// $pricelist = array(
	// 	'ST1' => array(
	// 		'2' => 
	// 	);
	// );
	// $result = '';
	// $row = 1;

	// $link = dirname(__FILE__) . '/pricelist.csv';
	// // print_r(PHP_EOL.$link.PHP_EOL);
	// // var_dump(file_exists($link));
	// $file = fopen($link, "r");
	// var_dump($file);

	// while(! feof($file)){
	//   print_r(fgetcsv($file));
	// }

	// fclose($file);

	// var_dump($handle = fopen($link, "r"));
	// if (($handle = fopen($link, "r")) !== FALSE) {
	//     while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	//         $num = count($data);
	//         $result .= "<p> $num полей в строке $row: <br /></p>\n";
	//         $row++;
	//         for ($c=0; $c < $num; $c++) {
	//             echo $data[$c] . "<br />\n";
	//         }
	//     }
	//     fclose($handle);
	// }
	// return $result;
	// $content = file_get_contents($link);
	// print_r($content);
	// $csv = str_getcsv($content);
	// print_r($csv);
}

function ch_ajax_price_handler() {
	print_r($_POST);
	if ($_POST['method'] == 'get_price') {
		echo 'get_price';
		echo get_skip_price(1, 1);
	}
	die();
}
add_action('wp_ajax_ch_price_handler', 'ch_ajax_price_handler');
add_action('wp_ajax_nopriv_ch_price_handler', 'ch_ajax_price_handler');

function ch_ajax_booking_handler() {
	// print_r($_POST);
	$_product = get_page_by_title('Skip Hire', OBJECT, 'product');
	global $woocommerce;
	$woocommerce->cart->add_to_cart($_product->ID);
	echo get_permalink(get_page_by_title('Cart'));
	die();
}
add_action('wp_ajax_ch_booking_handler', 'ch_ajax_booking_handler');
add_action('wp_ajax_nopriv_ch_booking_handler', 'ch_ajax_booking_handler');

function ch_add_item_data($cart_item_data, $product_id) {
    global $woocommerce;
    $new_value = array();
    $new_value['_custom_options'] = $_POST['product'];
    if (empty($cart_item_data)) {
        return $new_value;
    } else {
        return array_merge($cart_item_data, $new_value);
    }
}
add_filter('woocommerce_add_cart_item_data', 'ch_add_item_data', 1, 10);

function ch_get_cart_items_from_session($item, $values, $key) {
    if (array_key_exists( '_custom_options', $values)) {
        $item['_custom_options'] = $values['_custom_options'];
    }
    return $item;
}
add_filter('woocommerce_get_cart_item_from_session', 'ch_get_cart_items_from_session', 1, 3);

function add_usr_custom_session($product_name, $values, $cart_item_key) {
	if (empty($values['_custom_options']['period'])) $values['_custom_options']['period'] = date('Y-m-d', strtotime('+28 days'));
    $return_string = '<b><u>' . strip_tags($product_name) . "</u></b><br />" .
					'<span class="product-option-label">Size: </span>' . $values['_custom_options']['size'] . "<br />" .
					'<span class="product-option-label">Postcode: </span>' . $values['_custom_options']['postcode'] . "<br />" .
					'<span class="product-option-label">Period of Hire: </span>' . $values['_custom_options']['period'] . "<br />" .
					'<span class="product-option-label">Type of Waste: </span>' . $values['_custom_options']['wasteType'] . "<br />" .
					'<span class="product-option-label">Location: </span>' . $values['_custom_options']['landType'] . "<br />" .
					'<span class="product-option-label">Parking permit charge: </span>' . $values['_custom_options']['parkingPermit'] . "<br />";
    return $return_string;
}
add_filter('woocommerce_cart_item_name', 'add_usr_custom_session', 1, 3);

function ch_add_values_to_order_item_meta($item_id, $values) {
    global $woocommerce, $wpdb;
    wc_add_order_item_meta($item_id, 'item_details', $values['_custom_options']);
    // wc_add_order_item_meta($item_id, 'item_details', 'This is custom meta');
    // wc_add_order_item_meta($item_id, 'customer_image', $values['_custom_options']['another_example_field']);
    // wc_add_order_item_meta($item_id, '_hidden_field', $values['_custom_options']['hidden_info']);
}
add_action('woocommerce_add_order_item_meta', 'ch_add_values_to_order_item_meta', 1, 2);

function order_meta_customized_display( $item_id, $item, $product ) {
	$item_meta = get_metadata( 'order_item', $item_id, 'item_details' );
	if (!empty($item_meta)) {
		// echo '<pre>';
		// print_r($item_meta);
		// echo '</pre>';
		$item_meta = $item_meta[0];
		unset($item_meta['price']);
		echo '<h4>Order properties</h4>';
		foreach($item_meta as $data_meta_key => $value) {
	 		echo '<p><span style="display:inline-block; width:100px;">' . __( $data_meta_key ) . '</span><span>:&nbsp;' . $value . '</span></p>';
	 	}
	}
}
add_action( 'woocommerce_after_order_itemmeta', 'order_meta_customized_display', 10, 3 );

function update_custom_price($cart_object) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    foreach ( $cart_object->get_cart() as $key => $value ) {
        $value['data']->set_price( $value['_custom_options']['price'] );
    }
}
add_action('woocommerce_before_calculate_totals', 'update_custom_price', 1, 1);

add_filter( 'woocommerce_cart_item_thumbnail', '__return_false' );//Hide product thumbnails

function action_woocommerce_email_order_meta( $order, $sent_to_admin, $plain_text, $email ) { 
	$items = $order->get_items();
	$item = array_shift($items);
	$item_meta = access_protected_property($item, 'meta_data');
	$item_details = array_shift($item_meta);
	echo '<h2>Order details</h2>';
	echo '<p><b>Skip size:</b> '.$item_details->value['size'].'<br>';
	echo '<b>Period of Hire:</b> '.$item_details->value['period'].'<br>';
	echo '<b>Type of Waste:</b> '.$item_details->value['wasteType'].'<br>';
	echo '<b>Skip situated on:</b> '.$item_details->value['landType'].'<br>';
	echo '<b>Parking Permit:</b> '.$item_details->value['parkingPermit'].'</p>';
}; 
         
// add the action 
add_action( 'woocommerce_email_order_meta', 'action_woocommerce_email_order_meta', 10, 4 );

function access_protected_property($obj, $prop) {
	$reflection = new ReflectionClass($obj);
	$property = $reflection->getProperty($prop);
	$property->setAccessible(true);
	return $property->getValue($obj);
}

function ch_place_booking() {
	$postcodes = array('ST2', 'ST3', 'ST4', 'ST5', 'ST6', 'ST7', 'ST8', 'ST9', 'ST10', 'ST11', 'ST12', 'ST13',
						'ST14', 'ST15', 'ST16', 'ST17', 'ST18', 'ST20', 'ST21', 'TF9', 'CW1', 'CW2', 'CW5', 'CW11', 'CW12');
	ob_start(); ?>
	<form id="ch-booking-form" class="ch-booking-form" method="post" action="">
		<div class="ch-booking-form-header">
			<h2>Skip Hire Order Form</h2>
		</div>
		<div class="ch-booking-form-body">
			<?php
			/*<label for="skip-type-select" class="ch-form-label">Is the skip for Domestic or Commercial purposes?</label><br>
			<input type="hidden" id="skip-type-select" name="skip-type" value="domestic">
			<div class="chb-type-tabs">
				<span id="chb-type-domestic" class="chb-tab-selected" data-value="domestic">Domestic</span>
				<span id="chb-type-commercial" data-value="commercial">Commercial</span>
			</div>*/
			?>
			<label for="skip-size-select">1. Select your Skip Size:</label><br>
			<select id="skip-size-select" name="skip-size">
				<option value="2" selected>2yrd</option>
				<option value="3">3yrd</option>
				<option value="4">4yrd</option>
				<option value="5">5yrd</option>
				<option value="8">8yrd</option>
				<option value="10">10yrd</option>
				<!--option value="12">12yrd</option-->
				<!--option value="12e">12yrd enclosed</option-->
			</select>
			<label for="postcode-input">2. Postcode</label><br>
			<!-- <input type="text" id="postcode-input" name="postcode" placeholder="*ST1"><br> -->
			<select id="postcode-select" name="postcode">
				<option value="ST1" selected>ST1</option>
				<?php foreach ($postcodes as $code) {
					echo '<option value="'.$code.'">'.$code.'</option>';
				} ?>
			</select>
			<p class="ch-form-label">3. Period of Hire</p>
			<label for="hire-period-input-from" id="from-date-label">From: </label>
			<input type="text" id="hire-period-input-from" name="hire-period" class="hire-period" placeholder="select start date"><span class="calendar-icon" data-for="hire-period-input-from"><i class="fa fa-calendar" aria-hidden="true"></i></span>
			<label for="hire-period-input-until" id="until-date-label">Until: </label>
			<input type="text" id="hire-period-input-until" name="hire-period" class="hire-period" placeholder="/    /"><span class="calendar-icon" data-for="hire-period-input-until"><i class="fa fa-calendar" aria-hidden="true"></i></span>
			<!--added Dec 2018-->
			<p class="ch-form-notes">&bull; Call if you are in the CW3 area.</p>
			<p class="ch-form-notes">&bull; Bookings can only be made 3 Days in advance.</p>
			<p class="ch-form-notes">&bull; Skips required for more than 2 weeks are available to book via phone or email.</p>			
			<p class="ch-form-notes">&bull; Skips sited on a highway must be booked by calling the office.</p>			
			<!--end-->
			<p class="ch-form-label">4. Type of Waste:</p>
			<input type="radio" name="waste-type" id="waste-type-general" value="general" checked>
			<label for="waste-type-general" class="ch-form-radio-label">General</label>
			<input type="radio" name="waste-type" id="waste-type-bs" value="bs">
			<label for="waste-type-bs" class="ch-form-radio-label">Brick &amp; Soil</label>
			<p class="ch-form-label">5. Where will the skip be kept?</p>
			<input type="radio" name="land-type" id="land-type-private" value="private" checked>
			<label for="land-type-private" class="ch-form-radio-label">Private Land</label>
			<?php
			/*'<input type="radio" name="land-type" id="land-type-public" value="public">'*/
			/*'<label for="land-type-public" class="ch-form-radio-label">On Public Road</label><br>'*/
			?>
			<p class="ch-form-notes">If wanting to book a skip on a public road, Call us for more information, thanks</p>
			<div class="parking-permit-wrapper">
				<p class="ch-form-label">6. Is a Parking Permit required for this street?</p>
				<input type="radio" name="parking-permit" id="parking-permit-none" value="no" checked>
				<label for="parking-permit-none" class="ch-form-radio-label">No</label>
				<input type="radio" name="parking-permit" id="parking-permit-required" value="yes">
				<label for="parking-permit-required" class="ch-form-radio-label">Yes</label><br>
			</div>
			<div class="ch-form-float-container">
				<p class="ch-form-label">Your Quoted Price:</p>
				<p id="quoted-price">&pound;<span></span></p>
			</div>
			<div class="ch-form-float-container book-and-pay">
				<p class="ch-form-label">Book &amp; Pay:</p>
				<i class="fa fa-credit-card" aria-hidden="true"></i><i class="fa fa-paypal" aria-hidden="true"></i>
				<button type="button" id="ch-book-now">BOOK NOW</button>
			</div>
		</div>
		<div class="ch-booking-form-footer">
			<input type="hidden" id="form-sent" value="no">
		</div>
	</form>
	<?php return ob_get_clean();
}

add_shortcode( 'ch_booking', 'ch_place_booking' );


// VALIDATE POSTCODE - WPBAU-2155
add_filter( 'woocommerce_checkout_process', 'ch_booking_validation');

function ch_booking_validation() {
	
	// $is_correct = preg_match('/^[0-9]{11,11}$/', $_POST['billing_postcode']);
	$postcodes = array('ST1','ST2', 'ST3', 'ST4', 'ST5', 'ST6', 'ST7', 'ST8', 'ST9', 'ST10', 'ST11', 'ST12', 'ST13',
						'ST14', 'ST15', 'ST16', 'ST17', 'ST18', 'ST20', 'ST21', 'TF9', 'CW1', 'CW2', 'CW5', 'CW11', 'CW12');
	$pcode = '';
	$items = WC()->cart->get_cart();

	foreach($items as $item){
		$pcode = $item['_custom_options']['postcode'];
		break;
	}

	$shipdiff = $_POST['ship_to_different_address'];

	
	if($shipdiff){
		if (!empty($pcode) && isset($_POST['shipping_postcode']) && !empty($_POST['shipping_postcode'])) {
	      	$bpcode = $_POST['shipping_postcode'];
	      	$is_correct = (strtoupper(substr($bpcode, 0, strlen($pcode))) === strtoupper($pcode)) ? 1 : 0;
			
			$pcodekey = array_search(strtoupper($pcode), $postcodes);
			if($pcodekey !== false && $is_correct){
				// remove the selected postcode
				unset($postcodes[$pcodekey]);
				// compare shipping zip to all zip codes
				// return error if any matched
				foreach($postcodes as $pzip)	{
					$is_correct = (strtoupper(substr($bpcode, 0, strlen($pzip))) === strtoupper($pzip)) ? 0 : $is_correct;
					if(!$is_correct) break;
				}
			}
			
	      	if( !$is_correct ){
		        wc_add_notice( __( '<strong>Shipping Postcode</strong> must start with '.$pcode ), 'error' );
	      	}
	    }
	}else{
		if (!empty($pcode) && isset($_POST['billing_postcode']) && !empty($_POST['billing_postcode'])) {
	      	$bpcode = $_POST['billing_postcode'];
	      	$is_correct = (strtoupper(substr($bpcode, 0, strlen($pcode))) === strtoupper($pcode)) ? 1 : 0;
			
			$pcodekey = array_search(strtoupper($pcode), $postcodes);
			if($pcodekey !== false && $is_correct){
				// remove the selected postcode
				unset($postcodes[$pcodekey]);
				// compare billing zip to all zip codes
				// return error if any matched
				foreach($postcodes as $pzip)	{
					$is_correct = (strtoupper(substr($bpcode, 0, strlen($pzip))) === strtoupper($pzip)) ? 0 : $is_correct;
					if(!$is_correct) break;
				}
			}
			
	      	if( !$is_correct ){
		        wc_add_notice( __( '<strong>Billing Postcode</strong> must start with '.$pcode ), 'error' );
	      	}
	    }
	}
}