# ScandiPWA_ReviewsGraphQl

**ReviewsGraphQl** provides basic types and resolvers for adding and displaying customer reviews.

Module also adds new fields to `ProductInterface`:

-   `review_summary` which includes information about product review summary:

    -   `rating_summary`,

    -   `review_count`.
    
-   `reviews` - a list of product reviews containing following review information:

    -   `review_id`,

    -   `entity_id`,
    
    -   `entity_code`,
    
    -   `entity_pk_value`,
    
    -   `status_id`,
    
    -   `store_id`,
    
    -   `customer_id`,
    
    -   `nickname`,
    
    -   `title`,
    
    -   `detail_id`,
    
    -   `detail`,
    
    -   `created_at`,
    
    -   `rating_votes`.

### addProductReview
```graphql
mutation AddProductReview($productReviewItem: ProductReviewInput!) {
    addProductReview(productReviewItem: $productReviewItem) {
        review_id
        entity_id
        entity_pk_value
        status_id
        store_id
        customer_id
        nickname
        title
        detail
        created_at
    }
}
```

```json
{
    "productReviewItem": {
        "nickname": "John",
        "title": "Review Title",
        "detail": "Review Detail",
        "product_sku": "n31191497",
        "rating_data":[
            {
              "rating_id": 1,
              "option_id": 4
            },{
              "rating_id": 2,
              "option_id": 8
            }
        ]
    }
}
```

### getRatings
```graphql
query GetRatings {
    getRatings {
        rating_id
        rating_code
        rating_options {
            option_id
            value
        }
    }
}
```
