<?php
/**
 * ScandiPWA - Progressive Web App for Magento
 *
 * Copyright © Scandiweb, Inc. All rights reserved.
 * See LICENSE for license details.
 *
 * @license OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * @package scandipwa/reviews-graphql
 * @link    https://github.com/scandipwa/reviews-graphql
 */

declare(strict_types=1);


namespace ScandiPWA\ReviewsGraphQl\Model\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Exception;

/**
 * Class AddProductReview
 *
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver
 */
class AddProductReview implements ResolverInterface
{
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
     * @var RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * AddProductReview constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewFactory $reviewFactory
     * @param StoreManagerInterface $storeManager
     * @param RatingFactory $ratingFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        RatingFactory $ratingFactory,
        CustomerSession $customerSession
    ) {
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->ratingFactory = $ratingFactory;
        $this->customerSession = $customerSession;
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
        if (!isset($args['productReviewItem'])) {
            throw new GraphQlInputException(__('Review data is not valid'));
        }

        $productReviewItem = $args['productReviewItem'];
        $customerId = $context->getUserId();

        if ($customerId === 0) {
            $customerId = null;
        } else {
            $this->customerSession->setCustomerId($customerId);
        }

        $productId = $this->productRepository->get($productReviewItem['product_sku'])->getId();
        $storeId = $this->storeManager->getStore()->getId();
        $reviewData = [
            'nickname' => $productReviewItem['nickname'],
            'title' => $productReviewItem['title'],
            'detail' => $productReviewItem['detail']
        ];

        try {
            /** @var Review $review */
            $review = $this->reviewFactory->create()->setData($reviewData);
            $review->unsetData('review_id');
            $validate = $review->validate();

            if ($validate === true) {
                $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                    ->setEntityPkValue($productId)
                    ->setStatusId(Review::STATUS_PENDING)
                    ->setCustomerId($customerId)
                    ->setStoreId($storeId)
                    ->setStores([$storeId])
                    ->save();

                if (isset($productReviewItem['rating_data'])) {
                    foreach ($productReviewItem['rating_data'] as $rating) {
                        $this->ratingFactory->create()
                            ->setRatingId($rating['rating_id'])
                            ->setReviewId($review->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($rating['option_id'], $productId);
                    }
                }

                $review->aggregate();
            }
        } catch (Exception $e) {
            throw new GraphQlNoSuchEntityException(__('We cannot post your review right now.'));
        }

        return $review->getData();
    }
}
