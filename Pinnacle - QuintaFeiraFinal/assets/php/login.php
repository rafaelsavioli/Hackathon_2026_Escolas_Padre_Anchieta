<?php
session_start();
include("conect.php");

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM usuario WHERE usuario = :usuario";

$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario' => $usuario]);

$dados = $stmt->fetch();

if($dados){
    if(password_verify($senha, $dados['senha'])){
        $_SESSION['id'] = $dados['id'];
        $_SESSION['usuario'] = $dados['usuario'];
        $_SESSION['tipo'] = $dados['tipo'];

        header("Location: ../../homepage.php");
        exit;
    } else {
        header("Location: ../../login.html?erro=senha");
        exit;
    }
} else {
    header("Location: ../../login.html?erro=usuario");
    exit;
}
?>