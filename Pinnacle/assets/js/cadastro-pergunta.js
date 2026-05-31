/*
========================================
Pinnacle Quiz Creator
========================================

Sistema responsável por:

- Gerenciar perguntas
- Armazenar quiz em memória
- Upload de imagem
- Alternativas corretas
- Navegação entre perguntas

Preparado para futura integração:
- Banco de dados
- API
- FTP
- Firebase
- Backend Node/PHP/etc

========================================
*/

/* ========================================
   CONFIGURAÇÕES GLOBAIS
======================================== */

const MAX_QUESTIONS = 20;

/* ========================================
   ESTRUTURA DO QUIZ
======================================== */

const quiz = {
  title: "",

  questions: [createEmptyQuestion()],
};

let editingQuizId = null;

/* ========================================
   ESTADO ATUAL
======================================== */

let currentQuestionIndex = 0;

/* ========================================
   ELEMENTOS DO DOM
======================================== */

const questionList = document.getElementById("question-list");

const addQuestionBtn = document.getElementById("add-question-btn");

const quizTitleInput = document.getElementById("quiz-title");

const answerInputs = document.querySelectorAll(".answer-input");

const checkboxes = document.querySelectorAll(".correct-checkbox");

const pointsInput = document.getElementById("question-points");

const timeInput = document.getElementById("question-time");

const questionInput = document.getElementById("question-input");

/* ========================================
   FUNÇÃO CRIAR PERGUNTA VAZIA
======================================== */

function createEmptyQuestion() {
  return {
    question: "",

    points: 10,

    time: 20,

    image: null,

    answers: [
      {
        text: "",
        correct: false,
      },

      {
        text: "",
        correct: false,
      },

      {
        text: "",
        correct: false,
      },

      {
        text: "",
        correct: false,
      },
    ],
  };
}

/* ========================================
   RENDERIZAR SIDEBAR
======================================== */

function renderQuestionList() {
  questionList.innerHTML = "";

  quiz.questions.forEach((question, index) => {
    /*
      CARD
    */

    const card = document.createElement("div");

    card.classList.add("question-card");

    if (index === currentQuestionIndex) {
      card.classList.add("active");
    }

    /*
      CONTEÚDO
    */

    const title = document.createElement("span");

    title.textContent = `Pergunta ${index + 1}`;

    /*
      BOTÃO LIXEIRA
    */

    const deleteButton = document.createElement("button");

    deleteButton.classList.add("delete-question-btn");

    deleteButton.innerHTML = `
      <i class="bi bi-trash3-fill"></i>
    `;

    /*
      EXCLUIR PERGUNTA
    */

    deleteButton.addEventListener("click", (event) => {
      /*
        Impedir navegação do card
      */

      event.stopPropagation();

      /*
        Impedir apagar última pergunta
      */

      if (quiz.questions.length === 1) {
        alert("O quiz precisa ter pelo menos uma pergunta.");

        return;
      }

      /*
        Confirmação
      */

      const confirmDelete = confirm(`Deseja excluir a Pergunta ${index + 1}?`);

      if (!confirmDelete) return;

      /*
        Remover pergunta
      */

      quiz.questions.splice(index, 1);

      /*
        Ajustar índice atual
      */

      if (currentQuestionIndex >= quiz.questions.length) {
        currentQuestionIndex = quiz.questions.length - 1;
      }

      /*
        Atualizar interface
      */

      renderQuestionList();

      loadQuestion(currentQuestionIndex);
    });

    /*
      NAVEGAR ENTRE PERGUNTAS
    */

    card.addEventListener("click", () => {
      saveCurrentQuestion();

      currentQuestionIndex = index;

      loadQuestion(index);

      renderQuestionList();
    });

    /*
      ESTRUTURA FINAL
    */

    card.appendChild(title);

    card.appendChild(deleteButton);

    questionList.appendChild(card);
  });
}

/* ========================================
   CARREGAR PERGUNTA
======================================== */

function loadQuestion(index) {
  const question = quiz.questions[index];

  questionInput.value = question.question;

  pointsInput.value = question.points;

  timeInput.value = question.time;

  /*
    Respostas
  */

  answerInputs.forEach((input, i) => {
    input.value = question.answers[i].text;
  });

  /*
    Checkboxes
  */

  checkboxes.forEach((checkbox, i) => {
    checkbox.checked = question.answers[i].correct;
  });

  /*
    Imagem
  */

  renderImagePreview(question.image);

  /*
  Resetar input file
*/

  const input = document.getElementById("image-input");

  if (input) {
    input.value = "";
  }
}

