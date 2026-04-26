<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AvatarServiceInterface
{
    public function validate(UploadedFile $file): ?string;
    public function resizeAndSave(UploadedFile $file, int $userId): string;
    public function deleteOldAvatar(?string $avatarPath): void;
}
