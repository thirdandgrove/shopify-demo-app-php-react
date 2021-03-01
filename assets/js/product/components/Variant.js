import React, {useContext, useState} from 'react';
import {TextField} from '@shopify/polaris';
import ProductContext from '../contexts/productContext';
import Big from 'big.js';

const Variant = props => {
    const {state, dispatch} = useContext(ProductContext);
    const stateVariant = state.variants.get(props.id);
    // Use state for the value so that keystrokes are immediately displayed while refreshing only this component.
    const [value, setValue] = useState(stateVariant.price.toFixed(2));

    const handleOnBlur = e => {
        if (isNaN(e.target.value)) {
            return;
        }

        const value = new Big(e.target.value);

        dispatch({
            type: 'SET_PRICE',
            variantId: props.id,
            price: value
        });

        setValue(value.toFixed(2));
    };

    return (
        <TextField
            label="Price"
            labelHidden={true}
            type="text"
            align="right"
            value={value}
            onChange={setValue}
            onBlur={handleOnBlur}
            error={isNaN(value) ? 'Not a valid number' : ''}
        />
    );
};

export default Variant;
