<?php
include("conect.php");

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM usuario
WHERE usuario = :usuario";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario' => $usuario
]);

$dados = $stmt->fetch();

if($dados){

    if(password_verify($senha, $dados['senha'])){
        echo "Login realizado com sucesso!";
    }

    else{
        echo "Senha incorreta!";
    }
}

else{
    echo "Usuário não encontrado!";
}
?>