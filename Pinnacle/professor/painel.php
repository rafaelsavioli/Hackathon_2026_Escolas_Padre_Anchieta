<?php
/**
 * Painel de controle do professor (autônomo).
 * Permite inserir código do quiz e senha para iniciar/resetar
 * o quiz e visualizar alunos conectados.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Painel do Professor - Pinnacle</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Montserrat", sans-serif; }
        body { background: #f4f7ff; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { height: 80px; background: #0023ff; border-bottom: 4px solid #7b8cff; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; color: white; }
        .logo img { height: 68px; }
        .main { flex: 1; padding: 40px; display: flex; justify-content: center; align-items: center; }
        .panel { background: white; border-radius: 35px; padding: 50px; box-shadow: 0 8px 25px rgba(0, 35, 255, 0.12); width: 100%; max-width: 600px; }
        .panel h1 { font-size: 42px; color: #0023ff; margin-bottom: 15px; text-align: center; }
        .panel p { color: #666; text-align: center; margin-bottom: 30px; font-weight: 600; }
        .form-group { margin-bottom: 25px; }
        label { display: block; color: #0023ff; font-weight: 700; margin-bottom: 10px; }
        input { width: 100%; padding: 16px; border: 2px solid #e0e0e0; border-radius: 15px; font-size: 16px; font-weight: 600; transition: 0.2s; }
        input:focus { outline: none; border-color: #0023ff; box-shadow: 0 0 0 3px rgba(0, 35, 255, 0.1); }
        .button-group { display: flex; gap: 15px; margin-top: 35px; }
        button { flex: 1; padding: 16px; border: none; border-radius: 15px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-start { background: #00c76a; color: white; }
        .btn-start:hover { transform: scale(1.05); box-shadow: 0 8px 20px rgba(0, 199, 106, 0.3); }
        .btn-reset { background: #ff3355; color: white; }
        .btn-reset:hover { transform: scale(1.05); box-shadow: 0 8px 20px rgba(255, 51, 85, 0.3); }
        .player-list { background: #f4f7ff; border-radius: 20px; padding: 25px; margin-top: 30px; }
        .player-list h3 { color: #0023ff; margin-bottom: 15px; font-size: 20px; }
        .player-item { background: white; padding: 12px 18px; border-radius: 12px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .player-item i { color: #00c76a; font-size: 18px; }
        .player-item span { color: #333; font-weight: 600; }
        .status-badge { padding: 8px 16px; border-radius: 20px; font-weight: 700; text-align: center; margin-bottom: 20px; font-size: 14px; }
        .status-waiting { background: #fff4e6; color: #ff9800; }
        .status-started { background: #e8f5e9; color: #00c76a; }
        .footer { height: 60px; background: #0023ff; border-top: 4px solid #7b8cff; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; }
        .message { padding: 12px; border-radius: 10px; margin-bottom: 15px; text-align: center; font-weight: 600; display: none; }
        .message.success { background: #e8f5e9; color: #00c76a; display: block; }
        .message.error { background: #ffebee; color: #ff3355; display: block; }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <a href="../homepage.php">
                <img src="../assets/img/Pinnacle - Logo.png" alt="Pinnacle Logo" />
            </a>
        </div>
        <span style="font-size: 18px; font-weight: 700;">Painel do Professor</span>
        <div></div>
    </header>

    <main class="main">
        <div class="panel">
            <h1>Controlar Quiz</h1>
            <p>Gerenciar sala de alunos e iniciar a prova</p>

            <div class="message" id="message"></div>

            <div class="form-group">
                <label for="quizCode">Codigo do Quiz:</label>
                <input type="text" id="quizCode" placeholder="Ex: QUIZ001" maxlength="20" />
            </div>

            <div class="form-group">
                <label for="password">Senha do Professor:</label>
                <input type="password" id="password" placeholder="Digite a senha" />
            </div>

            <div class="status-badge" id="statusBadge">Carregando...</div>

            <div class="button-group">
                <button class="btn-start" onclick="startQuiz()">
                    <i class="bi bi-play-fill"></i> Iniciar Quiz
                </button>
                <button class="btn-reset" onclick="resetQuiz()">
                    <i class="bi bi-arrow-clockwise"></i> Resetar
                </button>
            </div>

            <div class="player-list">
                <h3>Alunos Conectados (<span id="playerCount">0</span>)</h3>
                <div id="playersList"></div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <span>Pinnacle&trade;</span>
    </footer>

    <script>
        let quizCode = "";
        let quizStarted = false;
        const urlParams = new URLSearchParams(window.location.search);
        const codeFromURL = urlParams.get("code");
        if (codeFromURL) {
            document.getElementById("quizCode").value = codeFromURL;
            quizCode = codeFromURL;
            loadStatus();
            setInterval(loadStatus, 2000);
        }

        document.getElementById("quizCode").addEventListener("change", (e) => {
            quizCode = e.target.value.trim();
            if (quizCode) {
                loadStatus();
                setInterval(loadStatus, 2000);
            }
        });

        function showMessage(text, type) {
            const el = document.getElementById("message");
            el.textContent = text;
            el.className = "message " + type;
            setTimeout(() => { el.className = "message"; }, 3000);
        }

        function loadStatus() {
            if (!quizCode) return;
            fetch("../api/quiz-status.php?code=" + quizCode + "&action=status")
                .then((r) => r.json())
                .then((data) => {
                    quizStarted = data.started || false;
                    const badge = document.getElementById("statusBadge");
                    badge.className = quizStarted ? "status-badge status-started" : "status-badge status-waiting";
                    badge.textContent = quizStarted ? "✓ Quiz Iniciado" : "⏳ Aguardando Inicializacao";

                    const players = data.players || {};
                    const list = document.getElementById("playersList");
                    const count = document.getElementById("playerCount");
                    count.textContent = Object.keys(players).length;
                    list.innerHTML = Object.keys(players).length === 0
                        ? '<div class="player-item"><span>Nenhum aluno conectado ainda...</span></div>'
                        : Object.values(players).map((p) => '<div class="player-item"><i class="bi bi-person-check-fill"></i><span>' + p.name + '</span></div>').join("");
                });
        }

        function startQuiz() {
            if (!quizCode) { showMessage("Digite o codigo do quiz", "error"); return; }
            const password = document.getElementById("password").value;
            if (!password) { showMessage("Digite a senha do professor", "error"); return; }
            const fd = new FormData();
            fd.append("password", password);
            fetch("../api/quiz-status.php?code=" + quizCode + "&action=start", { method: "POST", body: fd })
                .then((r) => r.json())
                .then((d) => { d.success ? (showMessage("Quiz iniciado!", "success"), loadStatus()) : showMessage("Senha incorreta", "error"); })
                .catch(() => showMessage("Erro ao iniciar quiz", "error"));
        }

        function resetQuiz() {
            if (!quizCode) { showMessage("Digite o codigo do quiz", "error"); return; }
            if (!confirm("Tem certeza que deseja resetar o quiz?")) return;
            const password = document.getElementById("password").value;
            if (!password) { showMessage("Digite a senha do professor", "error"); return; }
            const fd = new FormData();
            fd.append("password", password);
            fetch("../api/quiz-status.php?code=" + quizCode + "&action=reset", { method: "POST", body: fd })
                .then((r) => r.json())
                .then((d) => { d.success ? (showMessage("Quiz resetado!", "success"), loadStatus()) : showMessage("Senha incorreta", "error"); })
                .catch(() => showMessage("Erro ao resetar quiz", "error"));
        }
    </script>
</body>
</html>
