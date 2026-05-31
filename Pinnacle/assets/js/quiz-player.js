/**
 * quiz-player.js
 * Lógica principal do jogo multiplayer.
 * Gerencia: conexão à sala, timer, respostas, pontuação,
 * feedback, polling de estado, controle do professor e ranking.
 */

/* ========================================
   CONFIG & DOM REFERENCES
   ======================================== */

const quiz = window.quizData || {
  title: "Quiz",
  code: "TEMP",
  questions: [],
};

const userRole = window.userRole || "student";
const userId = window.userId || null;
let quizCode = quiz.code || "TEMP";
let playerId = null;
let playerName = "";

const quizTitle = document.getElementById("quiz-title");
const questionCounter = document.getElementById("question-counter");
const questionTitle = document.getElementById("question-title");
const timerElement = document.getElementById("timer");
const answerButtons = document.querySelectorAll(".answer-btn");
const feedbackModal = document.getElementById("feedback-modal");
const feedbackTitle = document.getElementById("feedback-title");
const feedbackPoints = document.getElementById("feedback-points");
const nextQuestionBtn = document.getElementById("next-question-btn");
const rankingScreen = document.getElementById("ranking-screen");
const rankingPodium = document.getElementById("ranking-podium");

const questionImageContainer = document.getElementById("question-image-container");
const questionImage = document.getElementById("question-image");
const playerScore = document.getElementById("player-score");
const waitingRoom = document.getElementById("waiting-room");
const professorPanel = document.getElementById("professor-panel");
const waitingCode = document.getElementById("waiting-code");
const waitingPlayers = document.getElementById("waiting-players");
const professorPlayers = document.getElementById("professor-players");
const professorList = document.getElementById("professor-list");
const quizHeader = document.querySelector(".quiz-header");
const questionSection = document.querySelector(".question-section");
const professorQuizBar = document.getElementById("professor-quiz-bar");
const profAnswerCount = document.getElementById("prof-answer-count");
const profAdvanceBtn = document.getElementById("prof-advance-btn");
const profQuestionStatus = document.getElementById("prof-question-status");

let currentQuestion = 0;
let score = 0;
let timer = null;
let timeLeft = 0;
let totalTimeSpent = 0;
let quizStarted = false;
let statusCheckInterval = null;
let advancePollInterval = null;
let hasAnswered = false;
let timerExpired = false;
let professorMonitorInterval = null;
let professorAdvanceInterval = null;

if (quiz.questions.length > 0) {
  initializeQuiz();
}

/* ========================================
   INICIALIZAÇÃO
   ======================================== */

function initializeQuiz() {
  const formData = new FormData();

  if (userRole === "professor") {
    playerName = "Professor";
    formData.append("playerName", "Professor");
  } else {
    playerName = prompt("Digite seu nome:");
    if (!playerName || !playerName.trim()) {
      playerName = "Aluno";
    }
    formData.append("playerName", playerName);
  }

  fetch(`api/quiz-status.php?code=${quizCode}&action=join`, {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      if (!res.ok) throw new Error(`Erro HTTP ${res.status}`);
      return res.json();
    })
    .then((data) => {
      if (data.success) {
        playerId = data.playerId;
        if (userRole === "professor") {
          showProfessorPanel();
          startProfessorMonitoring();
        } else {
          showWaitingRoom();
          startStatusCheck();
        }
      }
    })
    .catch((err) => {
      console.error("Erro ao inicializar:", err);
      quizTitle.textContent = quiz.title;
      quizHeader.classList.remove("hidden");
      questionSection.classList.remove("hidden");
      if (waitingRoom) waitingRoom.classList.add("hidden");
      if (professorPanel) professorPanel.classList.add("hidden");
      loadQuestion();
    });
}

function showProfessorPanel() {
  if (quizHeader) quizHeader.classList.add("hidden");
  if (questionSection) questionSection.classList.add("hidden");
  if (waitingRoom) waitingRoom.classList.add("hidden");
  if (feedbackModal) feedbackModal.classList.add("hidden");
  rankingScreen.classList.add("hidden");
  if (professorPanel) professorPanel.classList.remove("hidden");
  waitingCode.textContent = quizCode;
}

function showWaitingRoom() {
  if (quizHeader) quizHeader.classList.add("hidden");
  if (questionSection) questionSection.classList.add("hidden");
  if (feedbackModal) feedbackModal.classList.add("hidden");
  rankingScreen.classList.add("hidden");
  if (waitingRoom) waitingRoom.classList.remove("hidden");
  if (professorPanel) professorPanel.classList.add("hidden");
  waitingCode.textContent = quizCode;
}

