<?php
/**
 * SIMULADOR BANCARIO - Portal de Pago
 *
 * Este archivo simula el portal del banco o pasarela de pago.
 * Aquí el usuario (desarrollador) puede elegir qué tipo de respuesta quiere probar.
 *
 * Simula las interfaces de:
 * - Webpay Plus (Transbank)
 * - Mercado Pago
 * - PayPal
 * - Transferencia Bancaria
 */

session_start();

// Longitud máxima de caracteres del token mostrados en pantalla
define('TOKEN_DISPLAY_LENGTH', 24);

// Capturar datos que vienen desde checkout.php
$paymentMethod = $_POST['payment_method'] ?? 'unknown';
$amount = $_POST['amount'] ?? 0;
$returnUrl = $_POST['return_url'] ?? '';

// Parámetros específicos según método de pago
$token = $_POST['TBK_TOKEN'] ?? $_POST['token'] ?? $_POST['preference_id'] ?? $_POST['reference'] ?? '';
$orderId = $_POST['TBK_ORDEN_COMPRA'] ?? $_POST['external_reference'] ?? $_POST['order_id'] ?? '';

// Guardar en sesión para usar en callback
$_SESSION['simulator_data'] = [
  'payment_method' => $paymentMethod,
  'amount' => $amount,
  'token' => $token,
  'order_id' => $orderId,
  'return_url' => $returnUrl,
  'timestamp' => time()
];

// Configuración de marca según método de pago
$paymentConfig = [
  'webpay' => [
    'name' => 'Webpay Plus',
    'logo' => 'Transbank',
    'gradient' => 'linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)',
    'icon' => 'credit-card',
  ],
  'mercadopago' => [
    'name' => 'Mercado Pago',
    'logo' => 'Mercado Pago',
    'gradient' => 'linear-gradient(135deg,#00b1ea 0%,#00c8c8 100%)',
    'icon' => 'wallet2',
  ],
  'paypal' => [
    'name' => 'PayPal',
    'logo' => 'PayPal',
    'gradient' => 'linear-gradient(135deg,#009cde 0%,#003087 100%)',
    'icon' => 'paypal',
  ],
  'bank_transfer' => [
    'name' => 'Transferencia Bancaria',
    'logo' => 'Banco',
    'gradient' => 'linear-gradient(135deg,#64748b 0%,#475569 100%)',
    'icon' => 'bank',
  ],
];

$config = $paymentConfig[$paymentMethod] ?? $paymentConfig['webpay'];

