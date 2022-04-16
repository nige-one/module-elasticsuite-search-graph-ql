<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Search\Helper\Data;

class SearchResult implements ResolverInterface
{
    /**
     * @var Search\SearchResultProvider
     */
    private $searchProvider;
    /**
     * @var StringUtils
     */
    private $string;
    /**
     * @var Data
     */
    private $searchHelper;

    /**
     * @param Data $searchHelper
     * @param StringUtils $string
     * @param Search\SearchResultProvider $searchProvider
     */
    public function __construct(
        Data $searchHelper,
        StringUtils $string,
        Search\SearchResultProvider $searchProvider
    ) {
        $this->searchHelper = $searchHelper;
        $this->string = $string;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateArgs($args);
        $searchResult = $this->searchProvider->getResult($args, $info, $context);

        return [
            'total_count' => $this->searchProvider->getTotalCount(),
            'items' => $searchResult,
            'page_info' => [
                'page_size' => $this->searchProvider->getPageSize(),
                'current_page' => $this->searchProvider->getCurrentPage(),
                'total_pages' => $this->searchProvider->getTotalPages()
            ]
        ];
    }

    /**
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): void
    {
        $questTextLen = $this->string->strlen($args['phrase']);
        if ($questTextLen < $this->searchHelper->getMinQueryLength()) {
            throw new GraphQlInputException(__('Input phrase too short.'));
        }

        if ($questTextLen > $this->searchHelper->getMaxQueryLength()) {
            throw new GraphQlInputException(__('Input phrase too long.'));
        }
    }
}
