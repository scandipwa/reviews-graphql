<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ScandiPWA\ReviewsGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Review\Model\ResourceModel\Review\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;

/**
 * Adds passed in attributes to product collection results
 *
 * {@inheritdoc}
 */
class ReviewProcessor implements CollectionProcessorInterface
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
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        foreach ($attributeNames as $name) {
            if ($name !== self::REVIEW_SUMMARY) {
                continue;
            }

            /** @var $collection ProductCollection */
            $this->review->appendSummary($collection);
        }

        return $collection;
    }
}
