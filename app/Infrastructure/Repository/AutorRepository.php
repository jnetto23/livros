<?php

namespace App\Infrastructure\Repository;

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

    /** @return array<Autor> */
    public function findAll(): array
    {
        $models = AutorModel::all();

        return $models->map(fn(AutorModel $model) => $this->toEntity($model))->toArray();
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

