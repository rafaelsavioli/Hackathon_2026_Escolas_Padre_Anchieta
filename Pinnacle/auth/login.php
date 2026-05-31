<?php
/**
 * Processa o formulário de login.
 * Verifica as credenciais com password_verify(), inicia a sessão
 * e redireciona para homepage.php ou volta para login.html com erro.
 */

session_start();
include("../includes/conect.php");

// Captura dados do formulário
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

// Busca usuário no banco
$sql = "SELECT * FROM usuario WHERE usuario = :usuario";

$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario' => $usuario]);

$dados = $stmt->fetch();

if($dados){
    // Verifica a senha com bcrypt
    if(password_verify($senha, $dados['senha'])){
        $_SESSION['id'] = $dados['id'];
        $_SESSION['usuario'] = $dados['usuario'];
        $_SESSION['tipo'] = $dados['tipo'];

        $destino = "../homepage.php";

        if (!empty($_SESSION['pin_pendente'])) {
            $pin = $_SESSION['pin_pendente'];
            unset($_SESSION['pin_pendente']);
            $destino = "../entrar-quiz.php?pin-sala=" . urlencode($pin);
        }

        header("Location: $destino");
        exit;
    } else {
        header("Location: ../login.html?erro=senha");
        exit;
    }
} else {
    header("Location: ../login.html?erro=usuario");
    exit;
}
?>