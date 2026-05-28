CREATE DATABASE IF NOT EXISTS Hackathon_2026_Escolas_Padre_Anchieta;

USE Hackathon_2026_Escolas_Padre_Anchieta;

-- ========================================
-- TABELA: usuario
-- ========================================

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    RA VARCHAR(20) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'professor') NOT NULL,
    data_nascimento DATE DEFAULT NULL,
    instituicao VARCHAR(200) DEFAULT NULL
);

-- ========================================
-- TABELA: questionarios
-- ========================================

CREATE TABLE IF NOT EXISTS questionarios (
    COD_QUIZ INT AUTO_INCREMENT PRIMARY KEY,
    NOME_QUIZ VARCHAR(200) NOT NULL,
    codigo VARCHAR(6) NOT NULL UNIQUE,
    professor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (professor_id) REFERENCES usuario(id)
);

-- ========================================
-- TABELA: perguntas
-- ========================================

CREATE TABLE IF NOT EXISTS perguntas (
    COD_QUEST INT AUTO_INCREMENT PRIMARY KEY,
    COD_QUIZ INT NOT NULL,
    QUESTION TEXT NOT NULL,
    pontos INT DEFAULT 10,
    tempo_segundos INT DEFAULT 20,
    imagem_path VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (COD_QUIZ) REFERENCES questionarios(COD_QUIZ) ON DELETE CASCADE
);

-- ========================================
-- TABELA: answers
-- ========================================

CREATE TABLE IF NOT EXISTS answers (
    COD_ANSER INT AUTO_INCREMENT PRIMARY KEY,
    COD_QUEST INT NOT NULL,
    RESP VARCHAR(500) NOT NULL,
    ANWSERS CHAR(1) NOT NULL DEFAULT 'F',
    FOREIGN KEY (COD_QUEST) REFERENCES perguntas(COD_QUEST) ON DELETE CASCADE
);
