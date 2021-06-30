<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Wallets;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\UtilsController;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * Create New Transaction
     * @param Request $request
     * @return object
     */
    public function create(Request $request) {
        // Prepare validation
        $validator = Validator::make($request->all(), [
            'amount'             => ['required'],
            'beneficiary_name'   => ['required'],
            'beneficiary_phone'  => ['nullable'],
            'bank_code'          => ['required', 'numeric'],
            'account_number'     => ['required', 'numeric'],
            'narration'          => ['nullable']
        ]);

        // Confirm if the user initiating this transaction has enough funds.
        $user = $request->auth;

        $wallet = Wallets::where('account_id', $user->account_id)->where('currency', 'NGN')->first();
        if(!$wallet) {
            // wallet does not exist
            return UtilsController::errorResponse(['message' => 'User does not own a wallet.']);
        }
        
        if($request->amount > $wallet->amount) {
            // insufficient funds
            return UtilsController::errorResponse(['message' => 'Wallet balance is insufficient for this transaction.']);
        }

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {

            // Set vars
            $reference = UtilsController::generateUUID();
            
            // Create new transaction
            $transaction = new Transactions();
            $transaction->account_id = $user->account_id;
            $transaction->transaction_id = UtilsController::generateUUID();
            $transaction->reference = $reference;
            $transaction->amount = $request->amount;
            $transaction->beneficiary_name = $request->beneficiary_name;
            $transaction->beneficiary_phone = $request->beneficiary_phone;
            $transaction->bank_code = $request->bank_code;
            $transaction->account_number = $request->account_number;

            // Initiate funds transfer
            $rave = new RaveController();
            $data = [
                'fullname'       => $request->beneficiary_name,
                'account_number' => $request->account_number,
                'bank_code'      => $request->bank_code,
                'reference'      => $reference,
                'amount'         => $request->amount,
                'currency'       => 'NGN',
                'narration'      => $request->narration
            ];

            $init = $rave->init_bank_transfer($data);

            if(!$init) {
                return UtilsController::errorResponse($rave->error);
            }

            // Complete transaction creation
            $transaction->gateway = 'rave';
            $transaction->status = $rave->status;
            $transaction->request_payload = json_encode($data);
            $transaction->response_payload = json_encode($rave->response);
            $transaction->save();

            // Deduct Withdrawn Amount
            $wallet->deduct($user->account_id, $request->amount);

            return UtilsController::successResponse(['transaction' => $transaction]);
        }
    }

    /**
     * Fetch Transaction History
     * @param Request $request
     * @return object
     */
    public function history(Request $request) {
        // Fetch authenticated user
        $user = $request->auth;

        // Fetch transactions
        $transactions = Transactions::where('account_id', $user->account_id)->paginate(10);

        // Return results
        return UtilsController::successResponse($transactions);
    }

    /**
     * Fetch single transaction
     * @return object
     */
    public function single(Request $request) {
        $validator = Validator::make($request->all(), [
            'transaction_id' => ['required', 'exists:transactions']
        ]);

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {
            // Fetch authenticated user
            $user = $request->auth;
            // Fetch transaction
            $transaction = Transactions::where('account_id', $user->account_id)->where('transaction_id', $request->transaction_id)->first();
            return UtilsController::successResponse($transaction);
        }
    }

    /**
     * Search Transactions History
     * @param Request $request
     * @return object
     */
    public function search(Request $request) {
        $validator = Validator::make($request->all(), [
            'q'         => ['required'],
            'vector'    => ['required', Rule::in(['amount', 'id', 'name'])]
        ]);

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {

            $transaction = [];

            // Fetch authenticated user
            $user = $request->auth;

            switch($request->vector) {
                case 'amount':
                    $transaction = Transactions::where('account_id', $user->account_id)->where('amount', 'LIKE', '%'.$request->q.'%')->get();
                break;

                case 'id':
                    $transaction = Transactions::where('account_id', $user->account_id)->where('transaction_id', 'LIKE', '%'.$request->q.'%')->get();
                break;

                case 'name':
                    $transaction = Transactions::where('account_id', $user->account_id)->where('beneficiary_name', 'LIKE', '%'.$request->q.'%')->get();
                break;
            }
           
            return UtilsController::successResponse($transaction);
        }
    }

    /**
     * Verify Transaction (Transfer)
     * @param Request $request
     * @return object
     */
    public function verify(Request $request) {
        $validator = Validator::make($request->all(), [
            'transaction_id' => ['required', 'exists:transactions']
        ]);

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {
            // Fetch authenticated user
            $user = $request->auth;
            // Fetch transaction
            $transaction = Transactions::where('account_id', $user->account_id)->where('transaction_id', $request->transaction_id)->first();

            // Get Transfer ID
            $id = json_decode($transaction->response_payload)->data->id;

            // Verify Transaction (Money Transfer)
            $rave = new RaveController();
            $status = $rave->status($id);

            if(!$status) {
                return UtilsController::errorResponse($rave->status_error);
            }

            // Update Transaction Status
            $transaction->status = $status;
            $transaction->save();

            $message = '';
            if($status == 'failed') {
                $message = 'Your transfer failed, possible reason: ' . $rave->message;
            } elseif($status == 'completed') {
                $message = 'Your transfer has been completed successfully.';
            } else {
                $message = 'Your transfer status is being confirmed.';
            }

            return UtilsController::successResponse(['message' => $message, 'transaction' => $transaction]);
        }
    }
}
