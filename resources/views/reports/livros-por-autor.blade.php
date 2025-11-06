<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório - Livros por Autor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
            color: #000;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #666;
        }

        .autor-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .autor-nome {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f0f0f0;
            /* border-left: 4px solid #333; */
        }

        .livros-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .livros-table th {
            background-color: #333;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        .livros-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        .livros-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .livros-table tr:hover {
            background-color: #f5f5f5;
        }

        .codl {
            width: 5%;
            text-align: center;
        }

        .titulo {
            width: 30%;
        }

        .editora {
            width: 20%;
        }

        .edicao {
            width: 8%;
            text-align: center;
        }

        .ano {
            width: 10%;
            text-align: center;
        }

        .valor {
            width: 12%;
            text-align: right;
        }

        .assuntos {
            width: 15%;
            font-size: 8pt;
        }

        .valor-brl {
            font-weight: bold;
            color: #000;
        }

        .assuntos-tags {
            display: inline-block;
        }

        .assunto-tag {
            display: inline-block;
            background-color: #e0e0e0;
            padding: 2px 6px;
            margin: 2px 2px 2px 0;
            border-radius: 3px;
            font-size: 7pt;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        .sem-livros {
            font-style: italic;
            color: #999;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Livros por Autor</h1>
        <div class="subtitle">Gerado em: {{ now('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</div>
    </div>

    @foreach($autores as $autor)
        <div class="autor-section">
            <div class="autor-nome">{{ $autor['nome'] }}</div>

            @if(count($autor['livros']) > 0)
                <table class="livros-table">
                    <thead>
                        <tr>
                            <th class="codl">ID</th>
                            <th class="titulo">Título</th>
                            <th class="editora">Editora</th>
                            <th class="edicao">Edição</th>
                            <th class="ano">Ano</th>
                            <th class="valor">Valor</th>
                            <th class="assuntos">Assuntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($autor['livros'] as $livro)
                            <tr>
                                <td class="codl">{{ $livro['codl'] }}</td>
                                <td class="titulo">{{ $livro['titulo'] }}</td>
                                <td class="editora">{{ $livro['editora'] }}</td>
                                <td class="edicao">{{ $livro['edicao'] }}ª</td>
                                <td class="ano">{{ $livro['anoPublicacao'] }}</td>
                                <td class="valor">
                                    <span class="valor-brl">R$ {{ number_format($livro['valor'] / 100, 2, ',', '.') }}</span>
                                </td>
                                <td class="assuntos">
                                    @if(count($livro['assuntos']) > 0)
                                        <div class="assuntos-tags">
                                            @foreach($livro['assuntos'] as $assunto)
                                                <span class="assunto-tag">{{ $assunto }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="sem-livros">Sem assunto</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="sem-livros">Nenhum livro cadastrado para este autor.</div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Relatório gerado automaticamente pelo sistema de gestão de livros</p>
    </div>
</body>
</html>

