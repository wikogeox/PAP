<?php
session_start();
include 'config.php';

function gerarSequenciaRoleta() {
    $vermelhos = range(1, 18);
    $pretos = range(36, 19);
    $verde = [0];

    $sequencia = [];
    for ($i = 0; $i < count($vermelhos); $i++) {
        $sequencia[] = $vermelhos[$i];
        $sequencia[] = $pretos[$i];
    }

    $indiceMeio = intdiv(count($sequencia), 2);
    array_splice($sequencia, $indiceMeio, 0, $verde);

    return $sequencia;
}

header('Content-Type: application/json');
echo json_encode(gerarSequenciaRoleta());
?>
