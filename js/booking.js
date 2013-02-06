jQuery(document).ready(function(){
	resetValues();
	initDatePickers();
	bindEvents();
});

function resetValues()
{
	jQuery('#booking_store').val('');
	jQuery('#datepicker1').val('');
	jQuery('#datepicker2').val('');
}

function bindEvents()
{
	jQuery('#booking_store').bind('change',function(){
		if (jQuery(this).val()=='theobalds')
		{	
			jQuery('#onc_div').hide();
			jQuery('#theobalds_div').slideDown('slow');
			
		}
		else
		{	
			if (jQuery(this).val()!='')
			{
				jQuery('#theobalds_div').hide();
				jQuery('#onc_div').slideDown('slow');					
			}
			else
			{
				jQuery('#theobalds_div').hide();
				jQuery('#onc_div').hide();
			}
		}
	});

	jQuery("#onc_form").bind("submit",function(e){
		e.preventDefault();
		jQuery('.error').remove();
		var error=false;
		if (jQuery('#customer_name1').val()=='')
		{
			jQuery('#customer_name1_td').append('<span class="error">&nbsp;&nbsp;Please enter name!</span>');
			error=true;
			
		}
		if (jQuery('#customer_email1').val()=='')
		{
			jQuery('#customer_email1_td').append('<span class="error">&nbsp;&nbsp;Please enter email!</span>');
			error=true;
			
		}
		if (jQuery('#customer_phone1').val()=='')
		{
			jQuery('#customer_phone1_td').append('<span class="error">&nbsp;&nbsp;Please enter phone number!</span>');
			error=true;
			
		}
		if (jQuery('#datepicker1').val()=='')
		{
			jQuery('#datepicker1_td').append('<span class="error">&nbsp;&nbsp;Please select date of booking!</span>');
			error=true;
			
		}

		if (error)
		{
			jQuery('.error').toggle('slow');
			return false;
		}
		var form_data=jQuery(this).serializeArray();
		jQuery.post('booking/bookAppointment', form_data, function(data){
			if (data.status=='error')
			{
				jQuery("#mesage_div").html('<span class="error">'+data.message+'</span>');
				jQuery('.error').toggle('slow');
			}
			else
				if (data.status=='success')
				{
					jQuery("#mesage_div").html('<div class="success">'+data.message+'<br />Your reservation number is: '+data.reservation_id+'</div>');
					jQuery('.success').toggle('slow');				
				}
			 
		},'json');

	});

	jQuery("#theobalds_form").bind("submit",function(e){
		e.preventDefault();
		jQuery('.error').remove();
		var error=false;
		if (jQuery('#customer_name2').val()=='')
		{
			jQuery('#customer_name2_td').append('<span class="error">&nbsp;&nbsp;Please enter name!</span>');
			error=true;
			
		}
		if (jQuery('#customer_email2').val()=='')
		{
			jQuery('#customer_email2_td').append('<span class="error">&nbsp;&nbsp;Please enter email!</span>');
			error=true;
			
		}
		if (jQuery('#customer_phone2').val()=='')
		{
			jQuery('#customer_phone2_td').append('<span class="error">&nbsp;&nbsp;Please enter phone number!</span>');
			error=true;
			
		}
		if (jQuery('#datepicker2').val()=='')
		{
			jQuery('#datepicker2_td').append('<span class="error">&nbsp;&nbsp;Please select date of booking!</span>');
			error=true;
			
		}

		if (error)
		{
			jQuery('.error').toggle('slow');
			return false;
		}
		var form_data=jQuery(this).serializeArray();
		jQuery.post('booking/bookAppointment', form_data, function(data){
			if (data.status=='error')
			{
				jQuery("#mesage_div").html('<span class="error">'+data.message+'</span>');
				jQuery('.error').toggle('slow');
			}
			else
				if (data.status=='success')
				{
					jQuery("#mesage_div").html('<div class="success">'+data.message+'<br />Your reservation number is: '+data.reservation_id+'</div>');
					jQuery('.success').toggle('slow');				
				}
			 
		},'json');
	});
}

function initDatePickers()
{

	jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M, yy", minDate: 0,onSelect: function(date){
		date = new Date(date); 
		
		if (date.getDay() == 0 || date.getDay() == 6)
		{
			jQuery('#time_slot_type').val('weekend');
			jQuery('#booking_store_onc_weekdays_time_slot').hide();	
			jQuery('#booking_store_onc_weekends_time_slot').slideDown('slow');		
		}
		else
		{
			jQuery('#time_slot_type').val('weekday');
			jQuery('#booking_store_onc_weekends_time_slot').hide();
			jQuery('#booking_store_onc_weekdays_time_slot').slideDown('slow');							
		}

		//alert(jQuery('#time_slot_type').val());
	
	}});

	jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M, yy", minDate: 0 ,beforeShowDay: function(date)
	{
	if (date.getDay() == 0 || date.getDay() == 6)
	{
		return [true, ''];
	}
	else
		return [false, ''];
	},onSelect: function(){
		
	}});

}