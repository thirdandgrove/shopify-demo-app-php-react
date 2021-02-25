import React, {useContext} from 'react';
import {Button, Card, Stack} from '@shopify/polaris';
import ProductContext from '../contexts/productContext';
import AppContext from '../../contexts/appContext';

const ProductSaved = () => {
    const {state, dispatch, product} = useContext(ProductContext);
    const appSettings = useContext(AppContext);

    const redirectToProduct = () => {
        window.top.location.href = `https://${appSettings.shopOrigin}/admin/products/${product.legacyId}`;
    };

    const handleContinueEditing = () => {
        dispatch({
            type: 'CONTINUE_EDITING'
        });
    };

    return (
        <Card>
            <Card.Section>
                <p>Product {product.legacyId} has been updated</p>
            </Card.Section>
            <Card.Section>
                <Stack>
                    <Button
                        primary={true}
                        onClick={redirectToProduct}>
                        Return to product
                    </Button>
                    <Button onClick={handleContinueEditing}>Continue editing</Button>
                </Stack>
            </Card.Section>
        </Card>
    );
};

export default ProductSaved;
