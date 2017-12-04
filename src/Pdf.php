<?php

namespace Spatie\PdfToText;

use Spatie\PdfToText\Exceptions\CouldNotExtractText;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Symfony\Component\Process\Process;

class Pdf
{
    protected $pdf;

    protected $binPath;

    public function __construct(string $binPath = null)
    {
        $this->binPath = $binPath ?? '/usr/bin/pdftotext';
    }

    public function setOptions(array $options) : Pdf
    {
        if (!empty($options)) {
            foreach ($options as $option) {
                $this->binPath .= ' ' . escapeshellarg(trim($option));
            }
        }

        return $this;
    }

    public function setPdf(string $pdf) : Pdf
    {
        if (!file_exists($pdf)) {
            throw new PdfNotFound("could not find pdf {$pdf}");
        }

        $this->pdf = $pdf;

        return $this;
    }

    public function text() : string
    {
        $process = new Process("{$this->binPath} " . escapeshellarg($this->pdf) . " -");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new CouldNotExtractText($process);
        }

        return trim($process->getOutput(), " \t\n\r\0\x0B\x0C");
    }

    public function toArray() : array
    {
      $process = new Process("{$this->binPath} " . escapeshellarg($this->pdf) . " -");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new CouldNotExtractText($process);
        }

        $pages = explode("\f", $process->getOutput());
        foreach($pages as $key => $page)
        {
            $pages[$key] = explode("\n", $page);
        }

        return $pages;
   }

   public function json() : string
   {
        return json_encode($this->toArray());
   }

    public static function getText(string $pdf, string $binPath = null, $options = []) : string
    {
        return (new static($binPath))
            ->setOptions($options)
            ->setPdf($pdf)
            ->text();
    }
}
