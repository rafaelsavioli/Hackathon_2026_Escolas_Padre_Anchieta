<?php
/**
 * Página principal (dashboard) exibida após o login.
 * Mostra "Criar Quiz" para professores, "Entrar na Sala" para todos
 * e a lista de quizzes existentes para professores.
 */

session_start();

// Redireciona para login se não estiver logado
if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

// Busca quizzes do professor logado
$quizzesCriados = [];

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'professor') {
    require_once "includes/conect.php";

    $stmt = $pdo->prepare("
        SELECT COD_QUIZ, NOME_QUIZ, codigo
        FROM questionarios
        WHERE professor_id = :professor_id
        ORDER BY created_at DESC, COD_QUIZ DESC
    ");
    $stmt->execute([':professor_id' => $_SESSION['id']]);
    $quizzesCriados = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/homepage.css">
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Pinnacle - Homepage</title>
</head>

<body>
    <header class="header-homepage">
        <div class="header-esquerda">
            <img class="logo" src="assets/img/Pinnacle - Logo.png" alt="Logo da Pinnacle">
        </div>
        <div class="header-direita">
            <p class="tipo">
                Tipo de Conta:
                <span id="tipo-conta">
                    <?php echo isset($_SESSION['tipo']) ? ucfirst($_SESSION['tipo']) : ''; ?>
                </span>
            </p>
            <div class="perfil">
                <button class="perfil-botao" id="perfil-botao" type="button" title="Abrir perfil">
                    <i class="bi bi-person-fill"></i>
                </button>
                <div class="perfil-dropdown" id="perfil-dropdown">
                    <a href="auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-homepage">
        <section class="actions-row">

        <?php if(isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'professor'): ?>
        <div class="criar-quiz action-card">
            <div class="card-icon">
                <img src="assets/img/Icon - Manutenção do Aprendizado.png" alt="">
            </div>
            <h1 class="criar-quiz-h">Criar Quiz</h1>
            <p>Crie quizzes de forma simples, rápida e intuitiva. Personalize perguntas, defina respostas corretas,
                ajuste pontuação e tempo de cada desafio para tornar o aprendizado mais dinâmico, interativo e
                divertido.</p>
            <form class="criar-formulario" action="cadastro-pergunta.php">
                <button class="criar-botao" type="submit">Criar</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="entrar-sala action-card">
            <div class="card-icon">
                <img src="assets/img/Icon - Sistema Lúdico e Interativo.png" alt="">
            </div>
            <h1 class="entrar-sala-h">Entrar na sala</h1>
            <p>Participe de quizzes interativos em tempo real, desafie seus conhecimentos e aprenda de forma divertida.
                Entre em salas rapidamente, acompanhe sua pontuação e dispute posições no ranking enquanto responde
                perguntas dinâmicas e envolventes.</p>
            <form class="entrar-formulario" action="entrar-quiz.php" method="GET">
                <label for="pin-sala">PIN da Sala</label>
                <input type="text" id="pin-sala" name="pin-sala" maxlength="6" autocomplete="off">
                <button type="submit">Entrar</button>
            </form>
        </div>
        </section>

        <?php if(isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'professor'): ?>
        <section class="quizzes-criados">
            <h2>Quizzes criados</h2>

            <div class="quizzes-scroll">
                <?php if(count($quizzesCriados) > 0): ?>
                    <?php foreach($quizzesCriados as $quiz): ?>
                        <article class="quiz-card">
                            <a class="quiz-thumb" href="cadastro-pergunta.php?quiz_id=<?php echo (int) $quiz['COD_QUIZ']; ?>" title="Editar quiz">
                                <img src="assets/img/quiz-icon.png" alt="">
                            </a>
                            <h3><?php echo htmlspecialchars($quiz['NOME_QUIZ'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <span class="quiz-code"><?php echo htmlspecialchars($quiz['codigo'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a class="quiz-start-btn" href="quiz-player.php?pin-sala=<?php echo urlencode($quiz['codigo']); ?>" title="Iniciar Quiz">
                                <i class="bi bi-play-fill"></i> Iniciar
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="quiz-card quiz-empty">
                        <a class="quiz-thumb" href="cadastro-pergunta.php" title="Criar quiz">
                            <img src="assets/img/quiz-icon.png" alt="">
                        </a>
                        <h3>Nenhum quiz ainda</h3>
                        <span>Crie o primeiro</span>
                    </article>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>
    <script>
        const perfilBotao = document.getElementById("perfil-botao");
        const perfilDropdown = document.getElementById("perfil-dropdown");

        if (perfilBotao && perfilDropdown) {
            perfilBotao.addEventListener("click", () => {
                perfilDropdown.classList.toggle("active");
            });

            document.addEventListener("click", (event) => {
                if (!perfilBotao.contains(event.target) && !perfilDropdown.contains(event.target)) {
                    perfilDropdown.classList.remove("active");
                }
            });
        }
    </script>
</body>

</html>
