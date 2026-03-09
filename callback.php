<?php
/**
 * SIMULADOR BANCARIO - Callback / Respuesta
 *
 * Este archivo recibe la respuesta del "banco" y la procesa.
 * Simula exactamente las respuestas que recibirías de las APIs reales.
 *
 * En tu aplicación real:
 * - Aquí recibirías la notificación del banco
 * - Validarías la firma/token de seguridad
 * - Actualizarías el estado del pedido en tu base de datos
 * - Mostrarías confirmación al usuario
 */

session_start();

// Recuperar datos de la sesión
$simulatorData = $_SESSION['simulator_data'] ?? [];
$pendingTransaction = $_SESSION['pending_transaction'] ?? [];

$paymentMethod = $simulatorData['payment_method'] ?? 'unknown';
$amount = $simulatorData['amount'] ?? 0;
$orderId = $simulatorData['order_id'] ?? '';
$responseType = $_POST['response_type'] ?? 'approved';

// Configuración de respuestas
$responses = [
  'approved' => [
    'status'        => 'approved',
    'status_detail' => 'accredited',
    'title'         => 'Pago Aprobado',
    'message'       => '¡Tu pago fue procesado exitosamente!',
    'icon'          => 'check-circle-fill',
    'color'         => 'success',
    'hex'           => '#10b981',
    'gradient'      => 'linear-gradient(135deg,#10b981 0%,#059669 100%)',
    'code'          => '00',
    'description'   => 'Transacción aprobada sin problemas.',
  ],
  'rejected' => [
    'status'        => 'rejected',
    'status_detail' => 'cc_rejected_insufficient_amount',
    'title'         => 'Pago Rechazado',
    'message'       => 'Tu pago fue rechazado por el banco emisor.',
    'icon'          => 'x-circle-fill',
    'color'         => 'danger',
    'hex'           => '#ef4444',
    'gradient'      => 'linear-gradient(135deg,#ef4444 0%,#dc2626 100%)',
    'code'          => '51',
    'description'   => 'Fondos insuficientes o tarjeta rechazada.',
  ],
  'pending' => [
    'status'        => 'pending',
    'status_detail' => 'pending_contingency',
    'title'         => 'Pago Pendiente',
    'message'       => 'Tu pago está siendo procesado.',
    'icon'          => 'clock-fill',
    'color'         => 'warning',
    'hex'           => '#f59e0b',
    'gradient'      => 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)',
    'code'          => '02',
    'description'   => 'El pago requiere verificación o está en revisión.',
  ],
  'cancelled' => [
    'status'        => 'cancelled',
    'status_detail' => 'by_user',
    'title'         => 'Pago Cancelado',
    'message'       => 'Has cancelado la operación de pago.',
    'icon'          => 'arrow-left-circle-fill',
    'color'         => 'secondary',
    'hex'           => '#64748b',
    'gradient'      => 'linear-gradient(135deg,#64748b 0%,#475569 100%)',
    'code'          => 'USR_CANCEL',
    'description'   => 'El usuario abandonó el proceso de pago.',
  ],
  'error' => [
    'status'        => 'error',
    'status_detail' => 'internal_error',
    'title'         => 'Error del Sistema',
    'message'       => 'Ocurrió un error al procesar tu pago.',
    'icon'          => 'exclamation-triangle-fill',
    'color'         => 'dark',
    'hex'           => '#1e293b',
    'gradient'      => 'linear-gradient(135deg,#1e293b 0%,#0f172a 100%)',
    'code'          => 'ERR-500',
    'description'   => 'Error técnico en la plataforma de pagos.',
  ],
  'timeout' => [
    'status'        => 'timeout',
    'status_detail' => 'timeout_expired',
    'title'         => 'Tiempo Agotado',
    'message'       => 'El tiempo para completar el pago ha expirado.',
    'icon'          => 'alarm-fill',
    'color'         => 'info',
    'hex'           => '#3b82f6',
    'gradient'      => 'linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)',
    'code'          => 'TIMEOUT',
    'description'   => 'La sesión de pago expiró por inactividad.',
  ],
];

$response = $responses[$responseType] ?? $responses['approved'];

// Generar datos de transacción simulados
$transactionData = [
  'transaction_id'    => strtoupper(uniqid('TXN-')),
  'authorization_code'=> ($responseType === 'approved') ? rand(100000, 999999) : null,
  'payment_id'        => rand(1000000000, 9999999999),
  'timestamp'         => date('Y-m-d H:i:s'),
  'payment_method'    => $paymentMethod,
  'amount'            => $amount,
  'currency'          => 'CLP',
  'order_id'          => $orderId,
  'status'            => $response['status'],
  'status_detail'     => $response['status_detail'],
  'response_code'     => $response['code'],
];

