<?php
session_start();

// Redireciona para login se não estiver logado
if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/homepage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Pinnacle - Homepage</title>
</head>

<body>
    <header class="header-homepage">
        <div class="header-esquerda">
            <img class="logo" src="imagens/logoBranca.png" alt="Logo da Pinnacle">
        </div>
        <div class="header-direita">
            <p class="tipo">
                Tipo de Conta:
                <span id="tipo-conta">
                    <?php echo isset($_SESSION['tipo']) ? ucfirst($_SESSION['tipo']) : ''; ?>
                </span>
            </p>
            <form class="perfil" action="perfil-logout">
                <button class="perfil-botao">
                    <img class="logoPerfil" src="imagens/logoPerfil.png" alt="Foto de perfil">
                </button>
            </form>
        </div>
    </header>
    
    <main class="main-homepage">

        <?php if(isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'professor'): ?>
        <div class="criar-quiz">
            <h1 class="criar-quiz-h">Criar Quiz</h1>
            <p>Crie quizzes de forma simples, rápida e intuitiva. Personalize perguntas, defina respostas corretas,
                ajuste pontuação e tempo de cada desafio para tornar o aprendizado mais dinâmico, interativo e
                divertido.</p>
            <form class="criar-formulario" action="cadastro-pergunta.php">
                <button class="criar-botao" type="submit">Criar</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="entrar-sala">
            <h1 class="entrar-sala-h">Entrar em uma Sala</h1>
            <p>Participe de quizzes interativos em tempo real, desafie seus conhecimentos e aprenda de forma divertida.
                Entre em salas rapidamente, acompanhe sua pontuação e dispute posições no ranking enquanto responde
                perguntas dinâmicas e envolventes.</p>
            <form class="entrar-formulario" action="entrar">
                <label for="pin-sala">PIN da Sala</label>
                <input type="text" id="pin-sala" name="pin-sala">
                <button type="submit">Entrar</button>
            </form>
        </div>
    </main>
</body>

</html>