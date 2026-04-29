<?php

class ImageUploader {
    private static $uploadDir = 'images';
    private static $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private static $maxSize = 5 * 1024 * 1024; // 5MB

    /**
     * Procesa la subida de una imagen
     * 
     * @param array $file El archivo de $_FILES['imagen']
     * @param string|null $oldImage Ruta de la imagen anterior (para eliminar)
     * @param string $basePath Ruta base del proyecto
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function upload($file, $oldImage = null, $basePath = null) {
        if (!$basePath) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/../../';
        }

        // Validar que hay archivo
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            // Si hay imagen anterior, mantenerla
            if ($oldImage) {
                return ['success' => true, 'path' => $oldImage];
            }
            return ['success' => false, 'error' => 'Por favor seleccioná una imagen.'];
        }

        // Validar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo.'];
        }

        // Validar tipo de archivo
        if (!in_array($file['type'], self::$allowedTypes, true)) {
            return ['success' => false, 'error' => 'Solo se permiten imágenes (JPEG, PNG, GIF, WebP).'];
        }

        // Validar tamaño
        if ($file['size'] > self::$maxSize) {
            return ['success' => false, 'error' => 'La imagen no puede exceder 5MB.'];
        }

        // Generar nombre único
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $uploadPath = $basePath . 'public/' . self::$uploadDir . '/' . $filename;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'error' => 'No se pudo guardar la imagen.'];
        }

        // Eliminar imagen anterior si existe
        if ($oldImage && $oldImage !== '') {
            $oldPath = $basePath . 'public/' . $oldImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $relativePath = self::$uploadDir . '/' . $filename;
        return ['success' => true, 'path' => $relativePath];
    }
}
