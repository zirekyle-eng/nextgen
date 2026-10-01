<?php

namespace App\Services;

use RuntimeException;

class QuizQuestionCsvParser
{
    private const HEADER_ALIASES = [
        'question' => ['question', 'q', 'question_text'],
        'choice_1' => ['choice_1', 'choice1', 'option_1', 'option1', 'a', 'answer_a'],
        'choice_2' => ['choice_2', 'choice2', 'option_2', 'option2', 'b', 'answer_b'],
        'choice_3' => ['choice_3', 'choice3', 'option_3', 'option3', 'c', 'answer_c'],
        'choice_4' => ['choice_4', 'choice4', 'option_4', 'option4', 'd', 'answer_d'],
        'correct_choice' => ['correct_choice', 'correct', 'correct_answer', 'answer', 'key', 'correct_option'],
        'feedback' => ['feedback', 'explanation', 'note'],
    ];

    public function parse(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Questions file not found.');
        }

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to read questions file.');
        }

        try {
            $delimiter = $this->detectDelimiter($handle);
            rewind($handle);

            $rawHeader = fgetcsv($handle, 0, $delimiter);
            if (!$rawHeader) {
                throw new RuntimeException('Questions file is empty.');
            }

            $headerMap = $this->buildHeaderMap($rawHeader);
            $this->assertHeaderMap($headerMap);

            $questions = [];
            $rowIndex = 1;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowIndex++;
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $question = $this->mapRowToQuestion($row, $headerMap, $rowIndex);
                if ($question) {
                    $questions[] = $question;
                }
            }

            if (empty($questions)) {
                throw new RuntimeException('No valid questions found in the file.');
            }

            return $questions;
        } finally {
            fclose($handle);
        }
    }

    private function detectDelimiter($handle): string
    {
        $sample = fgets($handle);
        if ($sample === false) {
            return ',';
        }

        $commaCount = substr_count($sample, ',');
        $semiCount = substr_count($sample, ';');
        $tabCount = substr_count($sample, "\t");

        if ($tabCount > $commaCount && $tabCount > $semiCount) {
            return "\t";
        }

        return $semiCount > $commaCount ? ';' : ',';
    }

    private function buildHeaderMap(array $rawHeader): array
    {
        $headers = array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            return strtolower(trim($header));
        }, $rawHeader);

        $map = [];
        foreach ($headers as $index => $header) {
            foreach (self::HEADER_ALIASES as $canonical => $aliases) {
                if (in_array($header, $aliases, true) && !isset($map[$canonical])) {
                    $map[$canonical] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function assertHeaderMap(array $headerMap): void
    {
        foreach (['question', 'choice_1', 'choice_2', 'correct_choice'] as $required) {
            if (!array_key_exists($required, $headerMap)) {
                throw new RuntimeException('Missing required column: ' . $required . '.');
            }
        }
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function mapRowToQuestion(array $row, array $headerMap, int $rowIndex): ?array
    {
        $questionText = $this->valueFromRow($row, $headerMap, 'question');
        if ($questionText === '') {
            throw new RuntimeException("Row {$rowIndex}: Question text is required.");
        }

        $choice1 = $this->valueFromRow($row, $headerMap, 'choice_1');
        $choice2 = $this->valueFromRow($row, $headerMap, 'choice_2');
        $choice3 = $this->valueFromRow($row, $headerMap, 'choice_3');
        $choice4 = $this->valueFromRow($row, $headerMap, 'choice_4');

        if ($choice1 === '' || $choice2 === '') {
            throw new RuntimeException("Row {$rowIndex}: At least two choices are required.");
        }

        if ($choice3 === '' && $choice4 !== '') {
            throw new RuntimeException("Row {$rowIndex}: Choice 3 is required if Choice 4 is provided.");
        }

        $choices = array_values(array_filter([$choice1, $choice2, $choice3, $choice4], function ($value) {
            return $value !== '';
        }));

        $correctRaw = $this->valueFromRow($row, $headerMap, 'correct_choice');
        $correctIndex = $this->resolveCorrectIndex($correctRaw, $choices);
        if ($correctIndex === null) {
            throw new RuntimeException("Row {$rowIndex}: Correct choice is invalid.");
        }

        $feedback = $this->valueFromRow($row, $headerMap, 'feedback');

        return [
            'name' => 'Question ' . max(1, $rowIndex - 1),
            'type' => 'multichoice',
            'text' => $questionText,
            'choices' => $choices,
            'correct_index' => $correctIndex,
            'correct_count' => 1,
            'has_correct' => true,
            'feedback' => $feedback,
        ];
    }

    private function valueFromRow(array $row, array $headerMap, string $key): string
    {
        if (!array_key_exists($key, $headerMap)) {
            return '';
        }

        $index = $headerMap[$key];
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    private function resolveCorrectIndex(string $rawValue, array $choices): ?int
    {
        if ($rawValue === '') {
            return null;
        }

        $normalized = strtolower(trim($rawValue));
        if (is_numeric($normalized)) {
            $num = (int) $normalized;
            if ($num >= 1 && $num <= count($choices)) {
                return $num - 1;
            }
        }

        if (preg_match('/^[a-d]$/', $normalized)) {
            $num = ord($normalized) - 96;
            if ($num >= 1 && $num <= count($choices)) {
                return $num - 1;
            }
        }

        foreach ($choices as $index => $choice) {
            if (mb_strtolower($choice) === mb_strtolower($rawValue)) {
                return $index;
            }
        }

        return null;
    }
}
