<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImageUploadService
{
    private const MAX_WIDTH = 1200;
    private const QUALITY = 85;
    /** @var string[] */
    private array $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function upload(UploadedFile $file, string $subDir = 'uploads'): string
    {
        $mime = $file->getClientMimeType();
        if (!in_array($mime, $this->allowedMime, true)) {
            throw new \RuntimeException('Недопустимый тип файла. Разрешены: JPEG, PNG, GIF, WebP.');
        }

        $uploadDir = __DIR__ . '/../../public/' . $subDir . '/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new \RuntimeException('Не удалось создать директорию для загрузок.');
        }

        $filename = uniqid() . '.webp';
        $targetPath = $uploadDir . $filename;

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodeSplFileInfo($file);
        $image->scale(width: self::MAX_WIDTH);
        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: self::QUALITY);
        $encoded->save($targetPath);

        return '/' . $subDir . '/' . $filename;
    }
}
