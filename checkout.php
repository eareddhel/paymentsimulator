<?php
/**
 * SIMULADOR BANCARIO - Checkout
 *
 * Este archivo prepara la transacción y redirige al simulador bancario correspondiente.
 * Simula exactamente el flujo que harías con las APIs reales de cada proveedor de pago.
 *
 * En tu aplicación real:
 * - Aquí crearías la transacción en la API del proveedor
 * - Obtendrías un token o URL de pago
 * - Redirigirías al usuario al portal del banco
 */

session_start();

// Tiempo de espera (ms) antes de enviar el formulario de redirección automática
define('REDIRECT_DELAY_MS', 1500);

// Validar que vengan los datos necesarios
if (!isset($_POST['payment_method']) || !isset($_POST['amount'])) {
  die('Error: Datos de pago incompletos');
}

// Capturar datos de la transacción
$paymentMethod = $_POST['payment_method'];
$amount = $_POST['amount'];
$orderId = $_POST['order_id'] ?? 'ORD-' . time();
$description = $_POST['description'] ?? 'Compra en tienda';

// Generar un ID de transacción único
$transactionId = strtoupper(uniqid('TXN-'));

// Guardar datos en sesión
$_SESSION['pending_transaction'] = [
  'transaction_id' => $transactionId,
  'payment_method' => $paymentMethod,
  'amount'         => $amount,
  'order_id'       => $orderId,
  'description'    => $description,
  'timestamp'      => time(),
  'return_url'     => 'http://localhost/bank_simulator/callback.php',
];

// Generar token de sesión seguro
$token = hash('sha256', $transactionId . session_id() . time());
$_SESSION['transaction_token'] = $token;

// Preparar parámetros específicos según el método de pago
switch ($paymentMethod) {
  case 'webpay':
    $params = [
      'TBK_TOKEN'       => $token,
      'TBK_ID_SESION'   => session_id(),
      'TBK_ORDEN_COMPRA'=> $orderId,
      'TBK_MONTO'       => $amount,
      'payment_method'  => 'webpay',
    ];
    break;

  case 'mercadopago':
    $params = [
      'preference_id'    => 'MP-' . $transactionId,
      'external_reference'=> $orderId,
      'init_point'       => 'simulator',
      'amount'           => $amount,
      'payment_method'   => 'mercadopago',
    ];
    break;

  case 'paypal':
    $params = [
      'token'          => 'EC-' . substr($token, 0, 17),
      'order_id'       => $orderId,
      'amount'         => $amount,
      'currency'       => 'CLP',
      'payment_method' => 'paypal',
    ];
    break;

  case 'bank_transfer':
    $params = [
      'reference'      => $transactionId,
      'order_id'       => $orderId,
      'amount'         => $amount,
      'payment_method' => 'bank_transfer',
    ];
    break;

  default:
    die('Método de pago no soportado');
}

// Agregar URL de retorno
$params['return_url'] = $_SESSION['pending_transaction']['return_url'];

// Etiquetas legibles por método
$methodLabels = [
  'webpay'        => 'Webpay Plus',
  'mercadopago'   => 'Mercado Pago',
  'paypal'        => 'PayPal',
  'bank_transfer' => 'Transferencia Bancaria',
];
$methodLabel = $methodLabels[$paymentMethod] ?? ucfirst($paymentMethod);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirigiendo al Portal de Pago…</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/modern-style.css">
  <style>
    body {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .checkout-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.2);
      padding: 2.5rem 2rem;
      text-align: center;
      max-width: 420px;
      width: 100%;
    }
    .progress-ring {
      width: 64px;
      height: 64px;
      border: 5px solid rgba(79,70,229,0.15);
      border-top: 5px solid #4f46e5;
      border-radius: 50%;
      animation: spin 0.9s linear infinite;
      margin: 0 auto 1.5rem;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
  </style>
</head>
<body onload="document.getElementById('bank_form').submit();">

  <div class="checkout-card scale-in">
    <div class="progress-ring"></div>
    <h5 class="fw-700 mb-2">Redirigiendo al Portal de Pago</h5>
    <p class="text-muted mb-4" style="font-size:0.9rem;">
      <i class="bi bi-shield-lock-fill text-success"></i>
      Conexión segura establecida
    </p>

    <div class="alert alert-info text-start mb-4" style="font-size:0.85rem;">
      <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      <div>
        <div><span class="text-muted">Método:</span> <strong><?php echo htmlspecialchars($methodLabel); ?></strong></div>
        <div><span class="text-muted">Monto:</span> <strong>$<?php echo number_format($amount, 0, ',', '.'); ?> CLP</strong></div>
        <div><span class="text-muted">Orden:</span> <strong><?php echo htmlspecialchars($orderId); ?></strong></div>
      </div>
    </div>

    <p class="text-muted mb-0" style="font-size:0.8rem;">
      No cierres esta ventana. Serás redirigido automáticamente…
    </p>
  </div>

  <!-- Formulario automático de redirección -->
  <form id="bank_form" action="simulator.php" method="POST" style="display:none;">
    <?php foreach ($params as $key => $value): ?>
      <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
    <?php endforeach; ?>
  </form>

  <script>
    // Seguridad: envío automático con fallback
    setTimeout(function () {
      document.getElementById('bank_form').submit();
    }, <?php echo REDIRECT_DELAY_MS; ?>);
  </script>
</body>
</html>
