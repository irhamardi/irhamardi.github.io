<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Spin the Wheel</title>
  <link rel="shortcut icon" href="icon/favicon.ico" type="image/x-icon" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background: url("img/bg_lebaran_koitoto.jpg") no-repeat center center;
      background-size: cover;
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .logo-container {
      margin-bottom: 30px;
    }

    .wheel-container {
      position: relative;
      text-align: center;
    }

    canvas#canvas {
      border-radius: 50%;
      width: 320px;
      height: 320px;
      background-color: white;
      max-width: 100%;
    }

    .arrow {
      position: absolute;
      top: -40px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 35px;
      color: red;
    }

    .controls {
      margin-top: 20px;
    }

    #spinVoucher {
      margin-bottom: 15px;
      padding: 10px;
      font-size: 16px;
      width: 100%;
      max-width: 300px;
      margin-left: auto;
      margin-right: auto;
    }

    #spinBtn {
      background-color: rgb(37, 209, 2);
      color: white;
      padding: 12px 25px;
      font-size: 18px;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    @media (min-width: 768px) {
      canvas#canvas {
        width: 480px;
        height: 480px;
      }
    }

    @media (min-width: 992px) {
      canvas#canvas {
        width: 640px;
        height: 640px;
      }
    }
  </style>
</head>
<body>

  <div class="logo-container text-center">
    <img src="KOITOTO_LOGO_GERAK.gif" alt="logo" style="max-width: 300px;" />
  </div>

  <div class="wheel-container">
    <canvas id="canvas" width="640" height="640"></canvas>
    <div class="arrow">▼</div>

    <div class="controls mt-4">
      <input type="text" id="spinVoucher" placeholder="Enter Voucher Code" class="form-control form-control-lg" />
      <button id="spinBtn">Spin the Wheel</button>
    </div>
  </div>

  <!-- Audio -->
  <audio src="https://starsspin.store/assets/audio/reward.mp3" preload="auto" id="rewardAudio"></audio>
  <audio src="https://starsspin.store/assets/audio/sad_tune.mp3" preload="auto" id="sadAudio"></audio>

  <!-- Winwheel.js & GSAP -->
  <script src="https://starsspin.store/assets/js/winwheel.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>

  <script>
    $(document).ready(function () {
      function getMediaData() {
        return { font: 18, radius: 300 };
      }

      let theWheel = new Winwheel({
        'canvasId': 'canvas',
        'numSegments': 9,
        'outerRadius': getMediaData().radius,
        'textFontSize': getMediaData().font,
        'segments': [
          { 'fillStyle': "#008080", 'text': "Sling Bag / Tas Samping" },
          { 'fillStyle': "#008080", 'text': "Kaos / T-Shirt KOITOTO" },
          { 'fillStyle': "#008080", 'text': "Polo Shirt KOITOTO" },
          { 'fillStyle': "#A9A9A9", 'text': "Asbak & Korek KOITOTO" },
          { 'fillStyle': "#f5b43c", 'text': "Coba lagi" },
          { 'fillStyle': "#3D9970", 'text': "Jaket Hoodie KOITOTO" },
          { 'fillStyle': "#3D9970", 'text': "Tumbler Keren KOITOTO" },
          { 'fillStyle': "#3D9970", 'text': "10RB" },
          { 'fillStyle': "#f5b43c", 'text': "Coba lagi" }
        ],
        'animation': {
          'type': 'spinToStop',
          'duration': 5,
          'spins': 9,
          'callbackFinished': alertPrize
        }
      });

      let canvas = document.getElementById("canvas");
      let ctx = canvas.getContext("2d");
      let wheelImage = new Image();
      wheelImage.src = "img/bg_lebaran_koitoto.jpg";

      wheelImage.onload = function () {
        theWheel.wheelImage = wheelImage;
        theWheel.draw();
      };

      wheelImage.onerror = function () {
        console.error("Gagal memuat gambar roda. Periksa path-nya.");
      };

      function resetWheel() {
        theWheel.stopAnimation(false);
        theWheel.rotationAngle = 0;
        theWheel.draw();
      }

      function alertPrize(indicatedSegment) {
        let prize = indicatedSegment.text;
        if (prize === "Coba lagi") {
          document.getElementById("sadAudio").play();
          Swal.fire("😢 Belum beruntung!", "Silakan coba lagi ya!", "error");
        } else {
          document.getElementById("rewardAudio").play();
          Swal.fire({
            icon: "success",
            title: `🎉 Anda menang: ${prize}`,
            confirmButtonText: "CLAIM",
            confirmButtonColor: "#28a745"
          }).then((r) => {
            if (r.isConfirmed) {
              window.location = "https://direct.lc.chat/13532091";
            }
          });
        }
      }

      let isSpinning = false;
      $("#spinBtn").on("click", function () {
        if (isSpinning) return;
        let voucher = $('#spinVoucher').val().trim();
        if (!voucher) {
          Swal.fire('Error', 'Please enter a voucher code.', 'warning');
          return;
        }

        resetWheel();
        let randomSegment = Math.floor(Math.random() * theWheel.numSegments);
        let stopAt = theWheel.getRandomForSegment(randomSegment + 1);
        theWheel.animation.spins = 10;
        theWheel.animation.stopAngle = stopAt;
        isSpinning = true;
        theWheel.startAnimation();

        setTimeout(() => {
          isSpinning = false;
        }, theWheel.animation.duration * 1000 + 500);
      });
    });
  </script>
</body>
</html>