/* ========================================
   POLLING / MONITORAMENTO
   ======================================== */

function startProfessorMonitoring() {
  setInterval(() => {
    fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
      .then((res) => {
        if (!res.ok) throw new Error(`Erro HTTP`);
        return res.json();
      })
      .then((data) => {
        const players = data.players || {};
        const playerCount = Object.keys(players).length;
        professorPlayers.textContent = playerCount;
        professorList.innerHTML = "";
        if (playerCount === 0) {
          professorList.innerHTML = '<div class="player-item"><span>Nenhum aluno conectado ainda...</span></div>';
        } else {
          Object.values(players).forEach((player) => {
            if (player.name !== "Professor") {
              const div = document.createElement("div");
              div.className = "player-item";
              div.innerHTML = `<i class="bi bi-person-check-fill"></i><span>${player.name}</span>`;
              professorList.appendChild(div);
            }
          });
        }
      })
      .catch(() => {});
  }, 1000);
}

function startStatusCheck() {
  statusCheckInterval = setInterval(() => {
    fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
      .then((res) => {
        if (!res.ok) throw new Error(`Erro HTTP`);
        return res.json();
      })
      .then((data) => {
        const playersCount = Object.keys(data.players || {}).length;
        if (waitingPlayers) waitingPlayers.textContent = playersCount;
        if (data.started && !quizStarted) {
          quizStarted = true;
          clearInterval(statusCheckInterval);
          if (waitingRoom) waitingRoom.classList.add("hidden");
          if (quizHeader) quizHeader.classList.remove("hidden");
          if (questionSection) questionSection.classList.remove("hidden");
          quizTitle.textContent = quiz.title;
          loadQuestion();
        }
      })
      .catch(() => {});
  }, 1000);
}

/* ========================================
   CONTROLE DO PROFESSOR (INICIAR)
   ======================================== */

function startQuiz() {
  const btn = event.target;
  btn.disabled = true;
  btn.textContent = "Iniciando...";
  fetch(`api/quiz-status.php?code=${quizCode}&action=start`, {
    method: "POST",
    body: new FormData(),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        quizStarted = true;
        if (professorPanel) professorPanel.classList.add("hidden");
        if (quizHeader) quizHeader.classList.remove("hidden");
        if (questionSection) questionSection.classList.remove("hidden");
        if (professorQuizBar) professorQuizBar.classList.remove("hidden");
        quizTitle.textContent = quiz.title;
        loadQuestion();
        startProfessorQuizControls();
      }
    })
    .catch((err) => {
      console.error("Erro ao iniciar quiz:", err);
      btn.disabled = false;
      btn.textContent = "Iniciar Quiz";
    });
}

/* ========================================
   CARREGAMENTO DE PERGUNTAS
   ======================================== */

function loadQuestion() {
  resetAnswerStyles();
  hasAnswered = false;
  timerExpired = false;

  const question = quiz.questions[currentQuestion];
  if (!question) {
    finishQuiz();
    return;
  }

  questionTimeLimit = Number(question.time) || 20;

  if (questionCounter) {
    questionCounter.textContent = `Pergunta ${currentQuestion + 1} de ${quiz.questions.length}`;
  }
  if (questionTitle) {
    questionTitle.textContent = question.question;
  }
  if (question.image && questionImage) {
    questionImage.src = question.image;
    if (questionImageContainer) questionImageContainer.classList.remove("hidden");
  } else {
    if (questionImage) questionImage.removeAttribute("src");
    if (questionImageContainer) questionImageContainer.classList.add("hidden");
  }

  answerButtons.forEach((button, index) => {
    const answer = question.answers[index];
    button.classList.toggle("hidden", !answer);
    button.querySelector(".answer-text").textContent = answer ? answer.text : "";
    if (userRole === "professor") {
      button.disabled = true;
    }
  });

  startTimer(questionTimeLimit);
}

/* ========================================
   TIMER
   ======================================== */

