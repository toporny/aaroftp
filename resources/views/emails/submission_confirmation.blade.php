
<h3>Your submission has been accepted.</h3>

<p>Product{{ $letter_s }} are waiting to be installed:</p>

<p>
		{!! $products_list !!}
</p>

<p>&nbsp;</p>


<p>Data that you provided:</p>

<table border="1" style="background-color:FFFFCC;border-collapse:collapse;border:1px solid FFCC00;color:000000;" cellpadding="3" cellspacing="3">
	<tr>
		<td>Name:</td><td>{{ $first_name }} {{ $last_name }}</td>
	</tr>
	<tr>
		<td>Contact email:</td><td>{{ $contact_email }}</td>
	</tr>
	<tr>
		<td>FTP host:</td><td>{{ $ftp_host }}</td>
	</tr>
	<tr>
		<td>FTP username:</td><td>{{ $ftp_username }}</td>
	</tr>
	<tr>
		<td>FTP password:</td><td>{{ $ftp_password }}</td>
	</tr>
	<tr>
		<td>FTP install folder:</td><td>{{ $ftp_dir }}</td>
	</tr>
	<tr>
		<td>Your website:</td><td>{{ $website }}</td>
	</tr>
	<tr>
		<td>Date ordered:</td><td>{{ $date_ordered }}</td>
	</tr>
</table>

<p>We will be contact with you shortly.</p>

<p>SuperSalesMachine.com Team!</p>