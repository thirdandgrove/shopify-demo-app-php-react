import React, {useContext} from 'react';
import ProductContext from '../contexts/productContext';
import {Banner, List} from '@shopify/polaris';

const ErrorSavingProduct = () => {
    const {state} = useContext(ProductContext);

    return (
        <Banner
            title="An error occurred while updating the product"
            status="critical"
        >
            <p>Unfortunately an error occurred while updating the product. Shopify returned the following information.</p>
            <List type="bullet">
                {state.saveResponse.userErrors.map((error, index) => (
                    <List.Item key={index}>{error.message}</List.Item>
                ))}
            </List>
        </Banner>
    );
};

export default ErrorSavingProduct;
