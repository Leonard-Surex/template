<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\Processor\IProcessor;

    abstract class Operation
    {
        public abstract function process(IMemoryManager $memoryManager, IProcessor $processor): mixed;

        protected function getValue(IMemoryManager $memoryManager): mixed
        {
            $type = null;        
            $ip = $memoryManager->getIp();
            $token = $memoryManager->getToken();
            if (strlen($token) <= 0) {
                return null;
            }

            // String Literal
            if (substr($token, 0, 1) === '"' && substr($token, -1) === '"') {
                $type = "string";
                $value = substr($token, 1, strlen($token) - 2);
            } else {
                // Variable
                $value = $memoryManager->getVariable($token);
                if ($value !== null) {
                    if (is_numeric($value)) {
                        $type = "number";
                    } else if (is_string($value)) {
                        $type = "number";
                    } else if (is_array($value)) {
                        $type = "array";
                    } else {
                        $type = "unknown";
                    }
                }
            }

            if ($type === null) {
                $memoryManager->setIp($ip);
                return null;
            }

            if ($memoryManager->getToken(1) === '+') {
                $memoryManager->progress(2);
                $value .= $this->getValue($memoryManager);
            } 

            return $value;
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