<?php

class NetworkRestriction {
    // Redes permitidas (CIDR notation)
    private static $allowedNetworks = [
        '192.168.1.0/24',    // Tu red local
        '127.0.0.1/32',      // Localhost
    ];

    /**
     * Verifica si la IP actual está en una red permitida
     */
    public static function isAllowed(): bool {
        $clientIp = self::getClientIp();
        
        foreach (self::$allowedNetworks as $network) {
            if (self::ipInNetwork($clientIp, $network)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtiene la IP del cliente
     */
    private static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Verifica si una IP está en un rango CIDR
     */
    private static function ipInNetwork(string $ip, string $cidr): bool {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        list($network, $mask) = explode('/', $cidr);
        $network = ip2long($network);
        $ip = ip2long($ip);
        $mask = -1 << (32 - $mask);
        $mask = $mask & 0xffffffff;

        return ($ip & $mask) === ($network & $mask);
    }

    /**
     * Rechaza conexiones no autorizadas
     */
    public static function checkAccess(): void {
        if (!self::isAllowed()) {
            http_response_code(403);
            die('Acceso denegado. Conexiones solo desde red local (192.168.1.0/24).');
        }
    }
}
