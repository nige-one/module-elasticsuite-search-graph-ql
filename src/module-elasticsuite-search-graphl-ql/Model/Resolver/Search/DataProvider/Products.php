<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\SearchEngine;
use Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search\SearchResultInterface;
use Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\Collection\Provider as CollectionProvider;
use Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\DataProvider;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection as ProductCollection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Smile\ElasticsuiteCore\Search\Request\Builder;
use Smile\ElasticsuiteCore\Search\RequestInterface;

class Products implements SearchResultInterface
{
    /**
     * @var string
     */
    private $type = DataProvider::AUTOCOMPLETE_TYPE;

    /**
     * @var SearchEngine
     */
    private $searchEngine;
    /**
     * @var Builder
     */
    private $requestBuilder;
    /**
     * @var ProductSearch
     */
    private $productSearch;
    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;
    /**
     * @var SearchResponseBuilder
     */
    private $resultBuilder;
    /**
     * @var FieldSelection
     */
    private $fieldSelection;
    /**
     * @var string
     */
    private $searchRequestName;
    /**
     * @var SearchResultsInterface
     */
    private $searchResults;
    /**
     * @var ExtensibleDataInterface[]
     */
    private $items;

    /**
     * @param SearchEngine $searchEngine
     * @param Builder $requestBuilder
     * @param ProductSearch $productSearch
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param SearchResponseBuilder $resultBuilder
     * @param FieldSelection $fieldSelection
     * @param string $searchRequestName
     */
    public function __construct(
        SearchEngine $searchEngine,
        Builder $requestBuilder,
        ProductSearch $productSearch,
        SearchCriteriaBuilder $criteriaBuilder,
        SearchResponseBuilder $resultBuilder,
        FieldSelection $fieldSelection,
        string $searchRequestName = 'quick_search_container'
    ) {
        $this->searchEngine = $searchEngine;
        $this->requestBuilder = $requestBuilder;
        $this->productSearch = $productSearch;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->resultBuilder = $resultBuilder;
        $this->fieldSelection = $fieldSelection;
        $this->searchRequestName = $searchRequestName;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): array {
        if (!$this->items) {
            $searchResponse = $this->searchEngine->search(
                $this->createRequest(
                    (int) $context->getExtensionAttributes()->getStore()->getId(),
                    $args['phrase']
                )
            );
            $this->searchResults = $this->productSearch->getList(
                $this->criteriaBuilder->build($args, false),
                $this->resultBuilder->build($searchResponse),
                $this->fieldSelection->getProductsFieldSelection($info),
                $context
            );
            $this->items = $this->getItems();
        }

        return $this->items;
    }

    /**
     * @param int $storeId
     * @param string $searchPhrase
     * @return RequestInterface
     */
    private function createRequest(int $storeId, string $searchPhrase): RequestInterface
    {
        return $this->requestBuilder->create(
            $storeId,
            $this->searchRequestName,
            0,
            9999,
            $searchPhrase
        );
    }

    /**
     * @return ProductInterface[]
     */
    private function getItems(): array
    {
        $items = [];
        /** @var ProductInterface $item */
        foreach ($this->searchResults->getItems() as $item) {
            $items[$item->getId()] = $item;
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCount(): int
    {
        return $this->searchResults->getTotalCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getPageSize(): int
    {
        return $this->searchResults->getSearchCriteria()->getPageSize();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPage(): int
    {
        return $this->searchResults->getSearchCriteria()->getCurrentPage();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalCount() / $this->getPageSize());
    }
}
