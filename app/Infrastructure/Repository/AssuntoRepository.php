<?php

namespace App\Infrastructure\Repository;

use App\Application\Repository\AssuntoRepositoryInterface;
use App\Application\Usecases\Queries\ListAssuntosInputDTO;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;
use App\Models\AssuntoModel;

final class AssuntoRepository implements AssuntoRepositoryInterface
{
    public function save(Assunto $assunto): Assunto
    {
        $model = $assunto->codas()
            ? AssuntoModel::find($assunto->codas())
            : new AssuntoModel();

        $model->descricao = $assunto->descricao()->value();
        $model->save();

        // Retorna nova instância com ID atualizado
        return $this->toEntity($model);
    }

    public function findById(int $codas): ?Assunto
    {
        $model = AssuntoModel::find($codas);

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    /**
     * @return array{data: array<Assunto>, total: int}
     */
    public function findAll(ListAssuntosInputDTO $filters): array
    {
        $query = AssuntoModel::query();

        // Busca
        if ($filters->search !== null) {
            $query->where('descricao', 'like', "%{$filters->search}%");
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
            fn (AssuntoModel $m) => $this->toEntity($m),
            $paginator->items()
        );

        return [
            'data'  => $data,
            'total' => (int) $paginator->total(),
        ];
    }

    private function sortable(): array
    {
        return ['descricao'];
    }

    private function defaultSort(): string
    {
        return 'descricao';
    }

    public function delete(int $codas): void
    {
        AssuntoModel::destroy($codas);
    }

    public function findByDescricao(string $descricao): ?Assunto
    {
        $model = AssuntoModel::where('descricao', $descricao)->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    private function toEntity(AssuntoModel $model): Assunto
    {
        $descricao = DescricaoAssunto::create($model->descricao);
        return Assunto::restore($model->codas, $descricao);
    }
}

