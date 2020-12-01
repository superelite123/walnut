<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UPController extends Model
{
    //
    protected $table = "invupccont";
    protected $primaryKey = "iteminv_id";
    public $timestamps = false;

    public function Strain()
    {
        return $this->belongsTo(Strainame::class,'strain');
    }

    public function p_type()
    {
        return $this->belongsTo(Producttype::class,'type');
    }

    public function getLabelAttribute()
    {
        $label = $this->Strain != null?$this->Strain->strain:'Removed';
        $label .= ',';
        $label .= $this->p_type != null?$this->p_type->producttype:'Removed';

        return $label;
    }
}
