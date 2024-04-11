<?php

obtenerAlumnos("33336225", "2023");


function obtenerAlumnos($expe, $ejercicio) {
    $archivo = 'datos_alumnos.xml';
    // Crear un nuevo objeto DOMDocument
    $dom = new DOMDocument();
    // Cargar el archivo XML
    $dom->load($archivo);
    // Obtener todos los elementos <matricula>
    $expediente = $dom->getElementsByTagName('NroExpediente');
    // Recorrer los elementos <matricula>
    foreach ($expediente as $exp) {
        // Obtener el valor del atributo 'Grupo' de la matrícul
        // Obtener el valor del elemento <Ejercicio> dentro de la matrícula
        $expediente2 = $exp->getElementsByTagName('NroExpediente')[0]->nodeValue;
        // Verificar si coincide el grupo y el ejercicio
        if ($expediente2 == $expe) {
            // Obtener el elemento padre de la matrícula (el alumno)
            $alumno = $exp->parentNode;

            // Almacenar los detalles en el array de alumnos
            $alumnos[] = [
                'expediente' => $alumno->getElementsByTagName('NroExpediente')[0]->nodeValue,
                'nombre' => $alumno->getElementsByTagName('Nombre')[0]->nodeValue,
                'apellido1' => $alumno->getElementsByTagName('Apellido1')[0]->nodeValue,
                'apellido2' => $alumno->getElementsByTagName('Apellido2')[0]->nodeValue,
                'nif' => $alumno->getElementsByTagName('NIF')[0]->nodeValue,
                'ejercicio' => $ejercicio,
                // 'matricula' => [
                //     'Especialidad' => $exp->getElementsByTagName('Especialidad')[0]->nodeValue,
                    // 'Ciclo' => obtenerCicloCurso($matricula)['ciclo'],
                    // 'Curso' => obtenerCicloCurso($matricula)['curso'],
                // ],
            ];
        }
    }

    // Devolver los datos de los alumnos como JSON
    var_dump(json_encode($alumnos));
}