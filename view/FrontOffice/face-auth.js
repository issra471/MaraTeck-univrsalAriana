/**
 * face-auth.js
 * Implements Face Authentication using face-api.js
 */

const btnFaceLogin = document.getElementById('btnFaceLogin');
const loginEmail = document.getElementById('loginEmail');
const scanLine = document.querySelector('.scan-line');
const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';

let isModelsLoaded = false;

function toggleScanEffect(active) {
    if (active) {
        aiContainer.classList.add('active-scan');
        if (scanLine) scanLine.style.display = 'block';
    } else {
        aiContainer.classList.remove('active-scan');
        if (scanLine) scanLine.style.display = 'none';
    }
}
async function loadFaceModels() {
    if (isModelsLoaded) return;

    updateAIStatus('Chargement IA Visage...');
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
        ]);
        isModelsLoaded = true;
        updateAIStatus('IA Visage Prête');
    } catch (err) {
        console.error("Erreur chargement modèles Face-API:", err);
        showToast("Erreur lors du chargement des modèles de reconnaissance faciale.", "error");
    }
}

async function startFaceLogin() {
    const email = loginEmail.value;
    if (!email) {
        showToast("Veuillez saisir votre email d'abord pour le Face ID.", "info");
        return;
    }

    await loadFaceModels();

    // Show AI container if not already visible (for feedback)
    aiContainer.style.display = 'block';
    if (!aiVideo.srcObject) {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        aiVideo.srcObject = stream;
        aiVideo.play();
    }
    document.body.classList.add('active-face-id');

    updateAIStatus('Recherche descripteur...');

    const authPath = typeof FACE_AUTH_PATH !== 'undefined' ? FACE_AUTH_PATH : '../../controller/FaceAuthController.php';

    // 1. Fetch user descriptor from server
    const resp = await fetch(authPath + '?action=get_descriptor', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    });

    const result = await resp.json();
    if (!result.success) {
        showToast(result.message, "error");
        if (!isGestureActive) aiContainer.style.display = 'none';
        return;
    }

    const savedDescriptor = new Float32Array(result.descriptor);
    const userId = result.user_id;

    updateAIStatus('Placez votre visage...');
    toggleScanEffect(true);

    // 2. Start scanning
    let maxTries = 15;
    const scanInterval = setInterval(async () => {
        const detections = await faceapi.detectSingleFace(aiVideo, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.4 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detections) {
            const distance = faceapi.euclideanDistance(detections.descriptor, savedDescriptor);
            console.log("Distance faciale:", distance);

            if (distance < 0.5) { // Threshold for matching
                clearInterval(scanInterval);
                toggleScanEffect(false);
                updateAIStatus('Face ID OK !');
                showToast("Étape de vérification réussie. Connexion...", "success");
                loginUserWithFace(userId);

                if (!isGestureActive) {
                    setTimeout(() => {
                        aiContainer.style.display = 'none';
                        document.body.classList.remove('active-face-id');
                    }, 2000);
                }
            } else {
                updateAIStatus('Scan en cours...');
            }
        }

        maxTries--;
        if (maxTries <= 0) {
            clearInterval(scanInterval);
            toggleScanEffect(false);
            updateAIStatus('Échec Scan');
            showToast("Délai dépassé ou visage non reconnu.", "error");
            if (!isGestureActive) {
                setTimeout(() => {
                    aiContainer.style.display = 'none';
                    document.body.classList.remove('active-face-id');
                }, 2000);
            }
        }
    }, 1000);
}

async function loginUserWithFace(userId) {
    const authPath = typeof FACE_AUTH_PATH !== 'undefined' ? FACE_AUTH_PATH : 'controller/FaceAuthController.php';
    const resp = await fetch(authPath + '?action=login_with_face', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    });

    const result = await resp.json();
    if (result.success) {
        showToast("Bienvenue ! Redirection...", "success");
        setTimeout(() => window.location.href = result.redirect, 1000);
    } else {
        showToast("Erreur de connexion finale.", "error");
    }
}

// Global function to register face (can be called from dashboard)
window.registerMyFace = async function () {
    await loadFaceModels();
    aiContainer.style.display = 'block';

    if (!aiVideo.srcObject) {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        aiVideo.srcObject = stream;
        aiVideo.play();
    }

    document.body.classList.add('active-face-id');

    updateAIStatus('Analyse en cours...');
    showToast("Veuillez rester immobile face à la caméra.", "info");
    toggleScanEffect(true);

    let maxTries = 10;
    const scanInterval = setInterval(async () => {
        const detections = await faceapi.detectSingleFace(aiVideo, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detections) {
            clearInterval(scanInterval);
            toggleScanEffect(false);
            const descriptor = Array.from(detections.descriptor);
            const descriptorInput = document.getElementById('faceDescriptorInput');
            if (descriptorInput) {
                descriptorInput.value = JSON.stringify(descriptor);
                showToast("Visage capturé ! Complétez le formulaire pour finaliser.", "success");
                updateAIStatus('Visage prêt');
            } else {
                // Fallback for dashboard profile update where user is already logged in
                const authPath = typeof FACE_AUTH_PATH !== 'undefined' ? FACE_AUTH_PATH : '../../controller/FaceAuthController.php';
                const resp = await fetch(authPath + '?action=register_face', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ descriptor })
                });
                const resJson = await resp.json();
                showToast(resJson.message, resJson.success ? "success" : "error");
            }

            if (!isGestureActive) {
                setTimeout(() => {
                    aiContainer.style.display = 'none';
                    document.body.classList.remove('active-face-id');
                }, 2000);
            }
        }

        maxTries--;
        if (maxTries <= 0 && detections === undefined) {
            clearInterval(scanInterval);
            toggleScanEffect(false);
            updateAIStatus('Échec Capture');
            showToast("Aucun visage détecté. Veuillez réessayer dans un endroit mieux éclairé.", "error");
            if (!isGestureActive) {
                setTimeout(() => {
                    aiContainer.style.display = 'none';
                    document.body.classList.remove('active-face-id');
                }, 2000);
            }
        }
    }, 1000);
};

if (btnFaceLogin) {
    btnFaceLogin.addEventListener('change', function () {
        if (this.checked) {
            startFaceLogin();
        }
    });
}
