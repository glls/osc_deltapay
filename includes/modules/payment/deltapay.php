<?php
/* 

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License

  DeltaPay module for oscommerce / CRE Loaded
  Created by George Litos,  GL@cyberpunk.gr
  www.cyberpunk.gr
  version 1.0

*/

class deltapay {
  
	var $code, $title, $description, $enabled;

	// class constructor
	function deltapay() {
		
		global $order;
		$this->code = 'deltapay';
		$this->title = MODULE_PAYMENT_DELTAPAY_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_DELTAPAY_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_DELTAPAY_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_DELTAPAY_STATUS == 'True') ? true : false);
		
		if ((int)MODULE_PAYMENT_DELTAPAY_ORDER_STATUS_ID > 0)
			$this->order_status = MODULE_PAYMENT_DELTAPAY_ORDER_STATUS_ID;
		
		if (is_object($order)) 
			$this->update_status();
		
		$this->form_action_url = 'http://localhost/entry.php'; // debug page
			//'https://www.deltapay.gr/entry.asp';
	}
	
	// class methods
	function update_status() {
		
		global $order;
		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_DELTAPAY_ZONE > 0) ) {
			$check_flag = false;
			$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_DELTAPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
	
			while ($check = tep_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}
	
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}
	   
	function javascript_validation() {
		return false;
	}
		
	function selection() {
	
		global $order;
			
		$selection = array('id' => $this->code,
							'module' => $this->title,
							'fields' => array(	array( 'title' => MODULE_PAYMENT_DELTAPAY_TEXT_CREDIT_CARD_OWNER,
														'field' => tep_draw_input_field('cardholdername', $order->billing['firstname'] .' '. $order->billing['lastname'])),
							array('title' => MODULE_PAYMENT_DELTAPAY_TEXT_CREDIT_CARD_OWNER_EMAIL,
									'field' => tep_draw_input_field('cardholderemail'))));
		
		return $selection;
	}
		
	function pre_confirmation_check() {
		return false;
	}
		
	function confirmation() {
	
		global $HTTP_POST_VARS;
		$confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
								'fields' => array(array('title' => MODULE_PAYMENT_DELTAPAY_TEXT_CREDIT_CARD_OWNER,
													'field' => $HTTP_POST_VARS['cardholdername']),
											array('title' => MODULE_PAYMENT_DELTAPAY_TEXT_CREDIT_CARD_OWNER_EMAIL,
													'field' => $HTTP_POST_VARS['cardholderemail'])));
	
		return $confirmation;
	}
		
	function process_button() {
	
		global $HTTP_POST_VARS, $order, $insert_id ;
		 
		$process_button_string = tep_draw_hidden_field('merchantcode', MODULE_PAYMENT_DELTAPAY_MERCHANTID) .
								tep_draw_hidden_field('charge', number_format($order->info['total'], 2, ',', '') ) .
								tep_draw_hidden_field('currencycode', '978') . 						// 978 for euro, 840 for $
								tep_draw_hidden_field('cardholdername', $HTTP_POST_VARS['cardholdername']) .
								tep_draw_hidden_field('cardholderemail', $HTTP_POST_VARS['cardholderemail']) .
								tep_draw_hidden_field('installments', '0') .
								tep_draw_hidden_field('transactiontype', '0') .
								tep_draw_hidden_field('param1', $insert_id ) .
								tep_draw_hidden_field('param2', $_GET['osCsid']);					// TODO: check
	
		return $process_button_string;
	}
	
	function before_process() {
		
		global $HTTP_POST_VARS, $order;
		
		// send email notification
		if ( (defined('MODULE_PAYMENT_DELTAPAY_EMAIL')) && (tep_validate_email(MODULE_PAYMENT_DELTAPAY_EMAIL)) ) {
			$this->deltapayID = $HTTP_POST_VARS['DeltaPayID'];
		}
		// 1=OK 2=ERROR 3=CANCELED
		if ($HTTP_POST_VARS['result'] == '2') {
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_DELTAPAY_TEXT_ERROR_MESSAGE), 'SSL', true, false));
	
		} else if($HTTP_POST_VARS['result'] == '3') {
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_DELTAPAY_TEXT_ERROR_MESSAGE2), 'SSL', true, false));
		}
	}
	
	function after_process() {	
	
		global $insert_id;
		$this->deltapayID = $HTTP_POST_VARS['DeltaPayID'];
		
		if ( (defined('MODULE_PAYMENT_DELTAPAY_EMAIL')) && (tep_validate_email(MODULE_PAYMENT_DELTAPAY_EMAIL)) ) {
			$message = 'Order #' . $insert_id . "\n\n" . 'DeltaPayID : ' . $this->deltapayID . "\n\n";		
			tep_mail('', MODULE_PAYMENT_DELTAPAY_EMAIL, 'Extra Order Info: #' . $insert_id, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
		}
	}
	
	function get_error() {
	
		global $HTTP_GET_VARS;	
		$error = array('title' => MODULE_PAYMENT_DELTAPAY_TEXT_ERROR, 
						'error' => stripslashes(urldecode($HTTP_GET_VARS['ErrorMessage'])));
		return $error;
	}
	
	function check() {
	
		if (!isset($this->_check)) {
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_DELTAPAY_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}
	
	function install() {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('DELTAPAY Enable DeltaPay Module', 'MODULE_PAYMENT_DELTAPAY_STATUS', 'True', 'Do you want to accept DeltaPay payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('DELTAPAY Merchant ID', 'MODULE_PAYMENT_DELTAPAY_MERCHANTID', 'myMerchantID', 'MerchantID used for the DeltaPay service', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('DELTAPAY Email', 'MODULE_PAYMENT_DELTAPAY_EMAIL', 'True', 'send e-mail ?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");	  
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_DELTAPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_DELTAPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_DELTAPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())"); 
	}
	
	function remove() {
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}
	
	function keys() {
		return array('MODULE_PAYMENT_DELTAPAY_STATUS', 'MODULE_PAYMENT_DELTAPAY_EMAIL', 'MODULE_PAYMENT_DELTAPAY_MERCHANTID', 'MODULE_PAYMENT_DELTAPAY_ZONE', 'MODULE_PAYMENT_DELTAPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_DELTAPAY_SORT_ORDER');	  
	}

}

?>