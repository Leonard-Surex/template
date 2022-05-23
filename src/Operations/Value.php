<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\Operations\Operation;
    use Template\Processor\IProcessor;

    class Value extends Operation
    {
        public function process(IMemoryManager $memoryManager, IProcessor $processor): mixed
        {
            $data = $this->getValue($memoryManager);

            if ($data === null) {
                return null;
            }            
            
            $memoryManager->progress();

            return $data;
        }
    }