/* ========================================
   SALVAR PERGUNTA ATUAL
======================================== */

function saveCurrentQuestion() {
  const question = quiz.questions[currentQuestionIndex];

  question.question = sanitize(questionInput.value);

  question.points = Number(pointsInput.value);

  question.time = Number(timeInput.value);

  /*
    Respostas
  */

  answerInputs.forEach((input, i) => {
    question.answers[i].text = sanitize(input.value);
  });

  /*
    Alternativas corretas
  */

  checkboxes.forEach((checkbox, i) => {
    question.answers[i].correct = checkbox.checked;
  });

  /*
    Título do quiz
  */

  quiz.title = sanitize(quizTitleInput.value);

  /*
    Debug
  */

  console.log(quiz);
}

/* ========================================
   ADICIONAR PERGUNTA
======================================== */

addQuestionBtn.addEventListener("click", () => {
  /*
      Limite de perguntas
    */

  if (quiz.questions.length >= MAX_QUESTIONS) {
    alert("Limite máximo de 20 perguntas.");

    return;
  }

  saveCurrentQuestion();

  quiz.questions.push(createEmptyQuestion());

  currentQuestionIndex = quiz.questions.length - 1;

  renderQuestionList();

  loadQuestion(currentQuestionIndex);
});

/* ========================================
   SALVAR AUTOMÁTICO
======================================== */

questionInput.addEventListener("input", saveCurrentQuestion);

quizTitleInput.addEventListener("input", saveCurrentQuestion);

answerInputs.forEach((input) => {
  input.addEventListener("input", saveCurrentQuestion);
});

checkboxes.forEach((checkbox) => {
  checkbox.addEventListener("change", saveCurrentQuestion);
});

pointsInput.addEventListener("input", saveCurrentQuestion);

timeInput.addEventListener("input", saveCurrentQuestion);

/* ========================================
   CÓDIGO DO QUIZ
======================================== */

let currentQuizCode = "";

/* ========================================
   GERAR CÓDIGO ÚNICO
======================================== */

function generateQuizCode(forcedCode) {
  if (forcedCode) {
    currentQuizCode = forcedCode;
  } else {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let code = "";
    for (let i = 0; i < 6; i++) {
      const randomIndex = Math.floor(Math.random() * characters.length);
      code += characters[randomIndex];
    }
    currentQuizCode = code;
  }

  const codeElement = document.getElementById("quiz-code");

  if (codeElement) {
    codeElement.textContent = currentQuizCode;
  }

  generateQRCode(currentQuizCode);

  const regenerateBtn = document.getElementById("regenerate-code");

  if (regenerateBtn) {
    regenerateBtn.disabled = false;
  }

  console.log("Código gerado:", currentQuizCode);
}

/* ========================================
   REGERAR CÓDIGO
======================================== */

const regenerateButton = document.getElementById("regenerate-code");

if (regenerateButton) {
  regenerateButton.addEventListener("click", () => {
    generateQuizCode();
    const codeElement = document.getElementById("quiz-code");
    if (codeElement) codeElement.textContent = currentQuizCode;
  });
}

/* ========================================
   GERAR QR CODE
======================================== */

function generateQRCode(code) {
  const img = document.getElementById("qr-code");
  if (!img) return;

  const staticUrl = "https://escolas.anchieta.br/?utm_source=adwords&utm_medium=ppc&utm_campaign=%5BRede%20de%20Pesquisa%20-%20UniAnchieta%20Institucional&utm_id=23452130048&utm_term&utm_content=194238175369-792397952353&matchtype=a&targetid=kwl-3500001&network=g&hsa_acc=8565981132&hsa_cam=23452130048&hsa_grp=194238175369&hsa_ad=792397952353&hsa_src=g&hsa_tgt=kwl-3500001&hsa_kw&hsa_mt=a&hsa_net=adwords&hsa_ver=3&gad_source=1&gad_campaignid=23452130048&gbraid=0AAAAADnrZGBSxJUBss4s4PnE8iWZO4lEt&gclid=Cj0KCQjwz9_QBhD_ARIsADnSCfBKJobN3DsCyZLrQ6AqL0PReaDhIlkiWSDCz1kEvmKwuwCuTtOG-BoaAq8EEALw_wcB";

  const modalQuizCode = document.getElementById("modal-quiz-code");
  if (modalQuizCode) {
    modalQuizCode.textContent = code;
  }

  img.src = "Teste-Vendor/qrcode.php?texto=" + encodeURIComponent(staticUrl);

  console.log("QR gerado.");

  openQRModal();
}

