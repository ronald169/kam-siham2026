<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Treatment;
use App\Models\Toxicology;
use App\Models\Psychopathology;
use App\Models\Medecine;
use Illuminate\Support\Facades\DB;

new
#[Title('Tableau de bord')]
class extends Component {
    use Toast;

    public array $stats = [];
    public array $recentPatients = [];
    public array $todayAppointments = [];
    public array $recentTreatments = [];
    public array $admissionsPerMonth = [];
    public array $genderDistribution = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentPatients();
        $this->loadTodayAppointments();
        $this->loadRecentTreatments();
        $this->loadAdmissionsPerMonth();
        $this->loadGenderDistribution();
    }

    public function loadStats()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $this->stats = [
                'total_patients' => Patient::count(),
                'active_patients' => Patient::where('status', 'active')->count(),
                'total_doctors' => User::where('role', 'medecin')->count(),
                'total_consultants' => User::where('role', 'consultant')->count(),
                'today_appointments' => Appointment::whereDate('appointment_datetime', today())->count(),
                'total_treatments' => Treatment::count(),
                'toxicologies' => Toxicology::count(),
                'psychopathologies' => Psychopathology::count(),
                'medecines' => Medecine::count(),
            ];
        } elseif ($user->isMedecin()) {
            $this->stats = [
                'my_patients' => Patient::where('referring_doctor_id', $user->id)->count(),
                'today_appointments' => Appointment::where('doctor_id', $user->id)->whereDate('appointment_datetime', today())->count(),
                'upcoming_appointments' => Appointment::where('doctor_id', $user->id)->where('appointment_datetime', '>', now())->count(),
                'my_treatments' => Treatment::where('doctor_id', $user->id)->count(),
                'my_toxicologies' => Toxicology::where('doctor_id', $user->id)->count(),
                'my_psychopathologies' => Psychopathology::where('doctor_id', $user->id)->count(),
                'my_medecines' => Medecine::where('doctor_id', $user->id)->count(),
            ];
        } else {
            // Consultant
            $this->stats = [
                'total_patients' => Patient::count(),
                'active_patients' => Patient::where('status', 'active')->count(),
                'total_consultations' => Toxicology::count() + Psychopathology::count() + Medecine::count(),
                'total_treatments' => Treatment::count(),
            ];
        }
    }

    public function loadRecentPatients()
    {
        $query = Patient::with('referringDoctor')->latest()->limit(5);

        if (auth()->user()->isMedecin()) {
            $query->where('referring_doctor_id', auth()->id());
        }

        $this->recentPatients = $query->get()->toArray();
    }

    public function loadTodayAppointments()
    {
        if (auth()->user()->isMedecin()) {
            $this->todayAppointments = Appointment::with('patient')
                ->where('doctor_id', auth()->id())
                ->whereDate('appointment_datetime', today())
                ->orderBy('appointment_datetime')
                ->get()
                ->toArray();
        } elseif (auth()->user()->isAdmin()) {
            $this->todayAppointments = Appointment::with(['patient', 'doctor'])
                ->whereDate('appointment_datetime', today())
                ->orderBy('appointment_datetime')
                ->limit(10)
                ->get()
                ->toArray();
        }
    }

    public function loadRecentTreatments()
    {
        if (auth()->user()->isMedecin()) {
            $this->recentTreatments = Treatment::with('patient')
                ->where('doctor_id', auth()->id())
                ->latest()
                ->limit(10)
                ->get()
                ->toArray();
        }
    }

    public function loadAdmissionsPerMonth()
    {
        $this->admissionsPerMonth = Patient::select(
            DB::raw('DATE_FORMAT(admission_date, "%Y-%m") as month_key'),
            DB::raw('DATE_FORMAT(admission_date, "%M") as month'),
            DB::raw('COUNT(*) as total')
        )
        ->where('admission_date', '>=', now()->subMonths(6))
        ->whereNotNull('admission_date')
        ->groupBy('month_key', 'month')
        ->orderBy('month_key', 'asc')
        ->get()
        ->map(function ($item) {
            return [
                'month' => $item->month,
                'total' => $item->total,
            ];
        })
        ->toArray();
    }

    public function loadGenderDistribution()
    {
        $this->genderDistribution = Patient::select('sex', DB::raw('COUNT(*) as total'))
            ->whereNotNull('sex')
            ->groupBy('sex')
            ->get()
            ->toArray();
    }

};

