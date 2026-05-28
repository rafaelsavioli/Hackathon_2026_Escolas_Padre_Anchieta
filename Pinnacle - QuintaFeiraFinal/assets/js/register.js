const selectBtn = document.getElementById('selectBtn');
const selectWrapper = document.querySelector('.selectCustom');
const selectOptions = document.querySelector('.selectOptions');
const options = document.querySelectorAll('.selectOption');
const hiddenInput = document.getElementById('tipoInput');
const camposProfessor = document.getElementById('camposProfessor');
const form = document.querySelector('form');
const msgErro = document.getElementById('msgErro');

const params = new URLSearchParams(window.location.search);
const erro = params.get('erro');
if (erro === 'senhas') msgErro.textContent = 'As senhas não coincidem!';
else if (erro === 'campos') msgErro.textContent = 'Professores devem preencher data de nascimento e instituição!';
else if (erro === 'idade') msgErro.textContent = 'Professores devem ter pelo menos 18 anos!';

selectBtn.addEventListener('click', () => {
    selectOptions.classList.toggle('show');
    selectWrapper.classList.toggle('open');
});

options.forEach(option => {
    option.addEventListener('click', () => {
        options.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        selectBtn.querySelector('span').textContent = option.textContent;
        hiddenInput.value = option.dataset.value;
        selectOptions.classList.remove('show');
        selectWrapper.classList.remove('open');

        if (option.dataset.value === 'professor') {
            camposProfessor.style.display = 'block';
        } else {
            camposProfessor.style.display = 'none';
        }
    });
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.selectCustom')) {
        selectOptions.classList.remove('show');
        selectWrapper.classList.remove('open');
    }
});

form.addEventListener('submit', (e) => {
    msgErro.textContent = '';

    const senha = document.querySelector('input[name="senha"]').value;
    const confirmarSenha = document.querySelector('input[name="confirmarSenha"]').value;

    if (senha !== confirmarSenha) {
        e.preventDefault();
        msgErro.textContent = 'As senhas não coincidem!';
        return;
    }

    if (hiddenInput.value === 'professor') {
        const dataNascimento = document.querySelector('input[name="data_nascimento"]').value;
        const instituicao = document.querySelector('input[name="instituicao"]').value;

        if (!dataNascimento || !instituicao) {
            e.preventDefault();
            msgErro.textContent = 'Professores devem preencher data de nascimento e instituição!';
            return;
        }

        const nascimento = new Date(dataNascimento);
        const hoje = new Date();
        let idade = hoje.getFullYear() - nascimento.getFullYear();
        const mes = hoje.getMonth() - nascimento.getMonth();
        if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
            idade--;
        }

        if (idade < 18) {
            e.preventDefault();
            msgErro.textContent = 'Professores devem ter pelo menos 18 anos!';
        }
    }
});
