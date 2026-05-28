<?php
include("conect.php");

$ra = $_POST['ra'];
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];
$confirmarSenha = $_POST['confirmarSenha'];
$tipo = $_POST['tipo'];
$dataNascimento = $_POST['data_nascimento'] ?? null;
$instituicao = $_POST['instituicao'] ?? null;

if($senha != $confirmarSenha){
    header("Location: ../../register.html?erro=senhas");
    exit;
}

if($tipo == 'professor'){
    if(empty($dataNascimento) || empty($instituicao)){
        header("Location: ../../register.html?erro=campos");
        exit;
    }

    $nascimento = new DateTime($dataNascimento);
    $hoje = new DateTime();
    $idade = $nascimento->diff($hoje)->y;

    if($idade < 18){
        header("Location: ../../register.html?erro=idade");
        exit;
    }
}

$senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

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

echo "Cadastro realizado com sucesso!";
?>