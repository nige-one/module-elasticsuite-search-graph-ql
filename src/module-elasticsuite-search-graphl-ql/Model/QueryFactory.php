<?php declare(strict_types=1);

namespace Nige\ElasticsuiteSearchGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils as StdlibString;
use Magento\Search\Helper\Data;
use Magento\Search\Model\Query;

class QueryFactory
{
    /**
     * @var Query|null
     */
    private $query;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var StdlibString
     */
    private $string;
    /**
     * @var Data
     */
    private $queryHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param StdlibString $string
     * @param Data|null $queryHelper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StdlibString $string,
        Data $queryHelper = null
    ) {
        $this->objectManager = $objectManager;
        $this->string = $string;
        $this->queryHelper = $queryHelper ?? $this->objectManager->get(Data::class);
    }

    /**
     * @param string $rawQuery
     * @return Query
     * @throws LocalizedException
     */
    public function get(string $rawQuery): Query
    {
        if (!$this->query) {
            $maxQueryLength = $this->queryHelper->getMaxQueryLength();
            $minQueryLength = $this->queryHelper->getMinQueryLength();
            $rawQueryText = $this->getRawQueryText($rawQuery);
            $preparedQueryText = $this->getPreparedQueryText($rawQueryText, $maxQueryLength);
            $query = $this->create()->loadByQueryText($preparedQueryText);
            $query->setQueryText($preparedQueryText);
            $query->setIsQueryTextExceeded($this->isQueryTooLong($rawQueryText, $maxQueryLength));
            $query->setIsQueryTextShort($this->isQueryTooShort($rawQueryText, $minQueryLength));
            $this->query = $query;
        }

        return $this->query;
    }

    /**
     * @param array $data
     * @return Query
     */
    public function create(array $data = []): Query
    {
        return $this->objectManager->create(Query::class, $data);
    }

    /**
     * @param string $queryText
     * @return string
     */
    private function getRawQueryText(string $queryText): string
    {
        return $this->string->cleanString(trim($queryText));
    }

    /**
     * Prepare query text
     *
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return string
     */
    private function getPreparedQueryText(string $queryText, $maxQueryLength): string
    {
        if ($this->isQueryTooLong($queryText, $maxQueryLength)) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        return $queryText;
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return bool
     */
    private function isQueryTooLong(string $queryText, $maxQueryLength): bool
    {
        return ($maxQueryLength !== '' && $this->string->strlen($queryText) > $maxQueryLength);
    }

    /**
     * @param string $queryText
     * @param int|string $minQueryLength
     * @return bool
     */
    private function isQueryTooShort(string $queryText, $minQueryLength): bool
    {
        return ($this->string->strlen($queryText) < $minQueryLength);
    }
}
