<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

include("conect.php");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || empty($data['titulo']) || empty($data['perguntas'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

$titulo = $data['titulo'];
$perguntas = $data['perguntas'];
$codigo = $data['codigo'] ?? '';
$professorId = $_SESSION['id'];

if (empty($codigo)) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $chars[random_int(0, strlen($chars) - 1)];
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO questionarios (NOME_QUIZ, codigo, professor_id) VALUES (:nome, :codigo, :professor_id)");
    $stmt->execute([
        ':nome' => $titulo,
        ':codigo' => $codigo,
        ':professor_id' => $professorId
    ]);
    $quizId = $pdo->lastInsertId();

    $stmtPergunta = $pdo->prepare("INSERT INTO perguntas (COD_QUIZ, QUESTION, pontos, tempo_segundos) VALUES (:quiz_id, :question, :pontos, :tempo)");
    $stmtResposta = $pdo->prepare("INSERT INTO answers (COD_QUEST, RESP, ANWSERS) VALUES (:quest_id, :resp, :anwsers)");

    foreach ($perguntas as $pergunta) {
        $stmtPergunta->execute([
            ':quiz_id' => $quizId,
            ':question' => $pergunta['question'],
            ':pontos' => $pergunta['points'] ?? 10,
            ':tempo' => $pergunta['time'] ?? 20
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

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'codigo' => $codigo,
        'quiz_id' => $quizId
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar quiz']);
}
?>