// Escenarios de respuesta
$scenarios = [
  [
    'value'     => 'approved',
    'label'     => 'Pago Aprobado',
    'desc'      => 'Transacción exitosa',
    'code'      => '00',
    'code_cls'  => 'text-success',
    'icon'      => 'check-circle-fill',
    'btn_class' => 'btn-success',
    'border'    => '#10b981',
    'bg'        => '#f0fdf4',
    'icon_color'=> '#10b981',
  ],
  [
    'value'     => 'rejected',
    'label'     => 'Pago Rechazado',
    'desc'      => 'Fondos insuficientes o tarjeta rechazada',
    'code'      => '51',
    'code_cls'  => 'text-danger',
    'icon'      => 'x-circle-fill',
    'btn_class' => 'btn-danger',
    'border'    => '#ef4444',
    'bg'        => '#fff5f5',
    'icon_color'=> '#ef4444',
  ],
  [
    'value'     => 'pending',
    'label'     => 'Pago Pendiente',
    'desc'      => 'En revisión o procesamiento',
    'code'      => '02',
    'code_cls'  => '',
    'icon'      => 'clock-fill',
    'btn_class' => 'btn-warning',
    'border'    => '#f59e0b',
    'bg'        => '#fffbeb',
    'icon_color'=> '#f59e0b',
  ],
  [
    'value'     => 'cancelled',
    'label'     => 'Usuario Canceló',
    'desc'      => 'El usuario abandonó el proceso',
    'code'      => 'USR_CANCEL',
    'code_cls'  => 'text-secondary',
    'icon'      => 'arrow-left-circle-fill',
    'btn_class' => 'btn-secondary',
    'border'    => '#64748b',
    'bg'        => '#f8fafc',
    'icon_color'=> '#64748b',
  ],
  [
    'value'     => 'error',
    'label'     => 'Error del Sistema',
    'desc'      => 'Error técnico o de conexión',
    'code'      => 'ERR-500',
    'code_cls'  => '',
    'icon'      => 'exclamation-triangle-fill',
    'btn_class' => 'btn-dark',
    'border'    => '#1e293b',
    'bg'        => '#f8fafc',
    'icon_color'=> '#1e293b',
  ],
  [
    'value'     => 'timeout',
    'label'     => 'Timeout',
    'desc'      => 'Tiempo de espera agotado',
    'code'      => 'TIMEOUT',
    'code_cls'  => 'text-info',
    'icon'      => 'alarm-fill',
    'btn_class' => 'btn-info',
    'border'    => '#3b82f6',
    'bg'        => '#eff6ff',
    'icon_color'=> '#3b82f6',
  ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($config['name']); ?> — Portal de Pago Seguro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/modern-style.css">
  <style>
    .simulator-header {
      background: <?php echo $config['gradient']; ?>;
      padding: 2rem 0;
      color: white;
    }
    .scenario-card {
      border: 2px solid transparent;
      border-radius: var(--border-radius-lg);
      background: #fff;
      transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
      cursor: pointer;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem;
      text-align: center;
    }
    .scenario-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }
    .scenario-icon {
      font-size: 2.25rem;
      display: block;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>

  <!-- Header del Simulador -->
  <div class="simulator-header">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div style="font-size:1.4rem;font-weight:800;letter-spacing:-0.02em;">
            <i class="bi bi-<?php echo htmlspecialchars($config['icon']); ?>"></i>
            <?php echo htmlspecialchars($config['logo']); ?>
          </div>
          <div style="font-size:0.875rem;opacity:0.85;">Portal de Pago Seguro</div>
        </div>
        <span class="badge-dev">
          <i class="bi bi-gear-fill"></i> ENTORNO DE PRUEBAS
        </span>
      </div>
    </div>
  </div>

  <div class="container py-4 pb-5">
    <div class="row justify-content-center g-4">
      <div class="col-lg-8">

        <!-- Resumen de la transacción -->
        <div class="card fade-in-up delay-1">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div>
                <div class="text-muted mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;">Resumen de pago</div>
                <div class="mb-1" style="font-size:0.875rem;">
                  <span class="text-muted">Comercio:</span>
                  <strong class="ms-1">Mi Tienda Demo</strong>
                </div>
                <div class="mb-1" style="font-size:0.875rem;">
                  <span class="text-muted">Orden:</span>
                  <code class="ms-1"><?php echo htmlspecialchars($orderId); ?></code>
                </div>
                <div style="font-size:0.875rem;">
                  <span class="text-muted">Token:</span>
                  <code class="ms-1" style="font-size:0.78rem;"><?php echo substr(htmlspecialchars($token), 0, TOKEN_DISPLAY_LENGTH); ?>…</code>
                </div>
              </div>
              <div class="text-end">
                <div class="text-muted mb-1" style="font-size:0.8rem;">Total a pagar</div>
                <div class="stat-value">$<?php echo number_format($amount, 0, ',', '.'); ?></div>
                <div class="text-muted" style="font-size:0.8rem;">CLP</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Alerta modo desarrollador -->
        <div class="alert alert-warning fade-in-up delay-2" role="alert">
          <i class="bi bi-info-circle-fill flex-shrink-0"></i>
          <div>
            <strong>Modo Desarrollador Activo:</strong> Selecciona el escenario a probar.
            En producción, el usuario ingresaría sus datos de tarjeta aquí.
          </div>
        </div>

        <!-- Selección de escenario -->
        <div class="card fade-in-up delay-3">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-list-check text-primary"></i>
            <span>Selecciona el Escenario a Probar</span>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <?php foreach ($scenarios as $sc): ?>
              <div class="col-md-6">
                <form action="callback.php" method="POST" class="h-100">
                  <input type="hidden" name="response_type" value="<?php echo $sc['value']; ?>">
                  <div class="scenario-card" style="border-color:<?php echo $sc['border']; ?>;background:<?php echo $sc['bg']; ?>;">
                    <div>
                      <i class="bi bi-<?php echo $sc['icon']; ?> scenario-icon" style="color:<?php echo $sc['icon_color']; ?>;"></i>
                      <div class="fw-700 mb-1"><?php echo $sc['label']; ?></div>
                      <p class="text-muted mb-1" style="font-size:0.8rem;"><?php echo $sc['desc']; ?></p>
                      <p class="mb-3" style="font-size:0.78rem;">
                        Código: <strong class="<?php echo $sc['code_cls']; ?>"><?php echo $sc['code']; ?></strong>
                      </p>
                    </div>
                    <button type="submit" class="btn <?php echo $sc['btn_class']; ?> btn-sm w-100">
                      Simular este escenario
                    </button>
                  </div>
                </form>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Info para desarrolladores -->
        <div class="card fade-in-up delay-4">
          <div class="card-body" style="background:#f8fafc;">
            <div class="d-flex align-items-center gap-2 mb-3 text-muted">
              <i class="bi bi-code-square"></i>
              <span class="fw-600" style="font-size:0.85rem;">Información para Desarrolladores</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;">Método de Pago</div>
                <code><?php echo strtoupper(htmlspecialchars($paymentMethod)); ?></code>
              </div>
              <div class="col-md-6">
                <div class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;">URL de Retorno</div>
                <code style="font-size:0.78rem;word-break:break-all;"><?php echo htmlspecialchars($returnUrl) ?: '—'; ?></code>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="py-3 bg-white border-top mt-auto">
    <div class="container text-center text-muted" style="font-size:0.8rem;">
      <i class="bi bi-shield-lock-fill"></i> SSL 256-bit &nbsp;|&nbsp;
      <i class="bi bi-code-slash"></i> Simulador de Pagos v1.0 &nbsp;|&nbsp;
      <i class="bi bi-gear"></i> Modo Desarrollo
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
