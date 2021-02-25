import {gql} from '@apollo/client';

export const gqlGetProduct = gql`  
query getProduct($id: ID!) { 
    product (id: $id) {
        id,
        legacyResourceId,
        title,
        variants (first: 50) {
            edges {
                node {
                    id,
                    sku,
                    displayName,
                    price
                }
            }
        }
    }
}
`;