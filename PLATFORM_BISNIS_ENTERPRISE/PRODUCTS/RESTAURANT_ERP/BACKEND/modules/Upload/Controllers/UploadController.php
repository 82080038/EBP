<?php

namespace Modules\Upload\Controllers;

use Core\Response;

class UploadController
{
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
    }

    public function uploadImage($request)
    {
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return Response::error('No file uploaded or upload error', 400);
            }

            $file = $_FILES['file'];

            // Validate file size
            if ($file['size'] > $this->maxFileSize) {
                return Response::error('File size exceeds maximum limit of 5MB', 400);
            }

            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedTypes)) {
                return Response::error('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed', 400);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return Response::error('Failed to move uploaded file', 500);
            }

            // Return the URL path
            $url = '/uploads/' . $filename;

            return Response::success([
                'url' => $url,
                'filename' => $filename
            ], 'Image uploaded successfully');

        } catch (\Exception $e) {
            return Response::error('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function deleteImage($request)
    {
        try {
            $data = $request['body'] ?? [];
            $filename = $data['filename'] ?? null;

            if (!$filename) {
                return Response::error('Filename is required', 400);
            }

            $filepath = $this->uploadDir . $filename;

            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    return Response::success(null, 'Image deleted successfully');
                } else {
                    return Response::error('Failed to delete image', 500);
                }
            } else {
                return Response::error('Image not found', 404);
            }

        } catch (\Exception $e) {
            return Response::error('Delete failed: ' . $e->getMessage(), 500);
        }
    }
}
