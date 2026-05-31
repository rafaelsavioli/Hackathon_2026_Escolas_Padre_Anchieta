# Pinnacle 🏔️

Plataforma web de quizzes multiplayer em tempo real, inspirada no Kahoot!. Desenvolvida durante o Hackathon da Semana Técnica da **Escola Padre Anchieta** (2026).

---

## Funcionalidades

- Criação de quizzes com até 20 perguntas e 4 alternativas cada
- Timer e pontuação configuráveis por pergunta
- Salas ao vivo com código PIN de 6 dígitos
- Multiplayer em tempo real (HTTP polling)
- Pontuação com bônus por velocidade de resposta
- Ranking com pódio dos 3 primeiros
- QR Code para compartilhamento rápido da sala
- Upload de imagens nas perguntas
- Autenticação com cadastro/login e senhas hash (bcrypt)
- Design responsivo (desktop e mobile)

## Tecnologias

| Camada     | Tecnologia                      |
| ---------- | ------------------------------- |
| Frontend   | HTML5, CSS3, JavaScript (vanilla) |
| Backend    | PHP 7.4+ (vanilla)              |
| Banco      | MySQL (PDO)                     |
| QR Code    | chillerlan/php-qrcode           |
| Ícones     | Bootstrap Icons                 |
| Fontes     | Montserrat (Google Fonts)       |

## Estrutura do Projeto

```
├── index.html                # Landing page
├── login.html                # Tela de login
├── register.html             # Tela de cadastro
├── homepage.php              # Dashboard do professor
├── cadastro-pergunta.php     # Criador de quizzes
├── entrar-quiz.php           # Entrada na sala via PIN
├── quiz-player.php           # Tela do jogo (professor/aluno)
├── QR_CODE_SETUP.md          # Configuração do QR Code
│
├── api/
│   ├── salvar_quiz.php       # CRUD de quizzes
│   └── quiz-status.php       # API de estado multiplayer
│
├── auth/
│   ├── login.php             # Processamento de login
│   ├── register.php          # Processamento de cadastro
│   └── logout.php            # Encerramento de sessão
│
├── includes/
│   └── conect.php            # Conexão PDO com MySQL
│
├── professor/
│   └── painel.php            # Painel de controle do professor
│
├── assets/
│   ├── css/                  # Estilos por página
│   ├── js/                   # Lógica frontend
│   ├── img/                  # Imagens do layout
│   ├── sql/
│   │   └── banco.sql         # Schema do banco de dados
│   └── uploads/questions/    # Imagens enviadas nas perguntas
│
└── uploads/                  # Estado das salas (arquivos JSON)
```

## Como Rodar

1. Inicie o **XAMPP** (Apache + MySQL) ou equivalente
2. Acesse `http://localhost/phpmyadmin` e importe o arquivo `assets/sql/banco.sql`
3. Copie o projeto para a pasta `htdocs` do XAMPP
4. Acesse via `http://localhost/Pinnacle`
5. Para usar QR Code na rede local, veja as instruções em `QR_CODE_SETUP.md`

### Configuração do Banco

As credenciais padrão estão em `includes/conect.php`:

| Parâmetro | Valor                                      |
| --------- | ------------------------------------------ |
| Host      | localhost                                  |
| Database  | Hackathon_2026_Escolas_Padre_Anchieta      |
| Usuário   | root                                       |
| Senha     | (vazio)                                    |

## Banco de Dados

- **usuario** — alunos e professores (RA, login, senha bcrypt, tipo)
- **questionarios** — quizzes (código PIN único de 6 caracteres, FK para usuario)
- **perguntas** — questões do quiz (texto, pontos, timer, imagem, FK para questionarios com CASCADE)
- **answers** — alternativas de resposta (texto, correta T/F, FK para perguntas com CASCADE)

## API

### `api/salvar_quiz.php`

- **POST** — Cria ou atualiza um quiz (JSON). Recebe título, código, perguntas e alternativas.

### `api/quiz-status.php`

Gerencia o estado multiplayer via arquivos JSON. Ações disponíveis:

| Ação     | Método | Descrição                                     |
| -------- | ------ | --------------------------------------------- |
| join     | POST   | Entrar na sala                                |
| start    | POST   | Iniciar o quiz                                |
| status   | GET    | Obter estado atual (jogadores, pergunta, etc) |
| answer   | POST   | Enviar resposta                               |
| advance  | POST   | Avançar para próxima pergunta                 |
| submit   | POST   | Enviar pontuação final                        |
| reset    | POST   | Reiniciar a sala                              |

## Sobre o Desafio

Projeto desenvolvido como solução para um Hackathon com divisão por séries:

- **1º Ano** — Frontend (HTML, CSS, responsividade, identidade visual)
- **2º Ano** — Backend (PHP, lógica do sistema, integração com banco)
- **3º Ano** — Gestão (organização, testes, documentação, apresentação)
