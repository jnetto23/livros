<?php

namespace App\Infrastructure\Repository;

use App\Application\Repository\AssuntoRepositoryInterface;
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

    /** @return array<Assunto> */
    public function findAll(): array
    {
        $models = AssuntoModel::all();

        return $models->map(fn(AssuntoModel $model) => $this->toEntity($model))->toArray();
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

