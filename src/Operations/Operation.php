<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\MemoryManager\MemoryManager;
    use Template\Parser\TokenParser;
    use Template\Processor\IProcessor;
    use Template\Processor\Processor;

    abstract class Operation
    {
        public abstract function process(IMemoryManager $memoryManager, IProcessor $processor): mixed;

        protected function getValue(IMemoryManager $memoryManager, IProcessor $processor, bool $singleToken = false): mixed
        {
            $type = null;        
            $ip = $memoryManager->getIp();
            $token = $memoryManager->getToken();
            if (strlen($token) <= 0) {
                return null;
            }

            $one = $memoryManager->getToken(1);
            if ($one == "==" && !$singleToken) {
                // Boolean, is equal to
                $leftValue = $this->getValue($memoryManager, $processor, true);
                $memoryManager->progress(2);
                $rightValue = $this->getValue($memoryManager, $processor);
                $type = "bool";
                if ($leftValue == $rightValue) {
                    $value = true;
                } else {
                    $value = false;
                }
            } else if ($one == "+" && !$singleToken) {
                // Concat values 
                $leftValue = $this->getValue($memoryManager, $processor, true);
                $memoryManager->progress(2);
                $rightValue = $this->getValue($memoryManager, $processor);
                $type = "string";
                $value = $leftValue . "" . $rightValue;
            } else if (substr($token, 0, 1) === '"' && substr($token, -1) === '"') {
                // String Literal
                $type = "string";
                $value = substr($token, 1, strlen($token) - 2);
            }
            else if (substr($token, 0, 1) === '(' && substr($token, -1) === ')') {
                // Brackets
                $value = substr($token, 1, strlen($token) - 2);
                $tokens = TokenParser::parse($value);
                $variables = [];
                $childMemoryManager = new MemoryManager($tokens, $variables, $memoryManager);
                $childProcessor = new Processor($childMemoryManager, $processor->getFileManager(), $processor->getConfig());
                $value = $childProcessor->run(); 
                $type = $this->getType($value);
            } else if (is_numeric($token)) {                
                // Numeric constant
                $type = "number";
                $value = intval($token);
            } else {
                // Variable
                $value = $memoryManager->getVariable($token);
                $type = $this->getType($value);
            }

            if ($type === null) {
                $memoryManager->setIp($ip);
                return null;
            }

            return $value;
        }

        protected function getType(mixed $value): ?string
        {
            $type = null;
            if ($value !== null) {
                if (is_numeric($value)) {
                    $type = "number";
                } else if (is_bool($value)) {
                    $type = "bool";
                } else if (is_string($value)) {
                    $type = "number";
                } else if (is_array($value)) {
                    $type = "array";
                } else {
                    $type = "unknown";
                }
            }
            return $type;
        }

        protected function consumeBlock(string $tag, IMemoryManager $memoryManager): ?array
        {   
            $count = 0;
            $depth = 1;
            $result = [];

            do
            {
                $token = $memoryManager->getToken($count);

                if ($token === 'end') {
                    $end = $memoryManager->getToken($count + 1);
                    if ($end === $tag) {                        
                        $count += 2;
                        $depth--;
                        if ($depth !== 0) {
                            $result[] = $token;
                            $result[] = $end;
                        }
                    } else {
                        $result[] = $token;
                        $count++;        
                    }
                } else if ($token === $tag) {
                    $depth++;
                    $result[] = $token;
                    $count++;    
                } else {
                    $result[] = $token;
                    $count++;    
                }
            } while ($depth > 0 && $token !== null);

            if ($token !== null) {
                $memoryManager->progress($count);
                return $result;
            }

            return null;
        }
    }