/* ========================================
   MODAL QR
======================================== */

const qrModal = document.getElementById("qr-modal");

const closeModalButton = document.getElementById("close-modal");

/*
  Abrir modal
*/

function openQRModal() {
  if (!qrModal) return;

  qrModal.classList.remove("hidden");
}

/*
  Fechar modal
*/

function closeQRModal() {
  if (!qrModal) return;

  qrModal.classList.add("hidden");
}

/*
  Evento botão fechar
*/

if (closeModalButton) {
  closeModalButton.addEventListener("click", closeQRModal);
}

/*
  Fechar clicando fora
*/

if (closeModalButton) {
  closeModalButton.addEventListener("click", closeQRModal);
}

/* ========================================
   UPLOAD DE IMAGEM
======================================== */

function handleImageUpload(event) {
  const file = event.target.files[0];

  /*
    Segurança:
    validar existência
  */

  if (!file) return;

  /*
    Segurança:
    validar tipo
  */

  const allowedTypes = ["image/png", "image/jpeg", "image/webp"];

  if (!allowedTypes.includes(file.type)) {
    alert("Formato de imagem inválido.");

    return;
  }

  /*
    Segurança:
    validar tamanho
    máximo 5MB
  */

  const maxSize = 5 * 1024 * 1024;

  if (file.size > maxSize) {
    alert("Imagem muito grande.");

    return;
  }

  /*
    Ler imagem
  */

  const reader = new FileReader();

  reader.onload = (e) => {
    /*
      Salvar imagem da pergunta atual
    */

    quiz.questions[currentQuestionIndex].image = e.target.result;

    /*
      Atualizar preview
    */

    renderImagePreview(e.target.result);
  };

  reader.readAsDataURL(file);
}

/* ========================================
   PREVIEW DA IMAGEM
======================================== */

function renderImagePreview(src) {
  const uploadArea = document.querySelector(".image-upload");

  /*
    Limpar conteúdo atual
  */

  uploadArea.innerHTML = "";

  /*
    Criar input oculto
  */

  const input = document.createElement("input");

  input.type = "file";

  input.id = "image-input";

  input.accept = "image/png, image/jpeg, image/webp";

  input.style.display = "none";

  /*
    Reatribuir evento
  */

  input.addEventListener("change", handleImageUpload);

  /*
    Se NÃO existir imagem
  */

  if (!src) {
    uploadArea.innerHTML = `
      <div class="plus">+</div>
      <span>Carregar imagem</span>
    `;

    uploadArea.appendChild(input);

    return;
  }

  /*
    Criar preview
  */

  const image = document.createElement("img");

  image.src = src;

  image.classList.add("preview-image");

  image.style.width = "100%";
  image.style.height = "100%";
  image.style.objectFit = "cover";
  image.style.position = "absolute";
  image.style.top = "0";
  image.style.left = "0";

  uploadArea.appendChild(image);

  uploadArea.appendChild(input);
}

/* ========================================
   CLICK NA ÁREA DE UPLOAD
======================================== */

document.querySelector(".image-upload").addEventListener("click", () => {
  const input = document.getElementById("image-input");

  if (input) {
    input.click();
  }
});

/* ========================================
   SANITIZAÇÃO
======================================== */

/*
  Evita:
  - scripts
  - injeções
  - HTML malicioso
*/

function sanitize(text) {
  const div = document.createElement("div");

  div.textContent = text;

  return div.innerHTML.trim();
}

/* ========================================
   SALVAR QUIZ
======================================== */

/* ========================================
   SALVAR QUIZ
======================================== */

