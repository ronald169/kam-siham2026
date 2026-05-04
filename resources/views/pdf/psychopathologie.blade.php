<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Psychopathologie - {{ $consultation->patient->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
            background: white;
            padding: 20px;
        }

        /* En-tête */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .hospital-name {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 1px;
        }
        .hospital-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }
        .consultation-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            color: #1f2937;
        }
        .patient-info {
            margin-top: 10px;
            padding: 8px 12px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 10px;
        }

        /* Sections */
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #e5e7eb;
            padding: 6px 12px;
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
            color: #1f2937;
        }

        /* Grilles */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            border-bottom: 1px solid #e5e7eb;
        }
        .info-label {
            width: 35%;
            font-weight: bold;
            padding: 6px 8px;
            background: #f9fafb;
            vertical-align: top;
        }
        .info-value {
            width: 65%;
            padding: 6px 8px;
            vertical-align: top;
        }

        /* Grille 2 colonnes */
        .two-columns {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .col {
            flex: 1;
            min-width: 200px;
        }

        /* Badges */
        .badge-yes {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-no {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

{{-- ==================== EN-TÊTE ==================== --}}
<div class="header">
    <div class="hospital-name">Clinique Kam-Siham</div>
    <div class="hospital-subtitle">Douala - Cameroun</div>
    <div class="consultation-title">CONSULTATION PSYCHOPATHOLOGIE</div>

    <div class="patient-info">
        <strong>Patient:</strong> {{ $consultation->patient->name }} &nbsp;|&nbsp;
        <strong>N° Dossier:</strong> {{ $consultation->patient->medical_record_number }} &nbsp;|&nbsp;
        <strong>Date:</strong> {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}
    </div>
</div>

{{-- ==================== SECTION 1: ANAMNÈSE ==================== --}}
<div class="section">
    <div class="section-title">📋 ANAMNÈSE</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Motif principal :</td>
            <td class="info-value">{{ $consultation->chief_complaint ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Histoire de la maladie :</td>
            <td class="info-value">{{ $consultation->illness_history ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 2: PRÉSENTATION ==================== --}}
<div class="section">
    <div class="section-title">👤 PRÉSENTATION</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Apparence / Tenue :</td><td class="info-value">{{ $consultation->appearance ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Mimique :</td><td class="info-value">{{ $consultation->facial_expressions ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Démarche :</td><td class="info-value">{{ $consultation->gait ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Regard :</td><td class="info-value">{{ $consultation->eye_contact ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Qualité du contact :</td><td class="info-value">{{ $consultation->contact_quality ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Autres :</td><td class="info-value">{{ $consultation->other_presentation ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 3: COMPORTEMENT ==================== --}}
<div class="section">
    <div class="section-title">⚡ COMPORTEMENT</div>
    <div class="two-columns">
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Agitation :</td><td class="info-value"><span class="{{ $consultation->agitation ? 'badge-yes' : 'badge-no' }}">{{ $consultation->agitation ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Impulsions :</td><td class="info-value"><span class="{{ $consultation->impulses ? 'badge-yes' : 'badge-no' }}">{{ $consultation->impulses ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Stupeur :</td><td class="info-value"><span class="{{ $consultation->stupor ? 'badge-yes' : 'badge-no' }}">{{ $consultation->stupor ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Catalepsie :</td><td class="info-value"><span class="{{ $consultation->catalepsy ? 'badge-yes' : 'badge-no' }}">{{ $consultation->catalepsy ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Tics :</td><td class="info-value"><span class="{{ $consultation->tics ? 'badge-yes' : 'badge-no' }}">{{ $consultation->tics ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
    </div>
    @if($consultation->other_behaviors)
    <div class="mt-2"><span class="font-bold">Autres comportements :</span> {{ $consultation->other_behaviors }}</div>
    @endif
</div>

{{-- ==================== SECTION 4: SOMMEIL ==================== --}}
<div class="section">
    <div class="section-title">🌙 SOMMEIL</div>
    <div class="two-columns">
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Insomnie :</td><td class="info-value"><span class="{{ $consultation->insomnia ? 'badge-yes' : 'badge-no' }}">{{ $consultation->insomnia ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Somnolence diurne :</td><td class="info-value"><span class="{{ $consultation->daytime_sleepiness ? 'badge-yes' : 'badge-no' }}">{{ $consultation->daytime_sleepiness ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Hypersomnie :</td><td class="info-value"><span class="{{ $consultation->hypersomnia ? 'badge-yes' : 'badge-no' }}">{{ $consultation->hypersomnia ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Perturbation onirique :</td><td class="info-value"><span class="{{ $consultation->dream_disturbances ? 'badge-yes' : 'badge-no' }}">{{ $consultation->dream_disturbances ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
    </div>
    @if($consultation->other_sleep_issues)
    <div class="mt-2"><span class="font-bold">Autres :</span> {{ $consultation->other_sleep_issues }}</div>
    @endif
</div>

{{-- ==================== SECTION 5: ALIMENTATION ==================== --}}
<div class="section">
    <div class="section-title">🍽️ CONDUITES ALIMENTAIRES</div>
    <div class="two-columns">
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Restriction alimentaire :</td><td class="info-value"><span class="{{ $consultation->food_restriction ? 'badge-yes' : 'badge-no' }}">{{ $consultation->food_restriction ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Refus alimentaire :</td><td class="info-value"><span class="{{ $consultation->food_refusal ? 'badge-yes' : 'badge-no' }}">{{ $consultation->food_refusal ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Excès alimentaire :</td><td class="info-value"><span class="{{ $consultation->excessive_eating ? 'badge-yes' : 'badge-no' }}">{{ $consultation->excessive_eating ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Excès de boissons :</td><td class="info-value"><span class="{{ $consultation->excessive_drinking ? 'badge-yes' : 'badge-no' }}">{{ $consultation->excessive_drinking ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
    </div>
    @if($consultation->other_eating_behaviors)
    <div class="mt-2"><span class="font-bold">Autres :</span> {{ $consultation->other_eating_behaviors }}</div>
    @endif
</div>

{{-- ==================== SECTION 6: VIE SEXUELLE ==================== --}}
<div class="section">
    <div class="section-title">❤️ VIE SEXUELLE ET AFFECTIVE</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Orientation sexuelle :</td><td class="info-value">{{ $consultation->sexual_orientation ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Fréquence :</td><td class="info-value">{{ $consultation->sexual_activity_frequency ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Masturbation :</td><td class="info-value"><span class="{{ $consultation->masturbation ? 'badge-yes' : 'badge-no' }}">{{ $consultation->masturbation ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">Impuissance :</td><td class="info-value"><span class="{{ $consultation->impotence ? 'badge-yes' : 'badge-no' }}">{{ $consultation->impotence ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">Autres :</td><td class="info-value">{{ $consultation->other_sexual_issues ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 7: CONDUITES SOCIALES ==================== --}}
<div class="section">
    <div class="section-title">⚠️ TROUBLES DES CONDUITES SOCIALES</div>
    <div class="two-columns">
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Idées suicidaires :</td><td class="info-value"><span class="{{ $consultation->suicidal_ideation ? 'badge-yes' : 'badge-no' }}">{{ $consultation->suicidal_ideation ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Tentatives suicide :</td><td class="info-value"><span class="{{ $consultation->suicide_attempts ? 'badge-yes' : 'badge-no' }}">{{ $consultation->suicide_attempts ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Fugues :</td><td class="info-value"><span class="{{ $consultation->runaway ? 'badge-yes' : 'badge-no' }}">{{ $consultation->runaway ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
        <div class="col">
            <table class="info-grid">
                <tr class="info-row"><td class="info-label">Vol pathologique :</td><td class="info-value"><span class="{{ $consultation->pathological_stealing ? 'badge-yes' : 'badge-no' }}">{{ $consultation->pathological_stealing ? 'Oui' : 'Non' }}</span></td></tr>
                <tr class="info-row"><td class="info-label">Attentat aux mœurs :</td><td class="info-value"><span class="{{ $consultation->sexual_offenses ? 'badge-yes' : 'badge-no' }}">{{ $consultation->sexual_offenses ? 'Oui' : 'Non' }}</span></td></tr>
            </table>
        </div>
    </div>
    @if($consultation->other_social_conduct_disorders)
    <div class="mt-2"><span class="font-bold">Autres :</span> {{ $consultation->other_social_conduct_disorders }}</div>
    @endif
</div>

{{-- ==================== SECTION 8: ADDICTIONS ==================== --}}
<div class="section">
    <div class="section-title">🍺 ADDICTIONS</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Alcoolisme :</td><td class="info-value"><span class="{{ $consultation->alcoholism ? 'badge-yes' : 'badge-no' }}">{{ $consultation->alcoholism ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">Tabagisme :</td><td class="info-value"><span class="{{ $consultation->smoking ? 'badge-yes' : 'badge-no' }}">{{ $consultation->smoking ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">Autres addictions :</td><td class="info-value">{{ $consultation->other_addictions ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 9: COGNITIF ==================== --}}
<div class="section">
    <div class="section-title">🧠 TROUBLES COGNITIFS</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Troubles du langage :</td><td class="info-value">{{ $consultation->speech_disorders ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Troubles de la mémoire :</td><td class="info-value">{{ $consultation->memory_disorders ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Troubles cours pensée :</td><td class="info-value">{{ $consultation->thought_flow_disorders ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Troubles contenu pensée :</td><td class="info-value">{{ $consultation->thought_content_disorders ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Hallucinations :</td><td class="info-value">{{ $consultation->hallucinations ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Troubles jugement :</td><td class="info-value">{{ $consultation->judgment_disorders ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 10: CONSCIENCE ==================== --}}
<div class="section">
    <div class="section-title">👁️ CONSCIENCE ET VIGILANCE</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Attention :</td><td class="info-value">{{ $consultation->attention_quality ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Orientation temporo-spatiale :</td><td class="info-value">{{ $consultation->spatiotemporal_orientation ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Hypo/Hyper vigilance :</td><td class="info-value"><span class="{{ $consultation->hypovigilance_hypervigilance ? 'badge-yes' : 'badge-no' }}">{{ $consultation->hypovigilance_hypervigilance ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">États crépusculaires :</td><td class="info-value"><span class="{{ $consultation->twilight_states ? 'badge-yes' : 'badge-no' }}">{{ $consultation->twilight_states ? 'Oui' : 'Non' }}</span></td></tr>
        <tr class="info-row"><td class="info-label">États oniroïdes :</td><td class="info-value"><span class="{{ $consultation->oniric_states ? 'badge-yes' : 'badge-no' }}">{{ $consultation->oniric_states ? 'Oui' : 'Non' }}</span></td></tr>
    </table>
</div>

{{-- ==================== SECTION 11: AFFECTS ET HUMEUR ==================== --}}
<div class="section">
    <div class="section-title">😊 AFFECTS ET HUMEUR</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Troubles expression affects :</td><td class="info-value">{{ $consultation->affect_expression_disorders ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Troubles de l'humeur :</td><td class="info-value">{{ $consultation->mood_disorders ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 12: EXAMEN PHYSIQUE ==================== --}}
<div class="section">
    <div class="section-title">🩺 EXAMEN PHYSIQUE</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Constantes :</td><td class="info-value">{{ $consultation->vital_signs ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">État général :</td><td class="info-value">{{ $consultation->general_condition ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Examen cardiovasculaire :</td><td class="info-value">{{ $consultation->cardiovascular_exam ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Examen pulmonaire :</td><td class="info-value">{{ $consultation->pulmonary_exam ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Examen neurologique :</td><td class="info-value">{{ $consultation->neurological_exam ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 13: CONCLUSION ==================== --}}
<div class="section">
    <div class="section-title">📝 CONCLUSION</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Conclusion clinique :</td><td class="info-value">{{ $consultation->clinical_conclusion ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Discussion diagnostique :</td><td class="info-value">{{ $consultation->diagnostic_discussion ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Bilan psychologique :</td><td class="info-value">{{ $consultation->psychological_assesment_summary ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Recommandations thérapeutiques :</td><td class="info-value">{{ $consultation->treatment_recommendations ?? '-' }}</td></tr>
    </table>
</div>

{{-- ==================== SECTION 14: DOCUMENTS ==================== --}}
@if($consultation->documents && count($consultation->documents) > 0)
<div class="section">
    <div class="section-title">📎 DOCUMENTS JOINTs</div>
    <table class="info-grid">
        @foreach($consultation->documents as $doc)
        <tr class="info-row">
            <td class="info-label">Document :</td>
            <td class="info-value">{{ $doc['original_name'] }} ({{ number_format($doc['size'] / 1024, 1) }} KB)</td>
        </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ==================== SECTION 15: MÉDECIN ==================== --}}
<div class="section">
    <div class="section-title">👨‍⚕️ INFORMATIONS COMPLÉMENTAIRES</div>
    <table class="info-grid">
        <tr class="info-row"><td class="info-label">Médecin :</td><td class="info-value">{{ $consultation->doctor->name ?? '-' }}</td></tr>
        <tr class="info-row"><td class="info-label">Statut :</td><td class="info-value">{{ ucfirst($consultation->status) }}</td></tr>
        <tr class="info-row"><td class="info-label">Généré le :</td><td class="info-value">{{ now()->format('d/m/Y H:i') }}</td></tr>
    </table>
</div>

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    Document confidentiel - Clinique Kam-Siham | Tous droits réservés
</div>

</body>
</html>
