<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prototype AR Markerless</title>
  <link rel="stylesheet" href="{{ asset('css/ar.css') }}">
  <script src="{{ asset('js/three.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.137.0/examples/js/loaders/GLTFLoader.js"></script>
</head>
<body>

<video id="video" autoplay playsinline muted></video>

<button id="takePhoto" class="ar-button">Prendre photo</button>
<button id="switchCamera" class="ar-button">Changer caméra</button>

<script src="{{ asset('js/ar.js') }}"></script>
</body>
</html>
