<?php

namespace ScandiPWA\ReviewsGraphQl\Model\Resolver\Product\DataPostProcessor;

use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use ScandiPWA\Performance\Api\ProductsDataPostProcessorInterface;
use ScandiPWA\Performance\Model\Resolver\ResolveInfoFieldsTrait;


/**
 * Class ReviewSummary
 * @package ScandiPWA\ReviewsGraphQl\Model\Resolver\Product\DataPostProcessor
 */
class ReviewSummaries implements ProductsDataPostProcessorInterface
{
    use ResolveInfoFieldsTrait;

    const REVIEW_SUMMARY = 'review_summary';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;


    /**
     * Products constructor.
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    protected function getFieldContent($node)
    {

        $reviewSummaries = [];
        foreach ($node->selectionSet->selections as $selection) {
            if (!isset($selection->name)) {
                continue;
            };

            $name = $selection->name->value;

            if ($name === self::REVIEW_SUMMARY) {
                $reviewSummaries[] = $name;
            }
        }

        return $reviewSummaries;
    }


    private $reviewSummaries;

    function process(
        array $products,
        string $graphqlResolvePath,
        $graphqlResolveInfo,
        ?array $processorOptions = []
    ): callable
    {


        $fields = $this->getFieldsFromProductInfo(
            $graphqlResolveInfo,
            $graphqlResolvePath
        );

        if (!count($fields)) {
            return function (&$productData) {
            };
        }

        return function (&$productData) {
            $storeId = $this->storeManager->getStore()->getId();
            $reviewSummaries = $this->collectionFactory->create()
                ->addStoreFilter($storeId)
                ->getItems();

                $productId = $productData['entity_id'];

                foreach ($reviewSummaries as $summary) {
                    if ($productId === $summary->getData()['entity_pk_value']) {
                        $ratingSummary = $summary->getRatingSummary();
                        $reviewsCount = $summary->getReviewsCount();

                        $productData['review_summary'] = [
                            'rating_summary' => $ratingSummary,
                            'review_count' => $reviewsCount
                        ];
                    }
                    else {
                        $productData['review_summary'] = [];
                    }

                }
        };
    }
}
