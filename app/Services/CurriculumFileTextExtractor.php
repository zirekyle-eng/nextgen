<?php

namespace App\Services;

use ZipArchive;

class CurriculumFileTextExtractor
{
    private const MAX_TEXT_LENGTH = 30000;

    public function extract(string $absolutePath, ?string $extension = null): string
    {
        if (!is_file($absolutePath)) {
            return '';
        }

        $extension = strtolower((string) ($extension ?: pathinfo($absolutePath, PATHINFO_EXTENSION)));

        $text = match ($extension) {
            'txt' => $this->extractTxt($absolutePath),
            'docx' => $this->extractDocx($absolutePath),
            'xlsx' => $this->extractXlsx($absolutePath),
            'pptx' => $this->extractPptx($absolutePath),
            'pdf' => $this->extractPdf($absolutePath),
            'xml', 'html', 'htm' => $this->extractMarkup($absolutePath),
            default => '',
        };

        return $this->normalize($text);
    }

    private function extractTxt(string $path): string
    {
        $content = @file_get_contents($path);
        return $content === false ? '' : $content;
    }

    private function extractMarkup(string $path): string
    {
        $content = @file_get_contents($path);
        if ($content === false || $content === '') {
            return '';
        }

        $content = preg_replace('/<file\b[^>]*>.*?<\/file>/is', "\n", $content);
        $content = preg_replace('/<!\[CDATA\[(.*?)\]\]>/s', '$1', $content);
        $content = preg_replace('/<\s*\/\s*(questiontext|answer|name|text|p|div|br|item|question)\s*>/i', "\n", $content);
        $content = preg_replace('/\b[A-Za-z0-9+\/]{120,}={0,2}\b/', ' ', $content);

        return html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return '';
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml);
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractXlsx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        if (!$sharedStrings) {
            return '';
        }

        $sharedStrings = preg_replace('/<\/si>/', "\n", $sharedStrings);
        return html_entity_decode(strip_tags($sharedStrings), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractPptx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $slides = [];
        for ($i = 1; $i <= 200; $i++) {
            $slide = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if ($slide === false) {
                continue;
            }
            $slide = preg_replace('/<\/a:p>/', "\n", $slide);
            $slides[] = html_entity_decode(strip_tags($slide), ENT_QUOTES | ENT_XML1, 'UTF-8');
        }
        $zip->close();

        return implode("\n", $slides);
    }

    private function extractPdf(string $path): string
    {
        $text = $this->extractPdfUsingPdftotext($path);
        if ($text !== '') {
            return $text;
        }

        $text = $this->extractPdfUsingParser($path);
        if ($text !== '') {
            return $text;
        }

        return $this->extractPdfFallback($path);
    }

    private function extractPdfUsingPdftotext(string $path): string
    {
        $pdftotext = $this->resolvePopplerBinary('pdftotext');
        if ($pdftotext === '' || !is_file($pdftotext)) {
            return '';
        }

        $command = sprintf('%s -q -layout %s -', escapeshellarg($pdftotext), escapeshellarg($path));

        if (function_exists('shell_exec')) {
            $output = @shell_exec($command);
            if (is_string($output)) {
                return $output;
            }
        }

        if (class_exists('\Symfony\Component\Process\Process')) {
            try {
                $process = new \Symfony\Component\Process\Process([$pdftotext, '-q', '-layout', $path, '-']);
                $process->setTimeout(60);
                $process->run();
                if ($process->isSuccessful()) {
                    return $process->getOutput();
                }
            } catch (\Throwable $e) {
                // ignore and fallback
            }
        }

        if (function_exists('proc_open')) {
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = proc_open($command, $descriptors, $pipes);
            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                if (is_string($output)) {
                    return $output;
                }
            }
        }

        return '';
    }

    private function extractPdfFallback(string $path): string
    {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return '';
        }

        $result = [];
        if (preg_match_all('/stream[\r\n]+(.*?)endstream/s', $raw, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = $this->tryDecodePdfStream($stream);
                if ($decoded === '') {
                    continue;
                }

                if (preg_match_all('/\(([^()]|\\\\.)+\)/', $decoded, $strings)) {
                    foreach ($strings[0] as $value) {
                        $value = trim($value, '()');
                        $value = stripcslashes($value);
                        if ($value !== '') {
                            $result[] = $value;
                        }
                    }
                }
            }
        }

        return implode("\n", $result);
    }

    private function extractPdfUsingParser(string $path): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return '';
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            return (string) $pdf->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function tryDecodePdfStream(string $stream): string
    {
        $stream = ltrim($stream, "\r\n");

        $candidates = [];
        $candidates[] = @gzuncompress($stream);
        $candidates[] = @gzdecode($stream);
        $candidates[] = @gzinflate($stream);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return $stream;
    }

    private function normalize(string $text): string
    {
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        return mb_substr($text, 0, self::MAX_TEXT_LENGTH);
    }

    private function resolvePopplerBinary(string $binary): string
    {
        $binPath = trim((string) config('services.poppler.bin_path'));
        if ($binPath !== '') {
            $binPath = rtrim($binPath, '/\\') . DIRECTORY_SEPARATOR . $binary;
            if (stripos(PHP_OS, 'WIN') === 0 && !str_ends_with(strtolower($binPath), '.exe')) {
                $binPath .= '.exe';
            }
            if (is_file($binPath)) {
                return $binPath;
            }
        }

        return $binary;
    }
}
