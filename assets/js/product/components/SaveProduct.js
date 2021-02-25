import React, {useContext, useEffect} from 'react';
import {Spinner} from '@shopify/polaris';
import ProductContext from '../contexts/productContext';
import * as stateHelper from "../helper/stateHelper";
import {useMutation} from '@apollo/client';
import {gqlProductUpdate} from '../graphql/productUpdate';
import {gqlGetProduct} from '../graphql/getProduct';

const SaveProduct = () => {
    const {state, dispatch, product} = useContext(ProductContext);
    const [ProductUpdateRequest] = useMutation(gqlProductUpdate);

    const submitUpdate = async () => {

        const variantsInput = Array.from(state.variants.values()).map(variant => {
            let input = {id: variant.id};

            if (stateHelper.isVariantChanged(variant, product)) {
                input.price = variant.price.toFixed(2);
            }

            return input;
        });

        const input = {
            id: product.id,
            variants: variantsInput
        };

        const response = await ProductUpdateRequest({
            variables: { input },
            refetchQueries: [{
                query: gqlGetProduct,
                variables: { id: product.id },
            }],
        });

        dispatch({
            type: 'SET_SAVE_RESPONSE',
            response: response.data.productUpdate
        });
    };

    useEffect(() => {
        if (state.saveResponse === null) {
            submitUpdate();
        }
    },[state]);

    return (
        <Spinner accessibilityLabel="Saving..."/>
    );
};

export default SaveProduct;
