<?php
include '../config/config.php';

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
                 return obtenerMatricula($grupo, $ejercicio);
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
    $archivoLog = '../resources/log_actualizaciones.txt';
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

// function obtenerAlumnos($ejercicio, $grupo) {
//     global $configAlexia;
//     // Construye la URL para obtener los expedientes de los alumnos
//     $url = $configAlexia['URL'] . "GetAlumnos";
//     $params = array(
//         'codigo' => $configAlexia['CODIGO'],
//         'idInstitucion' => $configAlexia['IDINSTITUCION'],
//         'idCentro' => $configAlexia['IDCENTRO'],
//         'ejercicio' => $ejercicio,
//         'check' => $configAlexia['CHECK']
//     );
//     $url .= '?' . http_build_query($params);
//     // Realiza la solicitud HTTP y obtiene el resultado como un objeto SimpleXML
//     $xml = hacerSolicitudHTTP($url);
//     // Si la solicitud falla, devuelve un mensaje de error
//     if (!$xml) {
//         return json_encode(array('error' => 'Error al obtener expediente'));
//     }
//     // Procesa el resultado y construye un array de expedientes de alumnos
//     $alumnos = array();
//     foreach ($xml->datos->alumno as $alumno) {
//         $expediente = $alumno->NroExpediente;
//         if ($matricula = obtenerMatricula($expediente, $grupo, $ejercicio)) {
//             $nombre = $alumno->Nombre;
//             $apellido1 = $alumno->Apellido1;
//             $apellido2 = $alumno->Apellido2;
//             $nif = $alumno->NIF;
//             $alumnos[] = array(
//                 'expediente' => (string) $expediente,
//                 'nombre' => (string) $nombre,
//                 'apellido1' => (string) $apellido1,
//                 'apellido2' => (string) $apellido2,
//                 'nif' => (string) $nif,
//                 'ejercicio' => $ejercicio,
//                 'matricula' => $matricula
//             );
//         }
//     }
//     // Configura el encabezado y devuelve los expedientes de alumnos
//     header('Content-Type: application/json');
//     // Devolver el JSON
//     echo json_encode($alumnos);
// }

function obtenerMatricula($grupo, $ejercicio){
    // Ruta del archivo XML de datos de los alumnos
    $archivo = '../resources/datos_alumnos.xml';

    // Crear un nuevo objeto DOMDocument
    $dom = new DOMDocument();

    // Cargar el archivo XML
    $dom->load($archivo);

    // Array para almacenar los números de expediente de los alumnos con la matrícula correspondiente al grupo y ejercicio
    $expedientes = [];

    // Obtener todos los elementos <alumno>
    $alumnosXML = $dom->getElementsByTagName('alumno');

    // Recorrer los elementos <alumno>
    foreach ($alumnosXML as $alumnoXML) {
        $expedienteAlumno = $alumnoXML->getElementsByTagName('NroExpediente')[0]->nodeValue;

        global $configAlexia;
        $url = $configAlexia['URL'] . "GetMatriculasDeAlumno";
        $url .= "?codigo=" . urlencode($configAlexia['CODIGO']);
        $url .= "&idInstitucion=" . urlencode($configAlexia['IDINSTITUCION']);
        $url .= "&idCentro=" . urlencode($configAlexia['IDCENTRO']);
        $url .= "&check=" . urlencode($configAlexia['CHECK']);
        $url .= "&nroExpediente=" . urlencode($expedienteAlumno);
        // Hacer la solicitud GET y obtener la respuesta
        $response = file_get_contents($url);
        // Convertir la respuesta XML en un objeto SimpleXML
        $xml = simplexml_load_string($response);
        foreach ($xml->datos->matricula as $matricula) {
            if($matricula->Grupo == $grupo && $matricula->Ejercicio == $ejercicio){
                $expedientes[] = (string) $matricula->NumeroExpediente;
            }
        }
    }

    // Devolver los números de expediente
    return $expedientes;
}


// function obtenerMatricula($expediente, $grupo, $ejercicio){
//     global $configAlexia;
//     $url = $configAlexia['URL'] . "GetMatriculasDeAlumno";
//     $url .= "?codigo=" . urlencode($configAlexia['CODIGO']);
//     $url .= "&idInstitucion=" . urlencode($configAlexia['IDINSTITUCION']);
//     $url .= "&idCentro=" . urlencode($configAlexia['IDCENTRO']);
//     $url .= "&check=" . urlencode($configAlexia['CHECK']);
//     $url .= "&nroExpediente=" . urlencode($expediente);
//     // Hacer la solicitud GET y obtener la respuesta
//     $response = file_get_contents($url);
//     // Convertir la respuesta XML en un objeto SimpleXML
//     $xml = simplexml_load_string($response);
//     foreach ($xml->datos->matricula as $matricula) {
//         if($matricula->Grupo == $grupo && $matricula->Ejercicio == $ejercicio){
//             $expedientes[] = (string) $matricula->NumeroExpediente;
//         }
//     }
//     return $expedientes;
// }

function filtrarAlumnos($expedientes){
    $archivo = '../resources/datos_alumnos.xml';

    // Crear un nuevo objeto DOMDocument
    $dom = new DOMDocument();

    // Cargar el archivo XML
    $dom->load($archivo);

    // Array para almacenar los datos de los alumnos
    $alumnos = [];

    // Obtener todos los elementos <alumno>
    $alumnosXML = $dom->getElementsByTagName('alumno');

    // Recorrer los elementos <alumno>
    foreach ($alumnosXML as $alumnoXML) {
        // Obtener el número de expediente del alumno actual
        $expedienteAlumno = $alumnoXML->getElementsByTagName('NroExpediente')[0]->nodeValue;

        // Verificar si el número de expediente está en la lista proporcionada
        if (in_array($expedienteAlumno, $expedientes)) {
            // Obtener los datos del alumno
            $nombre = $alumnoXML->getElementsByTagName('Nombre')[0]->nodeValue;
            $apellido1 = $alumnoXML->getElementsByTagName('Apellido1')[0]->nodeValue;
            $apellido2 = $alumnoXML->getElementsByTagName('Apellido2')[0]->nodeValue;
            $nif = $alumnoXML->getElementsByTagName('NIF')[0]->nodeValue;
            $movil = $alumnoXML->getElementsByTagName('Movil')[0]->nodeValue;
            $email = $alumnoXML->getElementsByTagName('Email')[0]->nodeValue;

            // Almacenar los detalles en el array de alumnos
            $alumnos[] = [
                'expediente' => $expedienteAlumno,
                'nombre' => $nombre,
                'apellido1' => $apellido1,
                'apellido2' => $apellido2,
                'nif' => $nif,
                'movil' => $movil,
                'email' => $email
            ];
        }
    }

    // Devolver los datos de los alumnos como JSON
    return json_encode($alumnos);
}




?>
