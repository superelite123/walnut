//from server
const mode              = windowvar.mode
const invoice_id        = windowvar.id
const strains           = windowvar.strains
const p_types           = windowvar.p_types
const promos            = windowvar.promos
const isNew             = windowvar.isNew

let clients             = windowvar.clients
let Invoice_item        = windowvar.invoice_item
let sel_client          = windowvar.sel_client
let tax                 = parseFloat(windowvar.tax)
let tax_allow           = windowvar.tax_allow == null?0:windowvar.tax_allow
let avaliable_qty       = 0
let taxexempt           = 0
let inserted_data       = Array()

//inputs
const Strain_list       = $("#strain")
const P_type_list       = $("#p_type")
const QtyInput          = $("#qty")
const CaledUnitInput    = $('#units')
const Unit_priceInput   = $("#unit_price")
const Sub_totalInput    = $("#sub_total")
const DiscountInput     = $("#discount")
const LessDiscountInput = $("#less_discount")
const TaxInput          = $("#tax")
const Adjust_priceInput = $("#adjust_price")

const initialState = {
    clients:windowvar.clients,
    currentClient:windowvar.sel_client,
    orderItems:windowvar.invoice_item,
    tax:parseFloat(windowvar.tax),
    taxAllow:tax_allow,
    productAttr:{
        strain:0,
        pType:0,
        avaliableQty:0,
        weight:0,
        taxExempt:0,
    },
    inputData:{
        qty:1,
        basePrice:0,
        unitPrice:0,
        subTotal:0,
        cpu:0,
        discountID:0,
        discount:0,
        eDiscount:0,
        extended:0,
        tax:0,
        note:'',
        adjustPrice:0,
    },
    orderDiscountID:0,
    rowDiscountID:0
}
