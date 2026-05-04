<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    {{-- Flatpickr  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky full-width class="lg:hidden">
        <x-slot:brand>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
            <div class="text-xl font-bold text-primary">Kam-Siham</div>
        </x-slot:brand>
        <x-slot:actions>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                        <span class="text-lg font-bold">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                </label>
                <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
                    <li class="menu-title">
                        <span>{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                    </li>
                    <li><a class="text-sm">{{ auth()->user()->email ?? '' }}</a></li>
                    <div class="divider my-1"></div>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-error">
                                <x-icon name="o-arrow-left-end-on-rectangle" class="h-4 w-4" />
                                Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main with-nav full-width>

        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <div class="px-5 pt-5">
                <div class="text-2xl font-bold text-primary">Kam-Siham</div>
                <div class="text-xs text-base-content/60">Clinique Médicale</div>
            </div>

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-button icon="o-arrow-left-end-on-rectangle" class="btn-circle btn-ghost btn-xs" tooltip-left="Déconnexion" no-wire-navigate />
                            </form>
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                {{-- Dashboard --}}
                <x-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />

                {{-- Patients --}}
                <x-menu-item title="Patients" icon="o-users" link="{{ route('patients.index') }}" />

                {{-- Consultations (Admin & Médecin) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isMedecin())
                    <x-menu-sub title="Consultations" icon="o-clipboard-document-list">
                        <x-menu-item title="Toxicologie" icon="o-beaker" link="{{ route('consultations.toxicologie.index') }}" />
                        <x-menu-item title="Psychopathologie" icon="o-document-text" link="{{ route('consultations.psychopathologie.index') }}" />
                        <x-menu-item title="Médecine Générale" icon="custom.stethoscope" link="{{ route('consultations.medecine.index') }}" />
                    </x-menu-sub>

                    <x-menu-item title="Traitements" icon="o-document-check" link="{{ route('treatments.index') }}" />
                    <x-menu-item title="Rendez-vous" icon="o-calendar" link="{{ route('appointments.index') }}" />
                @endif

                {{-- Consultations (Consultant - lecture seule) --}}
                @if(auth()->user()->isConsultant())
                    <x-menu-sub title="Consultations" icon="o-document-magnifying-glass">
                        <x-menu-item title="Toxicologie" icon="o-beaker" link="{{ route('consultations.toxicologie.index') }}" />
                        <x-menu-item title="Psychopathologie" icon="o-document-text" link="{{ route('consultations.psychopathologie') }}" />
                        <x-menu-item title="Médecine Générale" icon="custom.stethoscope" link="{{ route('consultations.medecine') }}" />
                    </x-menu-sub>
                @endif

                {{-- Administration (Admin uniquement) --}}
                @if(auth()->user()->isAdmin())
                    <x-menu-separator />
                    <x-menu-item title="Utilisateurs" icon="o-user-group" link="{{ route('admin.users') }}" />
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- CONTENU PRINCIPAL --}}
        <x-slot:content>
            {{-- Messages flash --}}
            @if(session('success'))
                <x-alert title="Succès" icon="o-check-circle" class="alert-success shadow-lg mb-4">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if(session('error'))
                <x-alert title="Erreur" icon="o-exclamation-triangle" class="alert-error shadow-lg mb-4">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if($errors->any())
                <x-alert title="Erreur de validation" icon="o-exclamation-circle" class="alert-warning shadow-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            {{-- Contenu dynamique --}}
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- TOAST area --}}
    <x-toast />

    @livewireScripts
    @stack('scripts')
</body>
</html>
