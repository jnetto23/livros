<?php

namespace App\Application\Usecases\Queries;

abstract class ListInputDTO
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_DIR = 'asc';
    private const MAX_LIMIT = 100;
    private const MIN_PAGE = 1;
    private const MIN_LIMIT = 1;

    public readonly ?string $search;
    public readonly ?string $sort;
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
        // Validação e normalização de search
        $this->search = $search !== null && $search !== '' ? trim($search) : null;

        // Validação e normalização de sort
        $this->sort = $sort !== null && $sort !== '' ? trim($sort) : null;

        // Validação e normalização de dir
        if ($dir !== null) {
            $dir = strtolower(trim($dir));
            if (!in_array($dir, ['asc', 'desc'], true)) {
                throw new \InvalidArgumentException("O parâmetro 'dir' deve ser 'asc' ou 'desc'.");
            }
            $this->dir = $dir;
        } else {
            $this->dir = self::DEFAULT_DIR;
        }

        // Validação e normalização de page
        if ($page !== null) {
            if (!is_numeric($page) || $page < self::MIN_PAGE) {
                throw new \InvalidArgumentException("O parâmetro 'page' deve ser um número inteiro maior ou igual a " . self::MIN_PAGE . ".");
            }
            $this->page = (int) $page;
        } else {
            $this->page = self::DEFAULT_PAGE;
        }

        // Validação e normalização de limit
        if ($limit !== null) {
            if (!is_numeric($limit) || $limit < self::MIN_LIMIT) {
                throw new \InvalidArgumentException("O parâmetro 'limit' deve ser um número inteiro maior ou igual a " . self::MIN_LIMIT . ".");
            }
            $limit = (int) $limit;
            $this->limit = min($limit, self::MAX_LIMIT); // Limita ao máximo
        } else {
            $this->limit = self::DEFAULT_LIMIT;
        }
    }
}

