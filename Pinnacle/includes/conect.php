<?php
/**
 * includes/conect.php
 * Conexão com o banco de dados MySQL via PDO.
 * Configura charset utf8mb4, modo de erro exception e fetch associativo.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'Hackathon_2026_Escolas_Padre_Anchieta');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
 
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$opcoes = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];
 
try {
  $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
} catch (PDOException $e) {
  error_log($e->getMessage());
  die('Erro de conexao com o banco.');
}
?>