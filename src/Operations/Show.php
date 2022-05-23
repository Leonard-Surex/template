<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\Operations\Operation;
    use Template\Processor\IProcessor;

    class Show extends Operation
    {
        public function process(IMemoryManager $memoryManager, IProcessor $processor): mixed
        {
            $token = $memoryManager->getToken();
            if ($token !== 'show') {
                return null;
            }

            $memoryManager->progress();
            $label = $this->getValue($memoryManager) !== null ? $this->getValue($memoryManager) : $memoryManager->getToken();
            $memoryManager->progress();
        
            return isset($memoryManager->blocks[$label]) ? $memoryManager->blocks[$label] : "";
        }
    }