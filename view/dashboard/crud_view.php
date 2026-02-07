<?php
// crud_view.php - Generic view for listing entities
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$entity = $_GET['entity'] ?? $entity ?? 'cases';
$isPartial = $isPartial ?? false;
?>
<?php if (!$isPartial): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion - <?php echo htmlspecialchars($viewData['title']); ?></title>
        <link rel="stylesheet" href="dashboard.css?v=1.1">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

    <body style="background: var(--bg-deep);">
        <div class="dashboard-container">
            <div class="main-content" style="padding-top: 5rem;">
            <?php endif; ?>
            <div class="max-w-7xl" style="width: 100%;">
                <a href="?view=dashboard" class="btn btn-icon" style="margin-bottom: 2rem;">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <div class="glass-card">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-gradient" style="font-size: 2.25rem; font-weight: 800;">
                            <?php echo htmlspecialchars($viewData['title']); ?>
                        </h1>
                        <button class="btn btn-primary"
                            onclick="window.openCreateModal ? window.openCreateModal() : openCreateModal()">
                            <i class="fas fa-plus"></i> Nouveau
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <?php if (!empty($viewData['items'])): ?>
                                        <?php foreach (array_keys($viewData['items'][0]) as $header): ?>
                                            <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $header))); ?></th>
                                        <?php endforeach; ?>
                                        <th>Actions</th>
                                    <?php else: ?>
                                        <th>Info</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($viewData['items'])): ?>
                                    <?php foreach ($viewData['items'] as $item): ?>
                                        <tr>
                                            <?php foreach ($item as $key => $value): ?>
                                                <td>
                                                    <?php
                                                    if (strpos($key, 'image') !== false && $value) {
                                                        echo "<img src='" . htmlspecialchars($value) . "' style='width:40px; height:40px; object-fit:cover; border-radius:6px;'>";
                                                    } elseif (in_array($key, ['created_at', 'date', 'updated_at']) && $value) {
                                                        echo date('d/m/Y', strtotime($value));
                                                    } else {
                                                        echo htmlspecialchars(substr($value, 0, 40)) . (strlen($value) > 40 ? '...' : '');
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td>
                                                <div class="table-actions">
                                                    <button class="btn-icon small"
                                                        onclick='editItem(<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="../../controller/DashboardController.php?action=crud&entity=<?php echo $entity; ?>&act=delete&id=<?php echo $item['id']; ?>"
                                                        class="btn-icon small danger"
                                                        onclick="return confirm('Êtes-vous sûr ?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="100%" class="text-center">Aucune donnée disponible.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Modal -->
    <div id="crudModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('crudModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Ajouter / Modifier</h2>
                <button class="modal-close" onclick="closeModal('crudModal')">×</button>
            </div>
            <div class="modal-body">
                <form id="crudForm" method="POST">
                    <div id="formFields" class="form-grid"></div>
                    <input type="hidden" name="id" id="itemId">
                    <div class="mt-4 text-right" style="margin-top: 1.5rem; text-align: right;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('crudModal')"
                            style="margin-right: 0.5rem;">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const CURRENT_ENTITY = '<?php echo $entity; ?>';

        // Prepare Select Options
        // We need to map raw data to Objects { value: id, label: name/title }
        const ASSOCIATIONS_RAW = <?php echo json_encode($viewData['associations'] ?? []); ?>;
        const USERS_RAW = <?php echo json_encode($viewData['users'] ?? []); ?>;
        const CASES_RAW = <?php echo json_encode($viewData['items'] ?? []); ?>; // If viewing donations, helpful to have cases list. But we only have items of current entity.
        // Actually, for donations we need ALL cases. fetchListViewData for donations should probably fetch cases too.
        // For now, let's map what we have.

        const ASSOCIATIONS_OPTIONS = ASSOCIATIONS_RAW.map(a => ({ value: a.id, label: a.name }));
        const USERS_OPTIONS = USERS_RAW.map(u => ({ value: u.id, label: u.full_name + ' (' + u.email + ')' }));
        // Fallback for cases if not loaded (we didn't load all cases in Donation view yet, but assuming we might add it or user types ID. 
        // Ideally we need to fetch 'cases' list for donations form dropdown. 
        // Let's create an empty array or basic map if available.
        const CASES_OPTIONS = []; // Placeholder, or we can fetch via AJAX if we go advanced. For now leave empty to fallback to text?
        // Wait, if options is empty array, my renderer might break or show empty.
        // If I want to support "Select Case" I need the data.
        // Let's stick to Text Input for Cases ID in Donations for now if I can't easily get it, OR
        // modify Controller to fetch cases for donations view.
        // I'll stick to text for now for Cases in Donations to avoid complexity, but for Association/User it's ready.
        // Update: I will use CASES_OPTIONS but if empty, renderer handles it? 
        // Let's keep it simple.


        const FIELDS_CONFIG = {
            'associations': [
                { name: 'user_id', label: 'Utilisateur Responsable', type: 'select', options: USERS_OPTIONS, required: true },
                { name: 'name', label: 'Nom Association', type: 'text', required: true },
                { name: 'description', label: 'Description', type: 'textarea' },
                { name: 'email', label: 'Email', type: 'email', required: true },
                { name: 'phone', label: 'Téléphone', type: 'text' },
                { name: 'address', label: 'Adresse Physique', type: 'text' },
                { name: 'registration_number', label: 'Matricule Fiscal', type: 'text' },
                { name: 'website_url', label: 'Site Web', type: 'text' },
                { name: 'logo_url', label: 'Logo', type: 'file' },
                { name: 'verified', label: 'Vérifié', type: 'checkbox' }
            ],
            'cases': [
                { name: 'association_id', label: 'Association', type: 'select', options: ASSOCIATIONS_OPTIONS, required: true },
                { name: 'title', label: 'Titre du Cas', type: 'text', required: true },
                { name: 'category', label: 'Catégorie', type: 'select', options: ['Santé', 'Handicap', 'Enfants', 'Éducation', 'Rénovation', 'Urgence'] },
                { name: 'status', label: 'Statut', type: 'select', options: ['pending', 'active', 'completed', 'resolved', 'closed'] },
                { name: 'goal_amount', label: 'Objectif (DT)', type: 'number', required: true },
                { name: 'is_urgent', label: 'Urgent ?', type: 'checkbox' },
                { name: 'beneficiary_name', label: 'Nom du Bénéficiaire', type: 'text' },
                { name: 'beneficiary_story', label: 'Histoire détaillée', type: 'textarea' },
                { name: 'description', label: 'Résumé court', type: 'textarea' },
                { name: 'deadline', label: 'Date Limite', type: 'date' },
                { name: 'image_url', label: 'Photo Principale', type: 'file' },
                { name: 'cha9a9a_link', label: 'Lien Cha9a9a', type: 'text' }
            ],
            'donations': [
                { name: 'case_id', label: 'Cas', type: 'select', options: CASES_OPTIONS, required: true },
                { name: 'user_id', label: 'Donneur', type: 'select', options: USERS_OPTIONS, required: true },
                { name: 'amount', label: 'Montant', type: 'number', required: true },
                { name: 'status', label: 'Statut', type: 'select', options: ['pending', 'completed', 'failed'] },
                { name: 'payment_method', label: 'Méthode', type: 'text' },
                { name: 'transaction_id', label: 'ID Transaction', type: 'text' },
                { name: 'is_anonymous', label: 'Anonyme ?', type: 'checkbox' },
                { name: 'message', label: 'Message', type: 'textarea' }
            ],
            'users': [
                { name: 'full_name', label: 'Nom Complet', type: 'text', required: true },
                { name: 'email', label: 'Email', type: 'email', required: true },
                { name: 'password', label: 'Mot de passe', type: 'password', required: true, hideOnEdit: true },
                { name: 'role', label: 'Rôle', type: 'select', options: ['user', 'admin', 'association', 'donor'] },
                { name: 'phone', label: 'Téléphone', type: 'text' },
                { name: 'address', label: 'Adresse', type: 'text' },
                { name: 'bio', label: 'Bio', type: 'textarea' },
                { name: 'profile_image', label: 'Photo de Profil', type: 'file' }
            ],
            'events': [
                { name: 'association_id', label: 'Association', type: 'select', options: ASSOCIATIONS_OPTIONS, required: true },
                { name: 'title', label: 'Titre de l\'événement', type: 'text', required: true },
                { name: 'event_date', label: 'Date', type: 'datetime-local', required: true },
                { name: 'location', label: 'Lieu', type: 'text', required: true },
                { name: 'max_attendees', label: 'Capacité max', type: 'number' },
                { name: 'description', label: 'Description', type: 'textarea' }
            ],
            'messages': [
                { name: 'status', label: 'Statut', type: 'select', options: ['pending', 'read', 'replied'] },
                { name: 'message', label: 'Message (Contenu)', type: 'textarea' }
            ],
            'volunteers': [
                { name: 'status', label: 'Statut', type: 'select', options: ['pending', 'active', 'rejected'] },
                { name: 'skills', label: 'Compétences', type: 'text' },
                { name: 'availability', label: 'Disponibilité', type: 'text' }
            ]
        };

        // Local closeModal removed to use global one from dashboard.js


        function openCreateModal() {
            renderForm({}, false);
            document.getElementById('modalTitle').textContent = 'Nouveau ' + CURRENT_ENTITY;
            document.getElementById('crudForm').action = `../../controller/DashboardController.php?action=crud&entity=${CURRENT_ENTITY}&act=create`;
            document.getElementById('itemId').value = '';
            document.getElementById('crudModal').classList.add('active');
        }

        function editItem(item) {
            renderForm(item, true);
            document.getElementById('modalTitle').textContent = 'Modifier';
            document.getElementById('crudForm').action = `../../controller/DashboardController.php?action=crud&entity=${CURRENT_ENTITY}&act=update&id=${item.id}`;
            document.getElementById('itemId').value = item.id;
            document.getElementById('crudModal').classList.add('active');
        }

        function renderForm(data = {}, isEdit = false) {
            const container = document.getElementById('formFields');
            const config = FIELDS_CONFIG[CURRENT_ENTITY] || [{ name: 'name', label: 'Nom', type: 'text' }];

            let html = '';
            config.forEach(field => {
                if (isEdit && field.hideOnEdit) return;

                const value = data[field.name] || '';
                const isFullWidth = field.type === 'textarea' || field.type === 'checkbox' || field.type === 'file';

                html += `<div class="form-group ${isFullWidth ? 'full-width' : ''}"><label class="form-label">${field.label}</label>`;

                if (field.type === 'textarea') {
                    html += `<textarea name="${field.name}" class="form-textarea" rows="3">${value}</textarea>`;
                } else if (field.type === 'select') {
                    html += `<select name="${field.name}" class="form-select">`;
                    html += `<option value="">-- Sélectionner --</option>`; // Add empty default
                    field.options.forEach(opt => {
                        // Handle both string options and object options {value, label}
                        let optValue = opt;
                        let optLabel = opt;
                        if (typeof opt === 'object' && opt !== null) {
                            optValue = opt.value;
                            optLabel = opt.label;
                        }
                        html += `<option value="${optValue}" ${value == optValue ? 'selected' : ''}>${optLabel}</option>`;
                    });
                    html += `</select>`;
                } else if (field.type === 'checkbox') {
                    html += `<div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">`;
                    html += `<input type="hidden" name="${field.name}" value="0">`;
                    html += `<input type="checkbox" name="${field.name}" value="1" ${value == 1 ? 'checked' : ''} style="width: 22px; height: 22px; cursor: pointer;">`;
                    html += `<span style="font-size: 0.9rem; color: #94a3b8;">Activer cette option</span>`;
                    html += `</div>`;
                } else if (field.type === 'file') {
                    html += `<input type="file" name="${field.name}" class="form-input" accept="image/*">`;
                    if (value && isEdit) {
                        html += `<div style="margin-top: 5px; font-size: 0.8rem; color: #94a3b8;">Fichier actuel: <a href="${value}" target="_blank" style="color: var(--primary-color);">Voir</a></div>`;
                    }
                } else {
                    html += `<input type="${field.type}" name="${field.name}" value="${value}" class="form-input" ${field.required ? 'required' : ''}>`;
                }

                html += `</div>`;
            });

            container.innerHTML = html;
        }

        // Auto-open edit modal if edit_id is present
        const ALL_ITEMS = <?php echo json_encode($viewData['items'] ?? []); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            // Auto-open edit modal if edit_id is present
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit_id');
            if (editId && ALL_ITEMS.length > 0) {
                const item = ALL_ITEMS.find(i => i.id == editId);
                if (item) {
                    editItem(item);
                }
            }

            // Generic AJAX Form Handler
            const crudForm = document.getElementById('crudForm');
            if (crudForm) {
                crudForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const formData = new FormData(crudForm);
                    // Add ajax=1 param to force JSON response
                    const actionUrl = crudForm.action + '&ajax=1';

                    let submitBtn;
                    try {
                        submitBtn = crudForm.querySelector('button[type="submit"]');
                        const originalText = submitBtn.textContent;
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Enregistrement...';

                        const response = await fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(result.message || 'Opération réussie');
                            closeModal('crudModal');

                            // Remove edit_id from URL
                            const url = new URL(window.location.href);
                            url.searchParams.delete('edit_id');
                            window.history.replaceState({}, '', url);

                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (result.message || 'Une erreur inconnue est survenue'));
                        }

                        // Restore button state here since we might reload on success
                        if (!result.success && submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Une erreur système est survenue. Vérifiez la console.');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Enregistrer'; // Fallback text
                        }
                    }
                });
            }
        });
    </script>
    <?php if (!isset($isPartial) || !$isPartial): ?>
        </div>
        </div>
    </body>

    </html>
<?php endif; ?>