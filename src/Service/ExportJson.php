<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class ExportJson implements ExportInterface
{
    protected string $projectDir = '';

    public function __construct(KernelInterface $a)
    {
        $this->projectDir = $a->getProjectDir();
    }

    /**
     * $data = [
     *     ['id' => 1, 'name' => 'Alex',],
     *     ['id' => 2, 'name' => 'Alex',],
     *     ['id' => 3, 'name' => 'Alex',],
     * ]
     * 1,Alex\n
     * 2,Alex\n
     * 3,Alex\n
     *
     * @param array $data
     * @return string
     */
    public function run(array $data): string
    {
        $string = json_encode($data);

        $filename = tempnam(
            $this->projectDir . '/var',
            'export-json-'
        );
        file_put_contents($filename, $string);

        return $filename;
    }

    public function getFileType(): string
    {
        return 'application/json';
    }

    public function getFriendlyFileName(): string
    {
        return 'export-data.json';
    }
}
