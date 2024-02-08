<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class ExportCsv
{
    protected KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
        $lines = [];
        foreach ($data as $row) {
            $lines[] = implode(",", $row);
        }
        $string = implode("\n", $lines);

        $filename = tempnam(
            $this->kernel->getProjectDir() . '/var',
            'export-csv-'
        );
        file_put_contents($filename, $string);

        return $filename;
    }
}
