<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Booking extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->library('session');
		$this->load->model('booking_model');
		$this->load->helper('url');
		$this->load->helper('booking');
		$this->load->library('Zend/Loader');
		ini_set('include_path',   ini_get('include_path') . PATH_SEPARATOR . APPPATH .'libraries');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
	}

	public function index()
	{

		$this->load->view('common/header');
		$this->load->view('appointment/booking');
		$this->load->view('common/footer');
	}

	function bookAppointment()
	{
		$input_data=jsonToArray($this->input->post());


			if($this->validateData($input_data)) {

			if($input_data['store_id']==2) {
				$input_data['time_slot']=$input_data['booking_store_theobalds_time_slot'];
			}
			else {
				if($input_data['time_slot_type']=='weekday') {
					$input_data['time_slot']=$input_data['booking_store_onc_weekdays_time_slot'];
				}
				else {
					$input_data['time_slot']=$input_data['booking_store_onc_weekends_time_slot'];
				}
			}

			$this->checkGoogleCalendar($input_data);

			$availibility=$this->checkAvailibility($input_data);
			
			if(is_numeric($availibility) && $availibility!=0) {
				$reservation_id=$this->addReservation($input_data,$availibility);
				$this->updateGoogleCalendar($input_data);
				$this->sendBookingEmail($reservation_id,$customer_email);
				$data_array=array('status'=>'success','message'=>'Booking successfull','reservation_id'=>$reservation_id);
				echo(arrayToJson($data_array));
			}
			else {
				$data_array=array('status'=>'error','message'=>'Sorry, no tables available in the given time slot. Please select a different time slot!');
				echo(arrayToJson($data_array));				
			}
		}
		else {
			$data_array=array('status'=>'error','message'=>'Invalid Data Entered!');
			echo(arrayToJson($data_array));
		}
	}

	function validateData($input_data)
	{
		$input_data_array = ($input_data);
		
		if((isset($input_data_array['customer_name']) && trim($input_data_array['customer_name'])=='') || (isset($input_data_array['customer_email']) && trim($input_data_array['customer_email'])=='') ||  (isset($input_data_array['customer_phone']) && trim($input_data_array['customer_phone'])=='') ||  (isset($input_data_array['booking_quantity']) && trim($input_data_array['booking_quantity'])=='') ||   (isset($input_data_array['customer_name']) && trim($input_data_array['customer_name'])=='') ) {
			return (false);
		}

		return (true);
	}
	
	function updateGoogleCalendar($input_data)
	{
		$this->deleteCalendarReservationData($input_data);
		$this->addCalendarReservationData($input_data);
	}
	function addCalendarReservationData($input_data)
	{
	
		$booking_data=$this->booking_model->getBookingDataByDate($input_data['booking_date'],$input_data['store_id']);

		$client=$this->authenticateGoogle($input_data);
		$store_name=$this->booking_model->getStoreName($input_data['store_id']);
		foreach ($booking_data as $key=>$value) {
			
			$table_name=$this->booking_model->getTableName($value['capacity_id']);
			$title='Table # '.$table_name.' booked by '.$value['customer_name'];
			$description='Table # '.$table_name.', booked by '.$value['customer_name'].' for '.$value['party_size'].' people. Reservation #'.$value['id'];
			$event_href=$this->createEvent($client,$title,$description,$store_name,$value['appointment_date'],strftime('%H:%M:%S', strtotime($value['appointment_start_time_stamp'])),$value['appointment_date'],strftime('%H:%M:%S',strtotime($value['appointment_end_time_stamp'])));

			$this->booking_model->updateEventHref($value['id'],$event_href);
		}
	}

	function createEvent ($client, $title = '',
		$desc='', $where = '',
		$startDate = '', $startTime = '',
		$endDate = '', $endTime = '', $tzOffset = '+05:30')
		{
		  $gdataCal = new Zend_Gdata_Calendar($client);
		  $newEvent = $gdataCal->newEventEntry();

		  $newEvent->title = $gdataCal->newTitle($title);
		  $newEvent->where = array($gdataCal->newWhere($where));
		  $newEvent->content = $gdataCal->newContent("$desc");

		  $when = $gdataCal->newWhen();

		  $when->startTime = "{$startDate}T{$startTime}.000{$tzOffset}";
		   $when->endTime = "{$endDate}T{$endTime}.000{$tzOffset}";

		  $newEvent->when = array($when);

		  $createdEvent = $gdataCal->insertEvent($newEvent);
		  return $eventEditUrl = $createdEvent->getLink('edit')->href;

		}


	function deleteCalendarReservationData($input_data)
	{
		$booking_data=$this->booking_model->getBookingDataByDate($input_data['booking_date'],$input_data['store_id']);	
		$client=$this->authenticateGoogle($input_data);
		if(count($booking_data)>0) {
			foreach ($booking_data as $key=>$value) {
				if($value['event_href']!='') {
					$gdataCal = new Zend_Gdata_Calendar($client);
					$gdataCal->delete($value['event_href']);					
				}

			}				
		}
		
	}

	function checkGoogleCalendar($input_data)
	{

			$client=$this->authenticateGoogle($input_data);

			$gdataCal = new Zend_Gdata_Calendar($client);
			$query = $gdataCal->newEventQuery();
			$query->setUser('default');
			$query->setVisibility('private');
			$query->setProjection('full');
			$query->setOrderby('starttime');
			$query->setStartMin(strftime("%Y-%m-%d",strtotime($input_data['booking_date'])));
			$query->setStartMax(strftime("%Y-%m-%d",strtotime("+1 day",strtotime($input_data['booking_date']))));

			$eventFeed = $gdataCal->getCalendarEventFeed($query);
			$booking_data_href='';

			if(count($eventFeed)>0) {
				foreach ($eventFeed as $event) {

					$booking_data_href .= "'".$event->getLink('edit')->href."',";
				
				}
				
				$deletedEvents = $this->booking_model->getDeletedEvents(rtrim($booking_data_href,','),$input_data['booking_date']);
			}
			else {
				$deletedEvents = $this->booking_model->getDeletedEvents('',$input_data['booking_date']);				
			}
			
			if(count($deletedEvents)>0) {
					foreach ($deletedEvents as $key=>$value) {
						$this->booking_model->deleteData('allocation_master',array('id'=>$value['id']));
						$this->booking_model->deleteData('allocation_details_master',array('allocation_id'=>$value['id']));
					}
				}

	}

	function sendBookingEmail($reservation_id,$customer_email)
	{
		$this->load->library('email');
		$this->email->initialize(array('mailtype'=>'html'));

		$this->email->from(FROM_EMAIL, 'Admin');
		$this->email->to($customer_email);
		
		$this->email->subject('Reservation sucessfull');
		$this->email->message('Booking successfull<br />
								Your reservation number is: '.$customer_email);

		$this->email->send();	
		
		$this->email->to(STAFF_EMAIL);
		
		$this->email->subject('New Reservation');
		$this->email->message('A booking is made on the site<br />
								The reservation number is: '.$customer_email);
		$this->email->send();
	}

	function checkAvailibility($input_data)
	{
		return ($this->booking_model->checkAvailibility($input_data));
	}

	function addReservation($input_data,$availibility)
	{
		return ($this->booking_model->saveBookingData($input_data,$availibility));
	}


	function authenticateGoogle($input_data)
	{
		if($input_data['store_id']==1) {
			$username=STORE_1_USERNAME;
			$password=STORE_1_PASSWORD;
		}
		else {
			$username=STORE_2_USERNAME;
			$password=STORE_2_PASSWORD;		
		}
		
		$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
		$client = Zend_Gdata_ClientLogin::getHttpClient($username,$password,$service);		
		return $client;			
	}

}

/* End of file appointment.php */
/* Location: ./application/controllers/appointment.php */