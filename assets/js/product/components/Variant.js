import React, {useContext, useState} from 'react';
import {Stack, TextField} from '@shopify/polaris';
import ProductContext from '../contexts/productContext';
import Big from 'big.js';

const Variant = props => {
    const {state, dispatch, product} = useContext(ProductContext);
    const stateVariant = state.variants.get(props.id);
    // Use state for the value so that keystrokes are immediately displayed while refreshing only this component.
    const [value, setValue] = useState(stateVariant.price.toFixed(2));
    const productVariant = product.variants.get(props.id);

    const handleOnBlur = e => {
        const stringValue = !isNaN(e.target.value) ? e.target.value : '0.00';
        const value = new Big(stringValue);

        dispatch({
            type: 'SET_PRICE',
            variantId: props.id,
            price: value
        });

        setValue(value.toFixed(2));
    };

    return (
        <Stack alignment="center" distribution="fillEvenly">
            <div>{productVariant.sku}</div>
            <div>{productVariant.displayName}</div>
            <div>{productVariant.price.toFixed(2)}</div>
            <TextField
                label="Price" labelHidden={true}
                align="right"
                value={value}
                onChange={setValue}
                onBlur={handleOnBlur}
            />
         </Stack>
    );
};

export default Variant;
