<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InvoiceNew;
use App\Models\InvoiceCreditNote;
use App\Models\Customer;
use App\Models\Term;
use App\Models\Strainame;
use App\Models\Producttype;
use JavaScript;
use App\Helper\CommonFunction;

use DB;
class CreditNoteController extends Controller
{
    use CommonFunction;
    //
    public function form($id)
    {
        $order = InvoiceNew::find($id);
        $data = [
            'order'           => $order,
            'strains'         => Strainame::orderby('strain')->get(),
            'producttypes'    => Producttype::where('onordermenu',1)->orderby('producttype')->get(),
            'date'            => $order->date,
            'number'          => $order->number,
            'term'            => Term::find($order->term_id),
        ];
        return view('creditNote.form',$data);
    }
    public function store(Request $request)
    {
        $creditNote = new InvoiceCreditNote;
        $creditNote->fill($request->except(['items']));
        $creditNote->save();
        $creditNote->storeHasMany(['rItems' => $request->items]);
        return response()->json(['success' => 1]);
    }
    public function archive()
    {
        $data = [
            'start_date' => date('m/d/Y', strtotime('today - 31 days')),
            'end_date' => Date('m/d/Y'),
        ];
        return view('creditNote.archive',$data);
    }
    public function _archives(Request $request)
    {
        $date_range = $request->date_range;
        $date_range = $this->convertDateRangeFormat($date_range);
        $bCond = Customer::select('client_id','clientname')
                         ->with('rCreditNote')
                         ->whereHas('rCreditNote',function($query) use($date_range){
                                    $query->whereBetween('created_at', [
                                        $date_range['start_date']." 00:00:00",
                                        $date_range['end_date']." 23:59:59"
                                    ]);
        });
        //check order column
        $orderingColumn = $request->input('order.0.column');
        $dir = $request->input('order.0.dir');
        switch($orderingColumn)
        {
            case '1':
                $bCond = $bCond->orderBy('clientname',$dir);
            break;
            default:
                $bCond = $bCond->orderBy('clientname','desc');
        }

        $totalData = $bCond->count();
        $limit = $request->input('length') != -1?$request->input('length'):$totalData;
        $start = $request->input('start');
        $totalFiltered  = $bCond->count();
        if(!empty($request->input('search.value'))){
            $search = $request->input('search.value');
            $bCond = $bCond->Where(function($query) use ($search){
                        $query->where('clientname','like',"%{$search}%");
                    });
            $totalFiltered  = $bCond->count();
        }

        $data = $bCond->offset($start)->limit($limit)->get();
        $responseData = [];
        foreach($data as $key => $item)
        {
            $temp = [];
            $temp['no'] = $key + 1;
            $temp['id'] = $item->client_id;
            $temp['name'] = $item->clientname;
            $temp['items'] = [];
            $totalPrice = 0;
            foreach($item->rCreditNote as $creditNote)
            {
                $creditNoteTemp = [];
                $creditNoteTemp['so'] = $creditNote->rInvoice->number;
                $creditNoteTemp['total_price'] = $creditNote->total_price;
                $temp['items'][] = $creditNoteTemp;
                $totalPrice += $creditNote->total_price;
            }
            $temp['total_price'] = $totalPrice;
            $responseData[] = $temp;
        }
        return array(
			"draw"			=> intval($request->input('draw')),
			"recordsTotal"	=> intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"			=> $responseData,
		);
    }
}
