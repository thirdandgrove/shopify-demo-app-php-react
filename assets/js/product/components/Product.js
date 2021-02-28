import React, {useContext} from 'react';
import {Card, Stack, Button, TextStyle, DataTable} from '@shopify/polaris';
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

    const rows = Array.from(product.variants.values()).map(variant => ([
        variant.displayName,
        variant.sku,
        variant.price.toFixed(2),
        <Variant id={variant.id}/>
    ]));

    return (
        <Card>
            <Card.Section>
                <DataTable
                    columnContentTypes={[
                        'text',
                        'text',
                        'numeric',
                        'text',
                    ]}
                    headings={[
                        'SKU',
                        'Name',
                        'Original Price',
                        'New Price'
                    ]}
                    rows={rows}
                />
            </Card.Section>
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