function saveQuiz() {
  saveCurrentQuestion();

  const isValid = validateQuiz();
  if (!isValid) return;

  const saveBtn = document.getElementById("save-quiz-btn");
  saveBtn.disabled = true;
  saveBtn.textContent = "Salvando...";

  const payload = {
    quiz_id: editingQuizId,
    titulo: quiz.title,
    codigo: currentQuizCode,
    perguntas: quiz.questions.map(q => ({
      question: q.question,
      points: q.points,
      time: q.time,
      image: q.image,
      answers: q.answers.map(a => ({
        text: a.text,
        correct: a.correct
      }))
    }))
  };

  fetch("api/salvar_quiz.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(data => {
      if (data.sucesso) {
        editingQuizId = data.quiz_id || editingQuizId;
        currentQuizCode = data.codigo;
        const codeElement = document.getElementById("quiz-code");
        if (codeElement) codeElement.textContent = data.codigo;
        generateQuizCode(data.codigo);
        alert("Quiz salvo com sucesso!");
      } else {
        alert("Erro: " + (data.erro || "Falha ao salvar"));
      }
    })
    .catch(err => {
      console.error(err);
      alert("Erro de conexão ao salvar quiz.");
    })
    .finally(() => {
      saveBtn.disabled = false;
      saveBtn.textContent = "Salvar";
    });
}

/* ========================================
   EVENTO SALVAR
======================================== */

const saveQuizButton = document.getElementById("save-quiz-btn");

saveQuizButton.addEventListener("click", saveQuiz);

/* ========================================
   VALIDAR QUIZ
======================================== */

function validateQuiz() {
  /*
    Título obrigatório
  */

  if (!quiz.title.trim()) {
    alert("O quiz precisa ter um título.");

    return false;
  }

  /*
    Pelo menos 1 pergunta
  */

  if (quiz.questions.length === 0) {
    alert("O quiz precisa ter pelo menos uma pergunta.");

    return false;
  }

  /*
    Validar perguntas
  */

  for (let i = 0; i < quiz.questions.length; i++) {
    const question = quiz.questions[i];

    /*
      Pergunta obrigatória
    */

    if (!question.question.trim()) {
      alert(`A pergunta ${i + 1} está vazia.`);

      return false;
    }

    /*
      Pontuação obrigatória
    */

    if (!question.points || question.points <= 0) {
      alert(`Defina a pontuação da pergunta ${i + 1}.`);

      return false;
    }

    /*
      Tempo obrigatório
    */

    if (!question.time || question.time <= 0) {
      alert(`Defina o tempo da pergunta ${i + 1}.`);

      return false;
    }

    /*
      Todas respostas obrigatórias
    */

    for (let j = 0; j < question.answers.length; j++) {
      const answer = question.answers[j];

      if (!answer.text.trim()) {
        alert(`A resposta ${j + 1} da pergunta ${i + 1} está vazia.`);

        return false;
      }
    }

    /*
      Pelo menos 1 correta
    */

    const hasCorrect = question.answers.some((answer) => answer.correct);

    if (!hasCorrect) {
      alert(`Selecione pelo menos uma resposta correta na pergunta ${i + 1}.`);

      return false;
    }
  }

  return true;
}

/* ========================================
   USER DROPDOWN
======================================== */

const userButton =
  document.getElementById(
    "user-button"
  );

const userDropdown =
  document.getElementById(
    "user-dropdown"
  );

/*
  Abrir/fechar menu
*/

if (userButton && userDropdown) {
  userButton.addEventListener(
    "click",
    () => {

      userDropdown.classList.toggle(
        "active"
      );
    }
  );

  /*
    Fechar ao clicar fora
  */

  document.addEventListener(
    "click",
    (event) => {

      if (
        !userButton.contains(event.target)
        &&
        !userDropdown.contains(event.target)
      ) {

        userDropdown.classList.remove(
          "active"
        );
      }
    }
  );
}

/* ========================================
   INICIALIZAÇÃO
======================================== */

if (window.initialQuiz) {
  editingQuizId = window.initialQuiz.id || null;
  quiz.title = window.initialQuiz.title || "";
  quiz.questions = window.initialQuiz.questions && window.initialQuiz.questions.length
    ? window.initialQuiz.questions
    : [createEmptyQuestion()];
  quizTitleInput.value = quiz.title;
  if (window.initialQuiz.code) {
    generateQuizCode(window.initialQuiz.code);
  }
} else {
  generateQuizCode();
}

renderQuestionList();

loadQuestion(0);
