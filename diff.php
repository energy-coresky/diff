<?php

class Diff
{
    static function diffx(string $new, string $old, $boundary = 3, $in = false) {
        $ary = self::diff($new, $old, true, $boundary);
        $out = $plus = '';
        $add = $sub = $j = $last = 0;
        $rest = $ary && is_int($last = $ary[0][0]) ? array_shift($ary) : false;
        $gt = $last > 999;
        $len = $gt ? 4 : 3;
        $in or $in = fn($l, $n, $s, $x = '+') => sprintf("$x%{$len}s|%{$len}s %s\n", $l, $n, $s);
        if ($ary || $rest) {
            foreach ($ary as $v) {
                [$x, $n, $new, $l, $old] = $v;
                if ('+' == $x || '*' == $x) {
                    $add++;
                    $plus .= $in($j = '', $n, $new);
                }
                if ('+' != $x) {
                    if ($eq = '=' == $x) {
                        if ($j && ++$j != $n && !$plus)
                            $out .= ' =======' . ($gt ? '==' : '') . "\n";
                        [$out, $plus, $j] = [$out . $plus, '', $n];
                    } else {
                        $sub++;
                    }
                    $out .= $in($l, $eq ? $n : '', $old, '=' == $x ? ' ' : '-');
                }
            }
            $out .= $plus;
            if ($rest) {
                $cnt = count($ary = $rest[1] ?: $rest[2]);
                ($plus = (bool)$rest[1]) ? ($add += $cnt) : ($sub += $cnt);
                foreach ($ary as $i => $v)
                    $out .= $plus ? $in('', $last + $i, $v) : $in($last + $i, '', $v, '-');
            }
        }
        return [$out, $add, $sub];
    }

    static function diff(string $new, string $old, $mode = false, int $boundary = 3) {
        $new = explode("\n", str_replace(["\r\n", "\r"], "\n", $new));
        $old = explode("\n", str_replace(["\r\n", "\r"], "\n", $old));
        $diff = $eq = [];
        for ($rN = '', $n = $l = $rest = 0; $new && $old; $rN .= $chr) {
            if (($sn = $new[0]) == ($sl = $old[0])) {
                $ary = [$chr = '=', ++$n, $sn, ++$l, $sl];
                $rest++ ? ($diff[] = $ary) : ($eq[] = $ary);
                $rest < 1 or $rest = 0;
            } else {
                $diff = array_merge($diff, array_slice($eq, $rest = -$boundary));
                $eq = [];
                $fl = array_keys($new, $sl);
                if (!($fn = array_keys($old, $sn)) && !$fl) {
                    $diff[] = [$chr = '*', ++$n, $sn, ++$l, $sl];
                } elseif (!$fn) {
                    $diff[] = [$chr = '+', ++$n, $sn, 0, ''];
                } elseif (!$fl) {
                    $diff[] = [$chr = '.', 0, '', ++$l, $sl];
                } else { # both found
                    for ($in = 1, $tn = $fn[0]; ($new[$in] ?? 0) === ($old[$tn + $in] ?? 1); $in++);
                    for ($il = 1, $tl = $fl[0]; ($old[$il] ?? 0) === ($new[$tl + $il] ?? 1); $il++);
                    $diff[] = $in / ++$tn / count($fn) < $il / ++$tl / count($fl)
                        ? [$chr = '+', ++$n, $sn, 0, '']
                        : [$chr = '.', 0, '', ++$l, $sl];
                }
            }
            '.' == $chr or array_shift($new);
            '+' == $chr or array_shift($old);
        }
        if (!$mode)
            return $rN . str_pad('', count($new ?: $old), $new ? '+' : '.');
        return $new || $old ? array_merge([[$new ? ++$n : ++$l, $new, $old]], $diff) : $diff;
    }
}
