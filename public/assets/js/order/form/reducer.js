const { combineReducers } = Redux;

const SetProductAttr = (state={avaliableQty:0,weight:0,taxexempt:0},actions) => {
    switch (actions.type) {
        case 'SETPRODUCTATTR': return state = actions.value;
        default: return state
    }
}
const SetState = (state={
    avaliableQty:0,
    weight:0,
    taxexempt:0,
    taxAllow,
    qty:1,
},actions) => {

}
const ChangeTaxAllow = (state=tax_allow,actions) => {
    switch (actions.type) {
        case 'CHANGETAXALLOW': return actions.value
        default: return state
    }
}
const changeQty = (state=1,actions) => {
    switch (actions.type) {
        case 'CHANGEQTY': return parseInt(actions.value)
        default: return state
    }
}
const ChangeItem = (state=[],actions) => {
    switch (actions.type) {
        case 'ADDITEM': state.push(actions.item)
        case 'REMOVEITEM': state.filter((element,index) => index != actions.index)
        default: return state
    }
}

export const combinedReduers = combineReducers({
    productAttr:SetProductAttr,
    taxAllow:ChangeTaxAllow,
    orderItems:ChangeItem,
    qty:changeQty
})
