<?php namespace Zephyrus\Database\QueryBuilder;

use Zephyrus\Utilities\Components\Pagination;

class LimitClause
{
    private int $limit = Pagination::DEFAULT_LIMIT;
    private int $offset = 0;

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    public function getSql(): string
    {
        $sql = "LIMIT $this->limit";
        if ($this->offset != 0) {
            $sql .= " OFFSET $this->offset";
        }
        return $sql;
    }
}
