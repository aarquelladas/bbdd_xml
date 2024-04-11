<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['accion'])){
         if(isset($_POST['grupo'])){
             $grupo = $_POST['grupo'];
         }
         if(isset($_POST['ejercicio'])){
             $ejercicio = $_POST['ejercicio'];
         }
         $accion = $_POST['accion'];
         switch($accion){
             case 'obtG':
                 echo obtenerGrupos($ejercicio);
             break;
             case 'obtM':
                 return obtenerExpediente($ejercicio, $grupo);
                //  obtenerAlumnos($expediente, $ejercicio, $grupo);
             break;
         }
     }
 }

function hacerSolicitudHTTP($url) {
    // Realiza la solicitud HTTP y devuelve la respuesta
    $response = file_get_contents($url);

    // Si falla la solicitud, puedes manejar el error según tu lógica
    if ($response === false) {
        return false;
    }

    // Si todo está bien, puedes devolver los datos obtenidos
    return simplexml_load_string($response);
}

function obtenerBD($ejercicio, $archivoSalida){
    global $configAlexia;
    // Construye la URL para obtener los expedientes de los alumnos
    $url = $configAlexia['URL'] . "GetAlumnos";
    $params = array(
        'codigo' => $configAlexia['CODIGO'],
        'idInstitucion' => $configAlexia['IDINSTITUCION'],
        'idCentro' => $configAlexia['IDCENTRO'],
        'ejercicio' => $ejercicio,
        'check' => $configAlexia['CHECK']
    );
    $url .= '?' . http_build_query($params);
    // Realiza la solicitud HTTP y obtiene el resultado como un objeto SimpleXML
    $xml = hacerSolicitudHTTP($url);
    // Si la solicitud falla, registra un mensaje de error en el archivo de registro y devuelve un mensaje de error
    if (!$xml) {
        registrarLog("Error al obtener grupos - Fecha: " . date('Y-m-d H:i:s') . " - Error: La solicitud HTTP falló");
        return json_encode(array('error' => 'Error al obtener grupos'));
    }
    // Guarda el XML en el archivo especificado
    $xmlString = $xml->asXML();
    file_put_contents($archivoSalida, $xmlString);
    registrarLog("Actualización exitosa - Fecha: " . date('Y-m-d H:i:s'));
    return "Datos almacenados en el archivo $archivoSalida";
}

function registrarLog($mensaje) {
    // Ruta del archivo de registro
    $archivoLog = 'log_actualizaciones.txt';
    // Formatea el mensaje de registro
    $mensajeRegistro = "[" . date('Y-m-d H:i:s') . "] " . $mensaje . PHP_EOL;
    // Abre el archivo de registro en modo de escritura al final
    // Si el archivo no existe, se crea automáticamente
    $manejadorArchivo = fopen($archivoLog, 'a');
    // Escribe el mensaje en el archivo de registro
    fwrite($manejadorArchivo, $mensajeRegistro);
    // Cierra el archivo de registro
    fclose($manejadorArchivo);
}

function obtenerGrupos($ejercicio) {
    global $configAlexia;
    // Construye la URL para obtener los grupos
    $url = $configAlexia['URL'] . "GetGrupos";
    $params = array(
        'codigo' => $configAlexia['CODIGO'],
        'idInstitucion' => $configAlexia['IDINSTITUCION'],
        'idCentro' => $configAlexia['IDCENTRO'],
        'ejercicio' => $ejercicio,
        'check' => $configAlexia['CHECK']
    );
    $url .= '?' . http_build_query($params);
    // Realiza la solicitud HTTP y obtiene el resultado como un objeto SimpleXML
    $xml = hacerSolicitudHTTP($url);
    // Si la solicitud falla, devuelve un mensaje de error
    if (!$xml) {
        return json_encode(array('error' => 'Error al obtener grupos'));
    }
    // Procesa el resultado y construye un array de grupos
    $grupos = array();
    foreach ($xml->grupo as $grupo) {
        $grupos[] = array(
            'value' => (string) $grupo['Reducido'],
            'label' => (string) $grupo['Reducido']
        );
    }
    // Configura el encabezado y devuelve los grupos en formato JSON
    header('Content-Type: application/json');
    return json_encode($grupos);
}

