<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT. Peroni Karya Sentra</title>

    {{-- Manual Font Definition for Offline Support --}}
    <style>
        @font-face {
            font-family: 'Material Icons Round';
            font-style: normal;
            font-weight: 400;
            src: url("{{ asset('fonts/material-icons-round-latin-400-normal.woff2') }}") format('woff2'),
                url("{{ asset('fonts/material-icons-round-latin-400-normal.woff') }}") format('woff');
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            zoom: 0.8;
        }
    </style>
</head>

<body class="bg-white min-h-screen">

    <div class="flex min-h-screen">

        {{-- Left Side: Login Form (40%) --}}
        <div
            class="w-full lg:w-[480px] xl:w-[550px] flex flex-col justify-center p-8 lg:p-12 xl:p-16 relative z-10 bg-white shadow-2xl lg:shadow-none">

            <div class="max-w-sm mx-auto w-full">
                {{-- Logo & Branding --}}
                <div class="mb-10">
                    <img src="{{ asset('images/company-logo.png') }}" alt="Logo PT Peroni"
                        class="h-24 mb-6 object-contain">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase leading-tight">PT.
                        Peroni<br>Karya Sentra</h1>
                    <div class="h-1 w-12 bg-green-600 mt-4 mb-2"></div>
                    <p class="text-slate-500 font-medium text-sm">KPI Monitoring System</p>
                </div>

                {{-- Login Form --}}
                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-slate-700">Email Address</label>
                        <div class="relative group">
                            <span
                                class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-green-600 transition-colors text-xl">mail</span>
                            <input type="email" name="email" required
                                class="w-full h-14 pl-12 pr-4 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-green-600 focus:ring-4 focus:ring-green-600/10 transition-all text-slate-900 font-medium placeholder:text-slate-400"
                                placeholder="name@peroniks.com" value="{{ old('email') }}">
                        </div>
                        @error('email')
                            <p class="text-rose-600 text-xs font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-slate-700">Password</label>
                        <div class="relative group">
                            <span
                                class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-green-600 transition-colors text-xl">lock</span>
                            <input type="password" name="password" required
                                class="w-full h-14 pl-12 pr-4 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-green-600 focus:ring-4 focus:ring-green-600/10 transition-all text-slate-900 font-medium placeholder:text-slate-400"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="remember"
                                    class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-slate-300 shadow-sm transition-all checked:border-green-600 checked:bg-green-600 hover:border-green-600 focus:ring-green-500/20">
                                <span
                                    class="material-icons-round absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[16px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none">done</span>
                            </div>
                            <span
                                class="text-sm font-medium text-slate-600 group-hover:text-slate-800 transition-colors">Remember
                                me</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full h-14 bg-green-700 hover:bg-green-800 text-white rounded-xl font-bold text-lg shadow-xl shadow-green-900/10 hover:shadow-green-900/20 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                        <span>Sign In</span>
                        <span class="material-icons-round">arrow_forward</span>
                    </button>
                </form>

                {{-- Footer --}}
                <div class="mt-16 border-t border-slate-100 pt-8">
                    <p class="text-slate-400 text-xs font-medium">
                        &copy; {{ date('Y') }} PPIC DEPT. PT Peroni Karya Sentra.<br>All rights reserved.
                    </p>
                </div>
            </div>
        </div>

        {{-- Right Side: Background Image (60%) --}}
        <div class="hidden lg:block flex-1 relative bg-slate-900 overflow-hidden">
            {{-- Background Image with Zoom Effect --}}
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[10s] hover:scale-105"
                style="background-image: url('{{ asset('images/login-bg-lilin.jpg') }}');">
            </div>

            {{-- Strong Overlay for Text Readability --}}
            <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-slate-900/80 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-green-900/20"></div>

            {{-- Floating Content --}}
            <div
                class="absolute bottom-0 left-0 right-0 p-16 text-white bg-gradient-to-t from-slate-900/90 to-transparent">
                <div class="max-w-xl">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/20 border border-green-400/30 backdrop-blur-sm mb-6">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-green-100 uppercase tracking-wider">System Online</span>
                    </div>
                    <h2 class="text-4xl font-bold mb-4 tracking-tight leading-tight">Precision Manufacturing<br>&
                        Real-time Monitoring</h2>
                    <p class="text-slate-300 text-lg leading-relaxed max-w-md">
                        Platform terintegrasi untuk memantau performa mesin, produktivitas operator, dan kualitas
                        produksi secara real-time.
                    </p>

                    {{-- Stats / Decorative --}}
                    <div class="flex gap-8 mt-10 border-t border-white/10 pt-8">
                        <div>
                            <p class="text-3xl font-bold text-white">100%</p>
                            <p class="text-xs text-green-200 uppercase tracking-wider mt-1">Uptime</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">24/7</p>
                            <p class="text-xs text-green-200 uppercase tracking-wider mt-1">Monitoring</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

</html>