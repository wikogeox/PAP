console.log("Script carregado corretamente!");

let betInProgress = false;

const width = 620;
const height = 534;

// Note class
class Note {
    constructor(note) {
        this.synth = new Tone.PolySynth().toDestination();
        this.synth.set({ volume: -6 });
        this.note = note;
    }

    play() {
        return this.synth.triggerAttackRelease(
            this.note,
            "32n",
            Tone.context.currentTime
        );
    }
}

const multipliers = [50, 20, 10, 5, 3, 1, 0.7, 0.5, 0.3, 0.5, 0.7, 1, 3, 5, 10, 20, 50];

document.addEventListener("DOMContentLoaded", function () {
    // Garante que os multiplicadores correspondem corretamente aos elementos HTML
    multipliers.forEach((m, i) => {
        const noteElement = document.getElementById(`note-${i}`);
        if (noteElement) {
            noteElement.innerHTML = `${m}x`; // Atualiza corretamente o valor
        } else {
            console.warn(`⚠️ Elemento note-${i} não encontrado!`);
        }
    });
});


// Create notes
const notes = [
    "C#5", "C5", "B5", "A#5", "A5", "G#4", "G4", "F#4", "F4", "F#4", "G4", "G#4", "A5", "A#5", "B5", "C5", "C#5"
].map(note => new Note(note));


// Click noise synth when clicking drop
const clickSynth = new Tone.NoiseSynth({ volume: -26 }).toDestination();

// Drop button
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM totalmente carregado!");

    const dropButton = document.getElementById("drop-button");
    const autoDropCheckbox = document.getElementById("checkbox");

    if (!dropButton) {
        console.error("❌ Botão 'drop-button' não encontrado no DOM!");
        return;
    }

    console.log("✅ Botão 'drop-button' encontrado!");

    let autoDropEnabled = false;
    let autoDroppingInterval = null;

    dropButton.addEventListener("click", () => {
        if (autoDropEnabled) {
            console.log("🛑 Auto-drop ativo: parando...");
            // Desativar o auto-drop
            autoDropEnabled = false;
            dropButton.innerHTML = "Jogar";
            autoDropCheckbox.checked = false;
            if (autoDroppingInterval) {
                clearInterval(autoDroppingInterval);
                autoDroppingInterval = null;
            }
            return;
        }
    
        console.log("🖱️ Botão 'Jogar' clicado, chamando placeBet()...");
        placeBet();
    });    

    if (autoDropCheckbox) {
        autoDropCheckbox.addEventListener("input", (e) => {
            autoDropEnabled = e.target.checked;
            dropButton.innerHTML = autoDropEnabled ? "Parar" : "Jogar";
    
            // Se já está a correr, para
            if (autoDroppingInterval) {
                clearInterval(autoDroppingInterval);
                autoDroppingInterval = null;
            }
    
            // Se o checkbox estiver ativado, inicia o auto-drop
            if (autoDropEnabled) {
                autoDroppingInterval = setInterval(() => {
                    if (!betInProgress) {
                        placeBet();
                    }
                }, 500); // lança uma bola a cada 0.5 segundos
            }
        });
    } else {
        console.warn("⚠️ Checkbox de auto-drop não encontrado!");
    }
});


// Drop a ball
const BALL_RAD = 7;

const GAP = 32;  // Declara GAP antes de ser usado

function dropABall(betAmount) {
    let saldoElement = document.getElementById("saldo");
    let saldoAtual = parseFloat(saldoElement.value.replace(/[^\d.]/g, '')) || 0;

    // Verifica se o jogador tem saldo suficiente
    if (betAmount > saldoAtual) {
        console.error("Saldo insuficiente para lançar a bola.");
        return;
    }

    const dropLeft = width / 2 - GAP;
    const dropRight = width / 2 + GAP;
    const dropWidth = dropRight - dropLeft;
    const x = Math.random() * dropWidth + dropLeft;
    const y = -PEG_RAD;

    const ball = Bodies.circle(x, y, BALL_RAD, {
        label: "Ball",
        restitution: 0.6,
        render: { fillStyle: "#f23" }
    });

    // Armazena o valor da aposta na bola para uso no evento de colisão
    ball.betAmount = betAmount;

    clickSynth.triggerAttackRelease("32n", Tone.context.currentTime);
    Composite.add(engine.world, [ball]);
}

function updateSaldo(winAmount) {
    let saldoElement = document.getElementById("saldo");
    let saldoAtual = parseFloat(saldoElement.value.replace(/[^\d.]/g, '')) || 0;
    let novoSaldo = saldoAtual + winAmount;
    saldoElement.value = novoSaldo.toFixed(2);
}

// module aliases
const { Engine, Events, Render, Runner, Bodies, Composite } = Matter;

