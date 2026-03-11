<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'KPI Lilin')</title>

    {{-- Fonts & Icons (Localized via app.css) --}}

    {{-- Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page Specific Styles --}}
    @stack('styles')

    {{-- Select2 & jQuery Bundle (Local) --}}

</head>

<body class="bg-gray-100 text-gray-800 antialiased">
    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar (optional, aman walau kosong) --}}
            @includeIf('layouts.topbar')

            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>

            {{-- Footer (optional) --}}
            @includeIf('layouts.footer')

        </div>
    </div>

    {{-- Page Specific Scripts --}}
    @stack('scripts')

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    // timer: 3000,
                    // showConfirmButton: false
                });
            });
        </script>
    @endif
</body>

</html>