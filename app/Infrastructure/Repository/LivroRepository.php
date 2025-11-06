<?php

namespace App\Infrastructure\Repository;

use App\Application\UseCases\Queries\ListLivrosInputDTO;
use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\Entity\Livro;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;
use App\Models\LivroModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * @return array{data: array<Livro>, total: int}
     */
    public function findAll(ListLivrosInputDTO $filters): array
    {
        $query = LivroModel::query()
            ->with(['autores', 'assuntos']);

        if ($filters->search !== null) {
            $term = $filters->search;
            $query->where(function (Builder $q) use ($term) {
                $q->where('livro.titulo', 'like', "%{$term}%")
                ->orWhere('livro.editora', 'like', "%{$term}%")
                ->orWhereHas('autores', fn (Builder $qa) =>
                    $qa->where('autor.nome', 'like', "%{$term}%")
                )
                ->orWhereHas('assuntos', fn (Builder $qs) =>
                    $qs->where('assunto.descricao', 'like', "%{$term}%")
                );
            });

            // Evita duplicatas quando várias relações casam
            $query->distinct('livro.codl');
        }

        // Ordenação segura
        $dir = $filters->dir === 'desc' ? 'desc' : 'asc';
        $sortKey = $filters->sort ?? 'titulo';

        // Mapeamento de colunas básicas
        $baseCols = [
            'codl'          => 'livro.codl',
            'titulo'        => 'livro.titulo',
            'editora'       => 'livro.editora',
            'anopublicacao' => 'livro.anopublicacao',
            'valor'         => 'livro.valor',
        ];

        if ($sortKey === 'autor') {
            // ✅ só aqui adiciona o agregado e usa o alias
            $query->withMin('autores', 'nome'); // cria autores_min_nome
            $query->orderBy('autores_min_nome', $dir);
        } elseif ($sortKey === 'assunto') {
            $query->withMin('assuntos', 'descricao'); // cria assuntos_min_descricao
            $query->orderBy('assuntos_min_descricao', $dir);
        } else {
            $col = $baseCols[$sortKey] ?? 'livro.titulo';
            $query->orderBy($col, $dir);
        }

        // Paginação
        $paginator = $query->paginate(
            $filters->limit,
            ['*'],
            'page',
            $filters->page
        );

        $data = array_map(
            fn (LivroModel $m) => $this->toEntity($m),
            $paginator->items()
        );

        return [
            'data'  => $data,
            'total' => (int) $paginator->total(),
        ];
    }


    private function resolveSort(?string $sort, string $dir): array
    {
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        $map = [
            'codl'          => 'livro.codl',
            'titulo'        => 'livro.titulo',
            'editora'       => 'livro.editora',
            'anopublicacao' => 'livro.anopublicacao',
            'valor'         => 'livro.valor',
            'autor'         => 'autores_min_nome',
            'assunto'       => 'assuntos_min_descricao',
        ];

        $default = 'livro.titulo';

        $col = $map[$sort ?? ''] ?? $default;

        return [$col, $dir];
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

