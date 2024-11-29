<?php
// Gerar sequência da roleta com base na lógica especificada
function gerarSequenciaRoleta() {
    $vermelhos = range(1, 18); // Números vermelhos de 1 a 18
    $pretos = range(36, 19);  // Números pretos de 36 a 19
    $verde = [0];             // O número verde é apenas o 0

    // Criar a sequência intercalando vermelhos e pretos
    $sequencia = [];
    for ($i = 0; $i < count($vermelhos); $i++) {
        $sequencia[] = $vermelhos[$i]; // Adiciona um vermelho
        $sequencia[] = $pretos[$i];   // Adiciona um preto
    }

    // Inserir o 0 no meio da sequência
    $indiceMeio = intdiv(count($sequencia), 2);
    array_splice($sequencia, $indiceMeio, 0, $verde); // Insere o 0 no meio

    return $sequencia;
}

// Obter a sequência gerada
$sequenciaRoleta = gerarSequenciaRoleta();

// Exibir a sequência como JSON
header('Content-Type: application/json');
echo json_encode($sequenciaRoleta);
?>
