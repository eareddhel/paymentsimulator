<?php
/**
 * SIMULADOR BANCARIO - Página Principal
 *
 * Esta es la página de ejemplo que simula ser tu e-commerce o aplicación.
 * Aquí es desde donde iniciarías un pago en tu proyecto real.
 *
 * Medios de pago soportados:
 * - Webpay Plus (Transbank Chile)
 * - Mercado Pago
 * - PayPal
 * - Transferencia Bancaria
 */
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Simulador de Pagos — Demo E-commerce</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/modern-style.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="#">
        <span class="brand-icon"><i class="bi bi-shop"></i></span>
        Mi Tienda Demo
      </a>
      <span class="navbar-text ms-auto">
        <i class="bi bi-shield-lock-fill"></i> Pago Seguro
      </span>
    </div>
  </nav>

  <!-- Aviso modo simulación -->
  <div class="container mt-4">
    <div class="alert alert-info fade-in" role="alert">
      <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      <div>
        <strong>Modo Simulación Activo:</strong> Puedes probar diferentes respuestas de pago sin contratar servicios reales.
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <div class="container pb-5">
    <div class="row justify-content-center g-4">
      <div class="col-lg-7">

        <!-- Resumen de Compra -->
        <div class="card card-accent fade-in-up delay-1 mb-4">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-cart-check text-primary"></i>
            <span>Resumen de Compra</span>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#ede9fe,#ddd6fe);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="bi bi-laptop text-primary fs-4"></i>
                </div>
                <div>
                  <div class="fw-700">Curso de Desarrollo Web Completo</div>
                  <div class="text-muted" style="font-size:0.85rem;">SKU: WEB-2024-001 &middot; 1 unidad</div>
                </div>
              </div>
              <div class="text-end flex-shrink-0">
                <div class="stat-value">$49.990</div>
                <div class="text-muted" style="font-size:0.8rem;">CLP</div>
              </div>
            </div>
            <div class="divider"></div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted">Total a pagar</span>
              <span class="fw-700 fs-5">$49.990 CLP</span>
            </div>
          </div>
        </div>

        <!-- Selección de Método de Pago -->
        <div class="card fade-in-up delay-2">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-credit-card text-primary"></i>
            <span>Selecciona tu Método de Pago</span>
          </div>
          <div class="card-body">
            <form action="checkout.php" method="POST" id="paymentForm">
              <input type="hidden" name="amount" value="49990">
              <input type="hidden" name="order_id" value="ORD-<?php echo time(); ?>">
              <input type="hidden" name="description" value="Curso de Desarrollo Web Completo">

              <div class="d-flex flex-column gap-3">

                <!-- Webpay Plus -->
                <div class="payment-option">
                  <input type="radio" class="btn-check" name="payment_method" id="webpay" value="webpay" checked>
                  <label class="btn btn-outline-primary w-100 text-start payment-label" for="webpay">
                    <div class="d-flex align-items-center gap-3">
                      <i class="bi bi-credit-card-2-front payment-method-icon"></i>
                      <div class="flex-grow-1">
                        <div class="fw-600">Webpay Plus</div>
                        <div class="text-muted" style="font-size:0.82rem;">Transbank Chile — Crédito y débito</div>
                      </div>
                      <span class="badge bg-primary">Chile</span>
                    </div>
                  </label>
                </div>

                <!-- Mercado Pago -->
                <div class="payment-option">
                  <input type="radio" class="btn-check" name="payment_method" id="mercadopago" value="mercadopago">
                  <label class="btn btn-outline-primary w-100 text-start payment-label" for="mercadopago">
                    <div class="d-flex align-items-center gap-3">
                      <i class="bi bi-wallet2 payment-method-icon"></i>
                      <div class="flex-grow-1">
                        <div class="fw-600">Mercado Pago</div>
                        <div class="text-muted" style="font-size:0.82rem;">Tarjetas, efectivo y más opciones</div>
                      </div>
                      <span class="badge bg-info">Popular</span>
                    </div>
                  </label>
                </div>

                <!-- PayPal -->
                <div class="payment-option">
                  <input type="radio" class="btn-check" name="payment_method" id="paypal" value="paypal">
                  <label class="btn btn-outline-primary w-100 text-start payment-label" for="paypal">
                    <div class="d-flex align-items-center gap-3">
                      <i class="bi bi-paypal payment-method-icon"></i>
                      <div class="flex-grow-1">
                        <div class="fw-600">PayPal</div>
                        <div class="text-muted" style="font-size:0.82rem;">Paga con tu cuenta PayPal</div>
                      </div>
                      <span class="badge bg-secondary">Global</span>
                    </div>
                  </label>
                </div>

                <!-- Transferencia Bancaria -->
                <div class="payment-option">
                  <input type="radio" class="btn-check" name="payment_method" id="bank_transfer" value="bank_transfer">
                  <label class="btn btn-outline-primary w-100 text-start payment-label" for="bank_transfer">
                    <div class="d-flex align-items-center gap-3">
                      <i class="bi bi-bank payment-method-icon"></i>
                      <div class="flex-grow-1">
                        <div class="fw-600">Transferencia Bancaria</div>
                        <div class="text-muted" style="font-size:0.82rem;">Transferencia manual o automática</div>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="divider mt-4"></div>

              <!-- Botones -->
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-shield-check"></i> Proceder al Pago Seguro
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                  <i class="bi bi-arrow-left"></i> Volver
                </button>
              </div>

              <!-- Trust badges -->
              <div class="d-flex justify-content-center gap-4 mt-4 flex-wrap">
                <span class="trust-badge">
                  <i class="bi bi-lock-fill text-success"></i> Pago encriptado
                </span>
                <span class="trust-badge">
                  <i class="bi bi-shield-check text-success"></i> Datos protegidos
                </span>
                <span class="trust-badge">
                  <i class="bi bi-patch-check-fill text-success"></i> SSL 256-bit
                </span>
              </div>
            </form>
          </div>
        </div>

        <!-- Info simulador -->
        <div class="alert alert-warning fade-in-up delay-3 mt-4" role="alert">
          <i class="bi bi-lightbulb-fill flex-shrink-0"></i>
          <div>
            <strong>Sobre este Simulador:</strong> Replica los flujos exactos de Webpay, Mercado Pago, PayPal y Transferencia.
            Prueba escenarios (éxito, rechazo, pendiente…) sin contratar servicios reales.
            Ideal para desarrollo y QA antes de producción.
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="py-4 bg-white border-top mt-auto">
    <div class="container text-center text-muted" style="font-size:0.82rem;">
      <i class="bi bi-code-slash"></i> Simulador de Pagos &mdash; Open Source &mdash;
      <i class="bi bi-calendar3"></i> <?php echo date('Y'); ?>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>