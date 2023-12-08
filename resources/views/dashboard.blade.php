<x-app-layout>
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

    <script>
        // Agregar script para ocultar el mensaje después de 5 segundos
        setTimeout(function() {
            var element = document.getElementById('successMessage');
            if (element) {
                element.style.display = 'none';
            }
        }, 5000); // 5000 milisegundos = 5 segundos
    </script>

</x-app-layout>
