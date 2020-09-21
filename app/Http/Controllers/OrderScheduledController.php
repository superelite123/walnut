<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InvoiceNew;
use JavaScript;
class OrderScheduledController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        //load scheduled orders but not delivered
        $orders = InvoiceNew::where([
            ['delivery_time','!=',null],
        ])->get();
        $cData = [];
        foreach($orders as $order)
        {
            $item = [];
            $item['id']     = $order->id;
            $item['number'] = $order->number2;
            $item['dDate']  = date('m/d/Y',strtotime($order->delivery_time));
            $item['deliveryer'] = $order->rDevlieryer != null?$order->rDevlieryer->username:'';
            $item['time'] = date('h:i a',strtotime($order->delivery_time));
            $item['cName']  = $order->cName;
            $item['amount'] = $order->total_info['adjust_price'];
            $item['title1'] = 'Invoice: '.$item['number'];
            $item['title2'] = 'Store: '.$item['cName'];
            $item['title3'] = 'Total: $'.$item['amount'];
            $item['title4'] = 'Time: '.$item['time'];
            $item['isDelivered'] = $order->status == 4?1:0;
            if($item['isDelivered'] == 0)
            {
                $item['backgroundColor'] = '#d73925';
            }
            else
            {
                $item['backgroundColor'] = 'MediumSeaGreen';
            }
            $item['borderColor'] = $item['backgroundColor'];
            $item['start'] = $item['dDate'];
            $cData[] = $item;
        }
        return view('orderFulfilled.scheduled',[ 'cData' => $cData]);
    }

    public function changeDate(Request $request)
    {
        $date = date("Y-m-d H:i:s",strtotime($request->date));
        $invoice = InvoiceNew::find($request->id);
        $invoice->delivery_time = $date;
        $invoice->save();
        return response()->json(['success' => 1]);
    }
}
