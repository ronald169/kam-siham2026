<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Treatment;
use App\Models\Toxicology;
use App\Models\Psychopathology;
use App\Models\Medecine;
use Illuminate\Support\Facades\DB;

new
#[Title('Tableau de bord')]
class extends Component {

    public array $stats = [];
    public array $recentPatients = [];
    public array $upcomingAppointments = [];
    public array $recentTreatments = [];
    public array $admissionsPerMonth = [];
    public array $genderDistribution = [];
    public array $consultationsByService = [];

    // Données pour les graphiques (sérialisées en JSON)
    public string $admissionsLabels = '[]';
    public string $admissionsData = '[]';
    public string $genderLabels = '[]';
    public string $genderData = '[]';
    public string $serviceLabels = '[]';
    public string $serviceData = '[]';
    public bool $showServiceChart = false;

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentPatients();
        $this->loadUpcomingAppointments();
        $this->loadRecentTreatments();
        $this->loadAdmissionsPerMonth();
        $this->loadGenderDistribution();
        $this->loadConsultationsByService();

        // Préparer les données JSON pour les graphiques
        $this->admissionsLabels = json_encode(array_column($this->admissionsPerMonth, 'month_name'));
        $this->admissionsData = json_encode(array_column($this->admissionsPerMonth, 'total'));
        $this->genderLabels = json_encode(array_column($this->genderDistribution, 'sex'));
        $this->genderData = json_encode(array_column($this->genderDistribution, 'total'));
        $this->serviceLabels = json_encode(array_column($this->consultationsByService, 'service'));
        $this->serviceData = json_encode(array_column($this->consultationsByService, 'total'));
        $this->showServiceChart = auth()->user()->isAdmin();
    }

    public function loadStats()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isMedecin = $user->isMedecin();
        $isConsultant = $user->isConsultant();

        $this->stats = [
            'total_patients' => Patient::count(),
            'active_patients' => Patient::where('status', 'active')->count(),
            'today_appointments' => Appointment::whereDate('appointment_datetime', today())->count(),
            'total_appointments' => Appointment::count(),
            'total_treatments' => Treatment::count(),
        ];

        if ($isAdmin) {
            $this->stats['total_doctors'] = User::where('role', 'medecin')->count();
            $this->stats['total_consultants'] = User::where('role', 'consultant')->count();
            $this->stats['toxicologies'] = Toxicology::count();
            $this->stats['psychopathologies'] = Psychopathology::count();
            $this->stats['medecines'] = Medecine::count();
        }

        if ($isMedecin) {
            $this->stats['my_patients'] = Patient::where('referring_doctor_id', $user->id)->count();
            $this->stats['my_appointments_today'] = Appointment::where('doctor_id', $user->id)->whereDate('appointment_datetime', today())->count();
            $this->stats['my_upcoming_appointments'] = Appointment::where('doctor_id', $user->id)->where('appointment_datetime', '>', now())->count();
            $this->stats['my_treatments'] = Treatment::where('doctor_id', $user->id)->count();
            $this->stats['my_toxicologies'] = Toxicology::where('doctor_id', $user->id)->count();
            $this->stats['my_psychopathologies'] = Psychopathology::where('doctor_id', $user->id)->count();
            $this->stats['my_medecines'] = Medecine::where('doctor_id', $user->id)->count();
        }

        if ($isConsultant) {
            $this->stats['total_consultations'] = Toxicology::count() + Psychopathology::count() + Medecine::count();
            $this->stats['total_doctors'] = User::where('role', 'medecin')->count();
        }
    }

    public function loadRecentPatients()
    {
        $this->recentPatients = Patient::with('referringDoctor')
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadUpcomingAppointments()
    {
        $this->upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->where('appointment_datetime', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('appointment_datetime')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadRecentTreatments()
    {
        $this->recentTreatments = Treatment::with(['patient', 'doctor'])
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadAdmissionsPerMonth()
    {
        $this->admissionsPerMonth = Patient::select(
            DB::raw('MONTH(admission_date) as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('DATE_FORMAT(admission_date, "%M") as month_name')
        )
        ->where('admission_date', '>=', now()->subMonths(6))
        ->whereNotNull('admission_date')
        ->groupBy('month', 'month_name')
        ->orderBy('month')
        ->get()
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

    public function loadConsultationsByService()
    {
        $this->consultationsByService = [
            ['service' => 'Toxicologie', 'total' => Toxicology::count()],
            ['service' => 'Psychopathologie', 'total' => Psychopathology::count()],
            ['service' => 'Médecine', 'total' => Medecine::count()],
        ];
    }

    public function render()
    {
        return $this->view();
    }
};

?>

<div>
    {{-- ==================== EN-TÊTE ==================== --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Tableau de bord</h1>
        <p class="text-base-content/70 mt-1">Bienvenue, {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</p>
    </div>

    {{-- ==================== CARTES COMMUNES ==================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat title="Total Patients" :value="$stats['total_patients']" icon="o-users" class="text-primary" />
        <x-stat title="Patients Actifs" :value="$stats['active_patients']" icon="o-user-circle" class="text-success" />
        <x-stat title="RDV Aujourd'hui" :value="$stats['today_appointments']" icon="o-calendar" class="text-warning" />
        <x-stat title="Total Traitements" :value="$stats['total_treatments']" icon="o-document-check" class="text-info" />
    </div>

    {{-- ==================== CARTES ADMIN ==================== --}}
    @if(auth()->user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <x-stat title="Médecins" :value="$stats['total_doctors']" icon="o-user-group" class="text-secondary" />
            <x-stat title="Consultants" :value="$stats['total_consultants']" icon="o-user-group" class="text-accent" />
            <x-stat title="Toxicologie" :value="$stats['toxicologies']" icon="o-beaker" class="text-info" />
            <x-stat title="Psychopathologie" :value="$stats['psychopathologies']" icon="o-document-text" class="text-primary" />
            <x-stat title="Médecine Générale" :value="$stats['medecines']" icon="custom.stethoscope" class="text-success" />
        </div>
    @endif

    {{-- ==================== CARTES MÉDECIN ==================== --}}
    @if(auth()->user()->isMedecin())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <x-stat title="Mes Patients" :value="$stats['my_patients']" icon="o-users" class="text-primary" />
            <x-stat title="Mes RDV Aujourd'hui" :value="$stats['my_appointments_today']" icon="o-calendar" class="text-success" />
            <x-stat title="Mes RDV à venir" :value="$stats['my_upcoming_appointments']" icon="o-calendar-days" class="text-warning" />
            <x-stat title="Mes Traitements" :value="$stats['my_treatments']" icon="o-document-check" class="text-info" />
            <x-stat title="Toxicologie" :value="$stats['my_toxicologies']" icon="o-beaker" class="text-secondary" />
            <x-stat title="Psychopathologie" :value="$stats['my_psychopathologies']" icon="o-document-text" class="text-accent" />
        </div>
    @endif

    {{-- ==================== CARTES CONSULTANT ==================== --}}
    @if(auth()->user()->isConsultant())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <x-stat title="Total Consultations" :value="$stats['total_consultations']" icon="o-clipboard-document-list" class="text-info" />
            <x-stat title="Médecins" :value="$stats['total_doctors']" icon="o-user-group" class="text-secondary" />
        </div>
    @endif

    {{-- ==================== GRAPHIQUES ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <x-card title="Admissions par mois" icon="o-chart-bar" shadow separator>
            <div class="h-64">
                <canvas id="admissionsChart" class="w-full h-full"
                    data-labels='{!! $admissionsLabels !!}'
                    data-values='{!! $admissionsData !!}'></canvas>
            </div>
        </x-card>

        <x-card title="Répartition par sexe" icon="o-chart-pie" shadow separator>
            <div class="h-64">
                <canvas id="genderChart" class="w-full h-full"
                    data-labels='{!! $genderLabels !!}'
                    data-values='{!! $genderData !!}'></canvas>
            </div>
        </x-card>
    </div>

    {{-- Graphique Admin --}}
    @if(auth()->user()->isAdmin())
        <div class="grid grid-cols-1 gap-6 mb-8">
            <x-card title="Consultations par service" icon="o-chart-pie" shadow separator>
                <div class="h-64">
                    <canvas id="serviceChart" class="w-full h-full"
                        data-labels='{!! $serviceLabels !!}'
                        data-values='{!! $serviceData !!}'></canvas>
                </div>
            </x-card>
        </div>
    @endif

    {{-- ==================== TABLEAUX ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <x-card title="Derniers patients" icon="o-users" shadow separator>
            <x-table
                :headers="[
                    ['key' => 'name', 'label' => 'Nom'],
                    ['key' => 'medical_record_number', 'label' => 'N° Dossier'],
                    ['key' => 'referring_doctor.name', 'label' => 'Médecin'],
                    ['key' => 'admission_date', 'label' => 'Date admission'],
                ]"
                :rows="$recentPatients"
                striped />

            <x-slot:actions>
                <x-button label="Voir tous les patients" icon="o-arrow-right" link="{{ route('patients.index') }}" class="btn-ghost btn-sm" />
            </x-slot:actions>
        </x-card>

        <x-card title="Prochains rendez-vous" icon="o-calendar" shadow separator>
            <x-table
                :headers="[
                    ['key' => 'appointment_datetime', 'label' => 'Date'],
                    ['key' => 'patient.name', 'label' => 'Patient'],
                    ['key' => 'doctor.name', 'label' => 'Médecin'],
                    ['key' => 'service_type', 'label' => 'Service'],
                ]"
                :rows="$upcomingAppointments">

                @scope('cell_appointment_datetime', $appointment)
                    {{ \Carbon\Carbon::parse($appointment['appointment_datetime'])->format('d/m/Y H:i') }}
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
    </div>

    <div class="mt-6">
        <x-card title="Traitements récents" icon="o-document-check" shadow separator>
            <x-table
                :headers="[
                    ['key' => 'treatment_date', 'label' => 'Date'],
                    ['key' => 'patient.name', 'label' => 'Patient'],
                    ['key' => 'care_provided', 'label' => 'Soins'],
                    ['key' => 'doctor.name', 'label' => 'Médecin'],
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
</div>

{{-- ==================== SCRIPTS POUR GRAPHIQUES ==================== --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:navigated', function() {
        // Graphique admissions par mois
        const admissionsCanvas = document.getElementById('admissionsChart');
        if (admissionsCanvas) {
            const labels = JSON.parse(admissionsCanvas.dataset.labels || '[]');
            const values = JSON.parse(admissionsCanvas.dataset.values || '[]');
            if (labels.length && values.length) {
                new Chart(admissionsCanvas.getContext('2d'), {
                    type: 'line',
                    data: { labels: labels, datasets: [{ label: 'Nombre de patients', data: values, borderColor: 'rgb(59, 130, 246)', backgroundColor: 'rgba(59, 130, 246, 0.1)', tension: 0.3, fill: true }] },
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }

        // Graphique répartition par sexe
        const genderCanvas = document.getElementById('genderChart');
        if (genderCanvas) {
            const labels = JSON.parse(genderCanvas.dataset.labels || '[]');
            const values = JSON.parse(genderCanvas.dataset.values || '[]');
            if (labels.length && values.length) {
                new Chart(genderCanvas.getContext('2d'), {
                    type: 'pie',
                    data: { labels: labels, datasets: [{ data: values, backgroundColor: ['rgb(59, 130, 246)', 'rgb(236, 72, 153)'] }] },
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }

        // Graphique consultations par service (Admin uniquement)
        const serviceCanvas = document.getElementById('serviceChart');
        if (serviceCanvas) {
            const labels = JSON.parse(serviceCanvas.dataset.labels || '[]');
            const values = JSON.parse(serviceCanvas.dataset.values || '[]');
            if (labels.length && values.length) {
                new Chart(serviceCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: labels, datasets: [{ data: values, backgroundColor: ['rgb(59, 130, 246)', 'rgb(168, 85, 247)', 'rgb(34, 197, 94)'] }] },
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }
    });
</script>
@endpush
