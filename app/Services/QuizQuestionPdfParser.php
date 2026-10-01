<?php

namespace App\Services;

use RuntimeException;

class QuizQuestionPdfParser
{
    private CurriculumFileTextExtractor $extractor;

    public function __construct(CurriculumFileTextExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    public function parse(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Questions PDF not found.');
        }

        $text = $this->extractor->extract($absolutePath, 'pdf');
        if (trim($text) === '') {
            throw new RuntimeException('Unable to read text from the PDF.');
        }

        $clean = $this->cleanText($text);
        $blocks = $this->splitIntoBlocks($clean);

        $questions = [];
        foreach ($blocks as $block) {
            $question = $this->parseBlock($block['number'], $block['text']);
            if ($question) {
                $questions[] = $question;
            }
        }

        $questions = $this->attachImages($absolutePath, $questions);

        if (empty($questions)) {
            throw new RuntimeException('No questions detected in the PDF.');
        }

        return $questions;
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(["\r", "\f"], "\n", $text);
        $text = preg_replace('/Tick\s*(\d+)\s*correct\s*[\n\s]*answers?/i', 'Tick $1 correct answer', $text);

        $lines = preg_split('/\n+/', $text);
        $filtered = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));
            if ($line === '') {
                continue;
            }
            if ($this->isNoiseLine($line)) {
                continue;
            }
            $filtered[] = $line;
        }

        return implode("\n", $filtered);
    }

    private function isNoiseLine(string $line): bool
    {
        $patterns = [
            '/oak national academy/i',
            '/open government licence/i',
            '/produced in partnership/i',
            '/licensed on the/i',
            '/terms & conditions/i',
            '/^name:\s*exit quiz/i',
            '/exit quiz/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        if (preg_match('/^\d+\s*Multiplicative relationships$/i', $line)) {
            return true;
        }

        if (preg_match('/^Multiplicative relationships$/i', $line)) {
            return true;
        }

        if (preg_match('/^Multiplicative relationships Exit quiz$/i', $line)) {
            return true;
        }

        return false;
    }

    private function splitIntoBlocks(string $text): array
    {
        $blocks = [];
        if (!preg_match_all('/(?m)^\s*(?:Question\s+)?(\d{1,3})\b/', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return $blocks;
        }

        $positions = [];
        foreach ($matches[1] as $match) {
            $positions[] = $match[1];
        }

        $length = strlen($text);
        $total = count($positions);
        for ($i = 0; $i < $total; $i++) {
            $start = $positions[$i];
            $end = $positions[$i + 1] ?? $length;
            if ($end <= $start) {
                continue;
            }

            $chunk = trim(substr($text, $start, $end - $start));
            if ($chunk === '') {
                continue;
            }

            if (!preg_match('/^\s*(?:Question\s+)?(\d{1,3})\b/i', $chunk, $numMatch)) {
                continue;
            }

            $number = (int) $numMatch[1];
            $chunk = trim(preg_replace('/^\s*(?:Question\s+)?\d{1,3}\b\s*/i', '', $chunk));
            if ($chunk === '') {
                continue;
            }

            $blocks[] = [
                'number' => $number,
                'text' => $chunk,
            ];
        }

        return $blocks;
    }

    private function parseBlock(int $number, string $block): ?array
    {
        if (preg_match('/Tick\s*(\d+)\s*correct\s*answer/i', $block, $tickMatch, PREG_OFFSET_CAPTURE)) {
            $tickCount = (int) $tickMatch[1][0];
            $tickPos = $tickMatch[0][1];
            $tickLen = strlen($tickMatch[0][0]);

            $questionText = trim(substr($block, 0, $tickPos));
            $optionsText = trim(substr($block, $tickPos + $tickLen));
            $parseResult = $this->parseOptionsAndCorrect($optionsText);
            $choices = $parseResult['choices'];
            $correctAnswers = $parseResult['correct_answers'];

            if (count($choices) < 2) {
                return $this->buildEssayQuestion($number, $block);
            }

            $correctIndices = $this->resolveCorrectIndices($correctAnswers, $choices);
            if (!empty($correctAnswers) && empty($correctIndices)) {
                throw new RuntimeException("Question {$number}: Correct answer does not match any choice.");
            }

            $hasCorrect = !empty($correctIndices);
            $correctCount = $hasCorrect ? count($correctIndices) : max(1, $tickCount);

            return [
                'type' => 'multichoice',
                'number' => $number,
                'name' => 'Question ' . $number,
                'text' => $questionText,
                'choices' => $choices,
                'correct_indices' => $correctIndices,
                'correct_count' => max(1, $correctCount),
                'has_correct' => $hasCorrect,
            ];
        }
        $parsed = $this->parseBlockByBullets($block);
        if ($parsed) {
            $parsed['number'] = $number;
            $parsed['name'] = 'Question ' . $number;
            return $parsed;
        }

        return $this->buildEssayQuestion($number, $block);
    }

    private function buildEssayQuestion(int $number, string $text): array
    {
        return [
            'type' => 'essay',
            'number' => $number,
            'name' => 'Question ' . $number,
            'text' => $text,
            'choices' => [],
            'has_correct' => false,
        ];
    }

    private function splitChoices(string $optionsText): array
    {
        if ($optionsText === '') {
            return [];
        }

        $lines = preg_split('/\n+/', $optionsText);
        $choices = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^\s*[•\-]\s*(.+)$/u', $line, $match)) {
                $line = trim((string) $match[1]);
            }
            $choices[] = $line;
        }

        return $choices;
    }

    private function parseOptionsAndCorrect(string $optionsText): array
    {
        $lines = preg_split('/\n+/', $optionsText);
        $choices = [];
        $correctAnswers = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^Correct\s*Answers?\s*:\s*(.+)$/i', $line, $match)) {
                $correctAnswers = $this->splitCorrectAnswers($match[1]);
                continue;
            }

            $choices[] = $line;
        }

        return [
            'choices' => $choices,
            'correct_answers' => $correctAnswers,
        ];
    }

    private function splitCorrectAnswers(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $parts = preg_split('/\s*(?:,|;|and|\s+\/\s+)\s*/i', $value);
        $answers = [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part !== '') {
                $answers[] = $part;
            }
        }

        return $answers;
    }

    private function resolveCorrectIndices(array $correctAnswers, array $choices): array
    {
        if (empty($correctAnswers) || empty($choices)) {
            return [];
        }

        $indices = [];
        foreach ($correctAnswers as $answer) {
            $raw = trim((string) $answer);
            if ($raw === '') {
                continue;
            }

            $normalized = strtolower($raw);
            if (preg_match('/^[a-d]$/', $normalized)) {
                $idx = ord($normalized) - 97;
                if (isset($choices[$idx])) {
                    $indices[$idx] = true;
                    continue;
                }
            }

            if (is_numeric($normalized)) {
                $num = (int) $normalized;
                if ($num >= 1 && $num <= count($choices)) {
                    $indices[$num - 1] = true;
                    continue;
                }
            }

            $target = $this->normalizeMathText($raw);
            foreach ($choices as $idx => $choice) {
                if ($this->normalizeMathText((string) $choice) === $target) {
                    $indices[$idx] = true;
                    break;
                }
            }
        }

        return array_keys($indices);
    }

    private function normalizeMathText(string $text): string
    {
        $text = trim(mb_strtolower($text));
        $text = str_replace(['−', '–', '—'], '-', $text);
        $text = str_replace(['÷'], '/', $text);
        $text = str_replace(['×', 'x'], '*', $text);
        $text = preg_replace('/\s+/', '', $text);
        $text = trim($text, " \t\n\r\0\x0B()[]");

        return $text;
    }

    private function parseBlockByBullets(string $block): ?array
    {
        $lines = preg_split('/\n+/', $block);
        $questionLines = [];
        $choices = [];
        $correctAnswers = [];
        $optionsStarted = false;

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^Correct\s*Answers?\s*:\s*(.+)$/i', $line, $match)) {
                $correctAnswers = $this->splitCorrectAnswers($match[1]);
                continue;
            }

            if (preg_match('/^\s*[•\-]\s*(.+)$/u', $line, $match)) {
                $choices[] = trim((string) $match[1]);
                $optionsStarted = true;
                continue;
            }

            if ($optionsStarted && !empty($choices)) {
                $choices[count($choices) - 1] .= ' ' . $line;
                continue;
            }

            $questionLines[] = $line;
        }

        $questionText = trim(implode(' ', $questionLines));

        if (count($choices) < 2) {
            if (!empty($correctAnswers)) {
                return [
                    'type' => 'shortanswer',
                    'text' => $questionText,
                    'answers' => $correctAnswers,
                    'has_correct' => true,
                ];
            }
            return null;
        }

        $correctIndices = $this->resolveCorrectIndices($correctAnswers, $choices);
        if (!empty($correctAnswers) && empty($correctIndices)) {
            return [
                'type' => 'shortanswer',
                'text' => $questionText,
                'answers' => $correctAnswers,
                'has_correct' => true,
            ];
        }

        $hasCorrect = !empty($correctIndices);
        $correctCount = $hasCorrect ? count($correctIndices) : 1;

        return [
            'type' => 'multichoice',
            'text' => $questionText,
            'choices' => $choices,
            'correct_indices' => $correctIndices,
            'correct_count' => $correctCount,
            'has_correct' => $hasCorrect,
        ];
    }

    private function attachImages(string $absolutePath, array $questions): array
    {
        $imageMap = $this->extractImagesWithPoppler($absolutePath);
        if (empty($imageMap)) {
            $imageMap = $this->extractImagesWithPython($absolutePath);
        }

        if (empty($imageMap)) {
            return $questions;
        }

        foreach ($questions as &$question) {
            $number = (string) ($question['number'] ?? '');
            if ($number !== '' && !empty($imageMap[$number]) && is_array($imageMap[$number])) {
                $question['images'] = $imageMap[$number];
            }
        }
        unset($question);

        return $questions;
    }

    private function extractImagesWithPython(string $absolutePath): array
    {
        $script = base_path('scripts/quiz_pdf_image_map.py');
        if (!is_file($script)) {
            return [];
        }

        $commands = [
            sprintf('python %s %s', escapeshellarg($script), escapeshellarg($absolutePath)),
            sprintf('py -3 %s %s', escapeshellarg($script), escapeshellarg($absolutePath)),
        ];

        $output = null;
        foreach ($commands as $command) {
            $output = @shell_exec($command);
            if (is_string($output) && trim($output) !== '') {
                break;
            }
        }

        if (!is_string($output) || trim($output) === '') {
            return [];
        }

        $payload = json_decode($output, true);
        if (!is_array($payload) || empty($payload['questions']) || !is_array($payload['questions'])) {
            return [];
        }

        return $payload['questions'];
    }

    private function extractImagesWithPoppler(string $absolutePath): array
    {
        $pdftotext = $this->resolvePopplerBinary('pdftotext');
        $pdftoppm = $this->resolvePopplerBinary('pdftoppm');

        $bboxHtml = @shell_exec(sprintf(
            '%s -q -bbox -layout %s -',
            escapeshellarg($pdftotext),
            escapeshellarg($absolutePath)
        ));

        if (!is_string($bboxHtml) || trim($bboxHtml) === '') {
            return [];
        }

        $pages = $this->parseBboxPages($bboxHtml);
        if (empty($pages)) {
            return [];
        }

        $tempDir = storage_path('app/tmp/quiz-diagrams-' . uniqid('', true));
        if (!is_dir($tempDir) && !@mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            return [];
        }

        $imageMap = [];
        foreach ($pages as $pageNumber => $pageData) {
            $anchors = $pageData['anchors'] ?? [];
            $lines = $pageData['lines'] ?? [];
            if (empty($anchors)) {
                continue;
            }

            $prefix = $tempDir . DIRECTORY_SEPARATOR . 'page_' . $pageNumber;
            @shell_exec(sprintf(
                '%s -q -r 144 -f %d -l %d -png %s %s',
                escapeshellarg($pdftoppm),
                (int) $pageNumber,
                (int) $pageNumber,
                escapeshellarg($absolutePath),
                escapeshellarg($prefix)
            ));

            $pageImagePath = $prefix . '-' . $pageNumber . '.png';
            if (!is_file($pageImagePath)) {
                continue;
            }

            $pageImage = @imagecreatefrompng($pageImagePath);
            if (!$pageImage) {
                continue;
            }

            $imgWidth = imagesx($pageImage);
            $imgHeight = imagesy($pageImage);
            $pageWidth = (float) ($pageData['width'] ?? $imgWidth);
            $pageHeight = (float) ($pageData['height'] ?? $imgHeight);
            if ($pageWidth <= 0 || $pageHeight <= 0) {
                imagedestroy($pageImage);
                continue;
            }

            $scaleX = $imgWidth / $pageWidth;
            $scaleY = $imgHeight / $pageHeight;
            $scale = $scaleX > 0 ? $scaleX : $scaleY;

            $anchorCount = count($anchors);
            for ($i = 0; $i < $anchorCount; $i++) {
                $anchor = $anchors[$i];
                $nextAnchor = $anchors[$i + 1] ?? null;
                $y0 = max(0.0, ((float) $anchor['y']) - 6);
                $y1 = $nextAnchor ? max($y0 + 1, ((float) $nextAnchor['y']) - 4) : $pageHeight;

                $cropTop = $y0;
                $cropBottom = $y1;
                $questionLines = [];
                if (!empty($lines)) {
                    foreach ($lines as $line) {
                        if ($line['y'] < $y0 || $line['y'] > $y1) {
                            continue;
                        }
                        $questionLines[] = $line;
                    }
                }

                if (!empty($questionLines)) {
                    $lastQuestionLineY = null;
                    $firstOptionLineY = null;
                    foreach ($questionLines as $line) {
                        $text = $line['text'] ?? '';
                        if ($this->isOptionLine($text)) {
                            $firstOptionLineY = $line['y'];
                            break;
                        }
                        $lastQuestionLineY = $line['y'];
                    }

                    if ($firstOptionLineY !== null) {
                        $cropTop = $lastQuestionLineY !== null ? $lastQuestionLineY + 6 : $cropTop + 6;
                        $cropBottom = $firstOptionLineY - 6;
                        if ($cropBottom <= $cropTop + 10) {
                            $cropTop = $y0;
                            $cropBottom = $y1;
                        }
                    } elseif ($lastQuestionLineY !== null) {
                        $cropTop = $lastQuestionLineY + 6;
                        if ($cropBottom <= $cropTop + 10) {
                            $cropTop = $y0;
                        }
                    }
                }

                $cropY = (int) floor($cropTop * $scale);
                $cropHeight = (int) ceil(max(1.0, $cropBottom - $cropTop) * $scale);
                if ($cropHeight < 40) {
                    continue;
                }

                $cropRect = [
                    'x' => 0,
                    'y' => max(0, $cropY),
                    'width' => $imgWidth,
                    'height' => min($imgHeight - $cropY, $cropHeight),
                ];

                $cropped = @imagecrop($pageImage, $cropRect);
                if (!$cropped) {
                    continue;
                }

                $trimmed = $this->trimWhitespace($cropped, 6);
                if ($trimmed === null) {
                    imagedestroy($cropped);
                    continue;
                }

                ob_start();
                imagepng($trimmed);
                $pngData = (string) ob_get_clean();
                imagedestroy($cropped);
                if ($trimmed !== $cropped) {
                    imagedestroy($trimmed);
                }

                if ($pngData === '') {
                    continue;
                }

                $questionNumber = (string) $anchor['number'];
                $imageMap[$questionNumber][] = [
                    'name' => 'q' . $questionNumber . '_diagram.png',
                    'data' => base64_encode($pngData),
                    'mimetype' => 'image/png',
                ];
            }

            imagedestroy($pageImage);
        }

        $this->cleanupTempDir($tempDir);

        return $imageMap;
    }

    private function parseBboxPages(string $html): array
    {
        $pages = [];

        if (!preg_match_all('/<page\\b([^>]*)>(.*?)<\\/page>/s', $html, $pageMatches, PREG_SET_ORDER)) {
            return $pages;
        }

        foreach ($pageMatches as $index => $pageMatch) {
            $attrText = $pageMatch[1] ?? '';
            $pageBody = $pageMatch[2] ?? '';

            $pageNumber = $index + 1;
            $pageWidth = $this->parseAttributeFloat($attrText, 'width') ?? 0.0;
            $pageHeight = $this->parseAttributeFloat($attrText, 'height') ?? 0.0;

            $words = [];
            if (preg_match_all('/<word\\b([^>]*)>(.*?)<\\/word>/s', $pageBody, $wordMatches, PREG_SET_ORDER)) {
                foreach ($wordMatches as $wordMatch) {
                    $wordAttr = $wordMatch[1] ?? '';
                    $wordText = html_entity_decode(strip_tags((string) ($wordMatch[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $wordText = trim($wordText);
                    if ($wordText === '') {
                        continue;
                    }

                    $xMin = $this->parseAttributeFloat($wordAttr, 'xMin');
                    $yMin = $this->parseAttributeFloat($wordAttr, 'yMin');
                    if ($xMin === null || $yMin === null) {
                        continue;
                    }

                    $words[] = [
                        'text' => $wordText,
                        'x' => $xMin,
                        'y' => $yMin,
                    ];
                }
            }

            $lines = $this->groupWordsIntoLines($words);
            $anchors = [];
            foreach ($lines as $line) {
                $lineText = $line['text'];
                if ($lineText === '' || $this->isNoiseLine($lineText)) {
                    continue;
                }

                if (preg_match('/^(?:Question\\s+)?(\\d{1,3})\\b/i', $lineText, $match)) {
                    $anchors[] = [
                        'number' => (int) $match[1],
                        'y' => (float) $line['y'],
                    ];
                }
            }

            usort($anchors, static function ($a, $b) {
                return $a['y'] <=> $b['y'];
            });

            if (!empty($anchors)) {
                $pages[$pageNumber] = [
                    'width' => $pageWidth,
                    'height' => $pageHeight,
                    'lines' => $lines,
                    'anchors' => $anchors,
                ];
            }
        }

        return $pages;
    }

    private function parseAttributeFloat(string $attrText, string $name): ?float
    {
        if (preg_match('/\\b' . preg_quote($name, '/') . '=\"([0-9.]+)\"/i', $attrText, $match)) {
            return (float) $match[1];
        }

        return null;
    }

    private function groupWordsIntoLines(array $words): array
    {
        if (empty($words)) {
            return [];
        }

        usort($words, static function ($a, $b) {
            return $a['y'] <=> $b['y'] ?: ($a['x'] <=> $b['x']);
        });

        $lines = [];
        $tolerance = 2.5;

        foreach ($words as $word) {
            $matchedIndex = null;
            foreach ($lines as $index => $line) {
                if (abs($line['y'] - $word['y']) <= $tolerance) {
                    $matchedIndex = $index;
                    break;
                }
            }

            if ($matchedIndex === null) {
                $lines[] = [
                    'y' => $word['y'],
                    'words' => [$word],
                ];
            } else {
                $lines[$matchedIndex]['words'][] = $word;
                $lines[$matchedIndex]['y'] = min($lines[$matchedIndex]['y'], $word['y']);
            }
        }

        $result = [];
        foreach ($lines as $line) {
            usort($line['words'], static function ($a, $b) {
                return $a['x'] <=> $b['x'];
            });
            $text = implode(' ', array_map(static function ($word) {
                return $word['text'];
            }, $line['words']));

            $result[] = [
                'y' => $line['y'],
                'text' => trim($text),
            ];
        }

        return $result;
    }

    private function isOptionLine(string $lineText): bool
    {
        $lineText = trim($lineText);
        if ($lineText === '') {
            return false;
        }

        if (preg_match('/^[•\-\x{2013}\x{2014}]/u', $lineText)) {
            return true;
        }

        if (preg_match('/^[A-Da-d][\).\s]+/', $lineText)) {
            return true;
        }

        return false;
    }

    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        @rmdir($dir);
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

    private function trimWhitespace($image, int $padding = 4): ?\GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($alpha < 127 && ($r < 245 || $g < 245 || $b < 245)) {
                    if ($x < $minX) {
                        $minX = $x;
                    }
                    if ($y < $minY) {
                        $minY = $y;
                    }
                    if ($x > $maxX) {
                        $maxX = $x;
                    }
                    if ($y > $maxY) {
                        $maxY = $y;
                    }
                }
            }
        }

        if ($maxX < 0 || $maxY < 0) {
            return null;
        }

        $minX = max(0, $minX - $padding);
        $minY = max(0, $minY - $padding);
        $maxX = min($width - 1, $maxX + $padding);
        $maxY = min($height - 1, $maxY + $padding);

        $crop = @imagecrop($image, [
            'x' => $minX,
            'y' => $minY,
            'width' => ($maxX - $minX + 1),
            'height' => ($maxY - $minY + 1),
        ]);

        return $crop ?: null;
    }
}