function obtenerExpediente($ejercicio, $grupo) {
    global $configAlexia;
    // Construye la URL para obtener los expedientes de los alumnos
    $url = $configAlexia['URL'] . "GetAlumnos";
    $params = array(
        'codigo' => $configAlexia['CODIGO'],
        'idInstitucion' => $configAlexia['IDINSTITUCION'],
        'idCentro' => $configAlexia['IDCENTRO'],
        'ejercicio' => $ejercicio,
        'check' => $configAlexia['CHECK']
    );
    $url .= '?' . http_build_query($params);
    // Realiza la solicitud HTTP y obtiene el resultado como un objeto SimpleXML
    $xml = hacerSolicitudHTTP($url);
    // Si la solicitud falla, devuelve un mensaje de error
    if (!$xml) {
        return json_encode(array('error' => 'Error al obtener expediente'));
    }
    // Procesa el resultado y construye un array de expedientes de alumnos
    $alumnos = array();
    foreach ($xml->datos->alumno as $alumno) {
        $expediente = $alumno->NroExpediente;
        if ($matricula = obteneralumnos($expediente, $grupo, $ejercicio)) {
            $nombre = $alumno->Nombre;
            $apellido1 = $alumno->Apellido1;
            $apellido2 = $alumno->Apellido2;
            $nif = $alumno->NIF;
            $alumnos[] = array(
                'expediente' => (string) $expediente,
                'nombre' => (string) $nombre,
                'apellido1' => (string) $apellido1,
                'apellido2' => (string) $apellido2,
                'nif' => (string) $nif,
                'ejercicio' => $ejercicio,
                'matricula' => $matricula
            );
        }
    }
    // Configura el encabezado y devuelve los expedientes de alumnos
    header('Content-Type: application/json');
    // Devolver el JSON
    echo json_encode($alumnos);
}

function obtenerAlumnos($expediente, $grupo, $ejercicio) {
    $archivo = 'datos_alumnos.xml';

    // Crear un nuevo objeto DOMDocument
    $dom = new DOMDocument();

    // Cargar el archivo XML
    $dom->load($archivo);

    // Array para almacenar los datos de los alumnos
    $alumnos = [];

    // Obtener todos los elementos <matricula>
    $matriculas = $dom->getElementsByTagName('matricula');

    // Recorrer los elementos <matricula>
    foreach ($matriculas as $matricula) {
        // Obtener el valor del atributo 'Grupo' de la matrícula
        $grupoMatricula = $matricula->getAttribute('Grupo');

        // Obtener el valor del elemento <Ejercicio> dentro de la matrícula
        $ejercicioMatricula = $matricula->getElementsByTagName('Ejercicio')[0]->nodeValue;

        // Verificar si coincide el grupo y el ejercicio
        if ($grupoMatricula == $grupo && $ejercicioMatricula == $ejercicio) {
            // Obtener el elemento padre de la matrícula (el alumno)
            $alumno = $matricula->parentNode;

            // Almacenar los detalles en el array de alumnos
            $alumnos[] = [
                'expediente' => $alumno->getElementsByTagName('NroExpediente')[0]->nodeValue,
                'nombre' => $alumno->getElementsByTagName('Nombre')[0]->nodeValue,
                'apellido1' => $alumno->getElementsByTagName('Apellido1')[0]->nodeValue,
                'apellido2' => $alumno->getElementsByTagName('Apellido2')[0]->nodeValue,
                'nif' => $alumno->getElementsByTagName('NIF')[0]->nodeValue,
                'ejercicio' => $ejercicio,
                'matricula' => [
                    'Especialidad' => $matricula->getElementsByTagName('Especialidad')[0]->nodeValue,
                    // 'Ciclo' => obtenerCicloCurso($matricula)['ciclo'],
                    // 'Curso' => obtenerCicloCurso($matricula)['curso'],
                ],
            ];
        }
    }

    // Devolver los datos de los alumnos como JSON
    return json_encode($alumnos);
}



?>
