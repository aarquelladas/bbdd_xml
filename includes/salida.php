<?php
include 'funciones.php';

if(isset($_POST['ejercicio'])) {
    $archivoSalida = 'datos_alumnos.xml';
    $ejercicio = $_POST['ejercicio'];
    $resultado = obtenerBD($ejercicio, $archivoSalida);
    echo $resultado;
}
?>
