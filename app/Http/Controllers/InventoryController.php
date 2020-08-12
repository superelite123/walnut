<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
//Models
use App\Models\FGInventory;
use App\Models\InventoryVault;
use App\Models\ActiveInventory;
use App\Models\Harvest;
use App\Models\Producttype;
use App\Models\Strainame;
use App\Models\InventoryIgnored;
use App\Models\InventoryVSIgnored;
class InventoryController extends Controller
{
    //
    public function __construct()
    {

    }

    public function combinePanel()
    {
        $data = [];
        $data['s_date'] = date('Y-m-d', strtotime('today - 31 days'));
        $data['e_date'] = Date('Y-m-d');
        $p_types = Producttype::all();
        return view('inventory.combine_panel',['data' => $data,'p_types' => $p_types]);
    }

    public function getCombines(Request $request)
    {
        $inventory = [];
        $items = ActiveInventory::whereRaw('DATE(harvested_date) >= ?', [$request->s_date])
                ->whereRaw('DATE(harvested_date) <= ?', [$request->e_date])
                ->orderBy('harvested_date')
                ->get()->take(15);
        $cnt = 1;
        foreach($items as $key => $value)
        {
            $value->no         = $cnt;
            $value->i_type     = $value->type;
            $value->h_batch    = $value->Harvest != null?$value->Harvest->harvest_batch_id:'No Harvest';
            $value->strain_lbl = $value->Strain != null?$value->Strain->strain:'No Strain';
            $value->p_type_lbl = $value->AssetType != null?$value->AssetType->producttype:'No Type';
            $value->unit_lbl   = $value->UnitLabel;
            $value->upc_lbl    = $value->UpcLabel;
            $value->coa_lbl    = $value->CoaName;

            $inventory[] = $value;
            $cnt ++;
        }
        return $inventory;
    }

    public function combineItems(Request $request)
    {
        /**
         * Create New Harvest
         * Harvest Batch ID:Date-Strainalis-combined
         */
        return DB::transaction(function () use ($request){
            $strain = Strainame::find($request->data[0]['strainname']);
            $harvest = new Harvest;
            $harvest->harvest_batch_id = Date('Y-m-d').'-'.$strain->strainalias.'-combined';
            $harvest->archived = 1;
            $harvest->save();
            $combined = new FgInventory;
            $combined->parent_id = $harvest->id;
            $combined->strainname = $strain->itemname_id;
            $combined->asset_type_id = $request->p_type;
            $combined->metrc_tag = $request->metrc;
            $combined->um=4;
            $combined->weight    = 0;
            $combined->qtyonhand = 0;
            $combined->status = 1;
            $combined->harvested_date = date('Y-m-d');
            $combined->bestbefore = date('Y-m-d', strtotime('today - 31 days'));
            $insert_data = [];
            foreach($request->data as $item)
            {
                $combined->weight += $item['weight'];
                $combined->qtyonhand += $item['qty'];
                if($item['i_type'] == 1)
                {
                    $insert_data[] = FgInventory::find($item['fgasset_id'])->toarray();
                    FgInventory::find($item['fgasset_id'])->delete();
                }
                else
                {
                    $insert_data[] = InventoryVault::find($item['fgasset_id'])->toarray();
                    InventoryVault::find($item['fgasset_id'])->delete();
                }
            }
            $combined->save();

            $relation_data = [];
            foreach($insert_data as $item)
            {   
                $temp = [];
                $temp['parent'] = $combined->fgasset_id;
                $temp['child'] = InventoryIgnored::insert($item);
                $temp['type']   = 1;
                $relation_data[] = $temp;
            }
            $combined->storeHasMany([
                'CombineLog' => $relation_data
            ]);
            return 1;
        });
    }

    public function splitPanel()
    {
        $data = [];
        $data['s_date'] = date('Y-m-d', strtotime('today - 31 days'));
        $data['e_date'] = Date('Y-m-d');
        $p_types = Producttype::all();
        return view('inventory.split_panel',['data' => $data,'p_types' => $p_types]);
    }

