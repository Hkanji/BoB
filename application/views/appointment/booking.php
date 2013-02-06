<h2>Table booking</h2>
<div id="">
	<?php
		displayStoresDropDown();
	?>
</div>

<div id="onc_div">
<form id="onc_form">
<table>
<tr>
	<td>Name</td>
	<td id="customer_name1_td"><input type="text" name="customer_name" id="customer_name1"></td>
</tr>
<tr>
	<td>Email</td>
	<td id="customer_email1_td"><input type="text" name="customer_email" id="customer_email1"></td>
</tr>
<tr>
	<td>Phone</td>
	<td id="customer_phone1_td"><input type="text" name="customer_phone" id="customer_phone1"></td>
</tr>
<tr>
	<td>Date</td>
	<td id="datepicker1_td"><input type="text" name="booking_date" id="datepicker1" readonly="readonly"></td>
</tr>
<tr>
	<td>Time</td>
	<td>
	
	<?php
		getAvailableTimeSlotsDropDown(1);
	?>
	
	</td>
</tr>
<tr>
	<td>Number of persons</td>
	<td><?php
		getQuantityDropDown();
	?></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Book Now"></td>
</tr>
</table>
<input type="hidden" name="time_slot_type" id="time_slot_type" value="" />
<input type="hidden" name="store_id" id="store_id" value="1" />
</form>
</div>
<div class="clear"></div>

<div id="theobalds_div">
<form id="theobalds_form">
<table>
<tr>
	<td>Name</td>
	<td id="customer_name2_td"><input type="text" name="customer_name" id="customer_name2"></td>
</tr>
<tr>
	<td>Email</td>
	<td id="customer_email2_td"><input type="text" name="customer_email" id="customer_email2"></td>
</tr>
<tr>
	<td>Phone</td>
	<td id="customer_phone2_td"><input type="text" name="customer_phone" id="customer_phone2"></td>
</tr>
<tr>
	<td>Date</td>
	<td id="datepicker2_td"><input type="text" name="booking_date" id="datepicker2" readonly="readonly"></td>
</tr>
<tr>
	<td>Time</td>
	<td>	
	<?php
		getAvailableTimeSlotsDropDown(2);
	?>
	</td>
</tr>
<tr>
	<td>Number of persons</td>
	<td><?php
		getQuantityDropDown();
	?></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Book Now"></td>
</tr>
</table>
<input type="hidden" name="store_id" id="store_id" value="2" />
</form>	
</div>

<div class="clear"></div>
<div id="mesage_div">
</div>