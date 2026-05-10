<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Dto\AvatarDto;
use GreyPanel\Interface\Service\AvatarServiceInterface;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AvatarService implements AvatarServiceInterface
{
    private int $maxWidth = 200;
    private int $maxHeight = 200;

    public function __construct(
        private ImageManager $manager,
        private ValidatorInterface $validator
    ) {
        $this->manager = new ImageManager(Driver::class);
    }

    public function validate(UploadedFile $file): ?string
    {
        $dto = new AvatarDto($file);
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return $violations[0]->getMessage();
        }
        return null;
    }

    public function resizeAndSave(UploadedFile $file, int $userId): string
    {
        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodeSplFileInfo($file);
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

        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 95);
        $encoded->save($filePath);

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