// Simular respuesta específica por método de pago
switch ($paymentMethod) {
  case 'webpay':
    $transactionData['response_data'] = [
      'vci'                => 'TSY',
      'amount'             => $amount,
      'status'             => ($responseType === 'approved') ? 'AUTHORIZED' : 'FAILED',
      'buy_order'          => $orderId,
      'session_id'         => session_id(),
      'card_detail'        => ['card_number' => '****' . rand(1000, 9999)],
      'accounting_date'    => date('md'),
      'transaction_date'   => date('Y-m-d H:i:s'),
      'authorization_code' => $transactionData['authorization_code'],
      'payment_type_code'  => 'VN',
      'response_code'      => ($responseType === 'approved') ? 0 : -1,
      'installments_number'=> 0,
    ];
    break;

  case 'mercadopago':
    $transactionData['response_data'] = [
      'id'                  => $transactionData['payment_id'],
      'status'              => $response['status'],
      'status_detail'       => $response['status_detail'],
      'payment_method_id'   => 'visa',
      'payment_type_id'     => 'credit_card',
      'transaction_amount'  => $amount,
      'currency_id'         => 'CLP',
      'date_created'        => date('c'),
      'date_approved'       => ($responseType === 'approved') ? date('c') : null,
      'authorization_code'  => $transactionData['authorization_code'],
      'external_reference'  => $orderId,
      'merchant_order_id'   => rand(1000000, 9999999),
      'payer'               => [
        'id'             => rand(100000, 999999),
        'email'          => 'test_user@test.com',
        'identification' => ['type' => 'RUT', 'number' => '11111111-1'],
      ],
    ];
    break;

  case 'paypal':
    $transactionData['response_data'] = [
      'id'          => 'PAY-' . strtoupper(substr(md5(time()), 0, 17)),
      'intent'      => 'sale',
      'state'       => ($responseType === 'approved') ? 'approved' : 'failed',
      'cart'        => $orderId,
      'create_time' => date('c'),
      'update_time' => date('c'),
      'payer'       => [
        'payment_method' => 'paypal',
        'status'         => 'VERIFIED',
        'payer_info'     => [
          'email'        => 'test@example.com',
          'first_name'   => 'Test',
          'last_name'    => 'User',
          'payer_id'     => 'PAYERID' . rand(100000, 999999),
          'country_code' => 'CL',
        ],
      ],
      'transactions' => [[
        'amount'         => [
          'total'    => number_format($amount / 1000, 2, '.', ''),
          'currency' => 'USD',
        ],
        'description'    => 'Payment description',
        'invoice_number' => $orderId,
      ]],
    ];
    break;

  case 'bank_transfer':
    $transactionData['response_data'] = [
      'reference'      => $transactionData['transaction_id'],
      'status'         => $response['status'],
      'bank_name'      => 'Banco de Chile',
      'account_number' => '****5678',
      'amount'         => $amount,
      'currency'       => 'CLP',
      'date'           => date('Y-m-d H:i:s'),
    ];
    break;
}

// Guardar resultado en sesión
$_SESSION['last_transaction'] = $transactionData;

// Mapeo de método a etiqueta legible
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
  <title>Resultado del Pago — <?php echo htmlspecialchars($response['title']); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/modern-style.css">
