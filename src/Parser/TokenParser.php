<?php
    namespace Template\Parser;

    class TokenParser
    {
        public static function parse(string $code): array
        {
            $result = [];
            $byteAt = 0;
            $escaped = false;
            $quoteDepth = 0;
            $quoted = null;
            $codeLength = strlen($code);

            do
            {
                $token = '';
                do
                {
                    $charAt = substr($code, $byteAt, 1);
                    if ($escaped) {
                        $token .= $charAt;
                        $escaped = false;
                    } else {
                        if ($charAt == '\\') {
                            $escaped = true;
                        } else {                            
                            if ($quoted === null) {
                                if (self::openQuoteChar($charAt)) {
                                    $quoted = $charAt;
                                    $token .= $charAt;
                                    $quoteDepth = 1;
                                } else if ($charAt === ' ') {
                                } else {
                                    $token .= $charAt;
                                }
                            } else {
                                if (self::closeQuoteChar($charAt, $quoted)) {
                                    if ($quoteDepth === 1)
                                    {
                                        $quoted = null;
                                        $quoteDepth = 0;
                                    } else
                                    {
                                        $quoteDepth--;
                                    }
                                }
                                if (self::openQuoteChar($charAt))
                                {
                                    if ($charAt === $quoted)
                                    {
                                        $quoteDepth++;
                                        $token .= $charAt;
                                    } else
                                    {
                                        $token .= $charAt;
                                    }
                                }
                                else
                                {
                                    $token .= $charAt;
                                }
                            }
                        }
                    }

                    $byteAt++;
                } while ($byteAt <= $codeLength - 1 && ($quoted !== null || substr($code, $byteAt, 1) !== ' '));

                if (strlen($token) > 0) {
                    $result[] = $token;
                }
            } while ($byteAt < $codeLength);

            return $result;
        }

        private static function openQuoteChar(string $c): bool
        {
            if ($c === '"' || $c === '\'' || $c === '(')
            {
                return true;
            }
            return false;
        }

        private static function closeQuoteChar(string $c, ?string $quote): bool
        {
            return
                    (($c === '"' && $quote === '"') ? true :
                    (($c === '\'' && $quote === '\'') ? true :
                    (($c === ')' && $quote === '(') ? true :
                    false)));
        }
    }