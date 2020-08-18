<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\CommonFunction;
//Lib
use Storage;
//Models
use App\Models\InvoiceNew;
use App\Models\InvoiceItemAP;
use App\Models\InvoiceFulfilledItem;
use App\Models\InvoiceGood;
use PDF;
use File;
class OBaseController extends Controller
{
    use CommonFunction;
    //
    //construct
    public function __construct()
    {

    }

    /**
     * DateRange:01/27/2020 - 02/27/2020
     *
     * Status:
     *  0:pending
     *  1:fulfillment
     *  2:problem on fulfillment
     *  3:fulfilled
     *  4:delivered
     *
     * Paid:paid order
     *
     * Priority Check:order by order's priority
     *  Priority level
     *      1:Extreme
     *      2:High
     *      3:Medium
     *      4:Low
     */

    protected function getOrdersByDateRange($date_range,$status,$paid = -1,$priority_check = -1)
    {
        if($date_range == null)
        {
            $date_range['start_date'] = date('m/d/Y', strtotime('today - 31 days'));
            $date_range['end_date']   = date('Y-m-d');
        }
        else
        {
            $date_range = $this->convertDateRangeFormat($date_range);
        }
        $cond = InvoiceNew::with('salesperson')->where('status',$status)
               ->whereRaw('DATE(date) >= ?', [$date_range['start_date']])
               ->whereRaw('DATE(date) <= ?', [$date_range['end_date']]);
        if($paid == 1)
        {
            $cond = $cond->where('paid','!=',null);
        }
        if($priority_check == 1)
        {
            $cond = $cond->orderBy('priority_id');
        }
        return $cond->orderby('created_at','desc')->get();
    }

    protected function getOrdersByPagnation(Request $request)
    {
        $date_range = $request->date_range;
        if($date_range == null)
        {
            $date_range['start_date'] = date('m/d/Y', strtotime('today - 90 days'));
            $date_range['end_date']   = date('Y-m-d');
        }
        else
        {
            $date_range = $this->convertDateRangeFormat($date_range);
        }
        $bCond = InvoiceNew::whereRaw('DATE(date) >= ?', [$date_range['start_date']])
                            ->whereRaw('DATE(date) <= ?', [$date_range['end_date']])
                            ->whereIn('status',$request->status);
        $orderingColumn = $request->input('order.0.column');
        $dir = $request->input('order.0.dir');
        switch($orderingColumn)
        {
            case '2':
                $bCond = $bCond->orderBy('number',$dir);
            break;
            case '3':
                $bCond = $bCond->with(['customer' => function($query) use ($dir){
                    $query->orderBy('clientname',$dir);
                }]);
                break;
            case '4':
                $bCond = $bCond->orderBy('total',$dir);
                break;
            case '5':
                $bCond = $bCond->orderBy('date',$dir);
                break;
            default:
                $bCond = $bCond->orderBy('date','desc');
                $bCond = $bCond->orderBy('number','desc');
        }
        $totalData = $bCond->count();
        $limit = $request->input('length') != -1?$request->input('length'):$totalData;
		$start = $request->input('start');
        $totalFiltered = $bCond->count();
        if(empty($request->input('search.value'))){
            $totalFiltered  = $bCond->count();
            $orders = $bCond->offset($start)->limit($limit)->get();
        }
        else
        {
            $search = $request->input('search.value');
            $cond = $bCond->Where(function($query) use ($search)
                          {
                            $query->where('number','like',"%{$search}%")
                            ->orWhereHas('customer',function($query) use ($search){
                                $query->where('clientname','like',"%{$search}%");
                            })
                            ->orWhereHas('distuributor',function($query) use ($search){
                                $query->where('companyname','like',"%{$search}%");
                            })
                            ->orWhere('total','like',"%{$search}%")
                            ->orWhere('date','like',"%{$search}%");
                          });
            $totalFiltered  = $cond->count();
            $limit = $request->input('length') != -1?$request->input('length'):$totalFiltered;
            $orders      = $cond->offset($start)->limit($limit)->get();
        }
        $data = [];
        if($orders){
			foreach($orders as $order){

                $nestedData = [];
                $nestedData['id']               = $order->id;
                $nestedData['customer']         = $order->customer;
                $nestedData['deliver_note']     = $order->deliver_note;
                $nestedData['number']           = $order->number;
                $nestedData['salesRep']   = $order->salesperson != null?
                                $order->salesperson->firstname.' '.$order->salesperson->lastname:'';
                $nestedData['clientname']       = $order->CName;
                $nestedData['companyname']      = $order->CPName;
                $nestedData['companyemail']     = $order->customer != null?$order->customer->companyemail:'No';
                $nestedData['salesemail']       = $order->salesemail;
                $nestedData['total_info']       = $order->total_info;
                $nestedData['total_financial']  = $order->FinancialTotalInfo;
                $nestedData['date']             = $order->date;
                $nestedData['distuributor_id']  = $order->distuributor_id;
                $nestedData['metrc_manifest']   = $order->metrc_manifest;
                $nestedData['m_m_str']          = $order->m_m_str;
                $nestedData['coainbox']         = $order->coainbox;
                $nestedData['paid']             = $order->paid;
                $nestedData['metrc_ready']      = $order->metrc_ready;
                $nestedData['items']            = $order->getFulfilledItems();
                //get extra discount
                $nestedData['pDiscount']        = $order->rPDiscount;
				$data[] = $nestedData;
			}
        }
        return array(
			"draw"			=> intval($request->input('draw')),
			"recordsTotal"	=> intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"			=> $data
		);
    }
    protected function getOrdersByPagnation1(Request $request)
    {
        $date_range = $request->date_range;
        if($date_range == null)
        {
            $date_range['start_date'] = date('m/d/Y', strtotime('today - 31 days'));
            $date_range['end_date']   = date('Y-m-d');
        }
        else
        {
            $date_range = $this->convertDateRangeFormat($date_range);
        }
        $bCond = InvoiceNew::whereRaw('DATE(date) >= ?', [$date_range['start_date']])
                            ->whereRaw('DATE(date) <= ?', [$date_range['end_date']])
                            ->whereIn('status',$request->status);
        $orderingColumn = $request->input('order.0.column');
        $dir = $request->input('order.0.dir');
        switch($orderingColumn)
        {
            case '2':
                $bCond = $bCond->orderBy('number',$dir);
            break;
            case '3':
                $bCond = $bCond->with(['customer' => function($query) use ($dir){
                    $query->orderBy('clientname',$dir);
                }]);
                break;
            case '4':
                $bCond = $bCond->orderBy('total',$dir);
                break;
            case '5':
                $bCond = $bCond->orderBy('date',$dir);
                break;
            default:
                $bCond = $bCond->orderBy('date','desc');
                $bCond = $bCond->orderBy('number','desc');
        }
        $totalData = $bCond->count();
        $limit = $request->input('length') != -1?$request->input('length'):$totalData;
		$start = $request->input('start');
        $totalFiltered = $bCond->count();
        if(empty($request->input('search.value'))){
            $totalFiltered  = $bCond->count();
            $orders = $bCond->offset($start)->limit($limit)->get();
        }
        else
        {
            $search = $request->input('search.value');
            $cond = $bCond->where('number','like',"%{$search}%")
                    ->orWhereHas('customer',function($query) use ($search){
                        $query->where('clientname','like',"%{$search}%");
                    })
                    ->orWhereHas('distuributor',function($query) use ($search){
                        $query->where('companyname','like',"%{$search}%");
                    })
                    ->orWhere('total','like',"%{$search}%")
                    ->orWhere('date','like',"%{$search}%");
            $totalFiltered  = $cond->count();
            $limit = $request->input('length') != -1?$request->input('length'):$totalFiltered;
            $orders      = $cond->offset($start)->limit($limit)->get();
        }

        return array(
			"draw"			=> intval($request->input('draw')),
			"recordsTotal"	=> intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
            'data'        => $orders
		);
    }
    protected function setOrderStatus(Request $request)
    {
        $invoice = InvoiceNew::find($request->id);
        $invoice->status = $request->status;
        $invoice->save();
        $this->archiveOrder($request->id);
        return 1;
    }

