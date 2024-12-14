<?php

class Diff
{
    static function diffx(string $new, string $old, $boundary = 3, $in = false) {
        $ary = self::diff($new, $old, true, $boundary);
        $out = $plus = '';
        $add = $sub = $j = $last = 0;
        $rest = $ary && is_int($last = $ary[0][0]) ? array_shift($ary) : false;
        $len = $last > 999 ? 4 : 3;
        $break = $last > 999 ? " =========\n" : " =======\n";
        $in or $in = fn($x, $l, $n, $s) => ('=' == $x ? ' ' : ('+' == $x ? '+' : '-'))
            . sprintf("%{$len}s|%{$len}s %s\n", $l, $n, $s);
        if ($ary || $rest) {
            foreach ($ary as $v) {
                [$xn, $n, $new, $l, $old] = $v;
                if ('+' == $xn || '*' == $xn) {
                    $add++;
                    $plus .= $in('+', $j = '', $n, $new);
                }
                if ('+' != $xn) {
                    if ($eq = '=' == $xn) {
                        if ($j && ++$j != $n && !$plus)
                            $out .= $break;
                        [$out, $plus, $j] = [$out . $plus, '', $n];
                    } else {
                        $sub++;
                    }
                    $out .= $in($xn, $l, $eq ? $n : '', $old);
                }
            }
            $out .= $plus;
            if ($rest) {
                $cnt = count($ary = $rest[1] ?: $rest[2]);
                ($plus = (bool)$rest[1]) ? ($add += $cnt) : ($sub += $cnt);
                foreach ($ary as $i => $v)
                    $out .= $plus ? $in('+', '', $last + $i, $v) : $in('-', $last + $i, '', $v);
            }
        }
        return [$out, $add, $sub];
    }

    static function diff(string $new, string $old, $mode = false, int $boundary = 3) {
        $N = count($new = explode("\n", unl($new)));
        $L = count($old = explode("\n", unl($old)));
        $diff = $eq = [];
        for ($rN = '', $n = $l = $rest = 0; $n < $N && $l < $L; ) {
            $sn = pos($new);
            $sl = pos($old);
            if ($sn === $sl) {
                if ($rest) {
                    $rest--;
                    $diff[] = ['=', ++$n, $sn, ++$l, $sl];
                } else {
                    $eq[] = ['=', ++$n, $sn, ++$l, $sl];
                }
                $rN .= '=';
                array_shift($new);
                array_shift($old);
            } else {
                if ($eq)
                    $diff = array_merge($diff, array_slice($eq, -$boundary));
                $eq = [];
                $rest = $boundary;
                $fn = array_keys($old, $sn);
                $fl = array_keys($new, $sl);
                if (!$fn && !$fl) {
                    $rN .= '*';
                    $diff[] = ['*', ++$n, $sn, ++$l, $sl];
                    array_shift($new);
                    array_shift($old);
                } elseif (!$fn) {
                    $rN .= '+';
                    $diff[] = ['+', ++$n, $sn, 0, ''];
                    array_shift($new);
                } elseif (!$fl) {
                    $rN .= '.';
                    $diff[] = ['.', 0, '', ++$l, $sl];
                    array_shift($old);
                } else { # both found
                    $tn = pos($fn);
                    $tl = pos($fl);
                    for ($in = 1; ($new[$in] ?? 0) === ($old[$tn + $in] ?? 1); $in++);
                    for ($il = 1; ($old[$il] ?? 0) === ($new[$tl + $il] ?? 1); $il++);
                    if ($in / ++$tn / count($fn) < $il / ++$tl / count($fl)) {
                        $rN .= '+';
                        $diff[] = ['+', ++$n, $sn, 0, ''];
                        array_shift($new);
                    } else {
                        $rN .= '.';
                        $diff[] = ['.', 0, '', ++$l, $sl];
                        array_shift($old);
                    }
                }
            }
        }
        $rest = max($N -= $n, $L -= $l);
        if (!$mode)
            return $rN . str_pad('', $rest, $N > $L ? '+' : '.');
        return $rest ? array_merge([[$N > $L ? ++$n : ++$l, $new, $old]], $diff) : $diff;
    }
}
