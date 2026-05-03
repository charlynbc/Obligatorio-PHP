<?php

class Mailer {
    /**
     * Envía un email con el detalle de la compra al usuario.
     * Usa la función mail() nativa de PHP.
     * En entornos de desarrollo sin servidor SMTP configurado,
     * el mail se loguea en storage/logs/mails.log como fallback.
     */
    public static function enviarComprobante(string $toEmail, string $userName, array $items, float $total, string $fecha): bool {
        $subject = "Comprobante de compra - Restaurante";

        $body = "Hola {$userName},\n\n";
        $body .= "Tu compra fue realizada con éxito el {$fecha}.\n\n";
        $body .= "Detalle del pedido:\n";
        $body .= str_repeat('-', 40) . "\n";

        foreach ($items as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $body .= sprintf(
                "- %s x%d  ($%.2f c/u)  = $%.2f\n",
                $item['nombre'],
                $item['cantidad'],
                $item['precio'],
                $subtotal
            );
        }

        $body .= str_repeat('-', 40) . "\n";
        $body .= sprintf("TOTAL: $%.2f\n\n", $total);
        $body .= "¡Gracias por tu compra!\n";
        $body .= "— Restaurante";

        $headers = "From: noreply@restaurante.local\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $sent = @mail($toEmail, $subject, $body, $headers);

        // Fallback: guardar en log si mail() falla (entorno sin SMTP)
        $logPath = BASE_PATH . 'storage/logs/mails.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = "\n" . str_repeat('=', 60) . "\n";
        $logEntry .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $logEntry .= "Para: {$toEmail}\n";
        $logEntry .= "Asunto: {$subject}\n";
        $logEntry .= "Estado: " . ($sent ? 'ENVIADO' : 'FALLIDO (guardado en log)') . "\n";
        $logEntry .= str_repeat('-', 60) . "\n";
        $logEntry .= $body . "\n";

        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

        return $sent;
    }
}
