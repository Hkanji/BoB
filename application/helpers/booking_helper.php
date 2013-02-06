<?php
	
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	if(!function_exists('jsonToArray')) {
		function jsonToArray($jsonData)
		{	
			if(is_array($jsonData)) {
				return $jsonData;
			}
			return json_decode($jsonData);
		}
	}

	if(!function_exists('arrayToJson')) {
		function arrayToJson($arrayData)
		{
			return json_encode($arrayData);
		}
	}

	if(!function_exists('displayStoresDropDown')) {
		function displayStoresDropDown()
		{
			$store_array= getStoreArray();
			$drop_down = "<select id='booking_store' name='booking_store'>";
			$drop_down .= "<option value=''>Select Store</option>";
			foreach ($store_array as $key=>$value) {
				$drop_down .= "<option value='".$value['store_key']."'>".$value['store_name']."</option>";
			}
			$drop_down .= "</select>";
			echo($drop_down);
		}
	}

	if(!function_exists('getStoreArray')) {
		function getStoreArray()
		{
			$ci=& get_instance();
			$store_data= $ci->booking_model->getStoreArray();
			return ($store_data);
		}
	}


	if(!function_exists('getAvailableTimeSlotsArray')) {
		function getAvailableTimeSlots($store_id)
		{
			$ci=& get_instance();
			$store_time_data= $ci->booking_model->getTimeSlotsArray($store_id);
			return ($store_time_data);		
		}
	}

	if(!function_exists('getQuantityDropDown')) {
		function getQuantityDropDown()
		{
			$drop_down= "<select id='booking_quantity' name='booking_quantity'>";
			for ($i=2; $i <=20 ; $i++) {
				$drop_down .= "<option value='$i'>$i</option>";
			}		
			$drop_down .= "</select>";
			echo($drop_down);
		}
	}

	if(!function_exists('getAvailableTimeSlotsDropDown')) {
		function getAvailableTimeSlotsDropDown($store_id)
		{
			$store_time_array= getAvailableTimeSlots($store_id);
			if($store_id==2) {
				$drop_down= "<select id='booking_store_theobalds_time_slot' name='booking_store_theobalds_time_slot'>";
				foreach ($store_time_array as $key=>$value) {
					$drop_down .= "<option value='".$value['id']."'>".$value['timeslot_value']."</option>";
				}
				$drop_down .= "</select>";
				echo($drop_down);
			}
			else {
				$drop_down_weekdays= "<select id='booking_store_onc_weekdays_time_slot' name='booking_store_onc_weekdays_time_slot'>";
				$drop_down_weekends= "<select id='booking_store_onc_weekends_time_slot' name='booking_store_onc_weekends_time_slot' style='display:none;'>";
				foreach ($store_time_array as $key=>$value) {
					$drop_down_weekends .= "<option value='".$value['id']."'>".$value['timeslot_value']."</option>";
					if($value['on_weekdays']) {
						$drop_down_weekdays .= "<option value='".$value['id']."'>".$value['timeslot_value']."</option>";
					}
				}
				$drop_down_weekdays .= "</select>";
				$drop_down_weekends .= "</select>";	
				echo($drop_down_weekdays.$drop_down_weekends);
			}
			
						
		}
	}
?>