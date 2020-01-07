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

namespace ScandiPWA\ReviewsGraphQl\Model\Resolver\Products\CollectionPostProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\ResourceModel\Review\Product\Collection as ProductCollection;
use Magento\Review\Model\Review;
use ScandiPWA\Performance\Api\ProductsCollectionPostProcessorInterface;

class ReviewSummary implements ProductsCollectionPostProcessorInterface
{
    const REVIEW_SUMMARY = 'review_summary';

    /**
     * @var Review
     */
    protected $review;

    /**
     * AttributeProcessor constructor.
     * @param Review $review
     */
    public function __construct(
        Review $review
    ) {
        $this->review = $review;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function process(
        Collection $collection,
        array $attributeNames
    ): Collection {
        if (in_array(self::REVIEW_SUMMARY, $attributeNames)) {
            /** @var $collection ProductCollection */
            $this->review->appendSummary($collection);
        }

        return $collection;
    }
}