</head>
<body>

  <!-- Hero resultado -->
  <div class="result-hero" style="background:<?php echo $response['gradient']; ?>;color:white;">
    <div class="container">
      <div class="result-icon-wrap scale-in">
        <i class="bi bi-<?php echo htmlspecialchars($response['icon']); ?>"></i>
      </div>
      <h1 class="result-title fade-in-up delay-1"><?php echo htmlspecialchars($response['title']); ?></h1>
      <p class="result-subtitle fade-in-up delay-2"><?php echo htmlspecialchars($response['message']); ?></p>
    </div>
  </div>

  <div class="container py-4 pb-5">
    <div class="row justify-content-center g-4">
      <div class="col-lg-7">

        <!-- Detalle de la transacción -->
        <div class="card card-accent-<?php echo $response['color']; ?> fade-in-up delay-1">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-receipt text-primary"></i>
            <span>Detalle de la Transacción</span>
            <span class="badge bg-<?php echo $response['color']; ?> ms-auto">
              <?php echo strtoupper($response['status']); ?>
            </span>
          </div>
          <div class="card-body">
            <div class="detail-row">
              <span class="detail-label">ID de Transacción</span>
              <code class="detail-value"><?php echo htmlspecialchars($transactionData['transaction_id']); ?></code>
            </div>
            <div class="detail-row">
              <span class="detail-label">Orden de Compra</span>
              <span class="detail-value"><?php echo htmlspecialchars($orderId); ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Método de Pago</span>
              <span class="detail-value"><?php echo htmlspecialchars($methodLabel); ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Monto</span>
              <span class="detail-value" style="font-size:1.15rem;color:var(--color-primary);">
                $<?php echo number_format($amount, 0, ',', '.'); ?> CLP
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Fecha y Hora</span>
              <span class="detail-value"><?php echo $transactionData['timestamp']; ?></span>
            </div>
            <?php if ($transactionData['authorization_code']): ?>
            <div class="detail-row">
              <span class="detail-label">Código de Autorización</span>
              <code class="detail-value"><?php echo $transactionData['authorization_code']; ?></code>
            </div>
            <?php endif; ?>
            <div class="detail-row">
              <span class="detail-label">Código de Respuesta</span>
              <code class="detail-value"><?php echo htmlspecialchars($response['code']); ?></code>
            </div>

            <div class="alert alert-<?php echo $response['color']; ?> mt-3 mb-0">
              <i class="bi bi-info-circle flex-shrink-0"></i>
              <div><?php echo htmlspecialchars($response['description']); ?></div>
            </div>
          </div>
        </div>

        <!-- Respuesta JSON (Para desarrolladores) -->
        <div class="card fade-in-up delay-2 no-print">
          <div class="card-header d-flex align-items-center gap-2" style="background:#0d1117;color:#c9d1d9;">
            <i class="bi bi-code-square"></i>
            <span style="font-size:0.875rem;">Respuesta JSON</span>
            <span class="ms-auto" style="font-size:0.75rem;opacity:0.7;">Para desarrollo</span>
          </div>
          <div class="card-body p-0">
            <div class="json-viewer">
              <pre class="mb-0"><?php echo htmlspecialchars(json_encode($transactionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
            </div>
          </div>
          <div class="card-footer">
            <small class="text-muted">
              <i class="bi bi-info-circle"></i>
              Esta es la respuesta que recibirías en tu webhook o callback en producción.
            </small>
          </div>
        </div>

        <!-- Acciones -->
        <div class="card fade-in-up delay-3 no-print">
          <div class="card-body">
            <div class="fw-600 mb-3"><i class="bi bi-ui-checks text-primary"></i> ¿Qué hacer ahora?</div>
            <div class="d-grid gap-2">
              <a href="index.php" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-left-circle"></i> Volver a la Tienda
              </a>
              <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer"></i> Imprimir Comprobante
              </button>
              <button class="btn btn-outline-info" data-bs-toggle="collapse" data-bs-target="#webhookInfo">
                <i class="bi bi-webhook"></i> Ver Info de Webhook
              </button>
            </div>

            <!-- Webhook info colapsable -->
            <div class="collapse mt-3" id="webhookInfo">
              <div class="alert alert-info mb-0">
                <i class="bi bi-webhook flex-shrink-0"></i>
                <div>
                  <strong class="d-block mb-2">Integración con Webhook</strong>
                  <p class="small mb-2">
                    En producción, la plataforma enviará una notificación POST a tu URL de webhook.
                  </p>
                  <ol class="small mb-0 ps-3">
                    <li>Valida la firma/token de seguridad</li>
                    <li>Actualiza el estado del pedido en tu base de datos</li>
                    <li>Envía email de confirmación al cliente</li>
                    <li>Responde con HTTP 200 OK</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modo simulación aviso -->
        <div class="alert alert-warning fade-in-up delay-4 no-print" role="alert">
          <i class="bi bi-gear-fill flex-shrink-0"></i>
          <div>
            <strong>Modo Simulación Activo:</strong> Esta es una respuesta simulada.
            En producción recibirías la confirmación real y deberías actualizar tu base de datos.
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="py-3 bg-white border-top mt-auto">
    <div class="container text-center text-muted" style="font-size:0.8rem;">
      <i class="bi bi-code-slash"></i> Simulador de Pagos &nbsp;|&nbsp;
      <i class="bi bi-shield-check"></i> Entorno de Desarrollo &nbsp;|&nbsp;
      <i class="bi bi-github"></i> Open Source
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
