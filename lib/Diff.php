<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\DemoStarter;

/**
 * Zeilenweiser Vergleich zweier Texte (längste gemeinsame Teilfolge) – für „Unterschiede ansehen“.
 * Ausgabe als HTML: entfernte Zeilen rot (−), neue grün (+), unveränderte nur als Kontext rund um Änderungen.
 */
final class Diff
{
    public static function html(string $old, string $new, int $context = 3): string
    {
        $a = explode("\n", str_replace("\r\n", "\n", $old));
        $b = explode("\n", str_replace("\r\n", "\n", $new));
        $ops = self::ops($a, $b);

        // Nur Zeilen in der Nähe einer Änderung zeigen
        $show = [];
        foreach ($ops as $i => [$type]) {
            if ('=' !== $type) {
                for ($k = max(0, $i - $context); $k <= min(count($ops) - 1, $i + $context); ++$k) {
                    $show[$k] = true;
                }
            }
        }
        if ([] === $show) {
            return '<p class="text-muted">–</p>';
        }

        $html = '';
        $last = -1;
        foreach ($ops as $i => [$type, $line]) {
            if (!isset($show[$i])) {
                continue;
            }
            if ($last >= 0 && $i > $last + 1) {
                $html .= '<span class="demo-diff-gap">⋯</span>';
            }
            $class = ['+' => 'demo-diff-add', '-' => 'demo-diff-del'][$type] ?? 'demo-diff-same';
            $html .= '<span class="' . $class . '">' . ('=' === $type ? ' ' : $type) . ' ' . rex_escape($line) . '</span>';
            $last = $i;
        }
        return '<pre class="demo-diff">' . $html . '</pre>';
    }

    /**
     * @param list<string> $a
     * @param list<string> $b
     * @return list<array{0: string, 1: string}> ['=', Zeile] | ['-', Zeile] | ['+', Zeile]
     */
    private static function ops(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);
        // Länge der gemeinsamen Teilfolge ab Position i/j (von hinten aufgebaut)
        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; --$i) {
            for ($j = $m - 1; $j >= 0; --$j) {
                $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }
        $ops = [];
        $i = $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $ops[] = ['=', $a[$i]];
                ++$i;
                ++$j;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $ops[] = ['-', $a[$i++]];
            } else {
                $ops[] = ['+', $b[$j++]];
            }
        }
        while ($i < $n) {
            $ops[] = ['-', $a[$i++]];
        }
        while ($j < $m) {
            $ops[] = ['+', $b[$j++]];
        }
        return $ops;
    }
}
