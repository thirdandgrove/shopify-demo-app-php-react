import * as stateHelper from '../helper/stateHelper'
import * as utils from '../helper/utils'

// Product loaded from Shopify.
const setProduct = (state, action) => {
    state.saveResponse = null;
    state.variants = stateHelper.buildVariantsState(action.product);
    state.mode = 'EDIT';
    return utils.cloneObject(state);
};

// Price edited.
const setPrice = (state, action) => {
    const variant = state.variants.get(action.variantId);
    variant.price = action.price;
    return utils.cloneObject(state);
};

// Product attempted save.
const setSaveResponse = (state, action) => {
    state.saveResponse = action.response;
    state.mode = action.response.userErrors.length > 0
        ? 'ERROR'
        : 'PRODUCT_SAVED';

    return utils.cloneObject(state);
};

// Saved and continue to edit.
const continueEditing = (state, mode) => {
    state.mode = 'INIT';
    return utils.cloneObject(state);
};

const setMode = (state, mode) => {
    state.mode = mode;
    return utils.cloneObject(state);
};

const ProductReducer = (state, action) => {
    switch(action.type) {
        case 'SET_PRODUCT':
            return setProduct(state, action);

        case 'SET_PRICE':
            return setPrice(state, action);

        case 'SAVE_PRODUCT':
            return setMode(state, 'SAVING_PRODUCT');

        case 'SET_SAVE_RESPONSE':
            return setSaveResponse(state, action);

        case 'CONTINUE_EDITING':
            return continueEditing(state, action);

        default:
            return state;
    }
};

export default ProductReducer;
