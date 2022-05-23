<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\MemoryManager\MemoryManager;
    use Template\Operations\Operation;
    use Template\Processor\IProcessor;
    use Template\Processor\Processor;

    class Block extends Operation
    {
        public function process(IMemoryManager $memoryManager, IProcessor $processor): mixed
        {
            $token = $memoryManager->getToken();
            if ($token !== 'block') {
                return null;
            }                                   
             
            $memoryManager->progress();
            $label = $this->getValue($memoryManager) !== null ? $this->getValue($memoryManager) : $memoryManager->getToken();
            $memoryManager->progress();

            $block = $this->consumeBlock("block", $memoryManager);            
            if ($block === null) {
                return null;
            }                                        

            $variables = [];
            $childMemoryManager = new MemoryManager($block, $variables, $memoryManager);
            $childProcessor = new Processor($childMemoryManager, $processor->getFileManager(), $processor->getConfig());
            
            $result = $childProcessor->run();

            if (!isset($memoryManager->blocks[$label])) {
                $memoryManager->blocks[$label] = "";
            }
            $memoryManager->blocks[$label] .= $result;

            return "";
        }
    }