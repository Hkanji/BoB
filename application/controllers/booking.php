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

			$this->checkGoogleCalendar();
			$availibility=$this->checkAvailibility($input_data);
			if(is_numeric($availibility) && $availibility!=0) {
				$reservation_id=$this->addReservation($input_data,$availibility);
				$this->updateGoogleCalendar();
				//$this->sendBookingEmail($reservation_id,$customer_email);
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
	
	function updateGoogleCalendar()
	{
		$this->deleteReservation();
	}

	function checkGoogleCalendar()
	{
		
	}

	function sendBookingEmail($reservation_id,$customer_email)
	{
		$this->load->library('email');

		$this->email->from('admin@booking.com', 'Admin');
		$this->email->to($customer_email);
		
		$this->email->subject('Reservation sucessfull');
		$this->email->message('Booking successfull<br />
								Your reservation number is: '.$customer_email);

		$this->email->send();	
		
		$this->email->to("staff@booking.com");
		
		$this->email->subject('New Reservation');
		$this->email->message('A booking is made on the site<br />
								The reservation number is: '.$customer_email);

		$this->email->send();
	}

	function checkAvailibility($input_data)
	{
		return ($this->booking_model->checkAvailibility($input_data));
	}

	function addReservation()
	{
		return ($this->saveBookingData($input_data,$availibility));
	}

	function deleteReservation()
	{

	}

}

/* End of file appointment.php */
/* Location: ./application/controllers/appointment.php */