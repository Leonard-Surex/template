<?php
    namespace Template\MemoryManager;

    use Template\MemoryManager\IMemoryManager;
    use Template\Operations\Operation;

    class MemoryManager implements IMemoryManager
    {
        public array $blocks = [];

        private array $operations = [];

        private int $ip = 0;

        private array $variables;

        private array $code;

        private ?IMemoryManager $parent;

        private array $stack;

        public function __construct(array $code, array &$variables=[], ?IMemoryManager $memoryManager=null)
        {
            $this->code = $code;
            $this->variables = $variables;
            $this->parent = $memoryManager;
        }

        public function debug()
        {
            $previous = $this->getToken(-1);
            $token = $this->getToken();
            $next = $this->getToken(1);
            echo "IP: {$this->ip}, Token: {$token}, Previous: {$previous}, Next: {$next}<br/>";
        }

        public function getVariable(string $name, mixed $parent=null): mixed
        {            
            if (strlen($name) <= 0) {
                return $parent;
            }

            $leftPos = strpos($name, '.');
            if ($leftPos === false) {
                $left = $name;
            } else {
                $left = substr($name, 0, $leftPos);
                $children = substr($name, $leftPos + 1);
            }        

            $arrPos = strpos($left, '['); 
            $arrEndPos = strpos($left, ']');

            if ($arrPos === false && $arrEndPos === false) {                
                if ($parent === null) {
                    if (isset($this->variables[$left])) {
                        $instance = $this->variables[$left];
                    } else {
                        return $this->parentOrNull($name);
                    }
                } else {
                    $instance = $parent[$left];
                }
            } else {
                $farLeft = substr($left, 0, $arrPos);
                $index = substr($left, $arrPos+1, $arrEndPos - $arrPos - 1);
                $farRight = substr($left, $arrEndPos+1);
    
                if ($parent === null) {
                    $instance = $this->getVariable($farRight, array_values($this->getVariableOrParent($farLeft))[$index]);
                } else {
                    if (strlen($farLeft) <= 0) {
                        $instance = $parent[$index];
                    } else {
                        $instance = $this->getVariable($farRight, $parent);
                    }
                }
            }

            if (isset($children)) {
                return $this->getVariable($children, $instance);
            } else {
                return  $instance;
            }
        }

        public function getVariableOrParent($name): mixed {
            if (isset($this->variables[$name])) {
                return $this->variables[$name];
            } else if ($this->parent !== null) {
                return $this->parent->getVariableOrParent($name);
            } else {
                return null;
            }
        }

        public function getToken(int $plus=0): ?string
        {       
            return isset($this->code[$this->ip + $plus]) ? $this->code[$this->ip + $plus] : null;
        }

        public function progress(int $amount=1): self
        {
            $this->ip += $amount;

            return $this;
        }

        public function getIp(): int
        {
            return $this->ip;
        }

        public function setIp(int $ip): self
        {
            $this->ip = $ip;
            return $this;
        }

        public function done(): bool
        {
            return $this->ip >= count($this->code);
        }

        public function addOperation(Operation $operation): self
        {
            array_push($this->operations, $operation);

            return $this;
        }

        function getBlocks(): array
        {
            return $this->blocks;
        }

        public function getOperations(): array
        {
            return $this->operations;
        }

        public function push(mixed $item): self
        {
            array_push($this->stack, $item);

            return $this;
        }

        public function pop(mixed $item): mixed
        {
            return array_pop($this->stack);
        }

        private function parentOrNull(string $name): mixed {
            if ($this->parent === null) {
                return null;
            }
            return $this->parent->getVariable($name);
        }
    }