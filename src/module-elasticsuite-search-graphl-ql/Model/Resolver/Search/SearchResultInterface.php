<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

interface SearchResultInterface
{
    /**
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return array
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): array;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return int
     */
    public function getTotalCount(): int;

    /**
     * @return int
     */
    public function getPageSize(): int;

    /**
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * @return int
     */
    public function getTotalPages(): int;
}
