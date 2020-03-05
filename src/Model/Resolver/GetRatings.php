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
use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option as RatingOption;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;

/**
 * Class GetRatings
 *
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver
 */
class GetRatings implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RatingCollectionFactory
     */
    protected $ratingCollectionFactory;

    /**
     * GetRatings constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param RatingCollectionFactory $ratingCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RatingCollectionFactory $ratingCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
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
        $ratingData = [];
        $currentStoreId = $this->storeManager->getStore()->getId();
        $ratingCollection = $this->ratingCollectionFactory->create();
        $ratings = $ratingCollection
            ->setActiveFilter()
            ->setPositionOrder()
            ->addRatingPerStoreName($currentStoreId)
            ->addOptionToItems()
            ->getItems();

        /**
         * @var Rating $rating
         */
        foreach ($ratings as $rating) {
            $ratingOptions = $rating->getOptions();
            $ratingOptionData = [];

            /**
             * @var RatingOption $ratingOption
             */
            foreach ($ratingOptions as $ratingOption) {
                $ratingOptionData[] = [
                    'option_id' => $ratingOption->getId(),
                    'value' => $ratingOption->getValue()
                ];
            }

            $ratingData[] = [
                'rating_id' => $rating->getId(),
                'rating_code' => $rating->getRatingCode(),
                'rating_options' => $ratingOptionData
            ];
        }

        return $ratingData;
    }
}
