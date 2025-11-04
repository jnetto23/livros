<?php

namespace App\Infrastructure\Repository;

use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\Entity\Livro;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;
use App\Models\LivroModel;
use Illuminate\Support\Facades\DB;

final class LivroRepository implements LivroRepositoryInterface
{
    public function save(Livro $livro): Livro
    {
        return DB::transaction(function () use ($livro) {
            $model = $livro->codl()
                ? LivroModel::find($livro->codl())
                : new LivroModel();

            $model->titulo = $livro->titulo()->value();
            $model->editora = $livro->editora()->value();
            $model->edicao = $livro->edicao()->value();
            $model->anopublicacao = $livro->anoPublicacao()->value();
            $model->valor = $livro->valor()->value();
            $model->save();

            // Salva relacionamentos dentro da transação
            $model->autores()->sync($livro->autoresIds());
            $model->assuntos()->sync($livro->assuntosIds());

            // Recarrega relacionamentos para garantir consistência
            $model->load(['autores', 'assuntos']);

            // Retorna nova instância com ID e relacionamentos atualizados
            return $this->toEntity($model);
        });
    }

    public function findById(int $codl): ?Livro
    {
        $model = LivroModel::with(['autores', 'assuntos'])->find($codl);

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    /** @return array<Livro> */
    public function findAll(): array
    {
        $models = LivroModel::with(['autores', 'assuntos'])->get();

        return $models->map(fn(LivroModel $model) => $this->toEntity($model))->toArray();
    }

    public function delete(int $codl): void
    {
        $model = LivroModel::find($codl);
        if ($model) {
            $model->autores()->detach();
            $model->assuntos()->detach();
            $model->delete();
        }
    }

    public function existsByAssuntoId(int $codas): bool
    {
        return LivroModel::whereHas('assuntos', function ($query) use ($codas) {
            $query->where('codas', $codas);
        })->exists();
    }

    public function existsByAutorId(int $codau): bool
    {
        return LivroModel::whereHas('autores', function ($query) use ($codau) {
            $query->where('codau', $codau);
        })->exists();
    }

    private function toEntity(LivroModel $model): Livro
    {
        $titulo = TituloLivro::create($model->titulo);
        $editora = NomeEditora::create($model->editora);
        $edicao = NumeroEdicao::create($model->edicao);
        $anoPublicacao = AnoPublicacao::create($model->anopublicacao);
        $valor = ValorLivro::create($model->valor);

        $livro = Livro::restore($model->codl, $titulo, $editora, $edicao, $anoPublicacao, $valor);

        // Define os relacionamentos
        $autoresIds = $model->autores->pluck('codau')->toArray();
        $assuntosIds = $model->assuntos->pluck('codas')->toArray();
        $livro->definirAutores($autoresIds);
        $livro->definirAssuntos($assuntosIds);

        return $livro;
    }
}

