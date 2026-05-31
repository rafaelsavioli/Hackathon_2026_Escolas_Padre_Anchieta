# Hackathon_2026_Escolas_Padre_Anchieta

Pinnacle é uma plataforma web de quizzes multiplayer em tempo real, inspirada no Kahoot!. Desenvolvida durante o Hackathon da Semana Técnica da Escola Padre Anchieta (2026).

Funcionalidades

  Criação de quizzes — até 20 perguntas com 4 alternativas, timer e pontuação configuráveis
  Salas ao vivo — alunos entram com código PIN de 6 dígitos
  Multiplayer em tempo real — polling HTTP, pontuação com bônus por velocidade
  Ranking — pódio com os 3 primeiros ao final da partida
  QR Code — compartilhamento rápido da sala
  Upload de imagens — nas perguntas do quiz
  Autenticação — cadastro/login com sessão e senhas hash (bcrypt)
  Responsivo — funciona em desktop e mobile

Tecnologias

  Camada	  Tecnologia
  Frontend	HTML5, CSS3, JavaScript (vanilla)
  Backend	  PHP 7.4+ (vanilla)
  Banco	    MySQL (PDO)
  QR Code	  chillerlan/php-qrcode
  Ícones	  Bootstrap Icons
  Fontes	  Montserrat (Google Fonts)

Estrutura do Projeto
├── index.html              # Landing page
├── login.html / register.html
├── homepage.php            # Dashboard do professor
├── cadastro-pergunta.php   # Criador de quizzes
├── entrar-quiz.php         # Entrada na sala (PIN)
├── quiz-player.php         # Tela do jogo
├── api/                    # Endpoints REST
│   ├── salvar_quiz.php     # CRUD de quizzes
│   └── quiz-status.php     # Estado multiplayer (JSON)
├── auth/                   # Login, registro, logout
├── includes/conect.php     # Conexão PDO com MySQL
├── assets/
│   ├── css/                # Estilos por página
│   ├── js/                 # Lógica frontend
│   └── sql/banco.sql       # Schema do banco
└── uploads/                # Imagens e estado das salas

Como Rodar
  XAMPP (Apache + MySQL) ou similar
  Importe assets/sql/banco.sql no MySQL
  Configure a conexão em includes/conect.php (se necessário)
  Acesse via http://localhost/Pinnacle
  Para QR Code em rede local, veja QR_CODE_SETUP.md
  
Banco de Dados
  usuario — alunos e professores (RA, login, senha bcrypt, tipo)
  questionarios — quizzes (código PIN único de 6 caracteres)
  perguntas — até 20 questões por quiz (texto, pontos, timer, imagem)
  answers — 4 alternativas por questão (T = correta, F = incorreta)
  
Desafio Original

Este projeto foi desenvolvido como solução para um Hackathon com divisão por séries:
  1º Ano — Frontend (HTML, CSS, responsividade)
  2º Ano — Backend (PHP, lógica, banco de dados)
  3º Ano — Gestão (organização, testes, documentação, apresentação)
