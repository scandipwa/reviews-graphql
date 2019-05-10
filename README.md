# ScandiPWA_ReviewsGraphQl

**ReviewsGraphQl** provides basic types and resolvers for adding and displaying customer reviews.

Module also adds a new field to `ProductInterface`:

- `review_summary` which includes information about product reviews:

    - `rating_summary`,

    - `review_count`.

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
### getProductReviews
```graphql
query GetProductReviews($product_sku: String!) {
    getProductReviews(product_sku: $product_sku) {
        review_id
        entity_id
        entity_code
        entity_pk_value
        status_id
        customer_id
        nickname
        title
        detail_id
        detail
        created_at
        rating_votes {
            vote_id
            option_id
            remote_ip
            remote_ip_long
            customer_id
            entity_pk_value
            rating_id
            review_id
            percent
            value
            rating_code
            store_id
        }
    }
}
```

```json
{
    "product_sku": "n31191497"
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
