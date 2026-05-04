<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Médecine - {{ $consultation->patient->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .hospital-name { font-size: 20px; font-weight: bold; color: #2563eb; }
        .patient-info { margin-top: 10px; padding: 8px; background: #f3f4f6; border-radius: 6px; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 13px; font-weight: bold; background: #e5e7eb; padding: 6px 10px; border-left: 4px solid #2563eb; margin-bottom: 10px; }
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-label { width: 30%; font-weight: bold; padding: 6px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .info-value { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #6b7280; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">Clinique Kam-Siham</div>
        <div>Médecine Générale - {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</div>
        <div class="patient-info">Patient: {{ $consultation->patient->name }} ({{ $consultation->patient->medical_record_number }}) | Médecin: {{ $consultation->doctor->name ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <div class="section-title">📋 CONSULTATION</div>
        <table class="info-grid">
            <tr><td class="info-label">Motif :</td><td class="info-value">{{ $consultation->consultation_reason ?? '-' }}</td></tr>
            <tr><td class="info-label">Histoire maladie :</td><td class="info-value">{{ $consultation->illness_history ?? '-' }}</td></tr>
            <tr><td class="info-label">Antécédents :</td><td class="info-value">{{ $consultation->medical_history ?? '-' }}</td></tr>
            <tr><td class="info-label">Examen physique :</td><td class="info-value">{{ $consultation->physical_examination ?? '-' }}</td></tr>
            <tr><td class="info-label">Diagnostic suspecté :</td><td class="info-value">{{ $consultation->diagnostic_hypothesis ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">🔬 EXAMENS</div>
        <table class="info-grid"><tr><td class="info-label">Examens demandés :</td><td class="info-value">{{ $consultation->requested_exams ?? '-' }}</td></tr></table>
    </div>

    <div class="section">
        <div class="section-title">💊 TRAITEMENT</div>
        <table class="info-grid">
            <tr><td class="info-label">Traitements prescrits :</td><td class="info-value">{{ $consultation->prescribed_treatments ?? '-' }}</td></tr>
            <tr><td class="info-label">Ordonnance :</td><td class="info-value">{{ $consultation->medical_prescriptions ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">📅 SUIVI</div>
        <table class="info-grid">
            <tr><td class="info-label">Prochain rendez-vous :</td><td class="info-value">{{ $consultation->next_appointment ? \Carbon\Carbon::parse($consultation->next_appointment)->format('d/m/Y') : '-' }}</td></tr>
            <tr><td class="info-label">Instructions :</td><td class="info-value">{{ $consultation->follow_up_instructions ?? '-' }}</td></tr>
        </table>
    </div>

    @if($consultation->documents && count($consultation->documents) > 0)
    <div class="section">
        <div class="section-title">📎 DOCUMENTS</div>
        <table class="info-grid">@foreach($consultation->documents as $doc)<tr><td class="info-label">Document :</td><td class="info-value">{{ $doc['original_name'] }}</td></tr>@endforeach</table>
    </div>
    @endif

    <div class="footer">Généré le {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