function startTimer(seconds) {
  clearInterval(timer);
  const timerCircle = document.getElementById("timer-circle");
  if (timerCircle) {
    timerCircle.classList.remove("timer-warning", "timer-danger");
  }
  const questionStartTime = Date.now();
  timer = setInterval(() => {
    const elapsed = Date.now() - questionStartTime;
    timeLeft = Math.max(0, seconds - Math.floor(elapsed / 1000));
    totalTimeSpent++;
    if (timerElement) timerElement.textContent = timeLeft;
    if (timerCircle) {
      timerCircle.classList.toggle("timer-warning", timeLeft <= 10 && timeLeft > 5);
      timerCircle.classList.toggle("timer-danger", timeLeft <= 5);
    }
    if (timeLeft <= 0) {
      clearInterval(timer);
      timerExpired = true;
      answerButtons.forEach((btn) => {
        btn.disabled = true;
      });
      if (userRole !== "professor" && !hasAnswered) {
        answerButtons.forEach((btn, index) => {
          const q = quiz.questions[currentQuestion];
          if (q.answers[index] && q.answers[index].correct) {
            btn.classList.add("correct-answer");
          }
        });
        hasAnswered = true;
        showFeedback(false, 0);
        submitAnswerToServer(false, 0);
        startAdvancePolling();
      }
      if (userRole === "professor") {
        professorCheckAdvanceReady();
      }
    }
  }, 1000);
}

/* ========================================
   RESPOSTAS
   ======================================== */

answerButtons.forEach((button) => {
  button.addEventListener("click", () => {
    if (userRole === "professor" || hasAnswered || timerExpired) return;
    handleAnswer(button);
  });
});

function handleAnswer(button) {
  hasAnswered = true;
  clearInterval(timer);

  answerButtons.forEach((btn) => {
    btn.disabled = true;
  });

  const answerIndex = Number(button.dataset.index);
  const question = quiz.questions[currentQuestion];
  const selectedAnswer = question.answers[answerIndex];

  if (!selectedAnswer) {
    showFeedback(false, 0);
    return;
  }

  const isCorrect = selectedAnswer.correct;
  let earnedPoints = 0;

  if (isCorrect) {
    earnedPoints = Math.max(
      Number(question.points) || 10,
      (Number(question.points) || 10) * (timeLeft / questionTimeLimit)
    );
    score += earnedPoints;
    if (playerScore) playerScore.textContent = `${Math.floor(score)} pts`;
    button.classList.add("correct-answer");
  } else {
    button.classList.add("wrong-answer");
  }

  answerButtons.forEach((btn, index) => {
    if (question.answers[index] && question.answers[index].correct) {
      btn.classList.add("correct-answer");
    }
  });

  showFeedback(isCorrect, earnedPoints);
  submitAnswerToServer(isCorrect, earnedPoints);
  startAdvancePolling();
}

function submitAnswerToServer(isCorrect, earnedPoints) {
  if (!playerId) return;
  const answerData = new FormData();
  answerData.append("playerId", playerId);
  answerData.append("questionIndex", currentQuestion);
  answerData.append("correct", isCorrect ? "1" : "0");
  answerData.append("points", earnedPoints);
  answerData.append("timeLeft", timeLeft);

  fetch(`api/quiz-status.php?code=${quizCode}&action=answer`, {
    method: "POST",
    body: answerData,
  }).catch(() => {});
}

/* ========================================
   AVANÇO DE PERGUNTAS
   ======================================== */

function startAdvancePolling() {
  if (advancePollInterval) clearInterval(advancePollInterval);
  if (nextQuestionBtn) {
    nextQuestionBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Aguardando professor...';
    nextQuestionBtn.disabled = true;
  }
  advancePollInterval = setInterval(() => {
    fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
      .then((res) => res.json())
      .then((data) => {
        if (data.currentQuestion > currentQuestion) {
          clearInterval(advancePollInterval);
          advancePollInterval = null;
          advanceToNextQuestion(data.currentQuestion);
        }
      })
      .catch(() => {});
  }, 1000);
}

function advanceToNextQuestion(newQuestionIndex) {
  feedbackModal.classList.add("hidden");
  currentQuestion = newQuestionIndex !== undefined ? newQuestionIndex : currentQuestion + 1;
  hasAnswered = false;
  timerExpired = false;
  if (currentQuestion >= quiz.questions.length) {
    finishQuiz();
    return;
  }
  loadQuestion();
}

if (nextQuestionBtn) {
  nextQuestionBtn.addEventListener("click", () => {
    if (nextQuestionBtn.disabled) return;
    feedbackModal.classList.add("hidden");
    currentQuestion++;
    hasAnswered = false;
    timerExpired = false;
    if (currentQuestion >= quiz.questions.length) {
      finishQuiz();
      return;
    }
    loadQuestion();
  });
}

