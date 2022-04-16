<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model\Resolver\Search;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Smile\ElasticsuiteCatalog\Helper\Autocomplete as ConfigurationHelper;

class SearchResultProvider implements SearchResultInterface
{
    /**#
     * @var ConfigurationHelper
     */
    private $configurationHelper;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var SearchResultInterface[]
     */
    private $dataProviders;
    /**
     * @var array
     */
    private $searchResult;
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var int
     */
    private $currentPage;
    /**
     * @var int
     */
    private $totalPages = 0;
    /**
     * @var int
     */
    private $totalCount = 0;

    /**
     * @param ConfigurationHelper $configurationHelper
     * @param ManagerInterface $eventManager
     * @param array $dataProviders
     */
    public function __construct(
        ConfigurationHelper $configurationHelper,
        ManagerInterface $eventManager,
        array $dataProviders = []
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->eventManager = $eventManager;
        $this->dataProviders = $dataProviders;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): array {
        if (!$this->searchResult) {
            $this->pageSize = (int) $args['pageSize'];
            $this->currentPage = (int) $args['currentPage'];

            foreach ($this->dataProviders as $dataProvider) {
                $providerType = $dataProvider->getType();
                if ($this->configurationHelper->isEnabled($providerType)) {
                    foreach ($dataProvider->getResult($args, $info, $context) as $id => $item) {
                        $searchResult[$providerType . $id] = $item->getData();
                        $searchResult[$providerType . $id]['model'] = $item;
                    }
                    $this->totalPages = max($this->totalPages, $dataProvider->getTotalPages());
                    $this->totalCount += $dataProvider->getTotalCount();
                }
            }
            $this->searchResult = $searchResult ?? [];
        }

        $this->eventManager->dispatch(
            'smile_elasticsuite_search_graph_ql_result_provider_after',
            ['provider' => $this, 'args' => $args]
        );

        return $this->searchResult;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
}
