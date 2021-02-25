import React, {useEffect, useContext, useReducer, useState, useMemo} from 'react';
import AppContext from '../../contexts/appContext';
import ProductContext from '../contexts/productContext';
import {Layout, Page, Spinner} from '@shopify/polaris';
import {useAppBridge} from '@shopify/app-bridge-react';
import {authenticatedFetch} from '@shopify/app-bridge-utils';
import ProductReducer from '../reducers/productReducer';
import {useApolloClient} from '@apollo/client';
import * as stateHelper from '../helper/stateHelper';
import * as productHelper from "../helper/productHelper";
import {gqlGetProduct} from '../graphql/getProduct';
import Product from './Product';
import SaveProduct from './SaveProduct';
import ProductSaved from './ProductSaved';
import ErrorSavingProduct from './ErrorSavingProduct';

const ProductRoot = () => {
    const [product, setProduct] = useState(null);
    const [state, dispatch] = useReducer(ProductReducer, stateHelper.initialProductState());
    const appSettings = useContext(AppContext);
    const authFetch = authenticatedFetch(useAppBridge());

    const urlParams = new URLSearchParams(window.location.search);
    // Legacy product id from action link on order details page.
    const legacyProductId = urlParams.get('id');
    const apolloClient = useApolloClient();

    const redirectToProduct = () => {
        window.top.location.href = `https://${appSettings.shopOrigin}/admin/products/${legacyProductId}`;
    };

    const fetchData = async () => {
        // Shopify supplies the legacy product id on the iframe query string. Get the GraphQL id from a REST query.
        let productId = null;
        if (product === null) {
            const response = await authFetch(`/api/products/${legacyProductId}/info.json?shop=${appSettings.shopOrigin}`);
            const productInfo = await response.json();
            productId = productInfo.graphQlId;
        }
        else {
            // Returning from save to continue edit.
            productId = product.id;
        }

        // Use GraphQL to get the product.
        const result = await apolloClient.query({
            query: gqlGetProduct,
            variables: {id: productId}
        });

        // Product is static data for display purposes. It is loaded here and then set in context below.
        setProduct(productHelper.buildProduct(result.data.product));

        dispatch({
            type: 'SET_PRODUCT',
            product: result.data.product
        });
    };

    useEffect(() => {
        if (state.mode === 'INIT') {
            fetchData();
        }
    }, [state]);

    const titles = new Map();
    titles.set('INIT', 'Retrieving Product');
    titles.set('EDIT', 'Edit Prices');
    titles.set('SAVING_PRODUCT', 'Saving Product');
    titles.set('PRODUCT_SAVED', 'Saved!');
    titles.set('ERROR', 'Error!');

    // Memoize to avoid creating new context value on every refresh.
    const contextValue = useMemo(() => {
        return { state, dispatch, product };
    }, [state, dispatch, product]);

    return (
        <ProductContext.Provider value={contextValue}>
            <Page
                title={titles.get(state.mode)}
                breadcrumbs={state.mode !== 'INIT' ? [{content: product.title, onAction: redirectToProduct}] : []}>
                <Layout>
                    <Layout.Section>
                        { state.mode === 'INIT' &&
                            <Spinner accessibilityLabel="Loading..."/>
                        }
                        { state.mode === 'EDIT' &&
                            <Product/>
                        }
                        { state.mode === 'SAVING_PRODUCT' &&
                            <SaveProduct />
                        }
                        { state.mode === 'PRODUCT_SAVED' &&
                            <ProductSaved/>
                        }
                        { state.mode === 'ERROR' &&
                            <ErrorSavingProduct/>
                        }
                    </Layout.Section>
                </Layout>
            </Page>
        </ProductContext.Provider>
    );
};

export default ProductRoot;