/* ========================================
   FEEDBACK
   ======================================== */

function showFeedback(isCorrect, points) {
  if (feedbackModal) feedbackModal.classList.remove("hidden");
  const feedbackIcon = document.getElementById("feedback-icon");
  if (feedbackTitle) {
    feedbackTitle.textContent = isCorrect ? "Resposta Correta!" : "Resposta Errada!";
  }
  if (feedbackPoints) {
    feedbackPoints.textContent = isCorrect ? `+${Math.floor(points)} pontos` : "0 pontos";
  }
  if (feedbackIcon) {
    feedbackIcon.innerHTML = isCorrect
      ? '<i class="bi bi-check-circle-fill"></i>'
      : '<i class="bi bi-x-circle-fill"></i>';
    feedbackIcon.style.color = isCorrect ? "#00c76a" : "#ff3355";
  }
}

function resetAnswerStyles() {
  answerButtons.forEach((button) => {
    button.classList.remove("correct-answer", "wrong-answer");
    button.disabled = false;
  });
}

/* ========================================
   CONTROLES DO PROFESSOR NO QUIZ
   ======================================== */

function startProfessorQuizControls() {
  if (professorMonitorInterval) clearInterval(professorMonitorInterval);
  if (professorAdvanceInterval) clearInterval(professorAdvanceInterval);

  professorMonitorInterval = setInterval(() => {
    fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
      .then((res) => res.json())
      .then((data) => {
        const players = data.players || {};
        const studentPlayers = Object.values(players).filter(p => p.name !== "Professor");
        const studentCount = studentPlayers.length;
        const answersForQuestion = data.answers && data.answers[currentQuestion]
          ? Object.keys(data.answers[currentQuestion]).length
          : 0;

        if (profAnswerCount) {
          profAnswerCount.textContent = `${answersForQuestion} / ${studentCount}`;
        }
        if (profQuestionStatus) {
          if (studentCount === 0) {
            profQuestionStatus.textContent = "Nenhum aluno conectado";
            profQuestionStatus.style.color = "#ff9800";
          } else if (answersForQuestion >= studentCount) {
            profQuestionStatus.textContent = "Todos responderam!";
            profQuestionStatus.style.color = "#00c76a";
            if (profAdvanceBtn) profAdvanceBtn.disabled = false;
          } else {
            profQuestionStatus.textContent = "Aguardando respostas...";
            profQuestionStatus.style.color = "#ff9800";
            if (profAdvanceBtn) profAdvanceBtn.disabled = true;
          }
        }
        if (timerExpired && profAdvanceBtn) {
          profAdvanceBtn.disabled = false;
        }
      })
      .catch(() => {});
  }, 1000);

  if (profAdvanceBtn) {
    profAdvanceBtn.onclick = function () {
      professorAdvance();
    };
  }
}

function professorCheckAdvanceReady() {
  fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
    .then((res) => res.json())
    .then((data) => {
      const players = data.players || {};
      const studentCount = Object.values(players).filter(p => p.name !== "Professor").length;
      const answersForQuestion = data.answers && data.answers[currentQuestion]
        ? Object.keys(data.answers[currentQuestion]).length
        : 0;
      if (answersForQuestion >= studentCount || timerExpired) {
        if (profAdvanceBtn) profAdvanceBtn.disabled = false;
      }
    })
    .catch(() => {});
}

function professorAdvance() {
  if (profAdvanceBtn) {
    profAdvanceBtn.disabled = true;
    profAdvanceBtn.textContent = "Avançando...";
  }

  const isLastQuestion = currentQuestion + 1 >= quiz.questions.length;
  const formData = new FormData();
  formData.append("password", "professor123");

  fetch(`api/quiz-status.php?code=${quizCode}&action=advance`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        if (isLastQuestion) {
          finishQuiz();
          return;
        }
        currentQuestion = data.currentQuestion;
        if (profAdvanceBtn) {
          profAdvanceBtn.textContent = "Avançar Pergunta";
        }
        loadQuestion();
      }
    })
    .catch(() => {
      if (profAdvanceBtn) {
        profAdvanceBtn.disabled = false;
        profAdvanceBtn.textContent = "Avançar Pergunta";
      }
    });
}

/* ========================================
   MENU DO USUÁRIO (DROPDOWN)
   ======================================== */

const userButton = document.getElementById("user-button");
const userDropdown = document.getElementById("user-dropdown");

