import Big from 'big.js';

export const initialProductState = () => ({
    mode: 'INIT',
    product: null,
    variants: [],
    saveResponse: null
});

// Create a map of variant prices for editing.
export const buildVariantsState = product => {
    const variants = new Map();

    product.variants.edges.forEach(item => {
        const variant = item.node;
        variants.set(variant.id, {id: variant.id, price: new Big(variant.price)});
    });

    return variants;
};

// Has a variant price changed?
export const isVariantChanged = (variant, product) => {
    const productVariant = product.variants.get(variant.id);
    return !variant.price.eq(productVariant.price);
};

// Get an array of variants who's price has been edited and changed.
export const getChangedVariants = (state, product) => (
    Array.from(state.variants.values()).filter(variant => (
        isVariantChanged(variant, product)
    ))
);
