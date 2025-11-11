<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prototype AR Markerless</title>
  <script src="{{ asset('js/three.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.137.0/examples/js/loaders/GLTFLoader.js"></script>
  <style>
    body { margin:0; overflow:hidden; }
    #takePhoto {
      position:absolute;
      top:10px; left:10px; z-index:1;
      padding:10px; font-size:16px;
    }
  </style>
</head>
<body>

<button id="takePhoto">Prendre photo</button>

<script>
let scene, camera, renderer, model;

init();
animate();

function init() {
  // Créer la scène
  scene = new THREE.Scene();

  // Créer la caméra
  camera = new THREE.PerspectiveCamera(70, window.innerWidth/window.innerHeight, 0.01, 20);
  camera.position.z = 2;

  // Créer le renderer
  renderer = new THREE.WebGLRenderer({ antialias:true, alpha:true });
  renderer.setSize(window.innerWidth, window.innerHeight);
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
    model.position.set(0, -0.5, -1); // flottant devant caméra
    scene.add(model);
  });

  // Bouton pour prendre photo
  document.getElementById('takePhoto').addEventListener('click', () => {
    renderer.domElement.toBlob(blob => {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'ar_photo.png';
      a.click();
    });
  });

  // Gestion du resize
  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });

  // Activer caméra du smartphone
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
      const video = document.createElement('video');
      video.srcObject = stream;
      video.play();
      video.style.position = 'absolute';
      video.style.top = '0';
      video.style.left = '0';
      video.style.width = '100%';
      video.style.height = '100%';
      video.style.zIndex = '-1';
      document.body.appendChild(video);
    })
    .catch(err => { console.error("Erreur caméra:", err); });
}

// Animation
function animate() {
  requestAnimationFrame(animate);
  renderer.render(scene, camera);
}
</script>
</body>
</html>
