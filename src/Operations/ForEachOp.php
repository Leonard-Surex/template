<?php
    namespace Template\Operations;

    use Template\MemoryManager\IMemoryManager;
    use Template\MemoryManager\MemoryManager;
    use Template\Operations\Operation;
    use Template\Processor\IProcessor;
    use Template\Processor\Processor;

    class ForEachOp extends Operation
    {
        public function process(IMemoryManager $memoryManager, IProcessor $processor): mixed
        {
            $token = $memoryManager->getToken();
            if ($token !== 'foreach') {
                return null;
            }                                   
             
            $data = $memoryManager->getVariable($token);

            $memoryManager->progress();

            $state = $memoryManager->getToken(1);
            $altState = $memoryManager->getToken(3);

            if ($state === '=>' && $altState === 'in') {
                $itemName = $memoryManager->getToken(2);
                $indexName = $memoryManager->getToken();
                $memoryManager->progress(4);                        
            } else if ($state === 'in') {
                $itemName = $memoryManager->getToken();
                $memoryManager->progress(2);
            } else {
                return null;
            }

            $arrayName = $memoryManager->getToken();
            $array = $processor->next();

            if (!is_array($array)) {
                return null;
            }

            $block = $this->consumeBlock("foreach", $memoryManager);            
            if ($block === null) {
                return null;
            }                                        

            $result = "";
            foreach ($array as $index => $item)
            {
                $variables = [$itemName => $item];
                if (isset($indexName)) {
                    $variables[$indexName] = $index;
                }
                $childMemoryManager = new MemoryManager($block, $variables, $memoryManager);
                $childProcessor = new Processor($childMemoryManager, $processor->getFileManager(), $processor->getConfig());
                
                $result .= $childProcessor->run();
            }

            return $result;
        }
    }