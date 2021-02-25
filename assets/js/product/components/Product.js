import React, {useContext} from 'react';
import {Card, Stack, Button, TextStyle} from '@shopify/polaris';
import ProductContext from '../contexts/productContext';
import Variant from './Variant';
import * as stateHelper from '../helper/stateHelper';

const Product = () => {
    const {state, dispatch, product} = useContext(ProductContext);
    const changedVariants = stateHelper.getChangedVariants(state, product);

    const handleSaveChanges = () => {
        dispatch({
           type: 'SAVE_PRODUCT'
        });
    };

    return (
        <Card>
            <Card.Section>
                <Stack alignment="center" distribution="fillEvenly">
                    <TextStyle variation="strong">SKU</TextStyle>
                    <TextStyle variation="strong">Name</TextStyle>
                    <TextStyle variation="strong">Original Price</TextStyle>
                    <TextStyle variation="strong">New Price</TextStyle>
                </Stack>
            </Card.Section>

            { Array.from(product.variants.values()).map(variant => (
                <Card.Section key={variant.id}>
                    <Variant id={variant.id} />
                </Card.Section>
            ))}

            <Card.Section>
                <Stack distribution="trailing">
                    <Button
                        primary={true}
                        disabled={changedVariants.length === 0}
                        onClick={handleSaveChanges}>
                        Save changes
                    </Button>
                </Stack>
            </Card.Section>
        </Card>
    );
};

export default Product;
