<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Médecine - {{ $consultation->patient->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .hospital-name { font-size: 20px; font-weight: bold; color: #2563eb; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 13px; font-weight: bold; background: #f3f4f6; padding: 6px 10px; border-left: 4px solid #2563eb; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 30%; font-weight: bold; padding: 5px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .info-value { display: table-cell; padding: 5px; border-bottom: 1px solid #e5e7eb; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #6b7280; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">Clinique Kam-Siham</div>
        <div>Médecine Générale - Consultation du {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</div>
        <div>Patient: {{ $consultation->patient->name }} ({{ $consultation->patient->medical_record_number }})</div>
    </div>

    <div class="section">
        <div class="section-title">Consultation</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Motif :</div><div class="info-value">{{ $consultation->consultation_reason ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Histoire maladie :</div><div class="info-value">{{ $consultation->illness_history ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Antécédents :</div><div class="info-value">{{ $consultation->medical_history ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Examen physique :</div><div class="info-value">{{ $consultation->physical_examination ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Diagnostic :</div><div class="info-value">{{ $consultation->diagnostic_hypothesis ?? '-' }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Traitement</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Examens demandés :</div><div class="info-value">{{ $consultation->requested_exams ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Traitements :</div><div class="info-value">{{ $consultation->prescribed_treatments ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Prochain rendez-vous :</div><div class="info-value">{{ $consultation->next_appointment ? \Carbon\Carbon::parse($consultation->next_appointment)->format('d/m/Y') : '-' }}</div></div>
        </div>
    </div>

    <div class="footer">Médecin: {{ $consultation->doctor->name ?? 'N/A' }}</div>
</body>
</html>
