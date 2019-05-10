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


namespace ScandiPWA\ReviewsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GetProductReviews
 *
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver
 */
class GetProductReviews implements ResolverInterface
{
    /**
     * Review collection
     *
     * @var ReviewCollection
     */
    protected $reviewCollection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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
     * GetProductReviews constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewFactory $reviewFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $reviewCollectionFactory
    ) {
        $this->productRepository = $productRepository;
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
        if (!isset($args['product_sku'])) {
            throw new GraphQlInputException(__('Please specify a valid product'));
        }

        $reviewData = [];

        if ($this->reviewCollection === null) {
            $this->reviewCollection = $this->reviewCollectionFactory->create()->addStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addStatusFilter(
                Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->productRepository->get($args['product_sku'])->getId()
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
