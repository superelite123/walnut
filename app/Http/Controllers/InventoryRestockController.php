<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Models
use App\Models\FGInventory;
use App\Models\InventoryVault;
use App\Models\InventoryRestockLog;
class InventoryRestockController extends Controller
{
    //
    public function index()
    {
        $inventory = FGInventory::where('status',9)->get();
        $inventory = $inventory->merge(InventoryVault::where('status',9)->get());
        return view('InventoryRestock.index',['inventory' => $inventory]);
    }
    public function getList()
    {
        $inventory = FGInventory::where('status',9)->get();
        $inventory = $inventory->merge(InventoryVault::where('status',9)->get());

        $response = [];
        foreach($inventory as $item)
        {
            $tmp = [];
            $tmp['id'] = $item->fgasset_id;
            $tmp['metrc_tag'] = $item->metrc_tag;
            $tmp['i_type'] = $item->type;
            $tmp['strain'] = $item->Strain->strain;
            $tmp['pass1'] = 0;
            $tmp['pass2'] = 0;
            $tmp['type'] = $item->AssetType->producttype;
            $tmp['orderLabel'] = '';
            $tmp['retailer'] = '';
            if($item->rRestockLog != null)
            {
                $invoice = $item->rRestockLog->rOrder;
                if($invoice != null)
                {
                    $tmp['orderLabel']  = $invoice->number;
                    $tmp['retailer']    = $invoice->customer != null?$invoice->customer->clientname:'No Retailer Name';
                }
            }

            $response[] = $tmp;
        }

        return response()->json($response);
    }
    public function approve(Request $request)
    {
        if($request->type == '1')
        {
            $inventory = FGInventory::find($request->id);
        }
        else
        {
            $inventory = InventoryVault::find($request->id);
        }

        if($inventory == null)
        {
            return -1;
        }
        else
        {
            $inventory->status = 1;
            $inventory->save();
            //remove Log
            $inventory->rRestockLog()->delete();
            return 1;
        }
    }
}
