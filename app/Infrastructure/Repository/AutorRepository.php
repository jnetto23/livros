<?php

namespace App\Infrastructure\Repository;

use App\Application\Usecases\Queries\ListAutoresInputDTO;
use App\Application\Repository\AutorRepositoryInterface;
use App\Domain\Entity\Autor;
use App\Domain\VOs\NomeAutor;
use App\Models\AutorModel;

final class AutorRepository implements AutorRepositoryInterface
{
    public function save(Autor $autor): Autor
    {
        $model = $autor->codau()
            ? AutorModel::find($autor->codau())
            : new AutorModel();

        $model->nome = $autor->nome()->value();
        $model->save();

        // Retorna nova instância com ID atualizado
        return $this->toEntity($model);
    }

    public function findById(int $codau): ?Autor
    {
        $model = AutorModel::find($codau);

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

/**
     * @return array{data: array<Autor>, total: int}
     */
    public function findAll(ListAutoresInputDTO $filters): array
    {
        $query = AutorModel::query();

        // Busca
        if ($filters->search !== null) {
            $query->where('nome', 'like', "%{$filters->search}%");
        }

        // Ordenação (whitelist para segurança)
        $sort = \in_array($filters->sort, $this->sortable(), true)
            ? $filters->sort
            : $this->defaultSort();

        $dir  = $filters->dir === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $dir);

        $paginator = $query->paginate(
            $filters->limit,
            ['*'],
            'page',
            $filters->page
        );

        $data = array_map(
            fn (AutorModel $m) => $this->toEntity($m),
            $paginator->items()
        );

        return [
            'data'  => $data,
            'total' => (int) $paginator->total(),
        ];
    }

    private function sortable(): array
    {
        return ['nome'];
    }

    private function defaultSort(): string
    {
        return 'nome';
    }

    public function delete(int $codau): void
    {
        AutorModel::destroy($codau);
    }

    public function findByNome(string $nome): ?Autor
    {
        $model = AutorModel::where('nome', $nome)->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    private function toEntity(AutorModel $model): Autor
    {
        $nome = NomeAutor::create($model->nome);
        return Autor::restore($model->codau, $nome);
    }
}

