import Big from 'big.js';

// Extract data from the GraphQL response into a more convenient form.
export const buildProduct = product => {
    const variants = new Map();

    product.variants.edges.forEach(item => {
        const variant = item.node;
        variants.set(variant.id, {
            id: variant.id,
            sku: variant.displayName,
            displayName: variant.displayName,
            price: new Big(variant.price)
        });
    });

    return {
        id: product.id,
        legacyId: product.legacyResourceId,
        title: product.title,
        variants
    };
};
