<?php
/**
 * Encerra a sessão do usuário e redireciona para a tela de login.
 */

session_start();

session_destroy();

header("Location: ../login.html");
?>
