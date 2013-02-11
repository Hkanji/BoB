<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class booking_model extends CI_Model {
	  
	  function __construct()
	  {
		  // Call the Model constructor
		  parent::__construct();
	  }
		
	  function getTimeSlotValue($time_slot_id)
	  {
		$this->db->where('id',$time_slot_id);
		
		$query = $this->db->get('time_slot_master');
		
		$time_slot_data = $query->row_array();	
		
		return ($time_slot_data['timeslot_value']);
	  }	

	  function getBookingInterval($quantity)
	  {
		if($quantity<=4) {
			$interval="2";
		}
		else {
			$interval="2.5";
		}	
		
		return ($interval);
	  }

	  function saveBookingData($input_data,$capacity_id)
	  {
		
		$time_slot = $this->getTimeSlotValue($input_data['time_slot']);	

		$interval=$this->getBookingInterval($input_data['booking_quantity']);
		
		$startEndTimeStamps=$this->getStartEndTimeStamps($input_data['booking_date'],$time_slot,$interval);

		$appointment_start_timestamp=$startEndTimeStamps['start_timestamp'];
		$appointment_end_timestamp=$startEndTimeStamps['end_timestamp'];
		 
		$data = array(
		    'store_id' => $input_data['store_id'] ,
		    'capacity_id' => $capacity_id ,
		    'time_slot_id' => $input_data['time_slot'],
		    'appointment_date' => strftime("%Y-%m-%d",strtotime($input_data['booking_date'])),
		    'appointment_start_time_stamp' => strftime("%Y-%m-%d %H:%M:%S",$appointment_start_timestamp),
		    'appointment_end_time_stamp' => strftime("%Y-%m-%d %H:%M:%S",$appointment_end_timestamp),
			'party_size' => $input_data['booking_quantity'],
			'customer_name' => $input_data['customer_name'],
			'customer_email' => $input_data['customer_email'],
			'customer_phone' => $input_data['customer_phone']
		);

		$this->db->insert('allocation_master', $data); 	
		
		$allocation_id=$this->db->insert_id();


		$this->db->where('id',$capacity_id);
		
		$query = $this->db->get('capacity_master');
		
		$capacity_data = $query->row_array();	
		$capacity_data_table = $capacity_data['table_name'];	
		$capacity_data_tables=explode("-",$capacity_data_table);
		foreach ($capacity_data_tables as $key=>$value) {
			$this->db->insert('allocation_details_master', 
					array('store_id'=>$input_data['store_id'], 
							'table_id'=>$value,
							'allocation_id'=>$allocation_id,
							'appointment_date'=>strftime("%Y-%m-%d",strtotime($input_data['booking_date'])),
							'time_slot_id' => $input_data['time_slot'],
				 'appointment_start_time_stamp' => strftime("%Y-%m-%d %H:%M:%S",$appointment_start_timestamp),
		    'appointment_end_time_stamp' => strftime("%Y-%m-%d %H:%M:%S",$appointment_end_timestamp)
						)); 	
		}
		return ($allocation_id);
	  }
	  
	  function shuffleBookingData($input_data)
	  {
		  
		$eligible_tables=$this->getEligibleTablesId($input_data);
		
		$time_slot=$this->getTimeSlotValue($input_data['time_slot']);

		$interval=$this->getBookingInterval($input_data['booking_quantity']);
		
		$startEndTimeStamps=$this->getStartEndTimeStamps($input_data['booking_date'],$time_slot,$interval);

		$appointment_start_timestamp=strftime("%Y-%m-%d %H:%M:%S",$startEndTimeStamps['start_timestamp']);
		$appointment_end_timestamp=strftime("%Y-%m-%d %H:%M:%S",$startEndTimeStamps['end_timestamp']);
		$vacent=false;

		foreach ($eligible_tables as $capacity) {
			
			$this->db->where('capacity_id',$capacity['id']);
			$this->db->where('time_slot_id',$input_data['time_slot']);
			$this->db->where('appointment_date',strftime("%Y-%m-%d",strtotime($input_data['booking_date'])));

			$query = $this->db->get('allocation_master');
			$allocation = $query->row_array();
			if(count($allocation)>0) {
				
		
				$substitute_tables=$this->getEligibleTables(array('store_id'=>$input_data['store_id'], 'booking_quantity'=>$allocation['party_size']),array(0=>array('id',' !=', $allocation['capacity_id'])));	
				
				foreach ($substitute_tables as $substitute) {
				
					$this->db->where('capacity_id',$substitute['id']);
					$this->db->where('time_slot_id',$input_data['time_slot']);
					$this->db->where('appointment_date',strftime("%Y-%m-%d",strtotime($input_data['booking_date'])));

					$query = $this->db->get('allocation_master');
					
					$subs_allocation_data = $query->row_array();	
					
					if(count($subs_allocation_data)==0) {
							

							$this->db->where('id',$substitute['id']);
							$query = $this->db->get('capacity_master');
							
							$capacity_data = $query->row_array();	
							$capacity_data_table = $capacity_data['table_name'];	
							$capacity_data_tables=explode("-",$capacity_data_table);
							
							// Lopping through each table/table-combination to check if given table is available in the given time interval
							foreach ($capacity_data_tables as $value) {
								$eligible=true;
								$appointment_date=strftime("%Y-%m-%d",strtotime($input_data['booking_date']));

								$query="SELECT * FROM `allocation_details_master` 
										WHERE 
										`table_id` ='$value' and
										`appointment_date`='$appointment_date' and
										('$appointment_start_timestamp' between `appointment_start_time_stamp` and `appointment_end_time_stamp` ||
										'$appointment_end_timestamp' between `appointment_start_time_stamp` and `appointment_end_time_stamp`
										)";
										//echo($query);
								$query = $this->db->query($query);
							
								$allocation_details_data = $query->result_array();				
								if(count($allocation_details_data)>0) {
									$eligible=false;
									break;
								}
								
							
							}

							if($eligible) {
								$this->updateBookingData($allocation,$substitute['id']);
								return ($allocation['capacity_id']);
							}						
					}
				}
			
			}
		}
		return (false);
	  }
		

	  function updateBookingData($allocation_data,$substitute_id)
	  {
		    $allocation_id=$allocation_data['id'];
			$data = array(
               'capacity_id' => $substitute_id
            );

			$this->db->where('id', $allocation_id);
			$this->db->update('allocation_master', $data);

			$data = array(
               'allocation_id' => $allocation_id
            );

			$this->deleteData('allocation_details_master',$data);

			$this->db->where('id',$substitute_id);
			
			$query = $this->db->get('capacity_master');
			
			$capacity_data = $query->row_array();	
			$capacity_data_table = $capacity_data['table_name'];	
			$capacity_data_tables=explode("-",$capacity_data_table);
			foreach ($capacity_data_tables as $key=>$value) {
				$this->db->insert('allocation_details_master', 
						array('store_id'=>$allocation_data['store_id'], 
								'table_id'=>$value,
								'allocation_id'=>$allocation_id,
								'appointment_date'=>strftime("%Y-%m-%d",strtotime($allocation_data['appointment_date'])),
								'time_slot_id' => $allocation_data['time_slot_id'],
								 'appointment_start_time_stamp' => $allocation_data['appointment_start_time_stamp'],
								 'appointment_end_time_stamp' => $allocation_data['appointment_end_time_stamp']
							)); 	
			}
	  }

	  function deleteBookingData()
	  {
		
	  }

	  function getBookingDataByDate($date,$store_id)
	  {
		$this->db->where('store_id', $store_id);
		$this->db->where('appointment_date',strftime("%Y-%m-%d",strtotime($date)));		
	
		$query = $this->db->get('allocation_master');
		
		$allocation_data = $query->result_array();	
		return ($allocation_data);	 
	  
	  }
	  
	  function getEligibleTables($input_data,$extra_fields=array())
	  {
		$this->db->where('store_id', $input_data['store_id']);
		$this->db->where('capacity >=',$input_data['booking_quantity']);
		foreach ($extra_fields as $value) {
			$this->db->where($value[0].' '.$value[1],$value[2]);
		}
		$this->db->order_by('capacity', 'asc'); 

		$query = $this->db->get('capacity_master');
		
		$eligible_tables = $query->result_array();	
		return ($eligible_tables);
	  }
	  
	  function getEligibleTablesId($input_data)
	  {
		$this->db->where('store_id', $input_data['store_id']);
		$this->db->where('capacity >=',$input_data['booking_quantity']);
		$this->db->order_by('capacity', 'asc'); 
		$this->db->select('id');

		$query = $this->db->get('capacity_master');
		
		$eligible_tables = $query->result_array();	
		$eligible_tables_array=array();
		foreach ($eligible_tables as $key=>$value) {
			$eligible_tables_array[]=$value['id'];
		}

		return ($eligible_tables);
	  }
		
      function getStartEndTimeStamps($booking_date,$time_slot,$interval)
      {
		$appointment_start_timestamp=strtotime($booking_date." ".$time_slot.":00");
		$appointment_end_timestamp=$appointment_start_timestamp + (60*60*$interval);	
		return (array('start_timestamp'=>$appointment_start_timestamp,'end_timestamp'=>$appointment_end_timestamp));
      }

	  function checkAvailibility($input_data)
	  {
		$eligible=true;
		
		// get all eligible tables/table-combination for the given criteria
		$eligible_tables = $this->getEligibleTables($input_data);
		
		$time_slot=$this->getTimeSlotValue($input_data['time_slot']);

		$interval=$this->getBookingInterval($input_data['booking_quantity']);
		
		$startEndTimeStamps=$this->getStartEndTimeStamps($input_data['booking_date'],$time_slot,$interval);

		$appointment_start_timestamp=strftime("%Y-%m-%d %H:%M:%S",$startEndTimeStamps['start_timestamp']);
		$appointment_end_timestamp=strftime("%Y-%m-%d %H:%M:%S",$startEndTimeStamps['end_timestamp']);
		$vacent=false;

		if(count($eligible_tables)>0) {
			$not_available_combination=array();
			$not_available_single=array();
			
			$available_combination=array();
			$available_single=array();
			
			foreach ($eligible_tables as $capacity) {
				$this->db->where('capacity_id',$capacity['id']);
				$this->db->where('time_slot_id',$input_data['time_slot']);
				$this->db->where('appointment_date',strftime("%Y-%m-%d",strtotime($input_data['booking_date'])));

				$query = $this->db->get('allocation_master');
				
				$allocation_data = $query->row_array();	
				
				// if at least one table/table-combination is vacant in the given time slot and appointment date, process further

				
				if(count($allocation_data)==0) {
					
					$vacent=true;

					$this->db->where('id',$capacity['id']);
					$query = $this->db->get('capacity_master');
					
					$capacity_data = $query->row_array();	
					$capacity_data_table = $capacity_data['table_name'];	
					$capacity_data_tables=explode("-",$capacity_data_table);
					
					// Lopping through each table/table-combination to check if given table is available in the given time interval
					foreach ($capacity_data_tables as $key=>$value) {
						$eligible=true;
						$appointment_date=strftime("%Y-%m-%d",strtotime($input_data['booking_date']));

						$query="SELECT * FROM `allocation_details_master` 
								WHERE 
								`table_id` ='$value' and
								`appointment_date`='$appointment_date' and
								('$appointment_start_timestamp' between `appointment_start_time_stamp` and `appointment_end_time_stamp` ||
								'$appointment_end_timestamp' between `appointment_start_time_stamp` and `appointment_end_time_stamp`
								)";
								//echo($query);
						$query = $this->db->query($query);
					
						$allocation_details_data = $query->result_array();				
						if(count($allocation_details_data)>0) {
							//$available_combination[]=$capacity_data_table;
							//$available_single[]=$value;
							//return $this->shuffleBookingData();
							//echo('Uneligible');
							$eligible=false;
							break;
						}
						else {
							//$not_available_combination[]=$capacity_data_table;
							//$not_available_single[]=$value;
							//break;							
						}
					
					}

					if($eligible) {
						return ($capacity['id']);
					}
				
				}
			}

			if(!$vacent) {
				return (false);
			}

		}

		return $this->shuffleBookingData($input_data);
			 
		//return false;
		
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
	  
	  
	   
	   function getDateBookingArray($date)
	  {
			
			$query = $this->db->get_where('allocation_master',array('appointment_date'=>$date));
			$row = $query->result_array();	
			
			return ($row);
	  }
	  
	  
	    function deleteBooking($aId)
	  {
			//Uncomment when need to delete record
			//$query = $this->db->delete('allocation_master',array('id'=>$aId));
	  }
	  
		function getTableName($capacity_id)
		{
			$this->db->where('id',$capacity_id);
			
			$query = $this->db->get('capacity_master');
			
			$capacity_data = $query->row_array();	
			return $capacity_data['table_name'];			
		}

	  
		function getStoreName($store_id)
		{
			$this->db->where('id',$store_id);
			
			$query = $this->db->get('store_master');
			
			$store_data = $query->row_array();	
			return $store_data['store_name'];			
		}

		function updateEventHref($id,$event_href)
		{
		$data = array(
               'event_href' => $event_href
            );

			$this->db->where('id', $id);
			$this->db->update('allocation_master', $data); 			
		}

		function getDeletedEvents($href,$date)
		{
			$date=strftime("%Y-%m-%d",strtotime($date));
			if($href=='') {
				$date=strftime("%Y-%m-%d",strtotime($date));
				$sql="select id from allocation_master where appointment_date='$date'";
				$query = $this->db->query($sql);

				$row = $query->result_array();	
				
				return ($row);	
			}
			
			$sql="select id from allocation_master where appointment_date='$date' and event_href not in ($href)";
			$query = $this->db->query($sql);

			$row = $query->result_array();	
			
			return ($row);			
		}

		function deleteData($table_name,$condition)
		{
			 $this->db->delete($table_name, $condition); 
		}
}
?>