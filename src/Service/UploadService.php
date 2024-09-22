<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadService
{
    public function __construct(
        protected string $uploadDirectory,
    ) {
    }

    public function saveFile(Object $entity, FormInterface $form, string $property): void
    {
        $partsOfClassName = \explode('\\', \get_class($entity));
        $entityName = \strtolower(\array_pop($partsOfClassName));

        /** @var UploadedFile $file */
        $uploadedFile = $form->get($property)->getData();
        if (false === ($uploadedFile instanceof UploadedFile)) {
            return;
        }

        $file = $uploadedFile->move(
            $this->uploadDirectory . '/' . $entityName,
            $uploadedFile->getClientOriginalName(),
        );

        $method = 'set' . \ucfirst($property);
        $entity->$method($file->getBasename());
    }

    public function saveUploadedFile(UploadedFile $uploadedFile): File
    {
        $file = $uploadedFile->move(
            $this->uploadDirectory . '/editor',
            $uploadedFile->getClientOriginalName(),
        );

        return $file;
    }
}
