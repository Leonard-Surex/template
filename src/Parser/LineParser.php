<?php
    namespace Template\Parser;

    class LineParser
    {
        public static function parse(string $code): array
        {
            $leftPos = strpos($code, '{{');
            $rightPos = strpos($code, '}}');

            if ($leftPos === false || !$rightPos) {
                return ['"' . $code . '"'];
            }

            $left = '"' . substr($code, 0, $leftPos) . '"';
            $middle = substr($code, $leftPos+2, $rightPos-$leftPos-2);
            $right = substr($code, $rightPos+2);

            return array_merge([$left, $middle], self::parse($right));
        }
    }