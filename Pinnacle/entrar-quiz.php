<?php
/**
 * Página de ponte após inserir o PIN do quiz.
 * Valida se o código do quiz existe e está ativo,
 * exibe as informações do quiz e redireciona para quiz-player.php.
 */

session_start();

if (!isset($_SESSION['id'])) {
    $_SESSION['pin_pendente'] = $codigo;
    header("Location: login.html");
    exit;
}

// Captura o PIN da sala
$codigo = isset($_GET['pin-sala']) ? trim($_GET['pin-sala']) : '';
$quiz = null;
$perguntas = [];
$erro = '';

// Valida código e busca quiz no banco
if ($codigo === '') {
    $erro = 'Informe o PIN da sala.';
} else {
    include("includes/conect.php");

    $stmt = $pdo->prepare("SELECT * FROM questionarios WHERE codigo = :codigo AND ativo = 1");
    $stmt->execute([':codigo' => $codigo]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        $erro = 'Quiz não encontrado ou inativo.';
    } else {
        $stmtPerguntas = $pdo->prepare("SELECT * FROM perguntas WHERE COD_QUIZ = :quiz_id ORDER BY COD_QUEST");
        $stmtPerguntas->execute([':quiz_id' => $quiz['COD_QUIZ']]);
        $perguntas = $stmtPerguntas->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/homepage.css">
    <title>Entrar no Quiz</title>
</head>
<body>
    <header class="header-homepage">
        <div class="header-esquerda">
            <a href="homepage.php">
                <img class="logo" src="assets/img/Pinnacle - Logo.png" alt="Logo da Pinnacle">
            </a>
        </div>
        <div class="header-direita">
            <p class="tipo">
                Tipo de Conta:
                <span id="tipo-conta">
                    <?php echo isset($_SESSION['tipo']) ? ucfirst($_SESSION['tipo']) : ''; ?>
                </span>
            </p>
        </div>
    </header>

    <main class="main-homepage">
        <div class="entrar-sala">
            <?php if ($erro): ?>
                <h1 class="entrar-sala-h">Ops!</h1>
                <p><?php echo htmlspecialchars($erro); ?></p>
                <form class="entrar-formulario" action="homepage.php">
                    <button type="submit">Voltar</button>
                </form>
            <?php else: ?>
                <h1 class="entrar-sala-h"><?php echo htmlspecialchars($quiz['NOME_QUIZ']); ?></h1>
                <p>Quiz carregado com sucesso. Total de perguntas: <?php echo count($perguntas); ?>.</p>

                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'professor'): ?>
                    <div class="codigo-sala">
                        <strong>Código da Sala:</strong>
                        <span class="codigo-destaque"><?php echo htmlspecialchars($codigo); ?></span>
                    </div>
                    <form class="entrar-formulario" action="quiz-player.php" method="GET">
                        <input type="hidden" name="pin-sala" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit">Painel do Professor</button>
                    </form>
                <?php else: ?>
                    <form class="entrar-formulario" action="quiz-player.php" method="GET">
                        <input type="hidden" name="pin-sala" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit">Entrar na Sala</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
