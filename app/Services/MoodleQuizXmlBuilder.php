<?php

namespace App\Services;

use DOMDocument;

class MoodleQuizXmlBuilder
{
    public function build(array $questions): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $quiz = $doc->createElement('quiz');
        $doc->appendChild($quiz);

        foreach ($questions as $index => $question) {
            $quiz->appendChild($this->buildQuestionNode($doc, $question, $index + 1));
        }

        return $doc->saveXML();
    }

    private function buildQuestionNode(DOMDocument $doc, array $question, int $position)
    {
        $type = strtolower((string) ($question['type'] ?? 'multichoice'));
        if ($type === 'essay') {
            return $this->buildEssayQuestionNode($doc, $question, $position);
        }
        if ($type === 'shortanswer') {
            return $this->buildShortAnswerQuestionNode($doc, $question, $position);
        }

        $node = $doc->createElement('question');
        $node->setAttribute('type', 'multichoice');

        $nameText = $question['name'] ?? ('Question ' . $position);
        $node->appendChild($this->buildTextNode($doc, 'name', $nameText));

        $questionText = $question['text'] ?? '';
        $images = is_array($question['images'] ?? null) ? $question['images'] : [];
        $node->appendChild($this->buildQuestionTextNode($doc, $questionText, $images));

        $feedback = trim((string) ($question['feedback'] ?? ''));
        if ($feedback !== '') {
            $node->appendChild($this->buildTextNode($doc, 'generalfeedback', $feedback, 'html'));
        }

        $hasCorrect = array_key_exists('has_correct', $question) ? (bool) $question['has_correct'] : true;
        $node->appendChild($doc->createElement('defaultgrade', $hasCorrect ? '1.0000000' : '0.0000000'));
        $node->appendChild($doc->createElement('penalty', '0.0000000'));
        $node->appendChild($doc->createElement('hidden', '0'));
        $correctCount = (int) ($question['correct_count'] ?? 1);
        $node->appendChild($doc->createElement('single', $correctCount <= 1 ? 'true' : 'false'));
        $node->appendChild($doc->createElement('shuffleanswers', 'true'));
        $node->appendChild($doc->createElement('answernumbering', 'abc'));

        $choices = $question['choices'] ?? [];
        $correctIndex = $question['correct_index'] ?? null;
        $correctIndices = $question['correct_indices'] ?? null;
        $correctSet = [];

        if ($hasCorrect) {
            if (is_array($correctIndices)) {
                foreach ($correctIndices as $value) {
                    $correctSet[(int) $value] = true;
                }
            } elseif ($correctIndex !== null) {
                $correctSet[(int) $correctIndex] = true;
            }
        }

        $correctTotal = $hasCorrect ? max(1, count($correctSet)) : 0;

        foreach ($choices as $idx => $choice) {
            $fraction = '0';
            if ($hasCorrect && isset($correctSet[$idx])) {
                $fraction = (string) (100 / $correctTotal);
            }
            $node->appendChild($this->buildAnswerNode($doc, (string) $choice, $fraction));
        }

        return $node;
    }

    private function buildTextNode(DOMDocument $doc, string $tag, string $text, ?string $format = null)
    {
        $node = $doc->createElement($tag);
        if ($format !== null) {
            $node->setAttribute('format', $format);
        }

        $textNode = $doc->createElement('text');
        $textNode->appendChild($doc->createCDATASection($text));
        $node->appendChild($textNode);

        return $node;
    }

    private function buildQuestionTextNode(DOMDocument $doc, string $text, array $images)
    {
        $node = $doc->createElement('questiontext');
        $node->setAttribute('format', 'html');

        $html = $text;
        foreach ($images as $image) {
            if (empty($image['name'])) {
                continue;
            }
            $html .= '<p><img src="@@PLUGINFILE@@/' . htmlspecialchars((string) $image['name'], ENT_QUOTES, 'UTF-8') . '" alt="Question image"></p>';
        }

        $textNode = $doc->createElement('text');
        $textNode->appendChild($doc->createCDATASection($html));
        $node->appendChild($textNode);

        foreach ($images as $image) {
            if (empty($image['name']) || empty($image['data'])) {
                continue;
            }
            $fileNode = $doc->createElement('file', (string) $image['data']);
            $fileNode->setAttribute('name', (string) $image['name']);
            $fileNode->setAttribute('path', '/');
            $fileNode->setAttribute('encoding', 'base64');
            $node->appendChild($fileNode);
        }

        return $node;
    }

    private function buildAnswerNode(DOMDocument $doc, string $answerText, string $fraction)
    {
        $answer = $doc->createElement('answer');
        $answer->setAttribute('fraction', $fraction);
        $answer->setAttribute('format', 'html');

        $textNode = $doc->createElement('text');
        $textNode->appendChild($doc->createCDATASection($answerText));
        $answer->appendChild($textNode);

        $feedbackNode = $doc->createElement('feedback');
        $feedbackNode->setAttribute('format', 'html');
        $feedbackText = $doc->createElement('text');
        $feedbackText->appendChild($doc->createCDATASection(''));
        $feedbackNode->appendChild($feedbackText);
        $answer->appendChild($feedbackNode);

        return $answer;
    }

    private function buildEssayQuestionNode(DOMDocument $doc, array $question, int $position)
    {
        $node = $doc->createElement('question');
        $node->setAttribute('type', 'essay');

        $nameText = $question['name'] ?? ('Question ' . $position);
        $node->appendChild($this->buildTextNode($doc, 'name', $nameText));

        $questionText = $question['text'] ?? '';
        $images = is_array($question['images'] ?? null) ? $question['images'] : [];
        $node->appendChild($this->buildQuestionTextNode($doc, $questionText, $images));

        $node->appendChild($doc->createElement('defaultgrade', '0.0000000'));
        $node->appendChild($doc->createElement('penalty', '0.0000000'));
        $node->appendChild($doc->createElement('hidden', '0'));
        $node->appendChild($doc->createElement('responseformat', 'editor'));
        $node->appendChild($doc->createElement('responserequired', '0'));
        $node->appendChild($doc->createElement('responsefieldlines', '5'));
        $node->appendChild($doc->createElement('attachments', '0'));
        $node->appendChild($doc->createElement('attachmentsrequired', '0'));

        $graderInfo = $doc->createElement('graderinfo');
        $graderInfo->setAttribute('format', 'html');
        $graderInfoText = $doc->createElement('text');
        $graderInfoText->appendChild($doc->createCDATASection(''));
        $graderInfo->appendChild($graderInfoText);
        $node->appendChild($graderInfo);

        $responseTemplate = $doc->createElement('responsetemplate');
        $responseTemplate->setAttribute('format', 'html');
        $responseTemplateText = $doc->createElement('text');
        $responseTemplateText->appendChild($doc->createCDATASection(''));
        $responseTemplate->appendChild($responseTemplateText);
        $node->appendChild($responseTemplate);

        return $node;
    }

    private function buildShortAnswerQuestionNode(DOMDocument $doc, array $question, int $position)
    {
        $node = $doc->createElement('question');
        $node->setAttribute('type', 'shortanswer');

        $nameText = $question['name'] ?? ('Question ' . $position);
        $node->appendChild($this->buildTextNode($doc, 'name', $nameText));

        $questionText = $question['text'] ?? '';
        $images = is_array($question['images'] ?? null) ? $question['images'] : [];
        $node->appendChild($this->buildQuestionTextNode($doc, $questionText, $images));

        $node->appendChild($doc->createElement('defaultgrade', '1.0000000'));
        $node->appendChild($doc->createElement('penalty', '0.0000000'));
        $node->appendChild($doc->createElement('hidden', '0'));
        $node->appendChild($doc->createElement('usecase', '0'));

        $answers = $question['answers'] ?? [];
        if (!is_array($answers)) {
            $answers = [];
        }

        $answers = array_values(array_filter(array_map('strval', $answers), static function ($value) {
            return trim($value) !== '';
        }));

        $total = max(1, count($answers));
        foreach ($answers as $answerText) {
            $fraction = (string) (100 / $total);
            $node->appendChild($this->buildAnswerNode($doc, $answerText, $fraction));
        }

        return $node;
    }
}
