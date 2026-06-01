<?php
// Archivo: app/models/MailService.php
// Servicio para envío de correos usando symfony/mailer + symfony/mime.
// El autoloader de Composer (vendor/autoload.php) debe estar cargado antes de usar esta clase.

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailService {

    /**
     * Envía el email de confirmación de compra al cliente.
     *
     * @param string $toEmail  Email del destinatario
     * @param string $toName   Nombre del destinatario
     * @param array  $pedido   Datos del pedido: ['compra_id', 'total', 'items' => [...]]
     * @return bool  true si se envió correctamente, false si falló
     */
    public static function enviarConfirmacionCompra(
        string $toEmail,
        string $toName,
        array  $pedido
    ): bool {
        // Si no hay credenciales configuradas, no intentar enviar
        if (MAIL_USERNAME === '' || MAIL_PASSWORD === '') {
            return false;
        }

        try {
            $transport = new EsmtpTransport(MAIL_HOST, MAIL_PORT, MAIL_ENCRYPTION === 'ssl');
            $transport->setUsername(MAIL_USERNAME);
            $transport->setPassword(MAIL_PASSWORD);

            $mailer = new Mailer($transport);

            $htmlBody = self::buildHtmlBody($toName, $pedido);
            $textBody = self::buildTextBody($toName, $pedido);

            $email = (new Email())
                ->from(new Address(MAIL_FROM, MAIL_FROM_NAME))
                ->to(new Address($toEmail, $toName))
                ->subject('¡Pedido #' . $pedido['compra_id'] . ' confirmado! - ' . MAIL_FROM_NAME)
                ->html($htmlBody)
                ->text($textBody);

            $mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            // Loguear sin detener el flujo de la aplicación
            error_log('[MailService] Error al enviar email: ' . $e->getMessage());
            return false;
        }
    }

    // ── Plantillas ─────────────────────────────────────────────────────────────

    private static function buildHtmlBody(string $nombre, array $pedido): string {
        $filas = '';
        foreach ($pedido['items'] as $item) {
            $nombrePlato = htmlspecialchars($item['nombre']);
            $cantidad    = (int) $item['cantidad'];
            $precio      = number_format((float) $item['precio'], 2);
            $subtotal    = number_format((float) $item['precio'] * $cantidad, 2);
            $filas .= "
                <tr>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;'>{$nombrePlato}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;text-align:center;'>{$cantidad}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;text-align:right;'>\${$precio}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;text-align:right;font-weight:bold;'>\${$subtotal}</td>
                </tr>";
        }

        $total        = number_format((float) $pedido['total'], 2);
        $nombreHtml   = htmlspecialchars($nombre);
        $compraId     = (int) $pedido['compra_id'];
        $restaurante  = htmlspecialchars(MAIL_FROM_NAME);

        return "<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'></head>
<body style='font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:0;'>
  <div style='max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
    <div style='background:#198754;color:#fff;padding:24px 32px;'>
      <h1 style='margin:0;font-size:22px;'>🍽️ {$restaurante}</h1>
      <p style='margin:6px 0 0;font-size:15px;'>¡Tu pedido fue confirmado!</p>
    </div>
    <div style='padding:28px 32px;'>
      <p style='font-size:16px;'>Hola <strong>{$nombreHtml}</strong>,</p>
      <p>Tu pedido <strong>#${compraId}</strong> ha sido recibido y está siendo preparado. Aquí está tu resumen:</p>

      <table style='width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;'>
        <thead>
          <tr style='background:#f8f9fa;'>
            <th style='padding:10px 12px;text-align:left;border-bottom:2px solid #dee2e6;'>Plato</th>
            <th style='padding:10px 12px;text-align:center;border-bottom:2px solid #dee2e6;'>Cant.</th>
            <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #dee2e6;'>Precio</th>
            <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #dee2e6;'>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          {$filas}
        </tbody>
        <tfoot>
          <tr>
            <td colspan='3' style='padding:12px;text-align:right;font-weight:bold;font-size:15px;'>Total:</td>
            <td style='padding:12px;text-align:right;font-weight:bold;font-size:15px;color:#198754;'>\${$total}</td>
          </tr>
        </tfoot>
      </table>

      <p style='color:#6c757d;font-size:13px;margin-top:24px;'>
        Gracias por tu compra. Podés ver tu historial de pedidos en tu perfil.
      </p>
    </div>
    <div style='background:#f8f9fa;padding:16px 32px;text-align:center;font-size:12px;color:#aaa;'>
      © {$restaurante} — Este es un mensaje automático, no respondas este correo.
    </div>
  </div>
</body>
</html>";
    }

    private static function buildTextBody(string $nombre, array $pedido): string {
        $lineas = "¡Hola {$nombre}!\n\n";
        $lineas .= "Tu pedido #{$pedido['compra_id']} fue confirmado.\n\n";
        $lineas .= "DETALLE DEL PEDIDO\n";
        $lineas .= str_repeat('-', 40) . "\n";

        foreach ($pedido['items'] as $item) {
            $subtotal = number_format((float) $item['precio'] * (int) $item['cantidad'], 2);
            $lineas  .= "{$item['nombre']} x{$item['cantidad']}  -> \${$subtotal}\n";
        }

        $lineas .= str_repeat('-', 40) . "\n";
        $lineas .= 'TOTAL: $' . number_format((float) $pedido['total'], 2) . "\n\n";
        $lineas .= "Gracias por tu compra.\n-- " . MAIL_FROM_NAME;

        return $lineas;
    }
}
