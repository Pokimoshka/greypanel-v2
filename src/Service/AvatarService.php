<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Service\AvatarServiceInterface;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AvatarService implements AvatarServiceInterface
{
    private int $maxWidth = 200;
    private int $maxHeight = 200;
    private int $maxSize = 2 * 1024 * 1024;
    private array $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    public function validate(UploadedFile $file): ?string
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return 'Ошибка загрузки файла.';
        }
        if ($file->getSize() > $this->maxSize) {
            return 'Файл слишком большой (макс. 2 МБ).';
        }

        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return 'Файл не является изображением.';
        }
        $mime = $imageInfo['mime'];
        if (!in_array($mime, $this->allowedMime)) {
            return 'Разрешены только JPEG, PNG, GIF, WEBP.';
        }
        return null;
    }

    public function resizeAndSave(UploadedFile $file, int $userId): string
    {
        $image = $this->manager->decodeSplFileInfo($file);
        $image->cover($this->maxWidth, $this->maxHeight);

        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!is_writable($uploadDir)) {
            throw new \RuntimeException('Директория загрузок недоступна для записи');
        }

        $filename = 'avatar_' . $userId . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $filePath = $uploadDir . $filename;

        $image->save($filePath, quality: 95);

        if (!file_exists($filePath)) {
            throw new \RuntimeException('Не удалось сохранить аватар');
        }

        return '/uploads/avatars/' . $filename;
    }

    public function deleteOldAvatar(?string $avatarPath): void
    {
        if (!$avatarPath) {
            return;
        }
        if (str_contains($avatarPath, 'avatar_default.png')) {
            return;
        }
        $fullPath = __DIR__ . '/../../public' . $avatarPath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
