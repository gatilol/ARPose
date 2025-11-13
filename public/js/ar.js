let scene, camera, renderer, model;
let currentFacing = 'environment';
let currentStream = null;

init();
animate();

function init() {
    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.01, 20);
    camera.position.z = 2;

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x000000, 0); // fond transparent pour voir la vidéo
    document.body.appendChild(renderer.domElement);

    const light = new THREE.HemisphereLight(0xffffff, 0xbbbbff, 1);
    light.position.set(0.5, 1, 0.25);
    scene.add(light);

    // Charger le modèle 3D
    const loader = new THREE.GLTFLoader();
    loader.load('/models/human_model.glb', function (gltf) {
        model = gltf.scene;
        model.scale.set(0.5, 0.5, 0.5);
        model.position.set(0, -0.5, -1);
        scene.add(model);
    });

    // Bouton photo
    document.getElementById('takePhoto').addEventListener('click', () => {
        const video = document.getElementById('video');
        
        // Créer un canvas temporaire pour combiner vidéo + 3D
        const canvas = document.createElement('canvas');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        const ctx = canvas.getContext('2d');
        
        // Calculer les dimensions pour "object-fit: cover" (comme la vidéo CSS)
        const videoRatio = video.videoWidth / video.videoHeight;
        const canvasRatio = canvas.width / canvas.height;
        
        let drawWidth, drawHeight, drawX, drawY;
        
        if (videoRatio > canvasRatio) {
            // Vidéo plus large : on rogne les côtés
            drawHeight = canvas.height;
            drawWidth = drawHeight * videoRatio;
            drawX = (canvas.width - drawWidth) / 2;
            drawY = 0;
        } else {
            // Vidéo plus haute : on rogne le haut/bas
            drawWidth = canvas.width;
            drawHeight = drawWidth / videoRatio;
            drawX = 0;
            drawY = (canvas.height - drawHeight) / 2;
        }
        
        // 1. Dessiner la vidéo avec le bon ratio (cover)
        ctx.drawImage(video, drawX, drawY, drawWidth, drawHeight);
        
        // 2. Rendre la scène 3D
        renderer.render(scene, camera);
        
        // 3. Dessiner le rendu 3D par-dessus la vidéo
        ctx.drawImage(renderer.domElement, 0, 0, canvas.width, canvas.height);
        
        // 4. Télécharger l'image combinée
        canvas.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            a.download = `ar_photo_${timestamp}.png`;
            a.click();
            URL.revokeObjectURL(url);
        });
    });

    
    // Bouton switch caméra
    document.getElementById('switchCamera').addEventListener('click', () => {
        currentFacing = (currentFacing === 'environment') ? 'user' : 'environment';
        startCamera(currentFacing);
    });

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    startCamera(currentFacing);
}

function startCamera(facingMode) {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }

    const video = document.getElementById('video');

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facingMode } },
        audio: false
    })
    .then(stream => {
        currentStream = stream;
        video.srcObject = stream;
    })
    .catch(err => console.error("Erreur caméra:", err));
}

function animate() {
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
}