// create an engine
const engine = Engine.create({ gravity: { scale: 0.0007 } });

// create a renderer
const canvas = document.getElementById("canvas");
const render = Render.create({
    canvas,
    engine,
    options: { width, height, wireframes: false }
});

// Criação das áreas de multiplicadores na parte inferior
const sensorWidth = width / multipliers.length;
const sensors = multipliers.map((multiplier, index) => {
    const sensorX = index * sensorWidth + sensorWidth / 2;
    const sensorY = height - 20;

    const sensor = Bodies.rectangle(sensorX, sensorY, sensorWidth, 10, {
        isStatic: true,
        isSensor: true,
        label: `Multiplier-${index}`, // Nomeia o sensor com o índice
        render: { fillStyle: "transparent" }
    });

    sensor.multiplier = multiplier; // Armazena o multiplicador no sensor
    return sensor;
});

Composite.add(engine.world, sensors);

// Create pegs
const PEG_RAD = 4;
const pegs = [];
for (let r = 0; r < 16; r++) {
    const pegsInRow = r + 3;
    for (let c = 0; c < pegsInRow; c++) {
        const x = width / 2 + (c - (pegsInRow - 1) / 2) * GAP;
        const y = GAP + r * GAP;
        const peg = Bodies.circle(x, y, PEG_RAD, {
            isStatic: true,
            label: "Peg",
            render: { fillStyle: "#fff" }
        });
        pegs.push(peg);
    }
}

Composite.add(engine.world, pegs);

const pegAnims = new Array(pegs.length).fill(null);

// Create a ground
const ground = Bodies.rectangle(width / 2, height + 22, width * 2, 40, {
    isStatic: true,
    label: "Ground"
});
Composite.add(engine.world, [ground]);

// Collision check function
function checkCollision(event, label1, label2, callback) {
    event.pairs.forEach(({ bodyA, bodyB }) => {
        let body1, body2;
        if (bodyA.label === label1 && bodyB.label === label2) {
            body1 = bodyA;
            body2 = bodyB;
        } else if (bodyA.label === label2 && bodyB.label === label1) {
            body1 = bodyB;
            body2 = bodyA;
        }
        if (body1 && body2) {
            callback(body1, body2);
        }
    });
}

// Collision events
Matter.Events.on(engine, "collisionStart", (event) => {
    event.pairs.forEach(({ bodyA, bodyB }) => {
        let ball, sensor;

        // Identifica se a bola colidiu com um sensor
        if (bodyA.label.startsWith("Multiplier") && bodyB.label === "Ball") {
            sensor = bodyA;
            ball = bodyB;
        } else if (bodyB.label.startsWith("Multiplier") && bodyA.label === "Ball") {
            sensor = bodyB;
            ball = bodyA;
        }

        function addWinToSaldo(winAmount) {
            let saldoElement = document.getElementById("saldo");
            let saldoAtual = parseFloat(saldoElement.value.replace(/[^\d.]/g, '')) || 0;
            
            let novoSaldo = saldoAtual + winAmount; 
        
            saldoElement.value = novoSaldo.toFixed(2);
            console.log(`✅ Saldo atualizado corretamente: ${saldoElement.value}`);
        }                       
        
        function atualizarSaldoNoServidor(winAmount) {
            if (!winAmount || isNaN(winAmount) || winAmount <= 0) {
                console.error("Erro: winAmount inválido!", winAmount);
                return;
            }
        
            let formData = new FormData();
            formData.append("win_amount", winAmount.toFixed(2));
        
            fetch("plinko.php", {
                method: "POST",
                body: formData
            })  
            .then(response => response.json())
            .then(data => {
                console.log("Resposta do servidor:", data);
                if (data.success) {
                    updateSaldo(parseFloat(data.new_balance));
                } else {
                    console.error("Erro do servidor:", data.error);
                }
            })
            .catch(error => {
                console.error("Erro na requisição:", error);
            });
        }                   
        
        // Dentro do evento de colisão onde a bola cai no multiplicador:
        if (ball && sensor) {
            const multiplierIndex = parseInt(sensor.label.split("-")[1]);
            const multiplier = multipliers[multiplierIndex];
        
            console.log(`🎯 Bola caiu no multiplicador ${multiplier}x`);
        
            const noteElement = document.getElementById(`note-${multiplierIndex}`);
            if (noteElement) {
                noteElement.setAttribute("data-pressed", "true");
                setTimeout(() => {
                    noteElement.removeAttribute("data-pressed");
                }, 500);
            }
        
            if (ball.betAmount) {
                const winAmount = ball.betAmount * multiplier;
                const ballPosition = ball.position.x.toFixed(2);
        
                console.log(`💰 Jogador ganhou: ${winAmount.toFixed(2)}`);
        
                addWinToSaldo(winAmount);
                atualizarSaldoNoServidor(winAmount);
        
                registarLog(ball.betAmount, multiplier, winAmount, ballPosition);
            } else {
                console.error("⚠️ Erro: betAmount está indefinido na bola.");
            }
        
            Matter.Composite.remove(engine.world, ball);
        }               
    });
});

