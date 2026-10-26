<?php

namespace App\Services;

class DiffService
{
    /**
     * Compute a word-level diff between two text strings.
     * Returns an array of tokens: ['type' => 'same'|'add'|'del', 'text' => string]
     */
    public static function diffWords(string $oldText, string $newText): array
    {
        // Tokenize into words and punctuation/whitespace
        $oldTokens = self::tokenize($oldText);
        $newTokens = self::tokenize($newText);

        $matrix = self::computeLcsMatrix($oldTokens, $newTokens);
        return self::backtrackDiff($matrix, $oldTokens, $newTokens, count($oldTokens), count($newTokens));
    }

    /**
     * Render inline HTML diff with <del> and <ins> tags.
     */
    public static function renderInlineDiff(?string $oldText, ?string $newText): string
    {
        $oldText = (string) $oldText;
        $newText = (string) $newText;

        if ($oldText === $newText) {
            return htmlspecialchars($oldText);
        }

        $tokens = self::diffWords($oldText, $newText);
        $html = '';

        foreach ($tokens as $token) {
            $escaped = htmlspecialchars($token['text']);
            if ($token['type'] === 'del') {
                $html .= '<del class="diff-del bg-danger-subtle text-danger text-decoration-line-through px-1 rounded">' . $escaped . '</del>';
            } elseif ($token['type'] === 'add') {
                $html .= '<ins class="diff-add bg-success-subtle text-success text-decoration-none fw-semibold px-1 rounded">' . $escaped . '</ins>';
            } else {
                $html .= $escaped;
            }
        }

        return nl2br($html);
    }

    /**
     * Render side-by-side diff structure.
     */
    public static function renderSideBySide(?string $oldText, ?string $newText): array
    {
        $oldText = (string) $oldText;
        $newText = (string) $newText;

        $tokens = self::diffWords($oldText, $newText);
        $leftHtml = '';
        $rightHtml = '';

        foreach ($tokens as $token) {
            $escaped = htmlspecialchars($token['text']);
            if ($token['type'] === 'del') {
                $leftHtml .= '<del class="diff-del bg-danger-subtle text-danger text-decoration-line-through px-1 rounded">' . $escaped . '</del>';
            } elseif ($token['type'] === 'add') {
                $rightHtml .= '<ins class="diff-add bg-success-subtle text-success text-decoration-none fw-semibold px-1 rounded">' . $escaped . '</ins>';
            } else {
                $leftHtml .= $escaped;
                $rightHtml .= $escaped;
            }
        }

        return [
            'old' => nl2br($leftHtml),
            'new' => nl2br($rightHtml),
        ];
    }

    /**
     * Tokenize text into words, whitespace, and punctuation.
     */
    private static function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }
        preg_match_all('/[\w]+|[^\w\s]+|\s+/u', $text, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Compute LCS length matrix.
     */
    private static function computeLcsMatrix(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);
        $matrix = [];

        for ($i = 0; $i <= $n; $i++) {
            $matrix[$i] = array_fill(0, $m + 1, 0);
        }

        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $m; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $matrix[$i][$j] = $matrix[$i - 1][$j - 1] + 1;
                } else {
                    $matrix[$i][$j] = max($matrix[$i - 1][$j], $matrix[$i][$j - 1]);
                }
            }
        }

        return $matrix;
    }

    /**
     * Backtrack matrix to form diff token list.
     */
    private static function backtrackDiff(array $matrix, array $a, array $b, int $i, int $j): array
    {
        $diff = [];
        $currI = $i;
        $currJ = $j;

        while ($currI > 0 || $currJ > 0) {
            if ($currI > 0 && $currJ > 0 && $a[$currI - 1] === $b[$currJ - 1]) {
                $diff[] = ['type' => 'same', 'text' => $a[$currI - 1]];
                $currI--;
                $currJ--;
            } elseif ($currJ > 0 && ($currI === 0 || $matrix[$currI][$currJ - 1] >= $matrix[$currI - 1][$currJ])) {
                $diff[] = ['type' => 'add', 'text' => $b[$currJ - 1]];
                $currJ--;
            } elseif ($currI > 0 && ($currJ === 0 || $matrix[$currI][$currJ - 1] < $matrix[$currI - 1][$currJ])) {
                $diff[] = ['type' => 'del', 'text' => $a[$currI - 1]];
                $currI--;
            }
        }

        return array_reverse($diff);
    }
}
