<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCreditNoteItem extends Model
{
    protected $fillable = ['parent_id','strain','p_type','price','qty'];
    public  $timestamps = false;
}
