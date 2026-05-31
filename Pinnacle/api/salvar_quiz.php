<?php
/**
 * Recebe dados JSON via POST com título, perguntas e respostas do quiz.
 * Cria ou atualiza o quiz no banco com transação.
 * Gerencia upload de imagens das perguntas.
 */

session_start();
header('Content-Type: application/json');

// Verifica autenticação
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

// Valida método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

include("../includes/conect.php");

// Lê dados JSON do corpo da requisição
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Valida dados recebidos
if (!$data || empty($data['titulo']) || empty($data['perguntas'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

$titulo = $data['titulo'];
$perguntas = $data['perguntas'];
$codigo = $data['codigo'] ?? '';
$quizId = !empty($data['quiz_id']) ? (int) $data['quiz_id'] : null;
$professorId = $_SESSION['id'];

// Processa e salva imagem base64 da pergunta
function salvarImagemPergunta($imageData, $quizId, $questionIndex) {
    if (empty($imageData)) {
        return null;
    }

    if (strpos($imageData, 'assets/uploads/questions/') === 0) {
        return $imageData;
    }

    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $imageData, $matches)) {
        return null;
    }

    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
    $base64 = substr($imageData, strpos($imageData, ',') + 1);
    $binary = base64_decode($base64, true);

    if ($binary === false) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/questions';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileName = 'quiz_' . $quizId . '_q' . ($questionIndex + 1) . '_' . uniqid('', true) . '.' . $extension;
    $filePath = $uploadDir . '/' . $fileName;

    if (file_put_contents($filePath, $binary) === false) {
        return null;
    }

    return 'assets/uploads/questions/' . $fileName;
}

// Gera código aleatório de 6 caracteres se não informado
if (empty($codigo)) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $chars[random_int(0, strlen($chars) - 1)];
    }
}

// Transação: cria ou atualiza quiz e perguntas
try {
    $pdo->beginTransaction();

    // Atualiza quiz existente
    if ($quizId) {
        $stmt = $pdo->prepare("SELECT COD_QUIZ FROM questionarios WHERE COD_QUIZ = :quiz_id AND professor_id = :professor_id");
        $stmt->execute([
            ':quiz_id' => $quizId,
            ':professor_id' => $professorId
        ]);

        if (!$stmt->fetch()) {
            throw new PDOException('Quiz nao encontrado para este professor.');
        }

        $stmt = $pdo->prepare("UPDATE questionarios SET NOME_QUIZ = :nome, codigo = :codigo WHERE COD_QUIZ = :quiz_id AND professor_id = :professor_id");
        $stmt->execute([
            ':nome' => $titulo,
            ':codigo' => $codigo,
            ':quiz_id' => $quizId,
            ':professor_id' => $professorId
        ]);

        $stmt = $pdo->prepare("DELETE FROM perguntas WHERE COD_QUIZ = :quiz_id");
        $stmt->execute([':quiz_id' => $quizId]);
    // Insere novo quiz
    } else {
        $stmt = $pdo->prepare("INSERT INTO questionarios (NOME_QUIZ, codigo, professor_id) VALUES (:nome, :codigo, :professor_id)");
        $stmt->execute([
            ':nome' => $titulo,
            ':codigo' => $codigo,
            ':professor_id' => $professorId
        ]);
        $quizId = $pdo->lastInsertId();
    }

    // Prepara statements para perguntas e respostas
    $stmtPergunta = $pdo->prepare("INSERT INTO perguntas (COD_QUIZ, QUESTION, pontos, tempo_segundos, imagem_path) VALUES (:quiz_id, :question, :pontos, :tempo, :imagem_path)");
    $stmtResposta = $pdo->prepare("INSERT INTO answers (COD_QUEST, RESP, ANWSERS) VALUES (:quest_id, :resp, :anwsers)");

    // Percorre perguntas e insere no banco
    foreach ($perguntas as $index => $pergunta) {
        $imagemPath = salvarImagemPergunta($pergunta['image'] ?? null, $quizId, $index);

        $stmtPergunta->execute([
            ':quiz_id' => $quizId,
            ':question' => $pergunta['question'],
            ':pontos' => $pergunta['points'] ?? 10,
            ':tempo' => $pergunta['time'] ?? 20,
            ':imagem_path' => $imagemPath
        ]);
        $questId = $pdo->lastInsertId();

        foreach ($pergunta['answers'] as $resposta) {
            $stmtResposta->execute([
                ':quest_id' => $questId,
                ':resp' => $resposta['text'],
                ':anwsers' => $resposta['correct'] ? 'T' : 'F'
            ]);
        }
    }

    // Confirma transação
    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'codigo' => $codigo,
        'quiz_id' => $quizId
    ]);

// Reverte transação em caso de erro
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar quiz']);
}
?>