if (userButton && userDropdown) {
  userButton.addEventListener("click", () => {
    userDropdown.classList.toggle("active");
  });
  document.addEventListener("click", (event) => {
    if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) {
      userDropdown.classList.remove("active");
    }
  });
}

/* ========================================
   FINALIZAÇÃO / RANKING
   ======================================== */

function finishQuiz() {
  if (questionSection) questionSection.classList.add("hidden");
  if (quizHeader) quizHeader.classList.add("hidden");
  if (feedbackModal) feedbackModal.classList.add("hidden");
  if (professorQuizBar) professorQuizBar.classList.add("hidden");
  if (professorMonitorInterval) clearInterval(professorMonitorInterval);
  if (professorAdvanceInterval) clearInterval(professorAdvanceInterval);
  if (advancePollInterval) clearInterval(advancePollInterval);
  rankingScreen.classList.remove("hidden");

  rankingPodium.innerHTML = '<div class="waiting-spinner" style="font-size:40px;"><i class="bi bi-hourglass-split"></i></div><p style="color:#666;font-weight:600;margin-top:15px;">Carregando ranking...</p>';

  if (userRole !== "professor" && playerId) {
    const formData = new FormData();
    formData.append("playerId", playerId);
    formData.append("name", playerName);
    formData.append("score", Math.floor(score));
    formData.append("timeSpent", totalTimeSpent);
    fetch(`api/quiz-status.php?code=${quizCode}&action=submit`, {
      method: "POST",
      body: formData,
    }).catch(() => {});
  }

  if (userRole === "professor") {
    fetchAndDisplayRanking();
  } else {
    setTimeout(fetchAndDisplayRanking, 1000);
  }
}

function fetchAndDisplayRanking() {
  fetch(`api/quiz-status.php?code=${quizCode}&action=status`)
    .then((res) => res.json())
    .then((data) => {
      const results = data.results || [];
      const players = data.players || {};
      const studentCount = Object.values(players).filter(p => p.name !== "Professor").length;

      if (results.length < studentCount && studentCount > 0) {
        rankingPodium.innerHTML = `<div class="waiting-spinner" style="font-size:40px;"><i class="bi bi-hourglass-split"></i></div><p style="color:#666;font-weight:600;margin-top:15px;">Aguardando todos os alunos finalizarem... (${results.length}/${studentCount})</p>`;
        setTimeout(fetchAndDisplayRanking, 2000);
        return;
      }

      results.sort((a, b) => {
        if (b.score !== a.score) return b.score - a.score;
        return a.totalTimeSpent - b.totalTimeSpent;
      });

      displayRanking(results);
    })
    .catch(() => {
      setTimeout(fetchAndDisplayRanking, 2000);
    });
}

function displayRanking(results) {
  rankingPodium.innerHTML = "";
  const top3 = results.slice(0, 3);
  const classes = ["first", "second", "third"];

  top3.forEach((player, index) => {
    const card = document.createElement("div");
    card.classList.add("podium-card", classes[index]);
    card.innerHTML = `
      <h2>#${index + 1}</h2>
      <h3>${player.name}</h3>
      <p>${Math.floor(player.score)} pts</p>
      <span>${player.totalTimeSpent || 0}s</span>
    `;
    rankingPodium.appendChild(card);
  });

  if (results.length === 0) {
    rankingPodium.innerHTML = '<p style="color:#666;font-weight:600;">Nenhum resultado disponivel.</p>';
    return;
  }

  if (results.length > 3) {
    const othersList = document.createElement("div");
    othersList.style.cssText = "margin-top:30px;text-align:left;max-width:500px;margin-left:auto;margin-right:auto;width:100%;";
    othersList.innerHTML = "<h3 style='color:#0023ff;margin-bottom:15px;font-size:20px;'>Outros Participantes</h3>";
    results.slice(3).forEach((player, index) => {
      const row = document.createElement("div");
      row.style.cssText = "display:flex;justify-content:space-between;padding:12px 18px;background:#f4f7ff;border-radius:12px;margin-bottom:8px;font-weight:600;color:#333;";
      row.innerHTML = `<span>#${index + 4} - ${player.name}</span><span>${Math.floor(player.score)} pts</span>`;
      othersList.appendChild(row);
    });
    rankingPodium.appendChild(othersList);
  }

}

function exitQuiz() {
  if (confirm("Tem certeza que deseja sair do quiz?")) {
    window.location.href = "homepage.php";
  }
}
