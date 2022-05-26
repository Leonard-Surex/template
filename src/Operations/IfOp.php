<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\MemoryManager\MemoryManager;
    use Template\Operations\Operation;
    use Template\Processor\IProcessor;
    use Template\Processor\Processor;

    class IfOp extends Operation
    {
        public function process(IMemoryManager $memoryManager, IProcessor $processor): mixed
        {
            $token = $memoryManager->getToken();
            if ($token !== 'if') {
                return null;
            }                                   

            $memoryManager->progress();

            $value = $this->getValue($memoryManager, $processor);

            $memoryManager->progress();
            $block = $this->consumeBlock("if", $memoryManager);            
            if ($block === null) {
                return null;
            }                                        

            if ($value == true) {
                $variables = [];
                $childMemoryManager = new MemoryManager($block, $variables, $memoryManager);
                $childProcessor = new Processor($childMemoryManager, $processor->getFileManager(), $processor->getConfig());            
                return $childProcessor->run();    
            }

            return "";
        }
    }