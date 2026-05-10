<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractController
{
    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;
    protected TranslatorInterface $translator;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    protected function json(mixed $data, int $status = 200, array $context = []): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ], $context));
        return new JsonResponse($json, $status, true);
    }

    protected function validate(mixed $value, array|Assert\Collection $constraints): array
    {
        $violations = $this->validator->validate($value, $constraints);
        $errors = [];
        foreach ($violations as $violation) {
            $field = $violation->getPropertyPath();
            $errors[$field] = $violation->getMessage();
        }
        return $errors;
    }

    protected function trans(string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters);
    }
}
