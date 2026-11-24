<?php
require_once __DIR__ . '/../includes/auth.php';

$usuarioAtual = usuario_logado();
if ($usuarioAtual) {
    if (($usuarioAtual['papel'] ?? '') === 'admin') {
        header('Location: ' . url_site('admin/index.php'));
    } else {
        header('Location: ' . url_site('index.php'));
    }
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $mensagem] = autenticar_usuario((string) ($_POST['email'] ?? ''), (string) ($_POST['senha'] ?? ''));
    if ($ok) {
        $usuario = usuario_logado();
        header('Location: ' . url_site(($usuario['papel'] ?? '') === 'admin' ? 'admin/index.php' : 'index.php'));
        exit;
    }
    $erro = $mensagem;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-conta">
    <main class="painel-conta">
        <a href="<?= escapar(url_site('index.php')) ?>" class="logo">VANTROX<span>.</span></a>
        <h1>ENTRAR</h1>
        <p>Acesse sua conta VANTROX.</p>

        <?php if ($erro): ?><div class="mensagem erro"><?= escapar($erro) ?></div><?php endif; ?>

        <form class="formulario-conta" method="post">
            <label>E-mail
                <input type="email" name="email" required autocomplete="email">
            </label>
            <label>Senha
                <input type="password" name="senha" required autocomplete="current-password">
            </label>
            <button type="submit">ENTRAR</button>
        </form>
        <p class="link-conta">Não tem conta? <a href="cadastro.php">Criar conta</a></p>
    </main>
</body>
</html>
