<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search\DataProvider;

use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\ResourceModel\Query\Collection as TermCollection;
use Nige\ElasticsuiteSearchGraphQl\Model\QueryFactory;
use Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search\SearchResultInterface;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider;

class SearchTerms implements SearchResultInterface
{
    const TYPE_CODE = 'search_query';

    /**
     * @var TermCollection
     */
    private $termCollection;
    /**
     * @var string
     */
    private $type = DataProvider::AUTOCOMPLETE_TYPE;
    /**
     * @var QueryInterface[]
     */
    private $items;
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @param QueryFactory $queryFactory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
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
            $query = $this->queryFactory->get($args['phrase']);
            $this->termCollection = $query->getSuggestCollection()
                ->addFieldToFilter('is_spellchecked', 'false')
                ->setPageSize($args['pageSize'])
                ->setCurPage($args['currentPage']);

            $this->items = $this->getItems();
        }

        return $this->items;
    }

    /**
     * @return QueryInterface[]|DataObject[]
     */
    private function getItems(): array
    {
        foreach ($this->termCollection->getItems() as $item) {
            $item->setData('type_id', self::TYPE_CODE);
        }

        return $this->termCollection->getItems();
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
        return $this->termCollection->getSize();
    }

    /**
     * {@inheritDoc}
     */
    public function getPageSize(): int
    {
        return $this->termCollection->getPageSize();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPage(): int
    {
        return $this->termCollection->getCurPage();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalPages(): int
    {
        return count($this->items)
            ? $this->termCollection->getLastPageNumber()
            : 0;
    }
}
