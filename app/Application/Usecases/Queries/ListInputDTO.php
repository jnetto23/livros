<?php

namespace App\Application\Usecases\Queries;

abstract class ListInputDTO
{
    abstract protected function getDefaultSort(): string;

    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_DIR = 'asc';
    private const MAX_LIMIT = 100;
    private const MIN_PAGE = 1;
    private const MIN_LIMIT = 1;

    public readonly ?string $search;
    public readonly string $sort;
    public readonly string $dir;
    public readonly int $page;
    public readonly int $limit;

    public function __construct(
        ?string $search = null,
        ?string $sort = null,
        ?string $dir = null,
        ?int $page = null,
        ?int $limit = null
    ) {
        $this->search = $search !== null && $search !== '' ? trim($search) : null;

        $this->sort = $sort !== null && $sort !== ''
            ? trim($sort)
            : $this->getDefaultSort();

        if ($dir !== null) {
            $dir = strtolower(trim($dir));
            if (!in_array($dir, ['asc', 'desc'], true)) {
                throw new \InvalidArgumentException("O parâmetro 'dir' deve ser 'asc' ou 'desc'.");
            }
            $this->dir = $dir;
        } else {
            $this->dir = self::DEFAULT_DIR;
        }

        if ($page !== null) {
            if (!is_numeric($page) || $page < self::MIN_PAGE) {
                throw new \InvalidArgumentException("O parâmetro 'page' deve ser >= " . self::MIN_PAGE);
            }
            $this->page = (int) $page;
        } else {
            $this->page = self::DEFAULT_PAGE;
        }

        if ($limit !== null) {
            if (!is_numeric($limit) || $limit < self::MIN_LIMIT) {
                throw new \InvalidArgumentException("O parâmetro 'limit' deve ser >= " . self::MIN_LIMIT);
            }
            $limit = (int) $limit;
            $this->limit = min($limit, self::MAX_LIMIT);
        } else {
            $this->limit = self::DEFAULT_LIMIT;
        }
    }
}
