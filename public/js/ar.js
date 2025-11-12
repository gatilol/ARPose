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
        renderer.render(scene, camera);
        renderer.domElement.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            a.download = `ar_photo_${timestamp}.png`;
            a.click();
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
