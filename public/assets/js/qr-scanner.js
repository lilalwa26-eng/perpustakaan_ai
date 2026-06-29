// Minimal QR scanner initialization using qr-scanner library (https://github.com/nimiq/qr-scanner)
// Include qr-scanner.min.js in UI for this to work
function startQrScanner(videoElemId, onDecode){
  if (!window.QRScanner) return console.error('QRScanner lib not loaded');
  const video = document.getElementById(videoElemId);
  if (!video) return console.error('Video element not found');
  QRScanner.hasCamera().then(has=>{
    if (!has) return alert('No camera found');
    const scanner = new QRScanner(video, result => { onDecode(result); scanner.stop(); });
    scanner.start();
  });
}
