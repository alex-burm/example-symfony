<?php

namespace App\Service;

interface ExportInterface
{
    public function run(array $data): string;

    public function getFileType(): string;

    public function getFriendlyFileName(): string;
}
