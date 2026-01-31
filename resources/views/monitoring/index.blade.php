<x-app-layout>
    <style>
        /* Masquer la sidebar sur la page monitoring */
        aside.sidebar,
        aside[class*="sidebar"],
        .sidebar-wrapper,
        nav.sidebar,
        #sidebar,
        .iq-sidebar {
            display: none !important;
        }

        /* Ajuster le contenu principal pour occuper toute la largeur */
        main.main-content,
        .main-content,
        #main-content {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Ajuster le wrapper si nécessaire */
        .wrapper {
            padding-left: 0 !important;
        }
    </style>

    <div class="container-fluid content-inner pb-0">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Monitoring des Athlètes</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Sélection de l'athlète -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="userSelect" class="form-label">Sélectionner un athlète</label>
                                <select class="form-select" id="userSelect">
                                    <option value="">-- Choisir un athlète --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Zone de chargement -->
                        <div id="loadingIndicator" class="text-center d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-2">Chargement des statistiques...</p>
                        </div>

                        <!-- Zone de message initial -->
                        <div id="initialMessage" class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Sélectionnez un athlète pour voir ses statistiques
                        </div>

                        <!-- Zone de contenu des statistiques -->
                        <div id="statsContent" class="d-none">

                            <!-- ===== SECTION 1: STATISTIQUES D'ENTRAÎNEMENT ===== -->
                            <div id="trainingStatsCard" class="card mb-4" style="background: linear-gradient(135deg, rgba(58, 123, 213, 0.1), rgba(58, 123, 213, 0.05)); border: 1px solid rgba(58, 123, 213, 0.2);">
                                <div class="card-body">
                                    <!-- Header -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="p-2 rounded" style="background-color: rgba(58, 123, 213, 0.1);">
                                            <i class="fas fa-dumbbell" style="color: #3A7BD5; font-size: 20px;"></i>
                                        </div>
                                        <h5 class="ms-3 mb-0 fw-bold">Statistiques entraînement</h5>
                                    </div>

                                    <!-- Cartes principales -->
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body">
                                                    <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(0, 123, 255, 0.1);">
                                                        <i class="fas fa-calendar-alt" style="color: #007bff; font-size: 16px;"></i>
                                                    </div>
                                                    <p class="text-muted mb-1" style="font-size: 11px;">Total</p>
                                                    <h2 class="text-primary fw-bold mb-1" id="totalTrainings">0</h2>
                                                    <small class="text-muted">entraînements</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body">
                                                    <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(40, 167, 69, 0.1);">
                                                        <i class="fas fa-arrow-trend-up" style="color: #28a745; font-size: 16px;"></i>
                                                    </div>
                                                    <p class="text-muted mb-1" style="font-size: 11px;">Productifs</p>
                                                    <h2 class="text-success fw-bold mb-1" id="productiveTrainings">0</h2>
                                                    <small class="text-muted" id="productivityPercentage">0%</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body">
                                                    <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(255, 193, 7, 0.1);">
                                                        <i class="fas fa-arrow-trend-down" style="color: #ffc107; font-size: 16px;"></i>
                                                    </div>
                                                    <p class="text-muted mb-1" style="font-size: 11px;">Non productifs</p>
                                                    <h2 class="text-warning fw-bold mb-1" id="nonProductiveTrainings">0</h2>
                                                    <small class="text-muted">séances</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Moyennes détaillées -->
                                    <h6 class="mb-3 fw-bold">Moyennes détaillées</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Intensité</small>
                                                    <h4 class="mb-0" id="avgIntensity" style="color: #28a745;">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Focus</small>
                                                    <h4 class="mb-0" id="avgFocus" style="color: #28a745;">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Engagement</small>
                                                    <h4 class="mb-0" id="avgEngagement" style="color: #28a745;">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Fatigue</small>
                                                    <h4 class="mb-0 text-danger" id="avgFatigue">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Stress</small>
                                                    <h4 class="mb-0 text-danger" id="avgStress">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body p-2">
                                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">Énergie</small>
                                                    <h4 class="mb-0" id="avgEnergy" style="color: #28a745;">0.0</h4>
                                                    <small class="text-muted" style="font-size: 8px;">/10</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bouton Plus/Moins de statistiques -->
                                    <div id="expandButton" class="d-none">
                                        <button class="btn btn-outline-primary w-100" id="toggleExpandBtn" onclick="toggleExpandedStats()">
                                            <i class="fas fa-chevron-down me-2" id="expandIcon"></i>
                                            <span id="expandText">Plus de statistiques</span>
                                        </button>
                                    </div>

                                    <!-- Sections étendues (masquées par défaut) -->
                                    <div id="expandedStatsSection" class="d-none mt-3">

                                        <!-- Discipline favorite -->
                                        <div id="disciplineFavoriteSection" class="d-none mb-3">
                                            <div class="card shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="p-2 rounded" style="background-color: rgba(58, 123, 213, 0.1);">
                                                            <i class="fas fa-trophy" style="color: #3A7BD5; font-size: 16px;"></i>
                                                        </div>
                                                        <h6 class="ms-2 mb-0 fw-bold">Discipline favorite</h6>
                                                    </div>
                                                    <div class="p-3 rounded text-center mb-3" style="background-color: rgba(58, 123, 213, 0.06); border: 1px solid rgba(58, 123, 213, 0.18);">
                                                        <h5 class="mb-0 fw-bold" id="favDisciplineName" style="color: #3A7BD5;">-</h5>
                                                    </div>
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(0, 123, 255, 0.1);">
                                                                <i class="fas fa-calendar-alt" style="color: #007bff; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Séances</p>
                                                            <h4 class="fw-bold mb-0" id="favDisciplineCount">0</h4>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(40, 167, 69, 0.1);">
                                                                <i class="fas fa-clock" style="color: #28a745; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Durée moyenne</p>
                                                            <h4 class="fw-bold mb-0" id="favDisciplineDuration">0 min</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Préparation physique -->
                                        <div id="preparationPhysiqueSection" class="d-none mb-3">
                                            <div class="card shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="p-2 rounded" style="background-color: rgba(58, 123, 213, 0.1);">
                                                            <i class="fas fa-dumbbell" style="color: #3A7BD5; font-size: 16px;"></i>
                                                        </div>
                                                        <h6 class="ms-2 mb-0 fw-bold">Préparation physique</h6>
                                                    </div>
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(0, 123, 255, 0.1);">
                                                                <i class="fas fa-calendar-alt" style="color: #007bff; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Séances</p>
                                                            <h4 class="fw-bold mb-0" id="prepPhysiqueCount">0</h4>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(40, 167, 69, 0.1);">
                                                                <i class="fas fa-clock" style="color: #28a745; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Durée moyenne</p>
                                                            <h4 class="fw-bold mb-0" id="prepPhysiqueDuration">0 min</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Visualisation -->
                                        <div id="visualisationSection" class="d-none mb-3">
                                            <div class="card shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="p-2 rounded" style="background-color: rgba(58, 123, 213, 0.1);">
                                                            <i class="fas fa-eye" style="color: #3A7BD5; font-size: 16px;"></i>
                                                        </div>
                                                        <h6 class="ms-2 mb-0 fw-bold">Visualisation</h6>
                                                    </div>
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(0, 123, 255, 0.1);">
                                                                <i class="fas fa-calendar-alt" style="color: #007bff; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Séances</p>
                                                            <h4 class="fw-bold mb-0" id="visualisationCount">0</h4>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="p-2 rounded d-inline-block mb-2" style="background-color: rgba(40, 167, 69, 0.1);">
                                                                <i class="fas fa-clock" style="color: #28a745; font-size: 20px;"></i>
                                                            </div>
                                                            <p class="text-muted mb-1" style="font-size: 12px;">Durée moyenne</p>
                                                            <h4 class="fw-bold mb-0" id="visualisationDuration">0 min</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            <!-- ===== SECTION 2: ÉVOLUTION MENSUELLE PAR CATÉGORIE ===== -->
                            <div class="card mb-4" id="monthlyCategorySection" style="display: none; background-color: #000;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-chart-line me-2 text-white" style="font-size: 20px;"></i>
                                        <h5 class="mb-0 fw-bold text-white">Évolution mensuelle par catégorie</h5>
                                    </div>
                                    <div style="background-color: white; padding: 15px; border-radius: 8px; position: relative;">
                                        <canvas id="monthlyCategoryChart" height="100"></canvas>
                                        <div id="monthlyNoData" class="d-none text-center py-5">
                                            <i class="fas fa-chart-line mb-2" style="font-size: 40px; color: #6c757d;"></i>
                                            <p class="mb-0 text-muted">Aucune donnée disponible</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== SECTION 3: ÉVOLUTION PERFORMANCES COMPÉTITION ===== -->
                            <div class="card mb-4" id="competitionSection" style="display: none; background-color: #000;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-medal me-2 text-white" style="font-size: 20px;"></i>
                                        <h5 class="mb-0 fw-bold text-white">Évolution performances compétition</h5>
                                    </div>
                                    <div style="background-color: white; padding: 15px; border-radius: 8px; position: relative;">
                                        <canvas id="competitionChart" height="100"></canvas>
                                        <div id="competitionNoData" class="d-none text-center py-5">
                                            <i class="fas fa-medal mb-2" style="font-size: 40px; color: #6c757d;"></i>
                                            <p class="mb-0 text-muted">Aucune donnée disponible</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== MESSAGE AUCUNE DONNÉE ===== -->
                            <div id="noDataMessage" class="d-none">
                                <div class="card border-0" style="background-color: #f8f9fa;">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-info-circle mb-3" style="font-size: 48px; color: #6c757d;"></i>
                                        <h5 class="fw-bold mb-2" style="color: #495057;">Aucune donnée disponible</h5>
                                        <p class="text-muted mb-0">Cet athlète n'a pas encore de statistiques enregistrées.</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 CSS dans le head -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Style personnalisé pour Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            color: #495057 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }
    </style>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let monthlyCategoryChart = null;
        let competitionChart = null;
        let expandedStats = false;

        // Initialiser Select2 pour la recherche d'athlète
        $(document).ready(function() {
            $('#userSelect').select2({
                placeholder: '-- Choisir un athlète --',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Aucun athlète trouvé";
                    },
                    searching: function() {
                        return "Recherche en cours...";
                    }
                }
            });

            // Gérer le changement de sélection
            $('#userSelect').on('change', function() {
                const userId = $(this).val();

                if (!userId) {
                    document.getElementById('statsContent').classList.add('d-none');
                    document.getElementById('initialMessage').classList.remove('d-none');
                    return;
                }

                loadUserStats(userId);
            });
        });

        function toggleExpandedStats() {
            expandedStats = !expandedStats;
            const section = document.getElementById('expandedStatsSection');
            const icon = document.getElementById('expandIcon');
            const text = document.getElementById('expandText');

            if (expandedStats) {
                section.classList.remove('d-none');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                text.textContent = 'Moins de statistiques';
            } else {
                section.classList.add('d-none');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                text.textContent = 'Plus de statistiques';
            }
        }

        async function loadUserStats(userId) {
            // Afficher le loader
            document.getElementById('loadingIndicator').classList.remove('d-none');
            document.getElementById('initialMessage').classList.add('d-none');
            document.getElementById('statsContent').classList.add('d-none');

            try {
                // Charger toutes les stats en parallèle
                const [trainingStats, disciplineStats, monthlyData, competitionData] = await Promise.all([
                    fetch(`/monitoring/training-stats?user_id=${userId}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()),
                    fetch(`/monitoring/discipline-stats?user_id=${userId}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()),
                    fetch(`/monitoring/monthly-category?user_id=${userId}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()),
                    fetch(`/monitoring/competition-averages?user_id=${userId}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json())
                ]);

                // Afficher les statistiques
                displayTrainingStats(trainingStats);
                displayDisciplineStats(disciplineStats);
                displayMonthlyCategory(monthlyData);
                displayCompetitionData(competitionData);

                // Vérifier si on a des données
                checkIfHasData(trainingStats, monthlyData, competitionData);

                // Afficher le contenu
                document.getElementById('loadingIndicator').classList.add('d-none');
                document.getElementById('statsContent').classList.remove('d-none');

            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des statistiques: ' + error.message);
                document.getElementById('loadingIndicator').classList.add('d-none');
            }
        }

        function checkIfHasData(trainingStats, monthlyData, competitionData) {
            const hasTrainingData = trainingStats.success && trainingStats.data && trainingStats.data.total_trainings > 0;
            const hasMonthlyData = Array.isArray(monthlyData) && monthlyData.length > 0;
            const hasCompetitionData = Array.isArray(competitionData) && competitionData.length > 0;

            const hasAnyData = hasTrainingData || hasMonthlyData || hasCompetitionData;

            if (hasAnyData) {
                document.getElementById('noDataMessage').classList.add('d-none');
            } else {
                document.getElementById('noDataMessage').classList.remove('d-none');
            }
        }

        function displayTrainingStats(response) {
            if (!response.success || !response.data) {
                document.getElementById('trainingStatsCard').style.display = 'none';
                return;
            }

            const data = response.data;

            // Masquer la carte si aucun entraînement
            if (data.total_trainings === 0) {
                document.getElementById('trainingStatsCard').style.display = 'none';
                return;
            }

            // Afficher la carte
            document.getElementById('trainingStatsCard').style.display = 'block';

            document.getElementById('totalTrainings').textContent = data.total_trainings || 0;
            document.getElementById('productiveTrainings').textContent = data.productive_trainings || 0;
            document.getElementById('nonProductiveTrainings').textContent = (data.total_trainings - data.productive_trainings) || 0;

            const productivityPct = data.total_trainings > 0
                ? ((data.productive_trainings / data.total_trainings) * 100).toFixed(0)
                : 0;
            document.getElementById('productivityPercentage').textContent = productivityPct + '%';

            // Moyennes
            document.getElementById('avgIntensity').textContent = (data.average_intensity || 0).toFixed(1);
            document.getElementById('avgFocus').textContent = (data.average_focus || 0).toFixed(1);
            document.getElementById('avgEngagement').textContent = (data.average_engagement || 0).toFixed(1);
            document.getElementById('avgFatigue').textContent = (data.average_perceived_fatigue || 0).toFixed(1);
            document.getElementById('avgStress').textContent = (data.average_stress || 0).toFixed(1);
            document.getElementById('avgEnergy').textContent = (data.average_energie_jour || 0).toFixed(1);
        }

        function displayDisciplineStats(response) {
            if (!response.success || !response.data) return;

            const data = response.data;
            let hasExtendedStats = false;

            // Discipline favorite
            const mostPracticed = data.most_practiced_discipline;
            if (mostPracticed && mostPracticed.discipline) {
                hasExtendedStats = true;
                document.getElementById('disciplineFavoriteSection').classList.remove('d-none');
                document.getElementById('favDisciplineName').textContent = mostPracticed.discipline;
                document.getElementById('favDisciplineCount').textContent = mostPracticed.training_count || 0;
                document.getElementById('favDisciplineDuration').textContent = Math.round(mostPracticed.average_duration_minutes || 0) + ' min';
            } else {
                document.getElementById('disciplineFavoriteSection').classList.add('d-none');
            }

            // Préparation physique - TOUJOURS afficher même à 0
            const prepPhysique = data.preparation_physique;
            hasExtendedStats = true;
            document.getElementById('preparationPhysiqueSection').classList.remove('d-none');
            document.getElementById('prepPhysiqueCount').textContent = prepPhysique?.training_count || 0;
            document.getElementById('prepPhysiqueDuration').textContent = Math.round(prepPhysique?.average_duration_minutes || 0) + ' min';

            // Visualisation - TOUJOURS afficher même à 0
            const visualisation = data.visualisation;
            document.getElementById('visualisationSection').classList.remove('d-none');
            document.getElementById('visualisationCount').textContent = visualisation?.training_count || 0;
            document.getElementById('visualisationDuration').textContent = Math.round(visualisation?.average_duration_minutes || 0) + ' min';

            // Afficher le bouton expand si on a des stats étendues
            if (hasExtendedStats) {
                document.getElementById('expandButton').classList.remove('d-none');
            } else {
                document.getElementById('expandButton').classList.add('d-none');
            }
        }

        function displayMonthlyCategory(data) {
            // TOUJOURS afficher la section
            document.getElementById('monthlyCategorySection').style.display = 'block';

            // Si pas de données, afficher le message
            if (!Array.isArray(data) || data.length === 0) {
                document.getElementById('monthlyCategoryChart').style.display = 'none';
                document.getElementById('monthlyNoData').classList.remove('d-none');
                if (monthlyCategoryChart) {
                    monthlyCategoryChart.destroy();
                    monthlyCategoryChart = null;
                }
                return;
            }

            // Sinon afficher le graphique
            document.getElementById('monthlyCategoryChart').style.display = 'block';
            document.getElementById('monthlyNoData').classList.add('d-none');

            // Extraire les catégories uniques
            const categories = new Set();
            data.forEach(item => {
                Object.keys(item).forEach(key => {
                    if (key !== 'month' && key !== 'month_start') {
                        categories.add(key);
                    }
                });
            });

            const labels = data.map(d => d.month);
            const datasets = Array.from(categories).map((cat, index) => {
                const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
                return {
                    label: cat,
                    data: data.map(d => d[cat] || null),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '33',
                    tension: 0.4
                };
            });

            if (monthlyCategoryChart) {
                monthlyCategoryChart.destroy();
            }

            const ctx = document.getElementById('monthlyCategoryChart').getContext('2d');
            monthlyCategoryChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10
                        }
                    }
                }
            });
        }

        function displayCompetitionData(data) {
            // TOUJOURS afficher la section
            document.getElementById('competitionSection').style.display = 'block';

            // Si pas de données, afficher le message
            if (!Array.isArray(data) || data.length === 0) {
                document.getElementById('competitionChart').style.display = 'none';
                document.getElementById('competitionNoData').classList.remove('d-none');
                if (competitionChart) {
                    competitionChart.destroy();
                    competitionChart = null;
                }
                return;
            }

            // Sinon afficher le graphique
            document.getElementById('competitionChart').style.display = 'block';
            document.getElementById('competitionNoData').classList.add('d-none');

            const labels = data.map(d => {
                const date = new Date(d.competition_date);
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
            });

            const datasets = [
                {
                    label: 'Attention',
                    data: data.map(d => d.Attention),
                    borderColor: '#FF6384',
                    backgroundColor: '#FF638433',
                    tension: 0.4
                },
                {
                    label: 'Engagement',
                    data: data.map(d => d.Engagement),
                    borderColor: '#36A2EB',
                    backgroundColor: '#36A2EB33',
                    tension: 0.4
                },
                {
                    label: 'Ressentis',
                    data: data.map(d => d.Ressentis),
                    borderColor: '#FFCE56',
                    backgroundColor: '#FFCE5633',
                    tension: 0.4
                },
                {
                    label: 'Performance',
                    data: data.map(d => d.Performance),
                    borderColor: '#4BC0C0',
                    backgroundColor: '#4BC0C033',
                    tension: 0.4
                }
            ];

            if (competitionChart) {
                competitionChart.destroy();
            }

            const ctx = document.getElementById('competitionChart').getContext('2d');
            competitionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return data[index].competition_name + '\n' + data[index].competition_date;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
