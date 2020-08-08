<?php
session_start();
ob_start();
/*
 * PHP Kit Name: Layer Payment - Open Payment Gateway
 * Plugin URI: https://open.money/
 * Description: Open's Layer Payment Gateway integration kit 
 * for PHP 5 and 7 compatible mode
 * Version: 1.0.2
 * Author: Openers
 * Author URI: https://open.money/
*/
require_once 'layer_api.php';
require_once 'common.php';


//main logic
$error = '';
$tranid=date("ymd").'-'.rand(1,100);

$sample_data['mtx']=$tranid; //unique transaction id to be passed for each transaction 
$layer_api = new LayerApi($environment,$accesskey,$secretkey);
$layer_payment_token_data = $layer_api->create_payment_token($sample_data);
   
if(empty($error) && isset($layer_payment_token_data['error'])){
	$error = 'E55 Payment error. ' . ucfirst($layer_payment_token_data['error']);  
	if(isset($layer_payment_token_data['error_data']))
	{
		foreach($layer_payment_token_data['error_data'] as $d)
			$error .= " ".ucfirst($d[0]);
	}
}

if(empty($error) && (!isset($layer_payment_token_data["id"]) || empty($layer_payment_token_data["id"]))){				
    $error = 'Payment error. ' . 'Layer token ID cannot be empty.';        
}   

if(!empty($layer_payment_token_data["id"]))
    $payment_token_data = $layer_api->get_payment_token($layer_payment_token_data["id"]);
    
if(empty($error) && !empty($payment_token_data)){
    if(isset($layer_payment_token_data['error'])){
        $error = 'E56 Payment error. ' . $payment_token_data['error'];            
    }

    if(empty($error) && $payment_token_data['status'] == "paid"){
        $error = "Layer: this order has already been paid.";            
    }

    if(empty($error) && $payment_token_data['amount'] != $sample_data['amount']){
        $error = "Layer: an amount mismatch occurred.";
    }

    $jsdata['payment_token_id'] = html_entity_decode((string) $payment_token_data['id'],ENT_QUOTES,'UTF-8');
    $jsdata['accesskey']  = html_entity_decode((string) $accesskey,ENT_QUOTES,'UTF-8');
        
	$hash = create_hash(array(
        'layer_pay_token_id'    => $payment_token_data['id'],
        'layer_order_amount'    => $payment_token_data['amount'],
        'tranid'    => $tranid,
    ),$accesskey,$secretkey);
        
    $html =  "<form action='response.php' method='post' style='display: none' name='layer_payment_int_form'>
		<input type='hidden' name='layer_pay_token_id' value='".$payment_token_data['id']."'>
        <input type='hidden' name='tranid' value='".$tranid."'>
        <input type='hidden' name='layer_order_amount' value='".$payment_token_data['amount']."'>
        <input type='hidden' id='layer_payment_id' name='layer_payment_id' value=''>
        <input type='hidden' id='fallback_url' name='fallback_url' value=''>
        <input type='hidden' name='hash' value='".$hash."'>
        </form>";
    $html .= "<script>";
    $html .= "var layer_params = " . json_encode( $jsdata ) . ';'; 
    
    $html .="</script>";
    $html .= '<script src="./layer_checkout.js"></script>';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHP Kit for Layer Payment</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

<script src="<?php echo $remote_script; ?>"></script>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" >

</head>
<style type="text/css">
	.main {
		margin-left:30px;
		font-family:Verdana, Geneva, sans-serif, serif;
	}
	.text {
		float:left;
		width:180px;
	}
	.dv {
		margin-bottom:5px;
        margin-top:5px;
	}
	.logo {
		margin-bottom:20px;
        margin-top:5px;
	}
</style>
<body>
<div class="main">
	<div class="logo">
		<img src="logo.png" height="20" alt="Layer Payment" />
	</div>

	<div class="dv">
		<label>Full Name: <?php echo $sample_data['name']; ?></label>
	</div>
	<div class="dv">
		<label>E-mail: <?php echo $sample_data['email_id']; ?></label>
	</div>
	<div class="dv">
		<label>Mobile Number: <?php echo $sample_data['contact_number']; ?></label>
	</div>
	<div class="dv">
		<label>Amount: <?php echo $sample_data['currency'].' '.$sample_data['amount']; ?></label>
	</div>
		
	
	<div id="layerloader">
		
		<?php 
			if(!empty($error)) echo $error;
			if (isset($html)) { ?>
			<div class="dv">
				<input id="submit" name="submit" value="Pay" type="button" onclick="triggerLayer();">
			</div>
		<?php echo $html;}?>
	</div>
</div>
</body>
</html>