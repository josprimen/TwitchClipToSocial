<x-app-layout>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


@if (session('success'))
        <div id="successMessage" class="fixed top-0 right-0 m-4 bg-green-500 text-white p-4 rounded-md" role="alert">
            <p class="font-bold">Éxito:</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('canales.guardar') }}" method="post"
                      class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 w-1/2">
                    @csrf
                    <div class="mb-3">
                        <input type="text"
                               class="w-full px-4 py-2 border rounded-md dark:border-gray-700 dark:bg-gray-900 text-white focus:outline-none focus:border-blue-500 dark:focus:border-blue-500"
                               placeholder="Inserte la URL del canal" name="url_canal">
                        <div class="text-sm text-gray-500 mb-3">
                            El formato debe ser: https://www.twitch.tv/{nombre_canal}/clips?featured=false&filter=clips&range=30d
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:bg-blue-700"
                            type="submit">Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="table-responsive">
                    <table class="table table-row-dashed text-white" id="tabla_canales">
                        <thead>
                        <tr>
                            <th>Canal</th>
                            <th>url</th>
                            <th>Fecha de alta</th>
                            <th>Fecha de baja</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="table-responsive">
                    <table class="table table-row-dashed text-white" id="tabla_clips">
                        <thead>
                        <tr>
                            <th>Canal</th>
                            <th>titulo</th>
                            <th>Obtenido Vídeo</th>
                            <th>Fecha de alta</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dataTables_paginate,
        .dataTables_info,
        .dataTables_length,
        .dataTables_processing {
            color: white !important;
        }

        .dataTables_paginate a:hover {
            color: #e2e8f0 !important; /* Cambia el color al pasar el ratón */
        }
    </style>


    <!-- Incluye jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Luego, incluye DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <!-- SweetAlert 2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">

    <!-- SweetAlert 2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>


    <script>


        var tabla_canales;
        var tabla_clips;

        $(document).ready(function () {
            initTablaCanales();
            initTablaClips();
        });

        // Agregar script para ocultar el mensaje después de 5 segundos
        setTimeout(function() {
            var element = document.getElementById('successMessage');
            if (element) {
                element.style.display = 'none';
            }
        }, 5000); // 5000 milisegundos = 5 segundos

        function initTablaCanales() {
            tabla_canales = $('#tabla_canales').DataTable({
                pageLength: 10,
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                fixedHeader: true,
                stateSave: true,
                dom: `<'row'<'col-sm-6 col-md-6'f><'col-sm-6 col-md-6 botones_datatable'B>>
                <'row'<'col-sm-12'tr>>
                <'row'<'col-sm-6 col-md-6'i><'col-sm-3 col-md-3'l><'col-sm-3 col-md-3'p>>r`,
                ajax: {
                    url: '{{ route('canales.datatable-canales') }}',
                    type: 'POST',
                    data: function (data) {
                        data._token = '{{ csrf_token() }}';
                    },
                },
                buttons: [
                    {extend: 'colvis', text: 'COLUMNAS'},
                    {extend: 'excel', text: 'EXCEL'},
                    {extend: 'pdf', text: 'PDF'},
                ],
                order: [[1, 'desc']],
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                columns: [
                    {data: 'nombre_canal', name: 'nombre_canal'},
                    {data: 'url', name: 'url'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'deleted_at', name: 'deleted_at'},
                    {data: 'action', orderable: false, searchable: false, width: '120px', responsivePriority: -1},
                ],
                language: {
                    url: '{!! asset('js/translations/datatables-es.json') !!}'
                },
            });
        }

        function refrescarTablaCanales() {
            tabla_canales.draw();
        }

        function eliminarCanal(id){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Quieres desactivar los clips de este canal.",
                icon: 'warning',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                console.log(result)
                if (result.value) {
                    $.ajax({
                        url: '{{ route('canales.cambiar-estado') }}',
                        type: 'post',
                        data: {
                            'canal': id,
                            '_token': "{{Session::token()}}"
                        },
                        success: function (data) {
                            Swal.fire(
                                'Listo!',
                                'Cambio de estado realizado con éxito.',
                                'success'
                            );

                            refrescarTablaCanales();
                        },
                        error: function (result) {
                            Swal.fire(
                                'Error',
                                'Ha ocurrido un error.',
                                'error'
                            );
                        }
                    });
                }
            });
        }




        function initTablaClips() {
            tabla_clips = $('#tabla_clips').DataTable({
                pageLength: 10,
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                fixedHeader: true,
                stateSave: true,
                dom: `<'row'<'col-sm-6 col-md-6'f><'col-sm-6 col-md-6 botones_datatable'B>>
                <'row'<'col-sm-12'tr>>
                <'row'<'col-sm-6 col-md-6'i><'col-sm-3 col-md-3'l><'col-sm-3 col-md-3'p>>r`,
                ajax: {
                    url: '{{ route('canales.datatable-clips') }}',
                    type: 'POST',
                    data: function (data) {
                        data._token = '{{ csrf_token() }}';
                    },
                },
                buttons: [
                    {extend: 'colvis', text: 'COLUMNAS'},
                    {extend: 'excel', text: 'EXCEL'},
                    {extend: 'pdf', text: 'PDF'},
                ],
                order: [[4, 'desc']],
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                columns: [
                    {data: 'id_url_canal', name: 'id_url_canal'},
                    {data: 'titulo_clip', name: 'titulo_clip'},
                    {data: 'obtenido_video', name: 'obtenido_video'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', orderable: false, searchable: false, width: '120px', responsivePriority: -1},
                ],
                language: {
                    url: '{!! asset('js/translations/datatables-es.json') !!}'
                },
            });
        }

        function refrescarTablaClips() {
            tabla_clips.draw();
        }



    </script>

</x-app-layout>
