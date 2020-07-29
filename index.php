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

//Declarations
$error = '';
$name="";
$email="";
$phone="";
$amount="";
$comment="";
$remote_script="";

$tranid=date("ymd").'-'.rand(1,100);

//main logic
if(isset($_POST['submit']))
    extract($_POST);

if(empty($tranid)){
    $error =  'Transaction ID is empty.';        
}

if(empty($access_key)){
    $error =  'Access Key is empty.';        
}

if(empty($error) && empty($secret_key)){
    $error =  'Plugin error. Secret Key is empty.';        
}     

if(empty($error) && isset($submit)) {
	$remote_script = $remote_script_live; //production		
	if( $sandbox == "yes")
		$remote_script = $remote_script_test;
	
	$env = "test";
    if($sandbox != "yes"){  $env = "live"; }
    
    if(empty($error)) {
        $layer_api = new LayerApi($env,$access_key,$secret_key);
        $layer_payment_token_data = $layer_api->create_payment_token([
                'amount' => $amount,
                'currency' => $currency,
                'name'  => $name,
                'email_id' => $email,
                'contact_number' => $phone                
            ]);
    }
    
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

    if(empty($error))
        $_session["layer_payment_token_id"] = $layer_payment_token_data["id"];
    
    if(!empty($_session["layer_payment_token_id"]))
        $payment_token_data = $layer_api->get_payment_token($_session["layer_payment_token_id"]);
    
    
    if(empty($error) && !empty($payment_token_data)){
        if(isset($layer_payment_token_data['error'])){
            $error = 'E56 Payment error. ' . $payment_token_data['error'];            
        }

        if(empty($error) && $payment_token_data['status'] == "paid"){
            $error = "Layer: this order has already been paid.";            
        }

        if(empty($error) && $payment_token_data['amount'] != $amount){
            $error = "Layer: an amount mismatch occurred.";
        }

        $jsdata['payment_token_id'] = html_entity_decode((string) $payment_token_data['id'],ENT_QUOTES,'UTF-8');
        $jsdata['accesskey']  = html_entity_decode((string) $access_key,ENT_QUOTES,'UTF-8');
        
		$hash = create_hash(array(
           'layer_pay_token_id'    => $payment_token_data['id'],
           'layer_order_amount'    => $payment_token_data['amount'],
           'tranid'    => $tranid,
        ),$access_key,$secret_key);
        
        $html =  "<form action='".$redirect_page."' method='post' style='display: none' name='layer_payment_int_form'>
            <input type='hidden' name='layer_pay_token_id' value='".$payment_token_data['id']."'>
            <input type='hidden' name='tranid' value='".$tranid."'>
            <input type='hidden' name='layer_order_amount' value='".$payment_token_data['amount']."'>
            <input type='hidden' id='layer_payment_id' name='layer_payment_id' value=''>
            <input type='hidden' id='fallback_url' name='fallback_url' value='".$local_path."'>
            <input type='hidden' name='hash' value='".$hash."'>
            </form>";
        $html .= "<script>";
        $html .= "var layer_params = " . json_encode( $jsdata ) . ';'; 
        //$html .= "alert(layer_params.retry);";
        $html .="</script>";
        $html .= '<script src="'.$local_path.'/layer_checkout.js"></script>';
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHP Kit for Layer Payment</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>


<!-- this meta viewport is required for Layer //-->
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
	<form action="" id="frmkit" method="post">	
		<div class="dv">
			<span class="text"><label>Transaction ID:</label></span>
			<span><input type="text" id="tranid" name="tranid" value="<?php echo $tranid; ?>"></span>
		</div>
		<div class="dv">
			<span class="text"><label>Full Name:</label></span>
			<span><input type="text" id="name" name="name" value="<?php echo $name; ?>"></span>
		</div>
		<div class="dv">
			<span class="text"><label>E-mail:</label></span>
			<span><input type="text" name="email" id="email" value="<?php echo $email;?>"></span>
		</div>
		<div class="dv">
			<span class="text"><label>Mobile Number: </label></span>
			<span><input type="text" name="phone" id="phone" value="<?php echo $phone; ?>"></span>
		</div>
		<div class="dv">
			<span class="text"><label>Amount:</label></span>
			<span><input type="text" name="amount" id="amount" value="<?php echo $amount; ?>"></span>
		</div>
		<div class="dv">
			<input id="submit" name="submit" value="Pay" type="submit">
		</div>
	</form>

	<div id="alertinfo" class="dv">
		<?php echo '**** '. ((!empty($error))? $error : '');?>
	</div>

	<div id="layerloader">
		<script src="<?php echo $remote_script;?>"></script>
		<?php 
			if (isset($html))
				echo $html;
		?>
	</div>
</div>
</body>
</html>