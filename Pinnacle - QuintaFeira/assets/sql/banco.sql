CREATE DATABASE IF NOT EXISTS Hackathon_2026_Escolas_Padre_Anchieta;

USE Hackathon_2026_Escolas_Padre_Anchieta;

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    RA VARCHAR(20) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'professor') NOT NULL,
    data_nascimento DATE DEFAULT NULL,
    instituicao VARCHAR(200) DEFAULT NULL
);