// run the renderer
Render.run(render);

const ctx = canvas.getContext("2d");
function run() {
    const now = new Date().getTime();
    Engine.update(engine, 1000 / 60);
    requestAnimationFrame(run);
}

function adjustBet(multiplier) {
    let betInput = document.getElementById("bet-amount");
    let saldoElement = document.getElementById("saldo");
    
    let currentBet = parseFloat(betInput.value.replace(',', '.')) || 0;
    let saldoAtual = parseFloat(saldoElement.value.replace(/[^\d.]/g, '')) || 0; // Pega o saldo e remove caracteres não numéricos

    console.log("Aposta atual:", currentBet);
    console.log("Saldo atual:", saldoAtual);

    // Se a aposta for zero ou negativa, define um mínimo inicial de 0.20
    if (currentBet <= 0) {
        currentBet = 0.20;
    }

    let newBet = currentBet * multiplier;

    // Garante que o novo valor da aposta não seja menor que 0.20 e não ultrapasse o saldo
    if (newBet < 0.20) {
        newBet = 0.20;
    }
    if (newBet > saldoAtual) {
        newBet = saldoAtual;
    }

    console.log("Nova aposta após multiplicação:", newBet);

    betInput.value = newBet.toFixed(2);
}

function halfBet() {
    adjustBet(0.5);
}

function doubleBet() {
    adjustBet(2);
}

function getSaldo() {
    let saldoElement = document.getElementById("saldo");

    if (!saldoElement) {
        console.error("Elemento de saldo não encontrado!");
        return 0;
    }

    let saldoRaw = saldoElement.value.replace(',', '.').replace(/[^\d.]/g, ''); 
    let saldo = parseFloat(saldoRaw);

    console.log("🔍 Saldo lido:", saldoRaw, "-> Convertido para:", saldo);

    return saldo || 0; // Se não for um número válido, retorna 0
}

function updateSaldo(novoSaldo) {
    let saldoElement = document.getElementById("saldo");

    if (!saldoElement) {
        console.error("Elemento de saldo não encontrado!");
        return;
    }

    if (isNaN(novoSaldo)) {
        console.error("Tentativa de definir saldo inválido:", novoSaldo);
        return;
    }

    saldoElement.value = novoSaldo.toFixed(2);
    console.log("✅ Saldo atualizado para:", saldoElement.value);
}

// Adiciona eventos aos botões de multiplicar e dividir aposta
document.getElementById("half-bet").addEventListener("click", halfBet);
document.getElementById("double-bet").addEventListener("click", doubleBet);

function placeBet() {
    console.log("placeBet() foi chamada!");
    if (betInProgress) {
        console.log("Aposta já em andamento.");
        return;
    }

    betInProgress = true;

    let betAmount = parseFloat(document.getElementById("bet-amount").value.replace(',', '.')) || 0;
    let saldoAtual = getSaldo(); // Obtém saldo atualizado

    console.log("🎲 Tentando apostar:", betAmount);
    console.log("💰 Saldo antes da aposta:", saldoAtual);

    if (betAmount <= 0 || isNaN(betAmount)) {
        console.error("Valor da aposta inválido.");
        betInProgress = false;
        return;
    }

    if (betAmount > saldoAtual) {
        console.error("Saldo insuficiente.");
        betInProgress = false;
        return;
    }

    let formData = new FormData();
    formData.append("bet_amount", betAmount);

    fetch("plinko.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSaldo(parseFloat(data.new_balance)); // Atualiza saldo com o valor retornado pelo servidor
        } else {
            console.error(`Erro: ${data.error}`);
            updateSaldo(saldoAtual); // Reverte o saldo caso a aposta falhe
        }
    })
    .catch(error => {
        console.error("Erro na requisição:", error);
        updateSaldo(saldoAtual); // Reverte o saldo caso haja erro na requisição
    })
    .finally(() => {
        betInProgress = false;
    });

    dropABall(betAmount);
}

function registarLog(betAmount, multiplier, winAmount, ballPosition) {
    fetch("plinko.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `log=1&bet_amount=${betAmount}&multiplier=${multiplier}&win_amount=${winAmount}&ball_position=${ballPosition}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.error("Erro ao guardar log:", data.error);
        }
    })
    .catch(error => {
        console.error("Erro no envio de log:", error);
    });
}


run();