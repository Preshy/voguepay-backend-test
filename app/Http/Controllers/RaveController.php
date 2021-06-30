<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RaveController extends Controller
{
    public $reference, $status, $log, $message, $response, $error, $status_error;

    /*
     * Send authenticated request to Rave's api
     */

    public function __request($endpoint, $data, $type = 'post') {
        try {

            $headers = array('Accept' => 'application/json', 'Authorization' => 'Bearer '.env('RAVE_SECRET_KEY'));

            $client = new Client([
                'headers' => $headers
            ]); //GuzzleHttp\Client
            $result = $client->$type('https://api.flutterwave.com/v3/' . $endpoint, [
                'json' => $data
            ]);
            return $result->getBody()->getContents();

        } catch(\GuzzleHttp\Exception\ClientException $exception) {
            $response = $exception->getResponse();
            return (string)($response->getBody());
        }
    }

    /*
     * Initiate disbursement through bank transfer
     */
    public function init_bank_transfer($array) {

        try {
            $data = [
                'reference'             => $array['reference'],
                'account_bank'          => $array['bank_code'],
                'account_number'        => $array['account_number'],
                'amount'                => $array['amount'],
                'narration'             => $array['narration'],
                'seckey'                => env('RAVE_SECRET_KEY'),
                'reference'             => $array['reference'],
                'debit_currency'        => $array['currency'],
                'beneficiary_name'      => $array['fullname'],
                'callback_url'          => $array['callback'] ?? null
            ];

            $request = json_decode($this->__request('transfers', $data));

            if(isset($request->status)) {
                
                if($request->status != 'success') {
                    $this->error = 'Beneficiary bank account details could not be resolved.';
                    return FALSE;
                }

            }
                        
            $this->response = $request;
    
            $this->status = strtolower($request->data->status);
    
            return $request;
        } catch(\Exception $e) {
            return $e->getMessage();
        }

    }

    /*
     * Payment status
     */
    public function status($id) {
        try {

            // Make a call to rave to check if the transfer was done successfully.
            $request = json_decode($this->__request('transfers/'.$id, [], 'get'));
            
            if(isset($request->status)) {
                
                if($request->status != 'success') {
                    $this->status_error = 'Transfer status could not be determined.';
                    return FALSE;
                }

            }

            $status = $request->data->status;

            $this->message = $request->data->complete_message;

            if ($status == "SUCCESSFUL") {
                return "completed";
            } elseif($status == "PENDING") {
                return "pending";
            } elseif($status == "FAILED") {
                return "failed";
            } else {
                return "pending";
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
