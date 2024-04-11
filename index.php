<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualiza bbdd</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
<div class="container">
    <h2>Obtener base de datos</h2>
    <p>Seleccione el ejercicio:</p>
    <select name="ejercicio" id="ejercicio" class="form-control">
        <option value="-">Seleccione una opción</option>
        <option value="2022">2022</option>
        <option value="2023">2023</option>
        <option value="2024">2024</option>
    </select>

    <button id="btnAct" class='btn btn-primary my-2'>Actualiza</button>
    <div id="resultado"></div>

    <div id="divGrupos" class="mt-4" style="display: none;">
        <p>Seleccione el ejercicio:</p>
        <select id="grupos" class="form-control">
            <option value="">Seleccione un grupo</option>
        </select>
        <button id="btnMostrar" class='btn btn-primary my-2'>Mostrar Alumnos</button>
    </div>

    <div id="divTabla" style="display: none;">
        <label><input type='checkbox' id='seleccionarTodos'> Seleccionar Todos</label>
            <table id="alumnos" class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th></th>
                        <th>Expediente</th>
                        <th>Nombre</th>
                        <th>Apellido 1</th>
                        <th>Apellido 2</th>
                        <th>NIF</th>
                        <th>Ejercicio</th>
                        <th>Matrícula</th>
                        <th>Ciclo</th>
                        <th>Curso</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí se insertarán las filas de la tabla -->
                </tbody>
            </table>
        <button id='registrarCanvas' class='btn btn-primary my-2'>Registrar Alumnos en Canvas</button>
    </div>

    <div id="cargando" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p>Cargando...</p>
    </div>


</div>
<!-- Bootstrap and jQuery JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="script.js"></script>
</body>
</html>
