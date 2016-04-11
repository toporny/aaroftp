<?php
// STEP 1: Read POST data

// reading posted data from directly from $_POST causes serialization 
// issues with array data in POST
// reading raw POST data from input stream instead. 
$raw_post_data = file_get_contents('php://input');


$raw_post_array = explode('&', $raw_post_data);

$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}

$_POST = $myPost;

function jvzipnVerification() {
    $secretKey = "APNSWYZ1OWYDIYZ5";
    $pop = "";
    $ipnFields = array();
    foreach ($_POST AS $key => $value) {
        if ($key == "cverify") {
            continue;
        }
        $ipnFields[] = $key;
    }
    sort($ipnFields);
    foreach ($ipnFields as $field) {
        // if Magic Quotes are enabled $_POST[$field] will need to be
        // un-escaped before being appended to $pop
        $pop = $pop . $_POST[$field] . "|";
    }
    $pop = $pop . $secretKey;
    $calcedVerify = sha1(mb_convert_encoding($pop, "UTF-8"));
    $calcedVerify = strtoupper(substr($calcedVerify,0,8));
	return $calcedVerify;
}

$calcedVerify = jvzipnVerification();

if($calcedVerify == $_POST["cverify"])
{
	$customerFullName = $myPost['ccustname'];
	$customerEmail = $myPost['ccustemail'];
	$productName = $myPost['cprodtitle'];
	$transactionType = $myPost['ctransaction'];
	
	
	if($productName == "SetupMyProducts.com - Everything Done For Your You! ($197 Standard Package)") { $productName = "Setup My Products"; }
	if($productName == "[MyMonthlyMembership] Complete Membership Business In A Box! (Install Service)") { $productName = "Complete Newbie Training"; }
	if($productName == "[MyMonthlyMembership] Turn-Key Upsell Package! (Install Service)") { $productName = "Complete Newbie Training Upgrade"; }
	if($productName == "[MyBoxBusiness] 5 Internet Marketing Niches (Install Service)") { $productName = "MyBB 5PLR"; }
	if($productName == "[MyBoxBusiness] 5 Internet Marketing Niches (Install Service Upgrade)") { $productName = "MyBB 5PLR Upgrade"; }
	if($productName == "[MyBoxBusiness] Tube Traffic Mayhem (Install Service)") { $productName = "MyBB Tube Traffic Mayhem"; }
	if($productName == "[MyBoxBusiness] Tube Traffic Mayhem (Install Service Upgrade)") { $productName = "MyBB Tube Traffic Mayhem Upgrade"; }
	if($productName == "[MyBoxBusiness] Freebie List Converter (Install Service)") { $productName = "MyBB Freebie List Converter"; }
	if($productName == "[MyBoxBusiness] Freebie List Converter (Install Service Upgrade)") { $productName = "MyBB Freebie List Converter Upgrade"; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	if($productName == "") { $productName = ""; }
	
	if($transactionType=='SALE')
	{
		$subject = 'New order just came in from '.$customerFullName.' for '.$productName.'!';
		
		$message = '
				<html>
				<body>
				  <table cellpadding="6" cellspacing="3">
					<tr>
					  <td>Customer Name:</td><td>'.stripslashes($customerFullName).'</td>
					</tr>
					<tr>
					  <td>Customer Email:</td><td>'.stripslashes($customerEmail).'</td>
					</tr>
					<tr>
					  <td>Product Name:</td><td>'.stripslashes($productName).'</td>
					</tr>
					<tr>
					  <td colspan="2">Please chase them up to ensure they fill in their form.</td>
					</tr>
					<tr>
					  <td colspan="2">Thanks,</td>
					</tr>
					<tr>
					  <td colspan="2">Aaron.</td>
					</tr>
				  </table>
				</body>
				</html>
				';
				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$to = 'setupmyproducts@gmail.com';
				
				mail($to, $subject, $message, $headers);
	}
}
?>

