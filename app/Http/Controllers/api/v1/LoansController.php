<?php

namespace App\Http\Controllers\api\v1;

use App\Events\LoanCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\Loan as LoanResource;
use App\Models\Loan as LoanModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoansController extends ApiController
{
    public function create(Request $request)
    {
        $msg = "Loan Created!";
        $rules = [
            'approved_amount' => 'required|integer',
            'loan_tenor' => 'required|integer',
            'interest_rate' => 'required|between:1,4',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422);
        }
        // check if user already has a pending or approved (but not fully repaid) loan
        $loan_exists = LoanModel::where('user_id', Auth::id())
            ->whereIn('status', [LoanModel::LOAN_STATUS_PENDING, LoanModel::LOAN_STATUS_APPROVED])
            ->exists();

        // if no pending or approved (but not fully repaid) loan, create the loan
        if ($loan_exists === false) {
            $loans = new LoanModel;
            $loans->approved_amount = $request->approved_amount;
            $loans->loan_tenor = $request->loan_tenor;
            $loans->interest_rate = $request->interest_rate;
            //dd(request()->all());
            if ($response = $loans->save()) {
                $status = [
                    LoanModel::LOAN_STATUS_PENDING => 'Pending',
                    LoanModel::LOAN_STATUS_APPROVED => 'Approved',
                    LoanModel::LOAN_STATUS_REPAID => 'Repaid',
                    LoanModel::LOAN_STATUS_REJECTED => 'Rejected',
                ];
                //dd($loans);
                $total_interest = $loans->approved_amount * ($loans->interest_rate * $loans->loan_tenor / 100);
                $total_amount_repayable = $loans->approved_amount + $total_interest;
                $monthly_total_repayment = $total_amount_repayable / $loans->loan_tenor;
                $weekly_total_repayment = $monthly_total_repayment / 4;
        
                $data=  [
                    'id' => (int)$loans->id,
                    //'user' => $this->user->full_name,
                    'approved_amount' => number_format($loans->approved_amount, 2),
                    'loan_tenor' => $loans->loan_tenor . ' ' . 'months', $loans->loan_tenor,
                    'interest_rate' => $loans->interest_rate,
                    'disbursed_amount' => number_format($loans->disbursed_amount, 2),
                    'total_interest' => number_format($total_interest, 2),
                    'total_amount_repayable' => number_format($total_amount_repayable, 2),
                    'monthly_total_repayment' => number_format($monthly_total_repayment, 2),
                    'weekly_total_repayment' => number_format($weekly_total_repayment, 2),
                    'status' => $status[$loans->status],
                    //'repayments' => ['repayment_method' => $loans->repayment_method,]RepaymentResource::collection($this->whenLoaded('repayments')),
                ];
                $response = $this->sendResponse($data, $msg);
            } else {
                $response = $this->sendError($response);
            }
        }
        return $response;
    }

}
