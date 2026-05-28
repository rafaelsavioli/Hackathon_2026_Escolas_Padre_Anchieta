/*
========================================
Pinnacle Quiz Player
========================================
*/

/* ========================================
   PEGAR QUIZ
======================================== */

/* ========================================
   QUIZ TEMPORÁRIO
======================================== */

const quiz = {
  title: "Quiz de Conhecimentos Gerais",

  questions: [
    {
      question: "Qual é a capital do Brasil?",

      time: 20,

      points: 100,

      image: "",

      answers: [
        {
          text: "Brasília",

          correct: true,
        },

        {
          text: "São Paulo",

          correct: false,
        },

        {
          text: "Rio de Janeiro",

          correct: false,
        },

        {
          text: "Salvador",

          correct: false,
        },
      ],
    },

    {
      question: "Qual planeta é conhecido como planeta vermelho?",

      time: 20,

      points: 100,

      image: "",

      answers: [
        {
          text: "Marte",

          correct: true,
        },

        {
          text: "Terra",

          correct: false,
        },

        {
          text: "Júpiter",

          correct: false,
        },

        {
          text: "Saturno",

          correct: false,
        },
      ],
    },
  ],
};

const quizCode = "TEMP";

/* ========================================
   ELEMENTOS
======================================== */

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

const questionImageContainer = document.getElementById(
  "question-image-container",
);

const questionImage = document.getElementById("question-image");
const playerScore = document.getElementById("player-score");

/* ========================================
   ESTADO
======================================== */

let currentQuestion = 0;

let score = 0;

let timer = null;

let timeLeft = 0;

let totalTimeSpent = 0;

/* ========================================
   NOME DO ALUNO
======================================== */

let playerName = prompt("Digite seu nome:");

if (!playerName || !playerName.trim()) {
  playerName = "Aluno";
}

/* ========================================
   INICIAR
======================================== */

quizTitle.textContent = quiz.title;

loadQuestion();

/* ========================================
   CARREGAR PERGUNTA
======================================== */

function loadQuestion() {
  resetAnswerStyles();

  const question = quiz.questions[currentQuestion];

  /*
    CONTADOR
  */

  questionCounter.textContent = `Pergunta ${currentQuestion + 1} de ${
    quiz.questions.length
  }`;

  /*
    TEXTO
  */

  questionTitle.textContent = question.question;

  /*
    IMAGEM
  */

  if (question.image) {
    questionImage.src = question.image;

    questionImageContainer.classList.remove("hidden");
  } else {
    questionImageContainer.classList.add("hidden");
  }

  /*
    RESPOSTAS
  */

  answerButtons.forEach((button, index) => {
    button.querySelector(".answer-text").textContent =
      question.answers[index].text;
  });

  /*
    TIMER
  */

  startTimer(question.time);
}

/* ========================================
   TIMER
======================================== */

function startTimer(seconds) {
  clearInterval(timer);

  timeLeft = seconds;

  timerElement.textContent = timeLeft;
  const timerCircle = document.getElementById("timer-circle");

  timerCircle.classList.remove(
    "timer-warning",
    "timer-danger",
  );

  if (timeLeft <= 10) {
    timerCircle.classList.add("timer-warning");
  }

  if (timeLeft <= 5) {
    timerCircle.classList.remove("timer-warning");

    timerCircle.classList.add("timer-danger");
  }

  timer = setInterval(() => {
    timeLeft--;

    totalTimeSpent++;

    timerElement.textContent = timeLeft;

    if (timeLeft <= 0) {
      clearInterval(timer);

      showFeedback(false, 0);
    }
  }, 1000);
}

/* ========================================
   RESPOSTA
======================================== */

answerButtons.forEach((button) => {
  button.addEventListener("click", () => {
    handleAnswer(button);
  });
});

/* ========================================
   PROCESSAR RESPOSTA
======================================== */

