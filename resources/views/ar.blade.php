<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prototype AR Markerless</title>
  <link rel="stylesheet" href="/css/ar.css">
  <script src="{{ asset('js/three.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.137.0/examples/js/loaders/GLTFLoader.js"></script>
</head>
<body>

<button id="takePhoto">Prendre photo</button>

<script>
let scene, camera, renderer, model;

init();
animate();

function init() {
  scene = new THREE.Scene();

  // Caméra perspective
  camera = new THREE.PerspectiveCamera(70, window.innerWidth/window.innerHeight, 0.01, 20);
  camera.position.z = 2;

  // Renderer plein écran
  renderer = new THREE.WebGLRenderer({ antialias:true, alpha:true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.domElement.style.position = "fixed";
  renderer.domElement.style.top = "0";
  renderer.domElement.style.left = "0";
  renderer.domElement.style.width = "100vw";
  renderer.domElement.style.height = "100vh";
  renderer.domElement.style.zIndex = "0";
  document.body.appendChild(renderer.domElement);

  // Lumière
  const light = new THREE.HemisphereLight(0xffffff, 0xbbbbff, 1);
  light.position.set(0.5,1,0.25);
  scene.add(light);

  // Charger le modèle 3D
  const loader = new THREE.GLTFLoader();
  loader.load('{{ asset("models/human_model.glb") }}', function(gltf) {
    model = gltf.scene;
    model.scale.set(0.5,0.5,0.5);
    model.position.set(0, -0.5, -1);
    scene.add(model);
  });

  // Bouton pour prendre photo
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

  // Resize
  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });

  // Caméra arrière du smartphone
  navigator.mediaDevices.getUserMedia({
    video: { facingMode: { ideal: "environment" } },
    audio: false
  })
  .then(stream => {
      const video = document.createElement('video');
      video.srcObject = stream;
      video.autoplay = true;
      video.muted = true;
      video.playsInline = true;

      video.onloadedmetadata = () => {
          video.play();

          const videoTexture = new THREE.VideoTexture(video);
          videoTexture.minFilter = THREE.LinearFilter;
          videoTexture.magFilter = THREE.LinearFilter;
          videoTexture.format = THREE.RGBFormat;

          // Plan vidéo large pour “maximiser” l’écran
          const geometry = new THREE.PlaneGeometry(5, 5); // très large pour couvrir le plus possible
          const material = new THREE.MeshBasicMaterial({ map: videoTexture });
          const plane = new THREE.Mesh(geometry, material);
          plane.position.z = -2; // derrière le modèle
          scene.add(plane);
      };
  })
  .catch(err => {
      console.error("Erreur caméra:", err);
  });
}

function animate() {
  requestAnimationFrame(animate);
  renderer.render(scene, camera);
}
</script>

</body>
</html>
