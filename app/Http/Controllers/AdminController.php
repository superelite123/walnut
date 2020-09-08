<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//LIB
use JavaScript;
//Models
use App\Models\FGInventory;
use App\Models\InventoryVault;
use App\Helper\CommonFunction;
use App\Models\Customer;
use App\Models\Distributor;
use App\Models\MetrcManifest;

use App\Models\InvoiceNew;
use App\Models\InvoiceItemAP;
use App\Models\InvoiceFulfilledItem;
use App\Models\InvoiceGood;
use App\Models\OurDetail;
use App\Models\Promo;
use DB;
use PDF;
use Config;
class AdminController extends OBaseController
{
    //construct
    public function __construct()
    {

    }
    public function financialExport()
    {
        $invoices = InvoiceNew::whereIn('status',[4])->get();
        $data['collectionUrl'] = '../../signature/panel/';
        $data['viewUrl'] = '../../order_fulfilled/view/';
        $data['signFileUrl'] = asset('storage/dSigns/');
        JavaScript::put([
            'start_date' => date('m/d/Y', strtotime('today - 31 days')),
            'end_date' => Date('m/d/Y'),
        ]);
        return view('admin.financial_export',$data);
    }
    public function getInvoices(Request $request)
    {
        $invoiceStatus = [[3,4],[4],[3]];
        $request->status = $invoiceStatus[$request->type];
        $result = $this->getOrdersByPagnation1($request);
        $data = [];
        if($result['data']){
			foreach($result['data'] as $order){
                $nestedData = [];
                $fInfo = $order->FinancialTotalInfo;
                $nestedData['id']               = $order->id;
                $nestedData['mmstr']            = $order->m_m_str;
                $nestedData['number']           = $order->number2;
                $nestedData['clientname']       = $order->CName;
                $nestedData['total_info']       = $order->TotalInfoForExport;
                $nestedData['rSubTotal']        = $fInfo['rSubTotal'];
                $nestedData['rTax']             = $fInfo['rTax'];
                $nestedData['date']             = $order->date;
				$data[] = $nestedData;
			}
        }
        $result['data'] = $data;
        return response()->json($result);
    }
    public function getCustomers(Request $request)
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
        $totalData = Customer::count();
		$limit = $request->input('length');
		$start = $request->input('start');
        $dir = $request->input('order.0.dir');
        $totalFiltered = Customer::count();
        if(empty($request->input('search.value'))){
            $totalFiltered  = Customer::count();
            $customers = Customer::offset($start)
                ->whereRaw('DATE(created) >= ?', [$date_range['start_date']])
                ->whereRaw('DATE(created) <= ?', [$date_range['end_date']])
                ->limit($limit)
                ->orderBy('clientname')
                ->get();
        }
        else
        {
            $search = $request->input('search.value');
            $cond = Customer::where('clientname','like',"%{$search}%")
                    ->whereRaw('DATE(created) >= ?', [$date_range['start_date']])
                    ->whereRaw('DATE(created) <= ?', [$date_range['end_date']])
                    ->orWhere('legalname','like',"%{$search}%")
                    ->orWhere('companyemail','like',"%{$search}%")
                    ->orWhere('accounting_name','like',"%{$search}%");
            $totalFiltered  = $cond->count();
            $customers      = $cond->offset($start)->limit($limit)->get();
        }
        foreach($customers as $customer)
        {
            $customer->stateName    = $customer->state_name != null?$customer->state_name->name:'';
            $customer->termName     = $customer->Term != null?$customer->Term->term:'';
            $customer->LicTypeLabel = $customer->rLicenseType != null?$customer->rLicenseType->name:'';
            $temps = [];
            $sumSubTotal = 0;
            $sumTax = 0;
            $sumTotal = 0;
            $sumPTotal = 0;
            $sumPTax = 0;
            $sumRTotal = 0;
            $sumRTax = 0;
            foreach($customer->Invoices as $invoice)
            {
                $tf = $invoice->TotalInfo;
                $ff = $invoice->FinancialTotalInfo;
                $temp = [];
                $temp['number']     = $invoice->number;
                $temp['date']       = $invoice->date;
                $temp['subTotal']   = $tf['extended'];
                $temp['tax']        = $tf['tax'];
                $temp['total']      = $tf['adjust_price'];
                $temp['pTotal']     = $ff['pSubTotal'];
                $temp['pTax']       = $ff['pTax'];
                $temp['rTotal']     = $ff['rSubTotal'];
                $temp['rTax']       = $ff['rTax'];
                $temp['url']        = url('order_fulfilled/view/'.$invoice->id.'/0');
                $temp['download']   = url('order_fulfilled/_download_invoice_pdf/'.$invoice->id);

                $sumSubTotal    += $temp['subTotal'];
                $sumTax         += $temp['tax'];
                $sumTotal       += $temp['total'];
                $sumPTotal      += $temp['pTotal'];
                $sumPTax        += $temp['pTax'];
                $sumRTotal      += $temp['rTotal'];
                $sumRTax        += $temp['rTax'];

                $temps[]        = $temp;
            }
            $customer->sumSubTotal  = number_format((float)$sumSubTotal, 2, '.', '');
            $customer->sumTax       = number_format((float)$sumTax, 2, '.', '');
            $customer->sumTotal     = number_format((float)$sumTotal, 2, '.', '');
            $customer->sumPTotal    = number_format((float)$sumPTotal, 2, '.', '');
            $customer->sumPTax      = number_format((float)$sumPTax, 2, '.', '');
            $customer->sumRTotal    = number_format((float)$sumRTotal, 2, '.', '');
            $customer->sumRTax      = number_format((float)$sumRTax, 2, '.', '');

            $customer->myInvoices = $temps;
            foreach ($customer->toArray() as $name => $value) {
                if ($value == null) {
                    $customer->{$name} = '';
                }
            }
        }
        $json_data = array(
			"draw"			=> intval($request->input('draw')),
			"recordsTotal"	=> intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"			=> $customers
		);
        return response()->json($json_data);
    }
    public function invoiceCollection(Request $request)
    {
        return view('admin.invoice_collection');
    }
}
