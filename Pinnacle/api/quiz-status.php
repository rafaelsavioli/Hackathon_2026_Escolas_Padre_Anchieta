<?php
/**
 * API REST para gerenciar o estado multiplayer do quiz.
 * Ações: join, start, status, answer, advance, submit, reset.
 * Lê/grava arquivos JSON de estado na pasta uploads/.
 */

header('Content-Type: application/json');

$quizCode = $_GET['code'] ?? null;

if (!$quizCode) {
    http_response_code(400);
    echo json_encode(['error' => 'Quiz code required']);
    exit;
}

// Usar diretório seguro na raiz do projeto
$uploadDir = __DIR__ . '/../uploads';

// Criar pasta se não existir
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$statusFile = $uploadDir . "/quiz_{$quizCode}_status.json";
$action = $_GET['action'] ?? 'status';

// Criar status inicial se não existir
if (!file_exists($statusFile)) {
    $initialStatus = [
        'code' => $quizCode,
        'started' => false,
        'startTime' => null,
        'currentQuestion' => 0,
        'questionStartTime' => null,
        'players' => [],
        'answers' => [], // per-question answers: answers[questionIndex][playerId]
        'results' => [], // final results submitted by players
        'advanceReady' => false
    ];
    $written = @file_put_contents($statusFile, json_encode($initialStatus));
    if ($written === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Não foi possível criar arquivo de estado. Verifique permissões.']);
        exit;
    }
}

// Carrega estado atual do quiz
$status = json_decode(file_get_contents($statusFile), true);

if ($status === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao ler arquivo de estado']);
    exit;
}

// Dispara ação conforme parâmetro
switch ($action) {
    case 'join':
        // Aluno entra na sala
        $playerName = $_POST['playerName'] ?? 'Aluno';
        $playerId = uniqid();
        
        if (!isset($status['players'])) {
            $status['players'] = [];
        }
        
        $status['players'][$playerId] = [
            'id' => $playerId,
            'name' => $playerName,
            'joinedAt' => time()
        ];
        
        $written = @file_put_contents($statusFile, json_encode($status));
        if ($written === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar estado']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'playerId' => $playerId,
            'status' => $status
        ]);
        break;

    case 'start':
        // Professor inicia o quiz
        // Permitir se senha correta ou se sessão indica professor
        session_start();
        $password = $_POST['password'] ?? null;
        $isProfessorSession = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'professor';

        if (!$isProfessorSession && $password !== 'professor123') {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid password']);
            exit;
        }
        
            // Se já iniciado, não sobrescrever o startTime (idempotente)
            if (!empty($status['started'])) {
                echo json_encode([
                    'success' => true,
                    'startTime' => $status['startTime']
                ]);
                exit;
            }

            $status['started'] = true;
            $status['startTime'] = round(microtime(true) * 1000); // milliseconds para sincronizar com JS
        
            $written = @file_put_contents($statusFile, json_encode($status));
            if ($written === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao salvar estado']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'startTime' => $status['startTime']
            ]);
        break;

    case 'status':
    default:
        // Retorna status atual do quiz
        echo json_encode($status);
        break;

    case 'answer':
        // Recebe resposta de um jogador para a pergunta atual
        $playerId = $_POST['playerId'] ?? null;
        $questionIndex = isset($_POST['questionIndex']) ? intval($_POST['questionIndex']) : $status['currentQuestion'];
        $correct = isset($_POST['correct']) ? ($_POST['correct'] === '1' || $_POST['correct'] === 'true' || $_POST['correct'] === true) : false;
        $points = isset($_POST['points']) ? floatval($_POST['points']) : 0;
        $timeLeft = isset($_POST['timeLeft']) ? intval($_POST['timeLeft']) : null;

        if (!$playerId) {
            http_response_code(400);
            echo json_encode(['error' => 'playerId required']);
            exit;
        }

        if (!isset($status['answers'][$questionIndex])) {
            $status['answers'][$questionIndex] = [];
        }

        $status['answers'][$questionIndex][$playerId] = [
            'playerId' => $playerId,
            'correct' => $correct,
            'points' => $points,
            'timeLeft' => $timeLeft,
            'answeredAt' => round(microtime(true) * 1000)
        ];

        // Salvar
        $written = @file_put_contents($statusFile, json_encode($status));
        if ($written === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar resposta']);
            exit;
        }

        // Verificar se todos os jogadores (exceto professor) responderam
        $studentCount = 0;
        foreach ($status['players'] as $p) {
            if (isset($p['name']) && $p['name'] !== 'Professor') {
                $studentCount++;
            }
        }
        $answersCount = isset($status['answers'][$questionIndex]) ? count($status['answers'][$questionIndex]) : 0;
        if ($studentCount > 0 && $answersCount >= $studentCount) {
            $status['advanceReady'] = true;
            @file_put_contents($statusFile, json_encode($status));
        }

        echo json_encode(['success' => true, 'advanceReady' => $status['advanceReady']]);
        break;

    case 'advance':
        // Professor força avanço para próxima pergunta
        session_start();
        $password = $_POST['password'] ?? null;
        $isProfessorSession = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'professor';
        if (!$isProfessorSession && $password !== 'professor123') {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid password']);
            exit;
        }

        // Incrementar pergunta
        $status['currentQuestion'] = isset($status['currentQuestion']) ? intval($status['currentQuestion']) + 1 : 0;
        $status['questionStartTime'] = round(microtime(true) * 1000);
        $status['advanceReady'] = false;

        $written = @file_put_contents($statusFile, json_encode($status));
        if ($written === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao avançar pergunta']);
            exit;
        }

        echo json_encode(['success' => true, 'currentQuestion' => $status['currentQuestion'], 'questionStartTime' => $status['questionStartTime']]);
        break;

    case 'submit':
        // Jogador envia resultado final
        $playerId = $_POST['playerId'] ?? null;
        $name = $_POST['name'] ?? 'Aluno';
        $scoreVal = isset($_POST['score']) ? floatval($_POST['score']) : 0;
        $timeSpent = isset($_POST['timeSpent']) ? intval($_POST['timeSpent']) : 0;

        if (!$playerId) {
            http_response_code(400);
            echo json_encode(['error' => 'playerId required']);
            exit;
        }

        if (!isset($status['results'])) $status['results'] = [];
        $status['results'][] = [
            'id' => $playerId,
            'name' => $name,
            'score' => $scoreVal,
            'totalTimeSpent' => $timeSpent,
            'submittedAt' => round(microtime(true) * 1000)
        ];

        $written = @file_put_contents($statusFile, json_encode($status));
        if ($written === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar resultado']);
            exit;
        }

        echo json_encode(['success' => true]);
        break;

    case 'reset':
        // Reseta quiz (professor)
        $password = $_POST['password'] ?? null;
        
        if ($password !== 'professor123') {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid password']);
            exit;
        }
        
        $status['started'] = false;
        $status['startTime'] = null;
        $status['players'] = [];
        
        $written = @file_put_contents($statusFile, json_encode($status));
        if ($written === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar estado']);
            exit;
        }
        
        echo json_encode(['success' => true]);
        break;
}
?>
