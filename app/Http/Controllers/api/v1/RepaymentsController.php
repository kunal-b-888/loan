<?php

namespace App\Http\Controllers\api\v1;

use App\Events\RepaymentCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\Repayment as RepaymentResource;
use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class RepaymentsController extends ApiController
{
    public function create(Request $request)
    {
        $msg = 'Repayment created.';
        $data=[];
        $rules = [
            'repayment_amount' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'repayment_method' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422);
        }

        // if validation passes, proceed to create the repayment
        // if (Auth::user()) {
        //     $id = Auth::user()->id;
        // } else {
        //     $id = 2;
        // }
            // check if user has any approved (but not fully repaid) loan
            $loan = Loan::select(['id', 'approved_amount', 'interest_rate', 'loan_tenor'])
                //->where('user_id', auth()->user()->id)
                ->where('user_id',Auth::id())
                ->where('status', Loan::LOAN_STATUS_APPROVED)
                ->first();
               // dd($loan);

            // if approved (but not fully repaid) loan found, proceed to create a repayment for that loan
            if ($loan) {
                $repayment = new Repayment;
                $repayment->user_id = Auth::id();
                $repayment->loan_id = $loan->id;
                $repayment->repayment_amount = $request->repayment_amount;
                $repayment->repayment_method = $request->repayment_method;

                    $total_interest = $loan->approved_amount * ($loan->interest_rate * $loan->loan_tenor / 100);
                    $total_amount_repayable = $loan->approved_amount + $total_interest;
                    $monthly_total_repayment = number_format($total_amount_repayable / $loan->loan_tenor, 2, '.', '');
                    $weekly_total_repayment = number_format($monthly_total_repayment / 4, 2, '.', '');
                    $repayment_amount = number_format($request->repayment_amount, 2, '.', '');

                    if ($monthly_total_repayment === $repayment_amount) {
                        if ($response = $repayment->save()) { 
                            $data =  [
                                'id' => $repayment->id,
                                //'user' => $this->user->full_name,
                                'total_amount_repayable' => number_format($total_amount_repayable, 2),
                                'monthly_total_repayment' => number_format($monthly_total_repayment, 2),
                                'weekly_total_repayment' => number_format($weekly_total_repayment, 2),
                                'repayment_method' => $request->repayment_method,
                                'loan_tenor' =>$loan->loan_tenor . ' ' .'months', $loan->loan_tenor,
                                // 'repayment_paid' => $this->loan->repayments()->count(),
                            ];
                            $response = $this->sendResponse($data, $msg);
                        }
                        else{
                            $response = $this->sendError($response);
                        }
                    }
                    $response = $this->sendResponse($data, 'You must pay a repayment amount of '. number_format($monthly_total_repayment, 2));
                 
            }else{
                $response = $this->sendError('No unpaid loan found to make a repayment.');
            }
            return $response;
    }
}
