<?php
/**
 * Página de criação/edição de quiz.
 * Carrega quiz existente do banco se o parâmetro quiz_id estiver presente.
 * Renderiza a interface completa do criador com sidebar, formulário de pergunta e grid de respostas.
 */

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit;
}

// Processa salvamento via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "api/salvar_quiz.php";
    exit;
}

$quizInicial = null;

// Carrega quiz existente para edição
if (isset($_GET['quiz_id']) && ($_SESSION['tipo'] ?? '') === 'professor') {
    require_once "includes/conect.php";

    $stmt = $pdo->prepare("
        SELECT COD_QUIZ, NOME_QUIZ, codigo
        FROM questionarios
        WHERE COD_QUIZ = :quiz_id AND professor_id = :professor_id
    ");
    $stmt->execute([
        ':quiz_id' => $_GET['quiz_id'],
        ':professor_id' => $_SESSION['id']
    ]);
    $quizDb = $stmt->fetch();

    if ($quizDb) {
        // Busca perguntas e respostas do banco
        $stmtPerguntas = $pdo->prepare("
            SELECT COD_QUEST, QUESTION, pontos, tempo_segundos, imagem_path
            FROM perguntas
            WHERE COD_QUIZ = :quiz_id
            ORDER BY COD_QUEST
        ");
        $stmtPerguntas->execute([':quiz_id' => $quizDb['COD_QUIZ']]);
        $perguntasDb = $stmtPerguntas->fetchAll();

        $stmtRespostas = $pdo->prepare("
            SELECT RESP, ANWSERS
            FROM answers
            WHERE COD_QUEST = :quest_id
            ORDER BY COD_ANSER
        ");

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

            while (count($respostas) < 4) {
                $respostas[] = ['text' => '', 'correct' => false];
            }

            $perguntas[] = [
                'question' => $perguntaDb['QUESTION'],
                'points' => (int) $perguntaDb['pontos'],
                'time' => (int) $perguntaDb['tempo_segundos'],
                'image' => $perguntaDb['imagem_path'],
                'answers' => array_slice($respostas, 0, 4)
            ];
        }

        $quizInicial = [
            'id' => (int) $quizDb['COD_QUIZ'],
            'title' => $quizDb['NOME_QUIZ'],
            'code' => $quizDb['codigo'],
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
    <title>Pinnacle Quiz Creator</title>
    <link rel="stylesheet" href="assets/css/cadastro-pergunta.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />

    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <script>
      window.initialQuiz = <?php echo json_encode($quizInicial, JSON_UNESCAPED_UNICODE); ?>;
    </script>
  </head>

  <body>
    <!-- TOPBAR -->

    <header class="topbar">
      <!-- LOGO -->

      <div class="logo">
        <a href="homepage.php">
          <img src="assets/img/Pinnacle - Logo.png" alt="Pinnacle Logo" />
        </a>
      </div>

      <!-- USER AREA -->

      <div class="user-menu">

        <span class="account-type">
          Tipo de conta:
          <strong><?php echo isset($_SESSION['tipo']) ? ucfirst($_SESSION['tipo']) : ''; ?></strong>
        </span>
      
        <!-- BOTÃO -->
      
        <button id="user-button" class="user-icon">
          <i class="bi bi-person-fill"></i>
        </button>
      
        <!-- DROPDOWN -->
      
        <div id="user-dropdown" class="user-dropdown">
          <a href="auth/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            Logout
          </a>
        </div>
      </div>
    </header>

    <div class="container">
      <!-- SIDEBAR -->

      <aside class="sidebar">
        <div class="question-list" id="question-list">
          <div class="question-card active">Pergunta 1</div>
        </div>

        <button class="add-question" id="add-question-btn">
          + Adicionar Pergunta
        </button>
      </aside>

      <!-- MAIN -->

      <main class="main">
        <!-- CONTENT -->

        <section class="content">
          <!-- SETTINGS -->

          <div class="quiz-settings">
            <input
              type="text"
              id="quiz-title"
              class="quiz-title"
              placeholder="Insira o título do Quiz"
            />

            <!-- PONTUAÇÃO -->

            <div class="input-icon-box">

              <i class="bi bi-trophy-fill"></i>

              <input
                type="number"
                id="question-points"
                class="small-input"
                placeholder="Pontuação"
              />

            </div>

            <!-- TEMPO -->

            <div class="input-icon-box">

              <i class="bi bi-clock-fill"></i>

              <input
                type="number"
                id="question-time"
                class="small-input"
                placeholder="Tempo (s)"
              />

            </div>

            <div class="quiz-code-area">
              <span class="quiz-code-label"> Código do Quiz: </span>

              <span id="quiz-code"> ------ </span>

              <button id="regenerate-code" disabled>Gerar Novo</button>
            </div>

            <div class="top-actions">
              <a
                class="exit-btn"
                href="homepage.php"
                title="Sair"
              >

                <i class="bi bi-door-open-fill"></i>

              </a>

              <button class="save-btn" id="save-quiz-btn">Salvar</button>
            </div>
          </div>

          <!-- QUESTION BOX -->

          <div class="question-box">
            <input
              type="text"
              id="question-input"
              class="question-input"
              placeholder="Digite sua pergunta"
            />

            <!-- IMAGE -->

            <label class="image-upload">
              <div class="plus">+</div>

              <span>Carregar imagem</span>
              <!-- input criado dinamicamente via JS -->
            </label>

            <!-- ANSWERS -->

            <div class="answers-grid">
              <!-- ANSWER 1 -->

              <div class="answer answer-red">
                <div class="answer-color red"></div>

                <input
                  type="text"
                  class="answer-input"
                  data-index="0"
                  placeholder="Adicionar resposta 1"
                />

                <div class="correct">
                  <input
                    type="checkbox"
                    class="correct-checkbox"
                    data-index="0"
                  />
                </div>
              </div>

              <!-- ANSWER 2 -->

              <div class="answer answer-blue">
                <div class="answer-color blue"></div>

                <input
                  type="text"
                  class="answer-input"
                  data-index="1"
                  placeholder="Adicionar resposta 2"
                />

                <div class="correct">
                  <input
                    type="checkbox"
                    class="correct-checkbox"
                    data-index="1"
                  />
                </div>
              </div>

              <!-- ANSWER 3 -->

              <div class="answer answer-yellow">
                <div class="answer-color yellow"></div>

                <input
                  type="text"
                  class="answer-input"
                  data-index="2"
                  placeholder="Adicionar resposta 3"
                />

                <div class="correct">
                  <input
                    type="checkbox"
                    class="correct-checkbox"
                    data-index="2"
                  />
                </div>
              </div>

              <!-- ANSWER 4 -->

              <div class="answer answer-green">
                <div class="answer-color green"></div>

                <input
                  type="text"
                  class="answer-input"
                  data-index="3"
                  placeholder="Adicionar resposta 4"
                />

                <div class="correct">
                  <input
                    type="checkbox"
                    class="correct-checkbox"
                    data-index="3"
                  />
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>

    <footer class="footer">
      <span>Pinnacle™</span>
    </footer>

    <!-- ========================================
     MODAL QR CODE
======================================== -->

    <div class="qr-modal hidden" id="qr-modal">
      <div class="qr-modal-content">
        <!-- FECHAR -->

        <button class="close-modal" id="close-modal">✕</button>

        <!-- TÍTULO -->

        <h2>Entrar no Quiz</h2>

        <!-- CÓDIGO -->

        <div class="quiz-code-display">
          <span> Código: </span>

          <strong id="modal-quiz-code"> ------ </strong>
        </div>

        <!-- QR -->

        <img id="qr-code" alt="QR Code" />

        <!-- LINK -->

        <p class="qr-info">Escaneie para entrar no quiz</p>
      </div>
    </div>

    <script src="assets/js/cadastro-pergunta.js"></script>
  </body>
</html>
