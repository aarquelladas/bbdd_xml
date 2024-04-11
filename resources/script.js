$(document).ready(function(){
    $('#btnAct').click(function(){
        obtenerBase();
        obtenerGrupos(); // Llama a la función obtenerGrupos después de obtener la base de datos
        $('#btnAct').hide();
        $('#divGrupos').show();
    });

    $('#ejercicio').change(function(){
        $('#btnAct').show();
    });

    $('#btnMostrar').click(function(){
        var ejercicio = $('#ejercicio').val();
        var grupo = $('#grupos').val();
        obtenerMatriculas(ejercicio, grupo);
    });
});

function obtenerBase() {
    var ejercicio = $('#ejercicio').val();
    $.ajax({
        url: '/includes/salida.php',
        method: 'POST',
        data: {
                accion: 'obtB',
                ejercicio: ejercicio 
            },
        success: function(response){
            $('#resultado').html(response);
            console.log(response);
        }
    });
}

function obtenerGrupos() {
    var ejercicio = $('#ejercicio').val();
    $.ajax({
        url: '/includes/funciones.php',
        method: 'POST',
        data: { 
            accion: 'obtG',
            ejercicio: ejercicio 
        },
        success: function(response){
            // Limpia el select de grupos
            $('#grupos').empty();
            // Agrega una opción predeterminada
            $('#grupos').append($('<option>', { 
                value: '',
                text : 'Seleccione un grupo' 
            }));
            // Agrega cada grupo como una opción en el select
            $.each(response, function(key, value) {
                $('#grupos').append($('<option>', { 
                    value: value.value,
                    text : value.label 
                }));
            });
        }
    });
}

function obtenerMatriculas(ejercicio, grupo) {
    mostrarCargando();
    $.ajax({
        url: "/includes/funciones.php",
        method: "POST",
        data: {
            accion: "obtM",
            ejercicio: ejercicio,
            grupo: grupo,
        },
        dataType: "json",
        success: function (data) {
            ocultarCargando();
            console.log(data);
            // Verificar si hay datos disponibles
            if (data.length > 0) {
                // Mostrar la tabla
                $("#alumnos").show();
                // Limpiar el contenido previo de la tabla
                $("#alumnos tbody").empty();
                // Iterar sobre los datos y agregar filas a la tabla
                $.each(data, function (index, alumno) {
                    // Obtener el ciclo y el curso para la matrícula actual
                    //var cicloCurso = obtenerCicloCurso(alumno.matricula);
                    // Agregar la fila a la tabla
                    $("#alumnos tbody").append(
                        "<tr>" +
                        "<td><input type='checkbox'></td>" +
                        "<td>" + alumno.expediente + "</td>" +
                        "<td>" + alumno.nombre + "</td>" +
                        "<td>" + alumno.apellido1 + "</td>" +
                        "<td>" + alumno.apellido2 + "</td>" +
                        "<td>" + alumno.nif + "</td>" +
                        "<td>" + alumno.ejercicio + "</td>" +
                        "<td>" + alumno.matricula.Especialidad + "</td>" +
                        // "<td>" + cicloCurso.ciclo + "</td>" +
                        // "<td>" + cicloCurso.curso + "</td>" +
                        "</tr>"
                    );
                });

                $("#divTabla").show();

            } else {
                // No hay datos disponibles, muestra un mensaje de error
                mostrarError("No se encontraron datos para mostrar.");
                ocultarCargando();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Muestra el error utilizando la función errores
            mostrarError("Error al obtener datos: " + textStatus + ", " + errorThrown);
            ocultarCargando();
        },
    });
}

  // Función para mostrar un mensaje de error
  function mostrarError(mensaje) {
    $("#errores").text(mensaje); // Actualizar el contenido del párrafo con el mensaje de error
  }
  function mostrarCargando() {
    $("#cargando").show();
}
function ocultarCargando() {
    $("#cargando").hide();
}

