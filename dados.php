<?php

function caminho_dados(string $arquivo): string
{
    $diretorio = dirname(__DIR__) . '/data';
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0775, true);
    }
    return $diretorio . '/' . $arquivo;
}

function ler_json(string $arquivo, array $padrao = []): array
{
    $caminho = caminho_dados($arquivo);
    if (!file_exists($caminho)) {
        return $padrao;
    }
    $conteudo = json_decode((string) file_get_contents($caminho), true);
    return is_array($conteudo) ? $conteudo : $padrao;
}

function escrever_json(string $arquivo, array $dados): bool
{
    $caminho = caminho_dados($arquivo);
    $temporario = $caminho . '.tmp';
    $resultado = file_put_contents($temporario, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($resultado === false) {
        return false;
    }
    return rename($temporario, $caminho);
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