function handleAnswer(button) {
  clearInterval(timer);

  /*
    DESABILITAR
  */

  answerButtons.forEach((btn) => {
    btn.disabled = true;
  });

  const answerIndex = Number(button.dataset.index);

  const question = quiz.questions[currentQuestion];

  const selectedAnswer = question.answers[answerIndex];

  /*
    CORRETAS
  */

  const correctAnswers = question.answers.filter((answer) => answer.correct);

  /*
    CORRETA?
  */

  const isCorrect = selectedAnswer.correct;

  /*
    PONTOS
  */

  let earnedPoints = 0;

  if (isCorrect) {
    earnedPoints = Math.max(100, question.points * timeLeft);

    score += earnedPoints;
    playerScore.textContent = `${score} pts`;

    button.classList.add("correct-answer");
  } else {
    button.classList.add("wrong-answer");
  }

  /*
    MOSTRAR CORRETAS
  */

  answerButtons.forEach((btn, index) => {
    if (question.answers[index].correct) {
      btn.classList.add("correct-answer");
    }
  });

  /*
    FEEDBACK
  */

  showFeedback(isCorrect, earnedPoints);
}

/* ========================================
   FEEDBACK
======================================== */

function showFeedback(isCorrect, points) {
  feedbackModal.classList.remove("hidden");

  const feedbackIcon =
  document.getElementById("feedback-icon");

  feedbackTitle.textContent = isCorrect
    ? "Resposta Correta!"
    : "Resposta Errada!";

  feedbackPoints.textContent = isCorrect
    ? `+${points} pontos`
    : "0 pontos";

  feedbackIcon.innerHTML = isCorrect
    ? '<i class="bi bi-check-circle-fill"></i>'
    : '<i class="bi bi-x-circle-fill"></i>';

  feedbackIcon.style.color = isCorrect
    ? "#00c76a"
    : "#ff3355";
}

/* ========================================
   PRÓXIMA
======================================== */

nextQuestionBtn.addEventListener("click", () => {
  feedbackModal.classList.add("hidden");

  currentQuestion++;

  /*
      FINALIZAR
    */

  if (currentQuestion >= quiz.questions.length) {
    finishQuiz();

    return;
  }

  /*
      HABILITAR
    */

  answerButtons.forEach((btn) => {
    btn.disabled = false;
  });

  loadQuestion();
});

/* ========================================
   RESETAR ESTILOS
======================================== */

function resetAnswerStyles() {
  answerButtons.forEach((button) => {
    button.classList.remove("correct-answer");

    button.classList.remove("wrong-answer");

    button.disabled = false;
  });
}

/* ========================================
   USER DROPDOWN
======================================== */

const userButton = document.getElementById("user-button");

const userDropdown =
  document.getElementById("user-dropdown");

userButton.addEventListener("click", () => {
  userDropdown.classList.toggle("active");
});

document.addEventListener("click", (event) => {
  if (
    !userButton.contains(event.target) &&
    !userDropdown.contains(event.target)
  ) {
    userDropdown.classList.remove("active");
  }
});

/* ========================================
   FINALIZAR QUIZ
======================================== */

function finishQuiz() {
  /*
    ESCONDER QUIZ
  */

  document.querySelector(".question-section").classList.add("hidden");

  document.querySelector(".quiz-header").classList.add("hidden");

  feedbackModal.classList.add("hidden");

  /*
    MOSTRAR RANKING
  */

  rankingScreen.classList.remove("hidden");

  /*
    PLAYER
  */

  const playerData = {
    name: playerName,

    score,

    totalTimeSpent,
  };

  /*
    PEGAR RANKING
  */

  const ranking = JSON.parse(localStorage.getItem(`ranking_${quizCode}`)) || [];

  /*
    ADICIONAR
  */

  ranking.push(playerData);

  /*
    ORDENAR
  */

  ranking.sort((a, b) => {
    /*
      MAIOR PONTUAÇÃO
    */

    if (b.score !== a.score) {
      return b.score - a.score;
    }

    /*
      MENOR TEMPO
    */

    return a.totalTimeSpent - b.totalTimeSpent;
  });

  /*
    SALVAR
  */

  localStorage.setItem(`ranking_${quizCode}`, JSON.stringify(ranking));

  /*
    TOP 3
  */

  const top3 = ranking.slice(0, 3);

  /*
    RENDERIZAR
  */

  rankingPodium.innerHTML = "";

  const classes = ["first", "second", "third"];

  top3.forEach((player, index) => {
    const card = document.createElement("div");

    card.classList.add("podium-card");

    card.classList.add(classes[index]);

    card.innerHTML = `
        <h2>
          #${index + 1}
        </h2>

        <h3>
          ${player.name}
        </h3>

        <p>
          ${player.score} pts
        </p>

        <span>
          ${player.totalTimeSpent}s
        </span>
      `;

    rankingPodium.appendChild(card);
  });
}
