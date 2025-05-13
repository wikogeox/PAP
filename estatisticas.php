<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Você precisa estar logado para acessar esta funcionalidade.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$user_id = $_SESSION['user_id'];

function getStats($liga, $tabela, $colData, $colGanho, $colAposta, $user_id) {
    $sql = "SELECT COUNT(*) as total_jogos, SUM($colGanho) as total_ganho, SUM($colAposta) as total_gasto, MAX($colData) as ultima_jogada 
            FROM $tabela 
            WHERE user_id = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function getJogadasPorDia($liga, $tabela, $colData, $user_id, $colGanho, $colAposta) {
    $sql = "SELECT DATE($colData) as dia, 
                   COUNT(*) as total, 
                   SUM($colGanho) as ganhos, 
                   SUM($colAposta) as gastos
            FROM $tabela 
            WHERE user_id = ? 
              AND $colData >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY dia 
            ORDER BY dia";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


$mines = getStats($liga, "mines_logs", "game_date", "winnings", "bet_amount", $user_id);
$roleta = getStats($liga, "roleta_logs", "data_hora", "valor_ganho", "aposta", $user_id);
$plinko = getStats($liga, "plinko_logs", "created_at", "win_amount", "bet_amount", $user_id);

$jogos = [
    'Mines' => getJogadasPorDia($liga, "mines_logs", "game_date", $user_id, "winnings", "bet_amount"),
    'Roleta' => getJogadasPorDia($liga, "roleta_logs", "data_hora", $user_id, "valor_ganho", "aposta"),
    'Plinko' => getJogadasPorDia($liga, "plinko_logs", "created_at", $user_id, "win_amount", "bet_amount")
];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Estatísticas</title>
    <link rel="stylesheet" href="css/estatisticas.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Estatísticas Gerais</h2>
    <div class="estatisticas-container">
        <div class="jogo">
            <h3>Mines</h3>
            <p>Total jogadas: <?= $mines['total_jogos'] ?></p>
            <p>Total ganho: <?= $mines['total_ganho'] ?> €</p>
            <p>Total gasto: <?= $mines['total_gasto'] ?> €</p>
            <p>Lucro líquido: <?= $mines['total_ganho'] - $mines['total_gasto'] ?> €</p>
            <p>Última jogada: <?= $mines['ultima_jogada'] ?></p>
        </div>
        <div class="jogo">
            <h3>Roleta</h3>
            <p>Total jogadas: <?= $roleta['total_jogos'] ?></p>
            <p>Total ganho: <?= $roleta['total_ganho'] ?> €</p>
            <p>Total gasto: <?= $roleta['total_gasto'] ?> €</p>
            <p>Lucro líquido: <?= $roleta['total_ganho'] - $roleta['total_gasto'] ?> €</p>
            <p>Última jogada: <?= $roleta['ultima_jogada'] ?></p>
        </div>
        <div class="jogo">
            <h3>Plinko</h3>
            <p>Total jogadas: <?= $plinko['total_jogos'] ?></p>
            <p>Total ganho: <?= $plinko['total_ganho'] ?> €</p>
            <p>Total gasto: <?= $plinko['total_gasto'] ?> €</p>
            <p>Lucro líquido: <?= $plinko['total_ganho'] - $plinko['total_gasto'] ?> €</p>
            <p>Última jogada: <?= $plinko['ultima_jogada'] ?></p>
        </div>
    </div>

    <h2>Gráficos</h2>
    <div class="container-graficos">
        <canvas id="graficoBarras"></canvas>
        <canvas id="graficoLinha"></canvas>
        <canvas id="graficoPizza"></canvas>
    </div>

    <script>
        const dadosJogos = <?= json_encode($jogos) ?>;
        const dias = [...new Set(Object.values(dadosJogos).flat().map(e => e.dia))].sort();

        let lucroAcumulado = [];
        let totalLucro = 0;

        dias.forEach(dia => {
            let ganhos = 0, gastos = 0;
            ['Mines', 'Roleta', 'Plinko'].forEach(jogo => {
                const info = dadosJogos[jogo].find(e => e.dia === dia);
                if (info) {
                    ganhos += parseFloat(info.ganhos || 0);
                    gastos += parseFloat(info.gastos || 0);
                }
            });
            totalLucro += (ganhos - gastos);
            lucroAcumulado.push(totalLucro.toFixed(2));
        });

        const optionsComuns = {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: €${parseFloat(context.raw).toFixed(2)}`;
                        }
                    }
                },
                legend: {
                    labels: {
                        color: '#f5f5f5',
                        font: { size: 14 }
                    }
                },
                title: {
                    display: true,
                    text: '',
                    color: '#ffffff',
                    font: { size: 20 }
                }
            },
            scales: {
                y: {
                    ticks: {
                        color: '#ccc',
                        callback: (value) => `€${value}`
                    },
                    title: {
                        display: true,
                        text: 'Valor (€)',
                        color: '#f5f5f5'
                    }
                },
                x: {
                    ticks: {
                        color: '#ccc'
                    },
                    title: {
                        display: true,
                        text: 'Data',
                        color: '#f5f5f5'
                    }
                }
            }
        };

        new Chart(document.getElementById('graficoBarras'), {
            type: 'line',
            data: {
                labels: dias,
                datasets: [{
                    label: 'Lucro Total Acumulado',
                    data: lucroAcumulado,
                    borderColor: 'lime',
                    backgroundColor: 'rgba(0, 255, 0, 0.2)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 7
                }]
            },
            options: {
                ...optionsComuns,
                plugins: {
                    ...optionsComuns.plugins,
                    title: {
                        ...optionsComuns.plugins.title,
                        text: 'Lucro Acumulado Diário'
                    }
                }
            }
        });

        const dadosPorJogo = {
            Mines: dias.map(d => {
                const diaData = dadosJogos['Mines'].find(e => e.dia === d);
                return diaData ? parseInt(diaData.total) : 0;
            }),
            Roleta: dias.map(d => {
                const diaData = dadosJogos['Roleta'].find(e => e.dia === d);
                return diaData ? parseInt(diaData.total) : 0;
            }),
            Plinko: dias.map(d => {
                const diaData = dadosJogos['Plinko'].find(e => e.dia === d);
                return diaData ? parseInt(diaData.total) : 0;
            }),
        };

        new Chart(document.getElementById('graficoLinha'), {
            type: 'line',
            data: {
                labels: dias,
                datasets: [
                    {
                        label: 'Mines',
                        data: dadosPorJogo.Mines,
                        borderColor: '#2196f3',
                        backgroundColor: 'rgba(33, 150, 243, 0.2)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Roleta',
                        data: dadosPorJogo.Roleta,
                        borderColor: '#4caf50',
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Plinko',
                        data: dadosPorJogo.Plinko,
                        borderColor: '#ff9800',
                        backgroundColor: 'rgba(255, 152, 0, 0.2)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                ...optionsComuns,
                scales: {
                    y: {
                        ticks: {
                            color: '#ccc' // <- sem callback de €
                        },
                        title: {
                            display: true,
                            text: 'Número de Jogadas',
                            color: '#f5f5f5'
                        }
                    },
                    x: optionsComuns.scales.x // Reutiliza a escala X
                },
                plugins: {
                    ...optionsComuns.plugins,
                    title: {
                        ...optionsComuns.plugins.title,
                        text: 'Número de Jogadas por Jogo'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw} jogadas`;
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('graficoPizza'), {
            type: 'pie',
            data: {
                labels: ['Mines', 'Roleta', 'Plinko'],
                datasets: [{
                    data: [
                        <?= $mines['total_jogos'] ?>,
                        <?= $roleta['total_jogos'] ?>,
                        <?= $plinko['total_jogos'] ?>
                    ],
                    backgroundColor: ['#2196f3', '#4caf50', '#ff9800']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#f5f5f5',
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: ${context.raw} jogadas`
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribuição de Jogadas por Jogo',
                        color: '#ffffff',
                        font: { size: 20 }
                    }
                }
            }
        });
    </script>
</body>
</html>
