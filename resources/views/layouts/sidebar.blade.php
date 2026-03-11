<aside class="w-64 sticky top-0 h-screen bg-[#064e3b] text-white flex flex-col overflow-y-auto scrollbar-thin"
    style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.15) transparent">
    <div class="h-16 flex items-center px-6 border-b border-white/10 shrink-0">
        <span class="material-icons-round text-2xl mr-2 text-green-400">content_cut</span>
        <div>
            <h1 class="font-bold text-lg tracking-wide">KPI Lilin</h1>
            <p class="text-[10px] text-green-200 uppercase tracking-wider">Tracking System</p>
        </div>
    </div>

    {{-- User Profile Section - Moved to Top for better UX --}}
    <div class="px-6 py-5 border-b border-white/10 bg-black/10 shrink-0">
        <div class="flex items-center justify-between group">
            <div class="flex items-center gap-3 overflow-hidden">
                <div
                    class="shrink-0 w-9 h-9 rounded-xl bg-green-500/20 border border-green-400/30 flex items-center justify-center text-xs font-black text-green-300 shadow-inner uppercase">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] text-green-300 uppercase tracking-tighter mt-0.5">{{ Auth::user()->role }}
                    </p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="shrink-0 ml-2">
                @csrf
                <button type="submit"
                    class="p-1.5 rounded-lg text-green-400 hover:text-white hover:bg-red-500/20 transition-all"
                    title="Sign Out">
                    <span class="material-icons-round text-lg leading-none">logout</span>
                </button>
            </form>
        </div>
        <div class="mt-2 pl-12">
            <p class="text-[8px] text-green-400/60 truncate font-mono">{{ Auth::user()->email }}</p>
        </div>
    </div>

    @php
        $currUser = Auth::user();
        $isSpecialHr = in_array($currUser->email, ['adminhr@peroniks.com', 'managerhr@peroniks.com']);
        $isDirekturOrMR = in_array($currUser->role, ['direktur', 'mr', 'hr_admin', 'hr_manager', 'guest']) || $isSpecialHr;
        $isManager = $currUser->role === 'manager';
        $additionalDepts = $currUser->additional_department_codes ?? [];
        $hasAdditionalDepts = !empty($additionalDepts);
        $isReadOnly = $currUser->isReadOnly();
    @endphp

    @if($isDirekturOrMR || ($isManager && ($currUser->department_code || $hasAdditionalDepts)))
        <div class="px-4 py-4 border-b border-white/5 bg-black/10">
            <label class="block text-[10px] font-bold text-green-400 uppercase tracking-widest mb-2 px-2">
                {{ $isDirekturOrMR ? 'System Context' : 'Department Access' }}
            </label>
            <select id="contextSwitcher"
                class="w-full bg-black/20 border-white/10 rounded-xl text-xs font-bold text-green-100 focus:ring-green-500/50 focus:border-green-400 transition-all cursor-pointer py-2 px-3">

                @if($isDirekturOrMR)
                    <option value="all" {{ !session('selected_department_code') ? 'selected' : '' }}>🌎 ALL DEPARTMENTS</option>
                    @foreach(\Illuminate\Support\Facades\DB::connection('master')->table('md_departments')->whereIn('code', ['402.2.1', '402.2.2', '402.2.3'])->where('status', 'active')->orderBy('code')->get() as $dept)
                        <option value="{{ $dept->code }}" {{ session('selected_department_code') == $dept->code ? 'selected' : '' }}>
                            🏢 {{ $dept->code }} - {{ $dept->name }}
                        </option>
                    @endforeach
                @elseif($isManager)
                    @php
                        $allowedCodes = array_merge([$currUser->department_code], $additionalDepts);
                        $allowedDepts = \Illuminate\Support\Facades\DB::connection('master')
                            ->table('md_departments')
                            ->where(function ($q) use ($allowedCodes) {
                                foreach (array_filter($allowedCodes) as $code) {
                                    $q->orWhere('code', 'LIKE', $code . '%');
                                }
                            })
                            ->where('status', 'active')
                            ->orderBy('code')
                            ->get();
                    @endphp

                    <option value="all" {{ !session('selected_department_code') ? 'selected' : '' }}>🌎 ALL MY DEPARTMENTS
                    </option>
                    @foreach($allowedDepts as $dept)
                        <option value="{{ $dept->code }}" {{ session('selected_department_code') == $dept->code ? 'selected' : '' }}>
                            🏢 {{ $dept->code }} - {{ $dept->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
        <script>
            document.getElementById('contextSwitcher').addEventListener('change', async function () {
                const code = this.value;
                try {
                    const res = await fetch('{{ route("api.context.set") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ department_code: code })
                    });
                    if (res.ok) {
                        window.location.reload();
                    }
                } catch (e) {
                    console.error('Context switch failed', e);
                }
            });
        </script>
    @endif

    <nav class="flex-1 py-6 px-3 space-y-1 text-sm">

        <p class="px-3 text-[10px] font-semibold text-green-300 uppercase tracking-wider mb-2">Menu Utama</p>

        <a href="{{ url('/dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('dashboard') && !request()->is('dashboard/*') ? 'bg-green-600 text-white shadow-lg shadow-green-900/50' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">dashboard</span>
            </div>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="{{ url('/dashboard/operator') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('dashboard/operator') ? 'bg-green-600 text-white shadow-lg shadow-green-900/50' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">insights</span>
            </div>
            <span class="font-medium">KPI Trend</span>
        </a>

        @if(!$isReadOnly)
            <div class="mt-6 mb-2 px-3 text-[10px] font-semibold text-green-300 uppercase tracking-wider">Produksi</div>

            <a href="{{ route('production.create') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('production.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
                <div class="w-6 flex justify-center">
                    <span class="material-icons-round text-xl">add_circle</span>
                </div>
                <span class="font-medium">Input Produksi</span>
            </a>

            <a href="{{ route('reject.create') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('reject.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
                <div class="w-6 flex justify-center">
                    <span class="material-icons-round text-xl">error</span>
                </div>
                <span class="font-medium">Input Reject</span>
            </a>

            <a href="{{ route('downtime.create') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('downtime.create') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
                <div class="w-6 flex justify-center">
                    <span class="material-icons-round text-xl">timer_off</span>
                </div>
                <span class="font-medium">Input Downtime</span>
            </a>
        @endif

        <div class="mt-6 mb-2 px-3 text-[10px] font-semibold text-green-300 uppercase tracking-wider">Laporan</div>

        <a href="{{ url('/tracking/operator') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('tracking/operator') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">groups</span>
            </div>
            <span class="font-medium">Operator KPI</span>
        </a>

        <a href="{{ url('/tracking/mesin') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('tracking/mesin') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">precision_manufacturing</span>
            </div>
            <span class="font-medium">Mesin KPI</span>
        </a>

        <a href="{{ url('/downtime') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('downtime') && !request()->is('downtime/input') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">history</span>
            </div>
            <span class="font-medium">Riwayat Downtime</span>
        </a>

        <div class="mt-6 mb-2 px-3 text-[10px] font-semibold text-green-300 uppercase tracking-wider">Daftar Harian
        </div>

        <a href="{{ route('daily_report.operator.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('daily_report.operator.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">assignment_ind</span>
            </div>
            <span class="font-medium">Operator</span>
        </a>

        <a href="{{ route('daily_report.downtime.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('daily_report.downtime.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">timer_off</span>
            </div>
            <span class="font-medium">Downtime</span>
        </a>

        <div class="mt-6 mb-2 px-3 text-[10px] font-semibold text-green-300 uppercase tracking-wider">System</div>

        @if(in_array(Auth::user()->role, ['direktur', 'mr']))
            <a href="{{ route('audit_logs.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('audit_logs.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
                <div class="w-6 flex justify-center">
                    <span class="material-icons-round text-xl">security</span>
                </div>
                <span class="font-medium">Audit Logs</span>
            </a>
        @endif

        <a href="{{ route('settings.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-green-600 text-white shadow-lg' : 'text-green-100 hover:bg-white/5 hover:text-white' }}">
            <div class="w-6 flex justify-center">
                <span class="material-icons-round text-xl">settings</span>
            </div>
            <span class="font-medium">Setting</span>
        </a>

    </nav>

</aside>