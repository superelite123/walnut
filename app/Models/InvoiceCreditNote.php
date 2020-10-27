<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helper\HasManyRelation;

class InvoiceCreditNote extends Model
{
    use HasManyRelation;
    protected $fillable = ['invoice_id','customer_id','total_price','original_total','archive','archived_at'];
    //
    public function rItems()
    {
        return $this->hasMany(InvoiceCreditNoteItem::class,'parent_id');
    }
    public function rInvoice()
    {
        return $this->belongsTo(InvoiceNew::class,'invoice_id');
    }
}
