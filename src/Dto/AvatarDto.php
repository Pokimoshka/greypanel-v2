<?php

declare(strict_types=1);

namespace GreyPanel\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AvatarDto
{
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        maxSizeMessage: 'avatar.too_big',
        mimeTypesMessage: 'avatar.invalid_type',
        uploadIniSizeErrorMessage: 'avatar.too_big',
        uploadFormSizeErrorMessage: 'avatar.too_big',
        uploadErrorMessage: 'avatar.upload_error'
    )]
    public UploadedFile $file;

    public function __construct($file)
    {
        $this->file = $file;
    }
}
