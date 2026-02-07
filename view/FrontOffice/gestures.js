const gestureToggle = document.getElementById('gesture-toggle');
const aiContainer = document.getElementById('ai-container');
const aiVideo = document.getElementById('ai-video');
const aiCanvas = document.getElementById('ai-canvas');
const aiStatus = document.getElementById('ai-status');
const gestureIndicator = document.getElementById('gesture-indicator');
const virtualCursor = document.getElementById('virtual-cursor');

let hands;
let camera;
let isGestureActive = false;
let lastGestureTime = 0;
let lastClickTime = 0;
const GESTURE_COOLDOWN = 1200;
const CLICK_COOLDOWN = 1000;
const PINCH_THRESHOLD = 0.05; // Distance between thumb and index

function showGestureIndicator(emoji) {
    if (!gestureIndicator) return;
    gestureIndicator.textContent = emoji;
    gestureIndicator.classList.remove('active');
    void gestureIndicator.offsetWidth;
    gestureIndicator.classList.add('active');
}

async function initGestures() {
    hands = new Hands({
        locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
    });

    hands.setOptions({
        maxNumHands: 1,
        modelComplexity: 1,
        minDetectionConfidence: 0.7,
        minTrackingConfidence: 0.7
    });

    hands.onResults(onResults);

    camera = new Camera(aiVideo, {
        onFrame: async () => {
            await hands.send({ image: aiVideo });
        },
        width: 150,
        height: 110
    });
}

function onResults(results) {
    const ctx = aiCanvas.getContext('2d');
    ctx.save();
    ctx.clearRect(0, 0, aiCanvas.width, aiCanvas.height);

    if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
        const landmarks = results.multiHandLandmarks[0];

        // Draw Skeleton
        drawConnectors(ctx, landmarks, HAND_CONNECTIONS, { color: '#3b82f6', lineWidth: 2 });
        drawLandmarks(ctx, landmarks, { color: '#f43f5e', lineWidth: 1, radius: 2 });

        handleGestures(landmarks);
        updateVirtualCursor(landmarks);
    } else {
        if (virtualCursor) virtualCursor.style.display = 'none';
    }
    ctx.restore();
}

function updateVirtualCursor(landmarks) {
    if (!virtualCursor) return;

    // Use index finger tip (8) for cursor position
    const indexTip = landmarks[8];

    // Map coordinates (video is 150x110, inverted for mirror effect)
    const x = (1 - indexTip.x) * window.innerWidth;
    const y = indexTip.y * window.innerHeight;

    virtualCursor.style.display = 'block';
    virtualCursor.style.left = `${x}px`;
    virtualCursor.style.top = `${y}px`;

    // Detect Pinch (Thumb tip 4 and Index tip 8)
    const thumbTip = landmarks[4];
    const distance = Math.sqrt(
        Math.pow(indexTip.x - thumbTip.x, 2) +
        Math.pow(indexTip.y - thumbTip.y, 2)
    );

    if (distance < PINCH_THRESHOLD) {
        virtualCursor.classList.add('pinching');
        handlePinchClick(x, y);
    } else {
        virtualCursor.classList.remove('pinching');
    }
}

function handlePinchClick(x, y) {
    const now = Date.now();
    if (now - lastClickTime < CLICK_COOLDOWN) return;

    lastClickTime = now;

    // Visual Pulse
    const pulse = document.createElement('div');
    pulse.className = 'cursor-pulse';
    pulse.style.left = `${x}px`;
    pulse.style.top = `${y}px`;
    document.body.appendChild(pulse);
    setTimeout(() => pulse.remove(), 500);

    // Actual Click
    const element = document.elementFromPoint(x, y);
    if (element) {
        element.click();
        showToast("Air Click 🎯", "success");
    }
}

function handleGestures(landmarks) {
    const now = Date.now();
    if (now - lastGestureTime < GESTURE_COOLDOWN) return;

    const isOpen = isHandOpen(landmarks);
    const isClosed = isFist(landmarks);

    if (isOpen) {
        scrollPage('up');
        lastGestureTime = now;
        updateAIStatus('Scroll Up');
        showGestureIndicator('✋');
        showToast("Navigation : Défilement vers le haut", "info");
    } else if (isClosed) {
        scrollPage('down');
        lastGestureTime = now;
        updateAIStatus('Scroll Down');
        showGestureIndicator('✊');
        showToast("Navigation : Défilement vers le bas", "info");
    }
}

function isHandOpen(landmarks) {
    const fingers = [8, 12, 16, 20];
    return fingers.every(tip => landmarks[tip].y < landmarks[tip - 2].y);
}

function isFist(landmarks) {
    const fingers = [8, 12, 16, 20];
    return fingers.every(tip => landmarks[tip].y > landmarks[tip - 2].y);
}

function scrollPage(direction) {
    const amount = 300;
    window.scrollBy({
        top: direction === 'up' ? -amount : amount,
        behavior: 'smooth'
    });
}

function updateAIStatus(text) {
    aiStatus.textContent = text;
    setTimeout(() => {
        if (isGestureActive) aiStatus.textContent = 'IA Active';
    }, 1500);
}

gestureToggle.addEventListener('click', async () => {
    isGestureActive = !isGestureActive;

    if (isGestureActive) {
        aiContainer.style.display = 'block';
        gestureToggle.classList.add('active');
        if (!hands) await initGestures();
        camera.start();
        announceNotification("Air Control activé. Pincez pour cliquer, ouvrez/fermez pour défiler.");
    } else {
        aiContainer.style.display = 'none';
        if (virtualCursor) virtualCursor.style.display = 'none';
        gestureToggle.classList.remove('active');
        if (camera) camera.stop();
        announceNotification("Air Control désactivé.");
    }
});
