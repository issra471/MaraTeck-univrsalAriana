<?php
// Conditional check for AJAX
if (isset($_GET['ajax'])) {
    // Return only the main content fragment
    ?>
    <section class="profile-section max-w-7xl" style="width: 100%;">
        <div class="glass-card">
            <h3 class="text-gradient"
                style="font-size: 2.25rem; font-weight: 800; margin-bottom: 2rem; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-user-circle"></i>
                Mon Profil
            </h3>

            <form id="profileForm" enctype="multipart/form-data">
                <div class="profile-upload-container">
                    <?php
                    $avatarUrl = !empty($userData['profile_image']) ? '../' . $userData['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=3b82f6&color=fff';
                    ?>
                    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="profile-avatar-lg" id="avatarPreview">
                    <label for="avatarInput" class="avatar-edit-btn">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatarInput" name="avatar" style="display: none;" accept="image/*">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Nom Complet</label>
                        <input type="text" name="full_name" class="form-input"
                            value="<?php echo htmlspecialchars($userData['full_name'] ?? $_SESSION['user_name']); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input"
                            value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input"
                            value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-input" value="<?php echo strtoupper($_SESSION['user_role']); ?>"
                            disabled style="opacity: 0.6;">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Bio / Message</label>
                    <textarea name="bio" class="form-textarea"
                        placeholder="Parlez-nous de vous..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Profile specific scripts
        document.getElementById('avatarInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('profileForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch('../../controller/ProfileController.php?action=update', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showNotification('Profil mis à jour avec succès', 'success');
                    // Update header avatar and name if changed
                    if (result.image_url) {
                        document.querySelector('.user-profile-sm img').src = result.image_url;
                    }
                } else {
                    showNotification(result.message || 'Erreur', 'error');
                }
            } catch (error) {
                showNotification('Erreur de connexion', 'error');
            }
        });
    </script>
    <?php
}
?>