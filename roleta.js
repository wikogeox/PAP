const roleta = document.getElementById("roleta");
const totalNumerosVisiveis = 10; // Quantidade de números visíveis na roleta
const totalNumeros = 50; // Total de números na linha para o movimento
let numeros = [];

// Função para gerar números alternados (vermelho, preto) e verdes intercalados
function inicializarRoleta() {
    roleta.innerHTML = ""; // Limpa a roleta antes de popular
    numeros = [];
    
    for (let i = 0; i < totalNumeros; i++) {
        if (i % 9 === 0) { // Verde aparece a cada 9 números
            numeros.push(0);
        } else {
            numeros.push(i % 2 === 0 ? i : i); // Alterna entre números pares e ímpares
        }
    }

    // Adiciona os números à linha com as cores corretas
    numeros.forEach((numero) => {
        const numeroDiv = document.createElement("div");
        numeroDiv.classList.add("section");

        // Define a cor do número
        if (numero === 0) {
            numeroDiv.classList.add("green");
        } else if (numero % 2 === 0) {
            numeroDiv.classList.add("red");
        } else {
            numeroDiv.classList.add("black");
        }

        numeroDiv.textContent = numero;
        roleta.appendChild(numeroDiv);
    });
}

// Função para girar a roleta
async function girarRoleta() {
    try {
        // Solicita o número final ao servidor
        const response = await fetch("roleta.php");
        if (!response.ok) throw new Error("Erro ao obter número.");
        const numeroFinal = parseInt(await response.text());

        // Calcula a posição final do número
        const posicaoFinal = numeros.indexOf(numeroFinal);
        if (posicaoFinal === -1) throw new Error("Número inválido.");

        const deslocamentoFinal = -(posicaoFinal * 50) + 250; // Move para a posição do centro
        roleta.style.transition = "transform 4s cubic-bezier(0.33, 1, 0.68, 1)";
        roleta.style.transform = `translateX(${deslocamentoFinal}px)`;

        // Mostra o número sorteado após o movimento
        setTimeout(() => {
            document.getElementById("resultado").innerText = `Resultado: ${numeroFinal}`;
        }, 4000);
    } catch (error) {
        console.error(error);
        document.getElementById("resultado").innerText = "Erro ao girar a roleta. Tente novamente.";
    }
}

// Inicializa a roleta
inicializarRoleta();
