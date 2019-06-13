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
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Reviews
 *
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver\Product
 */
class Reviews implements ResolverInterface
{
    /**
     * Review collection
     *
     * @var ReviewCollection
     */
    protected $reviewCollection;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * Reviews constructor.
     *
     * @param ReviewFactory $reviewFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $reviewCollectionFactory
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

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
        $reviewData = [];

        if ($this->reviewCollection === null) {
            $this->reviewCollection = $this->reviewCollectionFactory->create()->addStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addStatusFilter(
                Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $product->getId()
            )->setDateOrder();
        }

        /**
         * @var ReviewCollection $reviews
         */
        $reviews = $this->reviewCollection->load()->addRateVotes();

        /**
         * @var Review $review
         */
        foreach ($reviews as $review) {
            $ratingVotes = $review->getRatingVotes()->getData();
            $reviewData[] = [
                'review_id' => $review->getReviewId(),
                'entity_id' => $review->getEntityId(),
                'entity_code' => $review->getEntityCode(),
                'entity_pk_value' => $review->getEntityPkValue(),
                'status_id' => $review->getStatusId(),
                'customer_id' => $review->getCustomerId(),
                'nickname' => $review->getNickname(),
                'title' => $review->getTitle(),
                'detail_id' => $review->getDetailId(),
                'detail' => $review->getDetail(),
                'created_at' => $review->getCreatedAt(),
                'rating_votes' => $ratingVotes
            ];
        }

        return $reviewData;
    }
}
