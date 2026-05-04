<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Psychopathologie - {{ $consultation->patient->name }}</title>
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
        <div>Psychopathologie - Consultation du {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</div>
        <div>Patient: {{ $consultation->patient->name }} ({{ $consultation->patient->medical_record_number }})</div>
    </div>

    <div class="section">
        <div class="section-title">Anamnèse</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Motif principal :</div><div class="info-value">{{ $consultation->chief_complaint ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Histoire maladie :</div><div class="info-value">{{ $consultation->illness_history ?? '-' }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Examen clinique</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Apparence :</div><div class="info-value">{{ $consultation->appearance ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Contact :</div><div class="info-value">{{ $consultation->contact_quality ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Agitation :</div><div class="info-value">{{ $consultation->agitation ? 'Oui' : 'Non' }}</div></div>
            <div class="info-row"><div class="info-label">Hallucinations :</div><div class="info-value">{{ $consultation->hallucinations ? 'Oui' : 'Non' }}</div></div>
            <div class="info-row"><div class="info-label">Idées suicidaires :</div><div class="info-value">{{ $consultation->suicidal_ideation ? 'Oui' : 'Non' }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Conclusion</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Conclusion clinique :</div><div class="info-value">{{ $consultation->clinical_conclusion ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Recommandations :</div><div class="info-value">{{ $consultation->treatment_recommendations ?? '-' }}</div></div>
        </div>
    </div>

    <div class="footer">Médecin: {{ $consultation->doctor->name ?? 'N/A' }}</div>
</body>
</html>
