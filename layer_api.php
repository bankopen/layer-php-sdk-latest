<?php
/*
 * Class File: Open Payment Gateway Class
 * Version: 1.1
 * Author: Openers
 * Author URI: https://open.money/
*/
Class LayerApi{
	const BASE_URL_SANDBOX = "https://sandbox-icp-api.bankopen.co/api";
    const BASE_URL_UAT = "https://icp-api.bankopen.co/api";

    public function __construct($env,$access_key,$secret_key){

        $this->env = $env;
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
    }

    public function create_payment_token($data){

        try {
            $pay_token_request_data = array(
                'amount'   			=> (!empty($data['amount']))? $data['amount'] : NULL,
                'currency' 			=> (!empty($data['currency']))? $data['currency'] : NULL,
                'name'     			=> (!empty($data['name']))? $data['name'] : NULL,
                'email_id' 			=> (!empty($data['email_id']))? $data['email_id'] : NULL,
                'contact_number' 	=> (!empty($data['contact_number']))?  $data['contact_number'] : NULL,
                'mtx'    			=> (!empty($data['mtx']))? $data['mtx'] : NULL,
                'udf'    			=> (!empty($data['udf']))? $data['udf'] : NULL,
            );

            $pay_token_data = $this->http_post($pay_token_request_data,"payment_token");

            return $pay_token_data;
        } catch (Exception $e){			
            return [
                'error' => $e->getMessage()
            ];

        } catch (Throwable $e){
			
			return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function get_payment_token($payment_token_id){

        if(empty($payment_token_id)){

            throw new Exception("payment_token_id cannot be empty.");
        }

        try {

            return $this->http_get("payment_token/".$payment_token_id);

        } catch (Exception $e){

            return [
                'error' => $e->getMessage()
            ];

        } catch (Throwable $e){

            return [
                'error' => $e->getMessage()
            ];
        }

    }

    public function get_payment_details($payment_id){

        if(empty($payment_id)){

            throw new Exception("payment_id cannot be empty.");
        }

        try {

            return $this->http_get("payment/".$payment_id);

        } catch (Exception $e){
			
            return [
                'error' => $e->getMessage()
            ];

        } catch (Throwable $e){

            return [
                'error' => $e->getMessage()
            ];
        }

    }


    function build_auth(){

        return array(                       
            'Content-Type: application/json',                                 
            'Authorization: Bearer '.$this->access_key.':'.$this->secret_key            
        );

    }


    function http_post($data,$route){

        foreach (@$data as $key=>$value){

            if(empty($data[$key])){

                unset($data[$key]);
            }
        }

        if($this->env == 'live'){

            $url = self::BASE_URL_UAT."/".$route;

        } else {

            $url = self::BASE_URL_SANDBOX."/".$route;
        }


        $header = $this->build_auth();
        
        try
        {
            $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($curl, CURLOPT_SSLVERSION, 6);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_MAXREDIRS,10);
		    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		    curl_setopt($curl, CURLOPT_ENCODING, '');		
		    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT ));
            
		    $response = curl_exec($curl);
            $curlerr = curl_error($curl);
            
            if($curlerr != '')
            {
                return [
                    "error" => "Http Post failed.",
                    "error_data" => $curlerr,
                ];
            }
            return json_decode($response,true);
        }
        catch(Exception $e)
        {
            return [
                "error" => "Http Post failed.",
                "error_data" => $e->getMessage(),
            ];
        }           
        
    }

    function http_get($route){

        if($this->env == 'live'){

            $url = self::BASE_URL_UAT."/".$route;

        } else {

            $url = self::BASE_URL_SANDBOX."/".$route;
        }


        $header = $this->build_auth();

        try
        {
           
            $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		    curl_setopt($curl, CURLOPT_ENCODING, '');		
		    curl_setopt($curl, CURLOPT_TIMEOUT, 60);		   
            $response = curl_exec($curl);
            $curlerr = curl_error($curl);
            if($curlerr != '')
            {
                return [
                    "error" => "Http Get failed.",
                    "error_data" => $curlerr,
                ];
            }
            return json_decode($response,true);
        }
        catch(Exception $e)
        {
            return [
                "error" => "Http Get failed.",
                "error_data" => $e->getMessage(),
            ];
        }
    }
}
