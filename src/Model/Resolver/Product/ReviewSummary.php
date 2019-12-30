<?php
/**
 * ScandiPWA - Progressive Web App for Magento
 *
 * Copyright © Scandiweb, Inc. All rights reserved.
 * See LICENSE for license details.
 *
 * @license OSL-3.0 (Open Software License ('OSL') v. 3.0)
 * @package scandipwa/reviews-graphql
 * @link    https://github.com/scandipwa/reviews-graphql
 */

declare(strict_types=1);

namespace ScandiPWA\ReviewsGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class GetProductReviews
 *
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver
 */
class ReviewSummary implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $ratingSummary = $product->getRatingSummary();

        if (!$ratingSummary) {
            return [];
        }

        return [
            'rating_summary' => $ratingSummary->getRatingSummary(),
            'review_count' => $ratingSummary->getReviewsCount()
        ];
    }
}