    public function getInventory(Request $request)
    {
        $columns = ['harvest_batch_id','strain','producttype.producttype',
                    'upc','coa','qtyonhand','weight','um','harvested_date' 
                   ];
        $bCond = ActiveInventory::whereRaw('DATE(harvested_date) >= ?', [$request->s_date])
                                ->whereRaw('DATE(harvested_date) <= ?', [$request->e_date]);
        $totalData = $bCond->count();
        $limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalFiltered = 0;
        if(empty($request->input('search.value'))){
            $totalFiltered  = $bCond->count();
            $inventory = $bCond->offset($start)->limit($limit)->get();    
        }
        else
        {
            $search = $request->input('search.value');
            $cond = $bCond->with(['Harvest','Strain','AssetType'])
                    ->whereHas('Harvest',function($query) use ($search){
                        $query->where('harvest_batch_id','like',"%{$search}%");
                    })
                    ->orWhereHas('Strain',function($query) use ($search){
                        $query->where('strain','like',"%{$search}%");
                    })
                    ->orWhereHas('AssetType',function($query) use ($search){
                        $query->where('producttype','like',"%{$search}%");
                    })
                    ->orWhere('metrc_tag','like',"%{$search}%");
            $totalFiltered  = $cond->count();
            $inventory      = $cond->offset($start)->limit($limit)->get();
        }
        $data = [];
        if($inventory){
			foreach($inventory as $i){
                $nestedData = [];
                $nestedData['hBatch']       = $i->Harvest != null?$i->Harvest->harvest_batch_id:'No Harvest';
                $nestedData['metrc_tag']    = $i->metrc_tag;
                $nestedData['strainname']   = $i->strainname;
                $nestedData['strain']       = $i->Strain->strain;
                $nestedData['pType']        = $i->AssetType->producttype;
                $nestedData['qty']          = $i->qtyonhand;
                $nestedData['weight']       = $i->weight;
                $nestedData['upc']          = $i->UPCLabel;
                $nestedData['coa']          = $i->coa;
                $nestedData['um']           = $i->unitVolume != null?$i->unitVolume->name:'No um';
                $nestedData['hDate']        = $i->harvested_date;
                $nestedData['fgasset_id']   = $i->fgasset_id;
                $nestedData['i_type']       = $i->i_type;
				$data[] = $nestedData;
			}
        }
        $json_data = array(
			"draw"			=> intval($request->input('draw')),
			"recordsTotal"	=> intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"			=> $data
		);
		
		return response()->json($json_data);
    }

    public function _checkMetrcDuplicate(Request $request)
    {
        return ActiveInventory::where('metrc_tag',$request->metrc)->first()!= null?1:0;
    }
    public function splitItem(Request $request)
    {
        $inventory = ActiveInventory::where([
                                    ['fgasset_id',$request->fgasset_id],
                                    ['i_type',$request->i_type]])->first();
        $parentId = InventoryIgnored::insert($inventory->toarray());
        foreach($request->splitData as $split)
        {
            $fg = new FGInventory;
            $fg->parent_id = $inventory->parent_id;
            $fg->strainname = $inventory->strainname;
            $fg->asset_type_id = $inventory->asset_type_id;
            $fg->metrc_tag = $split['metrc_tag'];
            $fg->um = $inventory->um;;
            $fg->weight = $split['weight'];
            $fg->coa = $inventory->coa;;
            $fg->qtyonhand = $split['qtyonhand'];
            $fg->harvested_date = $inventory->harvested_date;
            $fg->save();

            
        }
        if($request->i_type == 1)
        {
            FGInventory::find($request->fgasset_id)->delete();
        }
        else
        {
            InventoryVault::find($request->fgasset_id)->delete();
        }
        return 1;
    }
}
