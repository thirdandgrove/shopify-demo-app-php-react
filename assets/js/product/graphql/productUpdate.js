import {gql} from '@apollo/client';

export const gqlProductUpdate = gql`

mutation productUpdate($input: ProductInput!) {
  productUpdate(input: $input) {
    product {
      id
    }
    userErrors {
      field
      message
    }
  }    
}
`;