?>

<div>
    {{-- En-tête --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Tableau de bord</h1>
        <p class="text-base-content/70 mt-1">Bienvenue, {{ auth()->user()->name }}</p>
    </div>

    {{-- Cartes statistiques (Admin) --}}
    @if(auth()->user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
            <x-stat title="Total Patients" :value="$stats['total_patients']" icon="o-users" class="text-primary" />
            <x-stat title="Patients Actifs" :value="$stats['active_patients']" icon="o-user-circle" class="text-success" />
            <x-stat title="Médecins" :value="$stats['total_doctors']" icon="o-user-group" class="text-info" />
            <x-stat title="Consultants" :value="$stats['total_consultants']" icon="o-user-group" class="text-warning" />
            <x-stat title="RDV Aujourd'hui" :value="$stats['today_appointments']" icon="o-calendar" class="text-accent" />
            <x-stat title="Traitements" :value="$stats['total_treatments']" icon="o-document-check" class="text-secondary" />
            <x-stat title="Toxicologie" :value="$stats['toxicologies']" icon="o-beaker" class="text-info" />
            <x-stat title="Psychopathologie" :value="$stats['psychopathologies']" icon="o-document-text" class="text-primary" />
            <x-stat title="Médecine" :value="$stats['medecines']" icon="custom.stethoscope" class="text-success" />
        </div>
    @endif

    {{-- Cartes statistiques (Médecin) --}}
    @if(auth()->user()->isMedecin())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <x-stat title="Mes Patients" :value="$stats['my_patients']" icon="o-users" class="text-primary" />
            <x-stat title="RDV Aujourd'hui" :value="$stats['today_appointments']" icon="o-calendar" class="text-success" />
            <x-stat title="RDV à venir" :value="$stats['upcoming_appointments']" icon="o-calendar-days" class="text-warning" />
            <x-stat title="Mes Traitements" :value="$stats['my_treatments']" icon="o-document-check" class="text-info" />
            <x-stat title="Toxicologie" :value="$stats['my_toxicologies']" icon="o-beaker" class="text-secondary" />
            <x-stat title="Psychopathologie" :value="$stats['my_psychopathologies']" icon="o-document-text" class="text-accent" />
            <x-stat title="Médecine" :value="$stats['my_medecines']" icon="custom.stethoscope" class="text-primary" />
        </div>
    @endif

    {{-- Cartes statistiques (Consultant) --}}
    @if(auth()->user()->isConsultant())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <x-stat title="Total Patients" :value="$stats['total_patients']" icon="o-users" class="text-primary" />
            <x-stat title="Patients Actifs" :value="$stats['active_patients']" icon="o-user-circle" class="text-success" />
            <x-stat title="Consultations" :value="$stats['total_consultations']" icon="o-clipboard-document-list" class="text-info" />
            <x-stat title="Traitements" :value="$stats['total_treatments']" icon="o-document-check" class="text-warning" />
        </div>
    @endif

    {{-- Graphiques (Admin uniquement) --}}
    @if(auth()->user()->isAdmin() && (count($admissionsPerMonth) > 0 || count($genderDistribution) > 0))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Graphique admissions par mois --}}
            @if(count($admissionsPerMonth) > 0)
                <x-card title="Admissions par mois" icon="o-chart-bar" shadow separator>
                    <div class="h-64">
                        <canvas id="admissionsChart" class="w-full h-full"></canvas>
                    </div>
                </x-card>
            @endif

            {{-- Graphique répartition par sexe --}}
            @if(count($genderDistribution) > 0)
                <x-card title="Répartition par sexe" icon="o-chart-pie" shadow separator>
                    <div class="h-64">
                        <canvas id="genderChart" class="w-full h-full"></canvas>
                    </div>
                </x-card>
            @endif
        </div>
    @endif

    {{-- Contenu principal - 2 colonnes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Derniers patients --}}
        <x-card title="Derniers patients" icon="o-users" shadow separator>
            <x-table
                :headers="[
                    ['key' => 'name', 'label' => 'Nom'],
                    ['key' => 'medical_record_number', 'label' => 'N° Dossier'],
                    ['key' => 'referring_doctor.name', 'label' => 'Médecin'],
                ]"
                :rows="$recentPatients"
                striped />

            <x-slot:actions>
                <x-button label="Voir tous les patients" icon="o-arrow-right" link="{{ route('patients.index') }}" class="btn-ghost btn-sm" />
            </x-slot:actions>
        </x-card>

        {{-- Rendez-vous du jour --}}
        @if(count($todayAppointments) > 0)
            <x-card title="Rendez-vous du jour" icon="o-calendar" shadow separator>
                <x-table
                    :headers="[
                        ['key' => 'appointment_datetime', 'label' => 'Heure'],
                        ['key' => 'patient.name', 'label' => 'Patient'],
                        ['key' => 'service_type', 'label' => 'Service'],
                    ]"
                    :rows="$todayAppointments">

                    @scope('cell_appointment_datetime', $appointment)
                        {{ \Carbon\Carbon::parse($appointment['appointment_datetime'])->format('H:i') }}
                    @endscope

                    @scope('cell_service_type', $appointment)
                        @php
                            $labels = ['toxicologie' => 'Toxicologie', 'psychopathologie' => 'Psychopathologie', 'medecine' => 'Médecine'];
                        @endphp
                        <x-badge :value="$labels[$appointment['service_type']]" class="badge-info badge-soft" />
                    @endscope
                </x-table>

                <x-slot:actions>
                    <x-button label="Voir tous les rendez-vous" icon="o-arrow-right" link="{{ route('appointments.index') }}" class="btn-ghost btn-sm" />
                </x-slot:actions>
            </x-card>
        @endif
    </div>

    {{-- Traitements récents (Médecin uniquement) --}}
    @if(auth()->user()->isMedecin() && count($recentTreatments) > 0)
        <div class="mt-6">
            <x-card title="Traitements récents" icon="o-document-check" shadow separator>
                <x-table
                    :headers="[
                        ['key' => 'treatment_date', 'label' => 'Date'],
                        ['key' => 'patient.name', 'label' => 'Patient'],
                        ['key' => 'care_provided', 'label' => 'Soins'],
                        ['key' => 'patient_condition', 'label' => 'État'],
                    ]"
                    :rows="$recentTreatments">

                    @scope('cell_treatment_date', $treatment)
                        {{ \Carbon\Carbon::parse($treatment['treatment_date'])->format('d/m/Y') }}
                    @endscope

                    @scope('cell_patient_condition', $treatment)
                        @if($treatment['patient_condition'] === 'stable')
                            <x-badge value="Stable" class="badge-info badge-soft" />
                        @elseif($treatment['patient_condition'] === 'amélioré')
                            <x-badge value="Amélioré" class="badge-success badge-soft" />
                        @elseif($treatment['patient_condition'] === 'dégradé')
                            <x-badge value="Dégradé" class="badge-error badge-soft" />
                        @else
                            <x-badge value="-" class="badge-ghost" />
                        @endif
                    @endscope
                </x-table>
            </x-card>
        </div>
    @endif
</div>

{{-- Scripts pour les graphiques (Admin uniquement) --}}
@if(auth()->user()->isAdmin())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            // Graphique des admissions
            @if(count($admissionsPerMonth) > 0)
                const admissionsCtx = document.getElementById('admissionsChart')?.getContext('2d');
                if (admissionsCtx) {
                    new Chart(admissionsCtx, {
                        type: 'line',
                        data: {
                            labels: @json(array_column($admissionsPerMonth, 'month')),
                            datasets: [{
                                label: 'Nombre de patients',
                                data: @json(array_column($admissionsPerMonth, 'total')),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            @endif

            // Graphique de répartition par sexe
            @if(count($genderDistribution) > 0)
                const genderCtx = document.getElementById('genderChart')?.getContext('2d');
                if (genderCtx) {
                    new Chart(genderCtx, {
                        type: 'pie',
                        data: {
                            labels: @json(array_column($genderDistribution, 'sex')),
                            datasets: [{
                                data: @json(array_column($genderDistribution, 'total')),
                                backgroundColor: ['rgb(59, 130, 246)', 'rgb(236, 72, 153)']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            @endif
        });
    </script>
    @endpush
@endif
