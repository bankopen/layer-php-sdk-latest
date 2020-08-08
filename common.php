<?php
//Change below accesskey, secretkey to test 
$accesskey = '<accesskey>';
$secretkey = '<secretkey>';

//Changing environment to live requires remote_script also to be used for live and change accesskey,secretkey too
$environment = 'test';

//Sample data
$sample_data = [
                'amount' => 12.00,
                'currency' => 'INR',
                'name'  => 'John Doe',
                'email_id' => 'john.doe@dummydomain.com',
                'contact_number' => '9831111111',
				'mtx' => ''
            ];
$remote_script = "https://sandbox-payments.open.money/layer";
//for production
//$remote_script = "https://payments.open.money/layer";

//Hash functions requried in both request and response
function create_hash($data,$accesskey,$secretkey){
    ksort($data);
    $hash_string = $accesskey;
    foreach ($data as $key=>$value){
        $hash_string .= '|'.$value;
    }
    return hash_hmac("sha256",$hash_string,$secretkey);
}

function verify_hash($data,$rec_hash,$accesskey,$secretkey){
    $gen_hash = create_hash($data,$accesskey,$secretkey);
    if($gen_hash === $rec_hash){
        return true;
    }
    return false;
}