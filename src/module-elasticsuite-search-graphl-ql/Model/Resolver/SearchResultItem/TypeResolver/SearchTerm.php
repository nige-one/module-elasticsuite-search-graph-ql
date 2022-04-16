<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver\SearchResultItem\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search\DataProvider\SearchTerms;

class SearchTerm implements TypeResolverInterface
{
    /**
     * Term type resolver code
     */
    const TYPE_RESOLVER = 'SearchTerm';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        if (isset($data['type_id']) && $data['type_id'] === SearchTerms::TYPE_CODE) {
            return self::TYPE_RESOLVER;
        }
        return '';
    }
}
