<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class booking_model extends CI_Model {
	  
	  function __construct()
	  {
		  // Call the Model constructor
		  parent::__construct();
	  }

	  function saveBookingData($input_data,$capacity_id)
	  {
		
		$this->db->where('id',$input_data['time_slot']);
		
		$query = $this->db->get('timeslot_master');
		
		$time_slot_data = $query->row_array();	
		$time_slot = $time_slot['timeslot_value'];	

		if($input_data['booking_quantity']<=4) {
			$interval="2";
		}
		else {
			$interval="2.5";
		}
		
		$appointment_start_timestamp=strtotime($input_data['appointment_date']." ".$time_slot.":00");
		$appointment_end_timestamp=$appointment_start_timestamp + (60*60*$interval);
		 



		$data = array(
		    'store_id' => $input_data['store_id'] ,
		    'capacity_id' => $capacity_id ,
		    'time_slot_id' => $input_data['time_slot_id'],
		    'appointment_date' => strftime("%Y-%m-%d",strtotime($input_data['appointment_date'])),
		    'appointment_start_time_stamp' => $appointment_start_timestamp,
		    'appointment_end_time_stamp' => $appointment_end_timestamp,
			'party_size' => $input_data['booking_quantity'],
			'customer_name' => $input_data['customer_name'],
			'customer_email' => $input_data['customer_email'],
			'customer_phone' => $input_data['customer_phone']
		);

		$this->db->insert('allocation_master', $data); 		
	  }
	  
	  function shuffleBookingData()
	  {
		
	  }

	  function deleteBookingData()
	  {
		
	  }

	  function getBookingData()
	  {
		
	  }
	  
	  function checkAvailibility($input_data)
	  {
		$this->db->where('store_id', $input_data['store_id']);
		$this->db->where('capacity >=',$input_data['booking_quantity']);
		$this->db->order_by('capacity', 'asc'); 

		$query = $this->db->get('capacity_master');
		
		$eligible_tables = $query->result_array();	



		foreach ($eligible_tables as $capacity) {
			$this->db->where('capacity_id',$capacity['id']);
			$this->db->where('time_slot_id',$input_data['time_slot']);
			$this->db->where('appointment_date',$input_data['booking_date']);

			$query = $this->db->get('allocation_master');
			
			$allocation_data = $query->row_array();	
			if(count($allocation_data)==0) {
				return ($capacity['id']);
			}				
		}
		
	  }	


	  function getStoreArray()
	  {
			$query = $this->db->get('store_master');
			$row = $query->result_array();	
			
			return ($row);
	  }

	  function getTimeSlotsArray($store_id)
	  {
			
			$query = $this->db->get_where('time_slot_master',array('store_id'=>$store_id));
			$row = $query->result_array();	
			
			return ($row);
	  }


}

?>