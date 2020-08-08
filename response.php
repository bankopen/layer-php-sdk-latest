<?php
session_start();
ob_start();

require_once 'layer_api.php';
require_once 'common.php';

$error = "";
$status = "";

if(!isset($_POST['layer_payment_id']) || empty($_POST['layer_payment_id'])){
	$error = "Invalid response.";    
}
try {
    $data = array(
        'layer_pay_token_id'    => $_POST['layer_pay_token_id'],
        'layer_order_amount'    => $_POST['layer_order_amount'],
        'tranid'     			=> $_POST['tranid'],
    );

    if(empty($error) && verify_hash($data,$_POST['hash'],$accesskey,$secretkey) && !empty($data['tranid'])){
        $layer_api = new LayerApi($environment,$accesskey,$secretkey);
        $payment_data = $layer_api->get_payment_details($_POST['layer_payment_id']);


        if(isset($payment_data['error'])){
            $error = "Layer: an error occurred E14".$payment_data['error'];
        }

        if(empty($error) && isset($payment_data['id']) && !empty($payment_data)){
            if($payment_data['payment_token']['id'] != $data['layer_pay_token_id']){
                $error = "Layer: received layer_pay_token_id and collected layer_pay_token_id doesnt match";
            }
            elseif($data['layer_order_amount'] != $payment_data['amount']){
                $error = "Layer: received amount and collected amount doesnt match";
            }
            else {
                switch ($payment_data['status']){
                    case 'authorized':
                    case 'captured':
                        $status = "Payment captured: Payment ID ". $payment_data['id'];
                        break;
                    case 'failed':								    
                    case 'cancelled':
                        $status = "Payment cancelled/failed: Payment ID ". $payment_data['id'];                        
                        break;
                    default:
                        $status = "Payment pending: Payment ID ". $payment_data['id'];
                        exit;
                    break;
                }
            }
        } else {
            $error = "invalid payment data received E98";
        }
    } else {
        $error = "hash validation failed";
    }

} catch (Throwable $exception){

   $error =  "Layer: an error occurred " . $exception->getMessage();
    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHP Kit for Layer Payment</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

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
    <div id="alertinfo" class="dv">
    <?php 
		if(!empty($error))
            echo $error;
        else
            echo $status;		
    ?>
    </div>
    <div id="go" class="dv">
        <a href="index.php">Another Payment</a>
    </div>
</div>

</body>
</html>