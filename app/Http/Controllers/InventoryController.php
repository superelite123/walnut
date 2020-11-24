<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Config;
//Models
use App\Models\FGInventory;
use App\Models\InventoryVault;
use App\Models\ActiveInventory;
use App\Models\Harvest;
use App\Models\Producttype;
use App\Models\Strainame;
use App\Models\InventoryIgnored;
use App\Models\InventoryVSIgnored;
use App\Models\UPController;

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

    public function importPanel()
    {
        $data = [];
        $data['strains']    = Strainame::orderby('strain')->get();
        $data['p_types']    = Producttype::where('onordermenu',1)->orderby('producttype')->get();
        $data['harvests']   = Harvest::where('archived',0)->orderBy('created_at','desc')->get();
        return view('inventory.import_panel',$data);
    }
    public function importInventory(Request $request)
    {
        $file = $request->file('inventoryFile');
        if($file != null)
        {
            $path = $request->file('inventoryFile')->getRealPath();
            $csvArray = array_map('str_getcsv', file($path));
            $fgData = [];
            $vaultData = [];
            $cnt = 0;
            if(count($csvArray) >= 2)
            {
                for($i = 1; $i < count($csvArray); $i ++)
                {
                    $row = $csvArray[$i];
                    $temp = [];
                    $temp['parent_id'] = $row[0];
                    $temp['stockimage'] = $row[1];
                    $temp['strainname'] = $row[2];
                    $temp['asset_type_id'] = $row[3];
                    $temp['upc_fk'] = $row[4];
                    $temp['metrc_tag'] = $row[5];
                    $temp['batch_fk'] = $row[6];
                    $temp['coa'] = $row[7];
                    $temp['um'] = $row[8];
                    $temp['weight'] = $row[9];
                    $temp['qtyonhand'] = $row[10];
                    $temp['status'] = 14;
                    $temp['bestbefore'] = $row[12];
                    $temp['harvested_date'] = $row[13];
                    $temp['datelastmodified'] = date('Y-m-d H:i:s');
                    $temp['created_at'] = date('Y-m-d H:i:s');
                    $temp['updated_at'] = date('Y-m-d H:i:s');
                    if($row[14] == 1)
                    {
                        $model = FGInventory::updateOrInsert(
                            ['metrc_tag' => $temp['metrc_tag']],
                            $temp
                        );
                        $cnt ++;
                    }
                    if($row[14] == 2)
                    {
                        $model = InventoryVault::updateOrInsert(
                            ['metrc_tag' => $temp['metrc_tag']],
                            $temp
                        );
                        $cnt ++;
                    }
                }
            }
            return redirect('inventory/import')->with('success',$cnt.'Inventory is imported successfully!');
        }
        else
        {
            return  redirect('inventory/import')->with('warning','No Selected Files');
        }
    }

    public function bulk_import_confirm(Request $request)
    {
        $messages = [
            'required'  => 'The :attribute field is required.',
            'numeric'   => 'The :attribute field is number field',
            'gt'        => 'The :attribute field should be great than 1',
            'min'       => 'The :attribute field\'s length should be great than 5',
            'max'       => 'The :attribute field should be less than 99999',
        ];
        $validatedData = $request->validate([
            'metrc'     => 'required|min:5',
            'count'     => 'required|numeric|gt:0',
            'i_type'    => 'gt:0',
            'strain'    => 'gt:0',
            'p_type'    => 'gt:0',
            'weight'    => 'required|gt:0|max:99999',
            'harvest'   => 'gt:0',
        ],$messages);
        $nCutPoint = strlen($request->metrc) - 4;

        $metrcTag = [substr($request->metrc,0,$nCutPoint),substr($request->metrc,$nCutPoint,strlen($request->metrc))];
        $data = [];
        $data['bulk_import_data'] = [];
        for($i = 0; $i < $request->count; $i ++)
        {
            $data['bulk_import_data'][] = [
                'metrc'     => $metrcTag[0].(string)((int)$metrcTag[1] + $i),
                'strain'    => $request->strain,
                'p_type'    => $request->p_type,
                'weight'    => $request->weight,
                'harvest'   => $request->harvest,
                'i_type'   => $request->i_type,
            ];
        }
        $data['strains'] = Strainame::orderby('strain')->get();
        $data['p_types'] = Producttype::where('onordermenu',1)->orderby('producttype')->get();
        $data['harvests'] = Harvest::where('archived',0)->orderBy('created_at','desc')->get();
        $data['default_strain'] = $request->strain;
        $data['default_p_type'] = $request->p_type;
        $data['default_harvest'] = $request->harvest;
        //print_r($data['bulk_import_data']);exit;
        return view('inventory.import_bulk_confirm',$data);
    }

    public function bulk_import(Request $request)
    {
        $default_upc = UPController::where([
            ['strain' , $request->default_strain],
            ['type' , $request->default_p_type],
        ])->first();
        $default_harvest = Harvest::find($request->default_harvest);
        $insert_data = [];
        
        foreach($request->items as $item)
        {
            $temp = $item;
            unset($temp['i_type']);
            //set upc_fk
            $upc = $default_upc;
            
            if($item['strainname'] != $request->default_strain || $item['asset_type_id'] != $request->default_p_type)
            {
                $upc = UPController::where(
                    [
                        ['strain' , $item['strainname']],
                        ['type' , $item['asset_type_id']],
                    ]
                )->first();
            }

            $temp['upc_fk'] = $upc != null?$upc->id:1;
            //
            $temp['um'] = 4;
            $temp['qtyonhand'] = 1;
            $temp['status'] = 14;
            //set harvest_id
            $harvest = $default_harvest;
            if($item['parent_id'] != $request->default_harvest)
            {
                $harvest = Harvest::find($item['parent_id']);
            }
            $temp['harvested_date'] = $harvest != null?date('Y-m-d',strtotime($harvest->created_at)):date('Y-m-d');
            $temp['datelastmodified'] = date('Y-m-d H:i:s');
            $temp['created_at'] = date('Y-m-d H:i:s');
            $temp['updated_at'] = date('Y-m-d H:i:s');
            
            if($item['i_type'] == '1')
            {
                $model = FGInventory::updateOrInsert(
                    ['metrc_tag' => $temp['metrc_tag']],
                    $temp
                );
            }
            else
            {
                $model = InventoryVault::updateOrInsert(
                    ['metrc_tag' => $temp['metrc_tag']],
                    $temp
                );
            }
        }

        return redirect('inventory/import')->with('success',count($request->items).'Inventory is imported successfully!');
    }

    public function archive_imported()
    {
        $inventory = FGInventory::where('status','14')->get();
        $inventory = $inventory->merge(InventoryVault::where('status','14')->get());
        
        $sorted_data = [];
        foreach($inventory as $item)
        {
            $temp_item = $item;
            $temp_item->batch_id        = $item->HarvestBatchID;
            $temp_item->strain_name     = $item->StrainLabel;
            $temp_item->p_type          = $item->PType;
            $temp_item->i_type_label    = $item->type == 1?'Inv 2':'Inv 1';
            $temp_item->upc_label       = $item->UPCLabel;
            $temp = [
                        'id' => $item->parent_id,
                        'batch_id' => $item->HarvestBatchID,
                        'strain_label'  => $item->StrainLabel,
                        'type'          => $item->PType,
                        'harvested_date'=> $item->harvested_date,
                        'inventory' => [$temp_item]];
            $b_exist = false;
            foreach($sorted_data as $key => $sorted_item)
            {
                if($sorted_item['id'] == $item->parent_id)
                {
                    $sorted_data[$key]['inventory'][] = $item;
                    $b_exist = true;
                    break;
                }
            }
            if(!$b_exist)
            {
                $sorted_data[] = $temp;
            }
        }
        return view('inventory.archive_imported',['data' => $sorted_data]);
    }

    public function _approve_imported(Request $request)
    {
        FGInventory::where([
            ['parent_id',$request->id],
            ['status',14],
        ])->update(['status' => 1]);
        InventoryVault::where([
            ['parent_id',$request->id],
            ['status',14],
        ])->update(['status' => 1]);

        return response()->json(['success' => 1]);
    }
}
