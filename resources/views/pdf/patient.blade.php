<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche patient - {{ $patient->medical_record_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .hospital-name {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
        }
        .hospital-subtitle {
            font-size: 12px;
            color: #6b7280;
        }
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            background: #f3f4f6;
            padding: 6px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .info-value {
            display: table-cell;
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .two-columns {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-active { background: #10b981; color: white; }
        .badge-discharged { background: #3b82f6; color: white; }
        .badge-transferred { background: #f59e0b; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">Clinique Kam-Siham</div>
        <div class="hospital-subtitle">Douala - Cameroun</div>
        <div style="margin-top: 5px;">
            <strong>Fiche Patient</strong> | N° Dossier: {{ $patient->medical_record_number }}
        </div>
        <div style="font-size: 9px; color: #6b7280;">
            Généré le {{ now()->format('d/m/Y H:i') }} par {{ auth()->user()->name ?? 'Système' }}
        </div>
    </div>

    {{-- ==================== IDENTITÉ ==================== --}}
    <div class="section">
        <div class="section-title">IDENTITÉ</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Nom complet :</div><div class="info-value">{{ $patient->name }}</div></div>
            <div class="info-row"><div class="info-label">Date de naissance :</div><div class="info-value">{{ $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '-' }} ({{ $patient->age ?? '-' }} ans)</div></div>
            <div class="info-row"><div class="info-label">Sexe :</div><div class="info-value">{{ $patient->sex ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Lieu de naissance :</div><div class="info-value">{{ $patient->place_of_birth ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Nationalité :</div><div class="info-value">{{ $patient->nationality ?? 'Camerounaise' }}</div></div>
            <div class="info-row"><div class="info-label">Tribu :</div><div class="info-value">{{ $patient->tribe ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Religion :</div><div class="info-value">{{ $patient->religion ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Profession :</div><div class="info-value">{{ $patient->profession ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Niveau d'étude :</div><div class="info-value">{{ $patient->study_level ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Situation matrimoniale :</div><div class="info-value">{{ $patient->marital_status ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Adresse :</div><div class="info-value">{{ $patient->patient_address ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== CONTACTS ==================== --}}
    <div class="section">
        <div class="section-title">CONTACTS</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Téléphone :</div><div class="info-value">{{ $patient->patient_phone ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Email :</div><div class="info-value">{{ $patient->patient_email ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Contact urgence - Nom :</div><div class="info-value">{{ $patient->emergency_contact_name ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Contact urgence - Téléphone :</div><div class="info-value">{{ $patient->emergency_contact_phone ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Lien de parenté :</div><div class="info-value">{{ $patient->emergency_contact_relation ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== ANTÉCÉDENTS ==================== --}}
    <div class="section">
        <div class="section-title">ANTÉCÉDENTS</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Antécédents médicaux :</div><div class="info-value">{{ $patient->medical_antecedents ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Antécédents chirurgicaux :</div><div class="info-value">{{ $patient->surgical_antecedents ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Antécédents familiaux :</div><div class="info-value">{{ $patient->family_conflicts ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Problèmes de santé :</div><div class="info-value">{{ $patient->health_problems ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== PARENTS ==================== --}}
    <div class="section">
        <div class="section-title">PARENTS</div>
        <div class="two-columns">
            <div class="col">
                <div style="font-weight: bold; margin-bottom: 5px;">Père</div>
                <div class="info-grid">
                    <div class="info-row"><div class="info-label">Nom :</div><div class="info-value">{{ $patient->father_name ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Âge :</div><div class="info-value">{{ $patient->father_age ?? '-' }} ans</div></div>
                    <div class="info-row"><div class="info-label">Profession :</div><div class="info-value">{{ $patient->father_profession ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">État de santé :</div><div class="info-value">{{ $patient->father_health_status ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Niveau d'étude :</div><div class="info-value">{{ $patient->father_education_level ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">En vie :</div><div class="info-value">{{ $patient->father_alive ? 'Oui' : 'Non' }}</div></div>
                </div>
            </div>
            <div class="col">
                <div style="font-weight: bold; margin-bottom: 5px;">Mère</div>
                <div class="info-grid">
                    <div class="info-row"><div class="info-label">Nom :</div><div class="info-value">{{ $patient->mother_name ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Âge :</div><div class="info-value">{{ $patient->mother_age ?? '-' }} ans</div></div>
                    <div class="info-row"><div class="info-label">Profession :</div><div class="info-value">{{ $patient->mother_profession ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">État de santé :</div><div class="info-value">{{ $patient->mother_health_status ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Niveau d'étude :</div><div class="info-value">{{ $patient->mother_education_level ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">En vie :</div><div class="info-value">{{ $patient->mother_alive ? 'Oui' : 'Non' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== DYNAMIQUE FAMILIALE ==================== --}}
    <div class="section">
        <div class="section-title">DYNAMIQUE FAMILIALE</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Type de famille :</div><div class="info-value">{{ $patient->type_of_family ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Rang de l'enfant :</div><div class="info-value">{{ $patient->order_child ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Relation entre parents :</div><div class="info-value">{{ $patient->parents_relationship ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Relation entre frères/sœurs :</div><div class="info-value">{{ $patient->siblings_relationship ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Relation privilégiée :</div><div class="info-value">{{ $patient->privileged_relationship ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Fréquence retour parents :</div><div class="info-value">{{ $patient->frequency_stay_with_parents ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Dynamique familiale :</div><div class="info-value">{{ $patient->family_dynamics ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== RELATIONS SOCIALES ==================== --}}
    <div class="section">
        <div class="section-title">RELATIONS SOCIALES</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Nombre d'amis :</div><div class="info-value">{{ $patient->number_of_friends ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Vrais amis :</div><div class="info-value">{{ $patient->number_of_true_friends ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Qualité des relations intimes :</div><div class="info-value">{{ $patient->intimate_friends_quality ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Nature des relations :</div><div class="info-value">{{ $patient->social_relations_nature ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Loisirs :</div><div class="info-value">{{ $patient->leisure_activities ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== PSYCHOLOGIE ==================== --}}
    <div class="section">
        <div class="section-title">PSYCHOLOGIE</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Relation avec soi-même :</div><div class="info-value">{{ $patient->self_relationship ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Problèmes perçus :</div><div class="info-value">{{ $patient->self_perceived_problems ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Jugement sur soi :</div><div class="info-value">{{ $patient->self_judgment ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Attentes du psychologue :</div><div class="info-value">{{ $patient->expectations_from_psychologist ?? '-' }}</div></div>
        </div>
    </div>

    {{-- ==================== MÉDICAL ==================== --}}
    <div class="section">
        <div class="section-title">MÉDICAL</div>
        <div class="two-columns">
            <div class="col">
                <div class="info-grid">
                    <div class="info-row"><div class="info-label">Groupe sanguin :</div><div class="info-value">{{ $patient->blood_type ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Poids :</div><div class="info-value">{{ $patient->weight ? $patient->weight . ' kg' : '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Taille :</div><div class="info-value">{{ $patient->height ? $patient->height . ' cm' : '-' }}</div></div>
                    <div class="info-row"><div class="info-label">IMC :</div><div class="info-value">{{ $patient->weight && $patient->height ? round($patient->weight / pow($patient->height/100, 2), 1) : '-' }}</div></div>
                </div>
            </div>
            <div class="col">
                <div class="info-grid">
                    <div class="info-row"><div class="info-label">Allergies :</div><div class="info-value">{{ $patient->allergies ?? 'Aucune' }}</div></div>
                    <div class="info-row"><div class="info-label">Traitements en cours :</div><div class="info-value">{{ $patient->current_treatments ?? 'Aucun' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== DOSSIER ==================== --}}
    <div class="section">
        <div class="section-title">INFORMATIONS DOSSIER</div>
        <div class="info-grid">
            <div class="info-row"><div class="info-label">N° Dossier :</div><div class="info-value">{{ $patient->medical_record_number }}</div></div>
            <div class="info-row"><div class="info-label">Date d'admission :</div><div class="info-value">{{ $patient->admission_date ? $patient->admission_date->format('d/m/Y') : '-' }}</div></div>
            <div class="info-row"><div class="info-label">Médecin référent :</div><div class="info-value">{{ $patient->referringDoctor->name ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Créé par :</div><div class="info-value">{{ $patient->creator->name ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Date de création :</div><div class="info-value">{{ $patient->created_at ? $patient->created_at->format('d/m/Y H:i') : '-' }}</div></div>
            <div class="info-row"><div class="info-label">Statut :</div><div class="info-value">
                @if($patient->status === 'active')
                    <span class="badge badge-active">Actif</span>
                @elseif($patient->status === 'discharged')
                    <span class="badge badge-discharged">Sorti</span>
                @else
                    <span class="badge badge-transferred">Transféré</span>
                @endif
            </div></div>
            @if($patient->discharge_date)
                <div class="info-row"><div class="info-label">Date de sortie :</div><div class="info-value">{{ $patient->discharge_date->format('d/m/Y') }}</div></div>
                <div class="info-row"><div class="info-label">Motif de sortie :</div><div class="info-value">{{ $patient->discharge_reason ?? '-' }}</div></div>
            @endif
        </div>
    </div>

    @if($patient->toxicologies->count() > 0 || $patient->psychopathologies->count() > 0 || $patient->medecines->count() > 0)
    {{-- ==================== CONSULTATIONS ==================== --}}
    <div class="section">
        <div class="section-title">CONSULTATIONS</div>

        @if($patient->toxicologies->count() > 0)
            <div style="margin-bottom: 10px;">
                <div style="font-weight: bold; margin: 10px 0 5px 0;">Toxicologie</div>
                @foreach($patient->toxicologies as $consult)
                <div class="info-grid" style="margin-bottom: 5px;">
                    <div class="info-row"><div class="info-label">Date :</div><div class="info-value">{{ $consult->consultation_date ? $consult->consultation_date->format('d/m/Y') : '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Substances :</div><div class="info-value">{{ $consult->substances_used ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Conclusion :</div><div class="info-value">{{ $consult->diagnostic_conclusion ?? '-' }}</div></div>
                </div>
                @endforeach
            </div>
        @endif

        @if($patient->psychopathologies->count() > 0)
            <div style="margin-bottom: 10px;">
                <div style="font-weight: bold; margin: 10px 0 5px 0;">Psychopathologie</div>
                @foreach($patient->psychopathologies as $consult)
                <div class="info-grid" style="margin-bottom: 5px;">
                    <div class="info-row"><div class="info-label">Date :</div><div class="info-value">{{ $consult->consultation_date ? $consult->consultation_date->format('d/m/Y') : '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Motif :</div><div class="info-value">{{ $consult->chief_complaint ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Conclusion :</div><div class="info-value">{{ $consult->clinical_conclusion ?? '-' }}</div></div>
                </div>
                @endforeach
            </div>
        @endif

        @if($patient->medecines->count() > 0)
            <div>
                <div style="font-weight: bold; margin: 10px 0 5px 0;">Médecine Générale</div>
                @foreach($patient->medecines as $consult)
                <div class="info-grid" style="margin-bottom: 5px;">
                    <div class="info-row"><div class="info-label">Date :</div><div class="info-value">{{ $consult->consultation_date ? $consult->consultation_date->format('d/m/Y') : '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Motif :</div><div class="info-value">{{ $consult->consultation_reason ?? '-' }}</div></div>
                    <div class="info-row"><div class="info-label">Diagnostic :</div><div class="info-value">{{ $consult->diagnostic_hypothesis ?? '-' }}</div></div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ==================== TRAITEMENTS ==================== --}}
    @if($patient->treatments->count() > 0)
    <div class="section">
        <div class="section-title">TRAITEMENTS ADMINISTRÉS</div>
        @foreach($patient->treatments as $treatment)
        <div class="info-grid" style="margin-bottom: 8px;">
            <div class="info-row"><div class="info-label">Date :</div><div class="info-value">{{ $treatment->treatment_date ? $treatment->treatment_date->format('d/m/Y') : '-' }} {{ $treatment->treatment_time ? $treatment->treatment_time->format('H:i') : '' }}</div></div>
            <div class="info-row"><div class="info-label">Soins :</div><div class="info-value">{{ $treatment->care_provided ?? $treatment->observations ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">État :</div><div class="info-value">{{ $treatment->patient_condition ?? '-' }}</div></div>
            <div class="info-row"><div class="info-label">Médecin :</div><div class="info-value">{{ $treatment->doctor->name ?? '-' }}</div></div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        Document confidentiel - Clinique Kam-Siham | Tous droits réservés
    </div>
</body>
</html>
