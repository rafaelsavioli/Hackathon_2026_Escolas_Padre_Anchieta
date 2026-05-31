<?php
/**
 * Tela principal do jogo.
 * Carrega perguntas e respostas do banco, injeta como window.quizData (JSON)
 * e renderiza a interface completa do jogador.
 */

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

// Captura o PIN da sala
$codigo = isset($_GET['pin-sala']) ? trim($_GET['pin-sala']) : '';
$erro = '';
$quizData = null;

// Valida código e carrega quiz do banco
if ($codigo === '') {
    $erro = 'Informe o PIN da sala.';
} else {
    include("includes/conect.php");

    $stmt = $pdo->prepare("SELECT * FROM questionarios WHERE codigo = :codigo AND ativo = 1");
    $stmt->execute([':codigo' => $codigo]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        $erro = 'Quiz nao encontrado ou inativo.';
    } else {
        $stmtPerguntas = $pdo->prepare("SELECT * FROM perguntas WHERE COD_QUIZ = :quiz_id ORDER BY COD_QUEST");
        $stmtPerguntas->execute([':quiz_id' => $quiz['COD_QUIZ']]);
        $perguntasDb = $stmtPerguntas->fetchAll();

        $stmtRespostas = $pdo->prepare("SELECT * FROM answers WHERE COD_QUEST = :quest_id ORDER BY COD_ANSER");

        $perguntas = [];

        foreach ($perguntasDb as $perguntaDb) {
            $stmtRespostas->execute([':quest_id' => $perguntaDb['COD_QUEST']]);
            $respostasDb = $stmtRespostas->fetchAll();
            $respostas = [];

            foreach ($respostasDb as $respostaDb) {
                $respostas[] = [
                    'text' => $respostaDb['RESP'],
                    'correct' => $respostaDb['ANWSERS'] === 'T'
                ];
            }

            $perguntas[] = [
                'question' => $perguntaDb['QUESTION'],
                'time' => (int) $perguntaDb['tempo_segundos'],
                'points' => (int) $perguntaDb['pontos'],
                'image' => $perguntaDb['imagem_path'] ?? '',
                'answers' => $respostas
            ];
        }

        $quizData = [
            'title' => $quiz['NOME_QUIZ'],
            'code' => $quiz['codigo'],
            'questions' => $perguntas
        ];
    }
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pinnacle Quiz Player</title>
    <link rel="stylesheet" href="assets/css/quiz-player.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  </head>

  <body>
    <header class="topbar">
      <div class="logo">
        <a href="homepage.php">
          <img src="assets/img/Pinnacle - Logo.png" alt="Pinnacle Logo" />
        </a>
      </div>

      <div class="user-menu">
        <span class="account-type">
          Tipo de conta:
          <strong><?php echo isset($_SESSION['tipo']) ? ucfirst($_SESSION['tipo']) : ''; ?></strong>
        </span>

        <button id="user-button" class="user-button">
          <i class="bi bi-person-fill"></i>
        </button>

        <div id="user-dropdown" class="user-dropdown">
          <a href="assets/php/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            Logout
          </a>
        </div>
      </div>
    </header>

    <main class="main">
      <?php if ($erro): ?>
        <section class="player-error">
          <h1>Ops!</h1>
          <p><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></p>
          <a href="homepage.php">Voltar</a>
        </section>
      <?php else: ?>
        <section class="quiz-header">
          <div class="quiz-info">
            <h1 id="quiz-title">Carregando Quiz...</h1>
            <span id="question-counter">Pergunta 1 de 1</span>
          </div>

          <div class="score-box">
            <i class="bi bi-trophy-fill"></i>
            <span id="player-score">0 pts</span>
          </div>

          <div class="timer-circle" id="timer-circle">
            <span id="timer">20</span>
          </div>
        </section>

        <section class="question-section">
          <h2 class="question-title" id="question-title">Pergunta</h2>

          <div class="question-image hidden" id="question-image-container">
            <img id="question-image" alt="Imagem da pergunta" />
          </div>

          <div class="answers-grid">
            <button class="answer-btn answer-red" data-index="0">
              <div class="answer-bar red"></div>
              <div class="answer-content">
                <span class="answer-letter">A</span>
                <span class="answer-text"></span>
              </div>
            </button>

            <button class="answer-btn answer-blue" data-index="1">
              <div class="answer-bar blue"></div>
              <div class="answer-content">
                <span class="answer-letter">B</span>
                <span class="answer-text"></span>
              </div>
            </button>

            <button class="answer-btn answer-yellow" data-index="2">
              <div class="answer-bar yellow"></div>
              <div class="answer-content">
                <span class="answer-letter">C</span>
                <span class="answer-text"></span>
              </div>
            </button>

            <button class="answer-btn answer-green" data-index="3">
              <div class="answer-bar green"></div>
              <div class="answer-content">
                <span class="answer-letter">D</span>
                <span class="answer-text"></span>
              </div>
            </button>
          </div>
        </section>

        <section class="feedback-modal hidden" id="feedback-modal">
          <div class="feedback-content">
            <div id="feedback-icon" class="feedback-icon">
              <i class="bi bi-check-circle-fill"></i>
            </div>

            <h2 id="feedback-title">Resposta Correta!</h2>
            <p id="feedback-points">+100 pontos</p>
            <button id="next-question-btn">Proxima Pergunta</button>
          </div>
        </section>

        <!-- PROFESSOR QUIZ CONTROL -->
        <section class="professor-quiz-bar hidden" id="professor-quiz-bar">
          <div class="prof-quiz-content">
            <div class="prof-info">
              <i class="bi bi-people-fill"></i>
              <span>Responderam:</span>
              <strong id="prof-answer-count">0 / 0</strong>
              <span id="prof-question-status" class="prof-status">Aguardando...</span>
            </div>
            <button id="prof-advance-btn" class="btn-advance" disabled>
              <i class="bi bi-arrow-right-circle-fill"></i> Avançar Pergunta
            </button>
          </div>
        </section>

        <!-- WAITING ROOM -->
        <section class="waiting-room hidden" id="waiting-room">
          <div class="waiting-content">
            <div class="waiting-spinner">
              <i class="bi bi-hourglass-split"></i>
            </div>

            <h1>Aguardando o Professor</h1>

            <p id="waiting-message">
              O professor ainda não iniciou o quiz.
              <br />
              Prepare-se para responder rapido!
            </p>

            <div class="waiting-stats">
              <div class="stat">
                <span class="label">Código:</span>
                <span id="waiting-code" class="value">-</span>
              </div>

              <div class="stat">
                <span class="label">Alunos Conectados:</span>
                <span id="waiting-players" class="value">1</span>
              </div>
            </div>

            <button class="exit-btn" onclick="exitQuiz()">
              <i class="bi bi-box-arrow-left"></i> Sair
            </button>
          </div>
        </section>

        <!-- PROFESSOR PANEL -->
        <section class="professor-panel hidden" id="professor-panel">
          <div class="professor-content">
            <h1>Painel do Professor</h1>
            <p>Pronto para iniciar o quiz?</p>

            <div class="player-list">
              <h3>Alunos Conectados (<span id="professor-players">0</span>)</h3>
              <div id="professor-list"></div>
            </div>

            <button class="btn-start-quiz" onclick="startQuiz()">
              <i class="bi bi-play-fill"></i> Iniciar Quiz
            </button>

            <a href="homepage.php" class="btn-exit-professor">
              <i class="bi bi-box-arrow-left"></i> Sair
            </a>
          </div>
        </section>

        <section class="ranking-screen hidden" id="ranking-screen">
          <h1>Ranking Final</h1>
          <div class="ranking-podium" id="ranking-podium"></div>
          <button class="play-again-btn" onclick="location.href='homepage.php'">Voltar ao inicio</button>
        </section>
      <?php endif; ?>
    </main>

    <footer class="footer">
      <span>Pinnacle(TM)</span>
    </footer>

    <script>
      window.quizData = <?php echo json_encode($quizData, JSON_UNESCAPED_UNICODE); ?>;
      window.userRole = <?php echo json_encode(isset($_SESSION['tipo']) ? $_SESSION['tipo'] : 'student'); ?>;
      window.userId = <?php echo json_encode(isset($_SESSION['id']) ? $_SESSION['id'] : null); ?>;
    </script>
    <script src="assets/js/quiz-player.js"></script>
  </body>
</html>
