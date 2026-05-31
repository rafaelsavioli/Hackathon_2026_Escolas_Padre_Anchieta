# QR Code — Configuração para apresentação

## 1. Onde alterar

**Arquivo:** `assets/js/cadastro-pergunta.js` — linha ~423

```js
const baseURL = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, "");
```

## 2. O que alterar

Substitua a linha acima pelo seu IP local:

```js
const baseURL = "http://192.168.1.100/Hackathon-Equipe04/Pinnacle - VersãoFinal";
```

## 3. Como descobrir seu IP

- **Windows:** abra o cmd e digite `ipconfig` — procure o IPv4 (ex: `192.168.x.x`)
- **Mac/Linux:** terminal → `ip a` ou `ifconfig`

>  O IP muda conforme a rede.

## 4. Funcionamento

1. Aluno escaneia o QR → abre `entrar-quiz.php?pin-sala=CODIGO`
2. Se **não estiver logado** → PIN salvo na sessão → redireciona ao login
3. Após login bem-sucedido → volta automaticamente para o quiz com o PIN
4. Aluno clica "Iniciar Quiz" e joga

## 5. Pré-requisitos

- PC e celular na **mesma rede Wi-Fi**
- XAMPP rodando (Apache)
- Aluno deve estar logado (ou será redirecionado ao login automaticamente)
