<?php

require_once __DIR__ . '/dados.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function url_site(string $caminho): string
{
    $diretorio = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    if (in_array(basename($diretorio), ['conta', 'admin'], true)) {
        $diretorio = dirname($diretorio);
    }

    $base = rtrim($diretorio, '/');
    return ($base === '' || $base === '.' ? '' : $base) . '/' . ltrim($caminho, '/');
}

function cadastrar_usuario(array $entrada): array
{
    $nome = trim((string) ($entrada['nome'] ?? ''));
    $email = strtolower(trim((string) ($entrada['email'] ?? '')));
    $senha = (string) ($entrada['senha'] ?? '');

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Informe um nome e um e-mail válido.'];
    }
    if (strlen($senha) < 6) {
        return [false, 'A senha deve ter pelo menos 6 caracteres.'];
    }

    $usuarios = ler_json('usuarios.json');
    foreach ($usuarios as $usuario) {
        if (($usuario['email'] ?? '') === $email) {
            return [false, 'Este e-mail já está cadastrado.'];
        }
    }

    $novo = [
        'id' => bin2hex(random_bytes(8)),
        'nome' => $nome,
        'email' => $email,
        'senha' => password_hash($senha, PASSWORD_DEFAULT),
        'papel' => 'cliente',
        'criado_em' => date('Y-m-d H:i:s'),
    ];
    $usuarios[] = $novo;

    if (!escrever_json('usuarios.json', $usuarios)) {
        return [false, 'Não foi possível salvar o cadastro.'];
    }

    unset($novo['senha']);
    $_SESSION['usuario'] = $novo;
    session_regenerate_id(true);
    return [true, 'Cadastro realizado com sucesso!'];
}

function autenticar_usuario(string $email, string $senha): array
{
    $email = strtolower(trim($email));
    foreach (ler_json('usuarios.json') as $usuario) {
        if (($usuario['email'] ?? '') === $email && password_verify($senha, (string) ($usuario['senha'] ?? ''))) {
            unset($usuario['senha']);
            $_SESSION['usuario'] = $usuario;
            session_regenerate_id(true);
            return [true, 'Login realizado com sucesso!'];
        }
    }
    return [false, 'E-mail ou senha incorretos.'];
}

function usuario_logado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigir_login(): void
{
    if (!usuario_logado()) {
        header('Location: ' . url_site('conta/login.php'));
        exit;
    }
}

function exigir_admin(): void
{
    $usuario = usuario_logado();
    if (!$usuario || ($usuario['papel'] ?? '') !== 'admin') {
        header('Location: ' . url_site('conta/login.php'));
        exit;
    }
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
    }
    session_destroy();
}
