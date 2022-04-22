<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

class SearchResultItemTypeResolverComposite implements TypeResolverInterface
{
    /**
     * TypeResolverInterface[]
     */
    private $typeNameResolvers;

    /**
     * @param TypeResolverInterface[] $typeNameResolvers
     */
    public function __construct(array $typeNameResolvers = [])
    {
        $this->typeNameResolvers = $typeNameResolvers;
    }

    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data): string
    {
        foreach ($this->typeNameResolvers as $typeNameResolver) {
            if (!isset($data['type_id'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in product data', ['type_id'])
                );
            }
            try {
                $resolvedType = $typeNameResolver->resolveType($data);
                if ($resolvedType) {
                    return $resolvedType;
                }
            } catch (\Exception $e) {
                throw new GraphQlInputException(__($e->getMessage()), $e);
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['SearchResultItem'])
        );
    }
}