    public function generatePdf($invoice,$view_name)
    {
        $pdf = PDF::loadView($view_name, ['invoice' => $invoice]);
        $file_name = $invoice->number.'/invoice.pdf';

        $file_name_mail = $invoice->number.'/mail.pdf';
        if(!Storage::disk('public')->put($file_name, $pdf->output()))
        {
            return false;
        }

        if(!Storage::disk('public')->put($file_name_mail, $pdf->output()))
        {
            return false;
        }
    }
    public function _set_metrc_ready_order(Request $request)
    {
        $invoice = InvoiceNew::find($request->id);
        if($request->status == '1')
            $invoice->metrc_ready = date('Y-m-d');
        else
            $invoice->metrc_ready = null;

        $invoice->save();
    }
    public function getCoaList($invoice)
    {
        $coas = ['exist' => [],'n_exist' => []];
        foreach ($invoice->fulfilledItem as $i => $item)
        {
            foreach ($item->CoaList as $coa)
            {
                if ($coa['is_exist'])
                {
                    $exist = false;
                    foreach($coas['exist'] as $ncoa)
                    {
                        if($ncoa == $coa['coa'])
                        $exist = true;
                    }
                    if(!$exist)
                        $coas['exist'][] = $coa['coa'];
                }
                else
                {
                    $exist = false;
                    foreach($coas['n_exist'] as $ncoa)
                    {
                        if($ncoa == $coa['coa'])
                        $exist = true;
                    }
                    if(!$exist)
                        $coas['n_exist'][] = $coa['coa'];

                    // if(!array_search($coa['coa'],$coas['n_exist']))
                    //     $coas['n_exist'][] = $coa['coa'];
                }
            }
        }

        return $coas;
    }

    public function archiveOrder($id)
    {
        $invoice = InvoiceNew::find($id);
        if($invoice->status == 4 && $invoice->FinancialTotalInfo['completed'] == 1)
        {
            $invoice->paid = date('y-m-d');
            $invoice->save();
        }
    }

    public function setDeliveryStatus($id,$status)
    {
        $order = InvoiceNew::find($id);
        $order->delivery_status = $status;
        $order->delivered = date('Y-m-d');
        $result = 1;
        switch($status)
        {
            case 2:
                $order->status = 4;
                $result = 1;
            break;
            case 3:
                $order->status = 5;
                $result = 2;
            break;
            case 4:
                $order->status = 6;
                $result = 3;
            break;
        }

        $order->save();
        return $result;
    }
}
