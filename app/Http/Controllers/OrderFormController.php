<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Order Related
use App\Models\Order;
//Others
use App\Models\Customer;
use App\Models\ContactPerson;
use App\Models\Distributor;
use App\Models\Counter;
use App\Models\Carrier;
use App\Models\OurDetail;
use App\Models\Strainame;
use App\Models\Producttype;
use App\Models\Promo;
use App\Models\ActiveInventory;
use App\Models\InvoiceItemAP;
use App\Models\UPController;
use App\Models\Term;

use DB;
use JavaScript;
class OrderFormController extends Controller
{
    /**
     * 8.12 Chi
     *
     * Create/Update Order
     *
     * @param int $id
     * @return View
     */
    public function index(Request $request)
    {
        $invoice_id = $request->id;
        //change 8.12
        $counter = Counter::select(DB::raw('concat(prefix,value) as number'))->where('key','invoice')->first();
        $form = [
            'number'          => $counter->number,
            'date'            => date('Y-m-d'),
            'clients'         => Customer::with(['Term','basePrice'])->get(),
            'mode'            => 'store',
            'term'            => null,
            'contact_persons' => ContactPerson::getSalesPerson(),
            'distuributors'   => Distributor::all(),
            'carrier'         => Carrier::all(),
            'invoice'         => new Order,
            'invoice_items'   => null,
            'carriers'        => Carrier::all(),
            'tax'             => OurDetail::all()->first()->tax,
            'strains'         => Strainame::orderby('strain')->get(),
            'producttypes'    => Producttype::where('onordermenu',1)->orderby('producttype')->get(),
            'promos'          => Promo::all(),
            'isNew'           => 0,
        ];

        $aInvList = [];
        $fg = ActiveInventory::select(DB::raw('strainname'),DB::raw('asset_type_id'),DB::raw('upc_fk'),
              DB::raw('count(*) as qty'),DB::raw('sum(weight) as weights'))
              ->with(['Strain','AssetType'])
              ->where('qtyonhand','>',0)
              ->groupby('strainname','asset_type_id')
              ->orderby('strainname','asc')
              ->orderby('asset_type_id','asc')
              ->get();
        foreach($fg as $i)
        {
            $alreay_requested = InvoiceItemAP::whereHas('Order', function($q) {
                $q->whereIn('status', [0,1,2]);
            })->where([
                    ['strain',$i->strainname],
                    ['p_type',$i->asset_type_id],
                    ['invoice_id','!=',$request->id]
            ])->get();
            $temp = [];
            $temp['strain'] = $i->strain->strain;
            $temp['p_type'] = $i->AssetType->producttype;
            $temp['qty']    = $i->qty - $alreay_requested->sum('qty');
            $upc = UPController::where(
                [
                    ['strain',$i->strainname],
                    ['type',$i->asset_type_id]
                ])->first();
            $weight = $upc != null?$upc->weight:0;
            $temp['weight'] = number_format((float)($i->weights - $alreay_requested->sum('qty') * $weight), 2, '.', '');
            if($temp['qty'] >= 1)
                $aInvList[] = $temp;
        }

        $form['aInvList'] = $aInvList;
        //in update case
        if($invoice_id != null)
        {
            $form['invoice']         = Order::find($invoice_id);
            $form['invoice_items']   = $form['invoice']->itemAP()->get();
            foreach($form['invoice_items'] as $key => $item)
            {
                $form['invoice_items'][$key]->taxexempt = $item->Taxexempt;
            }
            $form['date']            = $form['invoice']->date;
            $form['number']          = $form['invoice']->number;
            $form['mode']            = 'update';
            $form['term']            = Term::find($form['invoice']->term_id);
            $form['shipping_method'] = $form['invoice']->shipping_method;
            $form['isNew']           = 1;
        }

        JavaScript::put([
            'strains'           => Strainame::all(),
            'p_types'           => Producttype::all(),
            'invoice_item'      => $form['invoice_items'],
            'mode'              => $form['mode'],
            'tax_allow'         => $form['invoice']->tax_allow,
            'id'                => $form['invoice']->id,
            'tax'               => OurDetail::all()->first()->tax,
            'clients'           => $form['clients'],
            'sel_client'        => $form['invoice']->customer_id,
            'promos'            => $form['promos'],
            'isNew'             => $form['isNew']
        ]);

        return view('order.form_new',$form);
    }
}
