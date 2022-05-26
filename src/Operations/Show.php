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
            $value = $this->getValue($memoryManager, $processor);
            $label = $value !== null ? $value : $memoryManager->getToken();
            $memoryManager->progress();
        
            return isset($memoryManager->blocks[$label]) ? $memoryManager->blocks[$label] : "";
        }
    }