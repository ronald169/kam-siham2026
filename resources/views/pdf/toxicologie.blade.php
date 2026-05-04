<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Toxicologie - {{ $consultation->patient->name }}</title>
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
    <div class="consultation-title">CONSULTATION TOXICOLOGIE</div>

    <div class="patient-info">
        <strong>Patient:</strong> {{ $consultation->patient->name }} &nbsp;|&nbsp;
        <strong>N° Dossier:</strong> {{ $consultation->patient->medical_record_number }} &nbsp;|&nbsp;
        <strong>Date:</strong> {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}
    </div>
</div>

{{-- ==================== SECTION 1: HISTOIRE ADDICTIVE ==================== --}}
<div class="section">
    <div class="section-title">📋 HISTOIRE DES CONDUITES ADDICTIVES</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Substances consommées :</td>
            <td class="info-value">{{ $consultation->substances_used ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Âge de début :</td>
            <td class="info-value">{{ $consultation->substances_start_age ?? '-' }} ans</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Raison du début :</td>
            <td class="info-value">{{ $consultation->substances_start_reason ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Motivation actuelle :</td>
            <td class="info-value">{{ $consultation->current_consumption_motivation ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Description de la tolérance :</td>
            <td class="info-value">{{ $consultation->tolerance_description ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Tentatives d'arrêt :</td>
            <td class="info-value">{{ $consultation->withdrawal_attempts ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Motivation à arrêter :</td>
            <td class="info-value">{{ $consultation->stop_motivation ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Types de substances :</td>
            <td class="info-value">{{ $consultation->substances_types ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Durée max d'abstinence :</td>
            <td class="info-value">{{ $consultation->max_abstinence_duration ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Relation avec la substance :</td>
            <td class="info-value">{{ $consultation->substance_relation ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 2: CONSÉQUENCES ==================== --}}
<div class="section">
    <div class="section-title">⚠️ CONSÉQUENCES DE LA CONSOMMATION</div>
    <div class="two-columns">
        <div class="col">
            <table class="info-grid">
                <tr class="info-row">
                    <td class="info-label">Amaigrissement :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->weight_loss ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->weight_loss ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Teint sombre :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->pale_complexion ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->pale_complexion ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Insomnie de manque :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->withdrawal_insomnia ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->withdrawal_insomnia ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Cauchemars :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->nightmares ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->nightmares ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Hallucinations :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->hallucinations ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->hallucinations ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col">
            <table class="info-grid">
                <tr class="info-row">
                    <td class="info-label">Troubles somatiques :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->somatic_disorders ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->somatic_disorders ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Délire/Trouble comportement :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->behavioral_delirium ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->behavioral_delirium ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Problèmes judiciaires :</td>
                    <td class="info-value">
                        <span class="{{ $consultation->legal_issues ? 'badge-yes' : 'badge-no' }}">
                            {{ $consultation->legal_issues ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Épanouissement affectif :</td>
                    <td class="info-value">{{ $consultation->affective_fulfillment ?? '-' }}</td>
                </tr>
                <tr class="info-row">
                    <td class="info-label">Épanouissement sexuel :</td>
                    <td class="info-value">{{ $consultation->sexual_fulfillment ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- ==================== SECTION 3: ÉVALUATION ==================== --}}
<div class="section">
    <div class="section-title">📊 ÉVALUATION DE LA CONSOMMATION ET DÉPENDANCE</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Fréquence et consommation :</td>
            <td class="info-value">{{ $consultation->consumption_pattern ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Investissement dans la dépendance :</td>
            <td class="info-value">{{ $consultation->dependency_investment ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 4: TABLEAU CLINIQUE ==================== --}}
<div class="section">
    <div class="section-title">🩺 TABLEAU CLINIQUE</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">État général :</td>
            <td class="info-value">{{ $consultation->general_condition ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Signes respiratoires :</td>
            <td class="info-value">{{ $consultation->respiratory_signs ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Signes neurologiques :</td>
            <td class="info-value">{{ $consultation->neurological_signs ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Troubles psychiatriques :</td>
            <td class="info-value">{{ $consultation->psychiatric_disorders ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Autres symptômes :</td>
            <td class="info-value">{{ $consultation->other_symptoms ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 5: ANTÉCÉDENTS ==================== --}}
<div class="section">
    <div class="section-title">📁 ANTÉCÉDENTS</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Médicaux / chirurgicaux :</td>
            <td class="info-value">{{ $consultation->medical_surgical_history ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Allergiques :</td>
            <td class="info-value">{{ $consultation->allergy_history ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Psychiatriques :</td>
            <td class="info-value">{{ $consultation->psychiatric_history ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Traumatiques :</td>
            <td class="info-value">{{ $consultation->trauma_history ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 6: EXAMENS ==================== --}}
<div class="section">
    <div class="section-title">🔬 EXAMENS</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Bilan psychologique :</td>
            <td class="info-value">{{ $consultation->psychological_assessment ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Bilan biologique :</td>
            <td class="info-value">{{ $consultation->biological_assessment ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 7: CONCLUSION ET CAT ==================== --}}
<div class="section">
    <div class="section-title">📝 CONCLUSION ET CONDUITE À TENIR</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Conclusion diagnostique :</td>
            <td class="info-value">{{ $consultation->diagnostic_conclusion ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Plan de traitement :</td>
            <td class="info-value">{{ $consultation->treatment_plan ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Recommandations :</td>
            <td class="info-value">{{ $consultation->recommendations ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- ==================== SECTION 8: DOCUMENTS ==================== --}}
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

{{-- ==================== SECTION 9: INFORMATIONS COMPLÉMENTAIRES ==================== --}}
<div class="section">
    <div class="section-title">ℹ️ INFORMATIONS COMPLÉMENTAIRES</div>
    <table class="info-grid">
        <tr class="info-row">
            <td class="info-label">Médecin :</td>
            <td class="info-value">{{ $consultation->doctor->name ?? '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Date de création :</td>
            <td class="info-value">{{ $consultation->created_at ? \Carbon\Carbon::parse($consultation->created_at)->format('d/m/Y H:i') : '-' }}</td>
        </tr>
        <tr class="info-row">
            <td class="info-label">Statut :</td>
            <td class="info-value">
                @if($consultation->status === 'completed')
                    <span class="badge-yes">Terminé</span>
                @else
                    <span class="badge-no">En cours</span>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    Document confidentiel - Clinique Kam-Siham | Généré le {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
