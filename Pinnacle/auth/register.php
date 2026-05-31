<?php
/**
 * Processa o formulário de cadastro.
 * Valida confirmação de senha, idade mínima de 18 anos para professores,
 * gera hash da senha e insere o registro no banco de dados.
 */

include("../includes/conect.php");

// Captura dados do formulário
$ra = $_POST['ra'];
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];
$confirmarSenha = $_POST['confirmarSenha'];
$tipo = $_POST['tipo'];
$dataNascimento = $_POST['data_nascimento'] ?? null;
$instituicao = $_POST['instituicao'] ?? null;

// Valida se as senhas conferem
if($senha != $confirmarSenha){
    header("Location: ../register.html?erro=senhas");
    exit;
}

// Valida campos obrigatórios e idade mínima para professor
if($tipo == 'professor'){
    if(empty($dataNascimento) || empty($instituicao)){
        header("Location: ../register.html?erro=campos");
        exit;
    }
    $nascimento = new DateTime($dataNascimento);
    $hoje = new DateTime();
    $idade = $nascimento->diff($hoje)->y;

    if($idade < 18){
        header("Location: ../register.html?erro=idade");
        exit;
    }
}

// Gera hash seguro da senha
$senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

// Insere usuário no banco
$sql = "INSERT INTO usuario (RA, usuario, senha, tipo, data_nascimento, instituicao)
VALUES (:ra, :usuario, :senha, :tipo, :data_nascimento, :instituicao)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':ra' => $ra,
    ':usuario' => $usuario,
    ':senha' => $senhaCriptografada,
    ':tipo' => $tipo,
    ':data_nascimento' => $dataNascimento,
    ':instituicao' => $instituicao
]);

// Redireciona para login com mensagem de sucesso
header("Location: ../login.html?cadastro=sucesso");
exit;
?>
