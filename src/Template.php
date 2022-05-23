<?php
    namespace Template;

    use Template\FileManager\FileManager;
    use Template\FileManager\IFileManager;
    use Template\MemoryManager\MemoryManager;
    use Template\MemoryManager\IMemoryManager;
    use Template\Parser\LineParser;
    use Template\Parser\TokenParser;
    use Template\Processor\Processor;
    use Template\Processor\IProcessor;

    class Template
    {
        public array $config;

        private IMemoryManager $memoryManager;

        private IProcessor $processor;

        private IFileManager $fileManager;

        private array $data;

        public function __construct(array $config=null) {
            if ($config !== null) {
                $this->config = $config;
            } else {
                $this->config = self::defaultConfig();
            }
        }

        public function __call(string $method, array $params): mixed
        {            
            if ($method === "render") {
                $call = "_irender";
            }
            if ($method === "renderFrom") {
                $call = "_irenderFrom";
            }

            if (count($params) === 3) {
                return $this->$call($params[0], $params[1], $params[2]);
            } else if (count($params) === 2) {
                return $this->$call($params[0], $params[1], $this->config);
            } else if (count($params) === 1) {
                $variables = [];
                return $this->call($params[0], $variables, $this->config);
            }             

            return null;
        }

        public function block($label): ?string
        {
            $blocks = $this->memoryManager->getBlocks();
            if (isset($blocks[$label])) {
                return $blocks[$label];
            }
            return null;
        }

        public static function __callStatic(string $method, array $params): mixed
        {            
            if ($method === "render") {
                $call = "_render";
            }
            if ($method === "renderFrom") {
                $call = "_renderFrom";
            }

            if (count($params) === 3) {
                return self::$call($params[0], $params[1], $params[2]);
            } else if (count($params) === 2) {
                return self::$call($params[0], $params[1]);
            } else if (count($params) === 1) {
                $variables = [];
                return self::$call($params[0], $variables);
            }

            return null;
        }

        public static function _render(string $script, $params=[], array $config = []): string
        {
            $allConfig = $config + self::defaultConfig();
            
            $path = '\\';
            if (isset($allConfig['template_path'])) {
                $path = $allConfig['template_path'];
            }

            if (!isset($allConfig['file_manager'])) {
                $fileManager = new FileManager($path);
                $allConfig['file_manager'] = $fileManager;
            } else {
                $fileManager = $allConfig['file_manager'];
            }

            $lines = LineParser::parse($script);
            $code = [];
            foreach ($lines as $line)
            {                
                $code = array_merge($code, TokenParser::parse($line));
            }

            $memoryManager = new MemoryManager($code, $params);
            $processor = new Processor($memoryManager, $fileManager, $allConfig);
            
            $allConfig['memoryManager'] = $memoryManager;
            $allConfig['processor'] = $processor;

            return $processor->run();
        }

        public function _irender(string $script, $params=[], array $config = []): string
        {
            $allConfig = $config + self::defaultConfig();
            
            $path = '\\';
            if (isset($allConfig['template_path'])) {
                $path = $allConfig['template_path'];
            }

            if (!isset($allConfig['file_manager'])) {
                $this->fileManager = new FileManager($path);
                $allConfig['file_manager'] = $this->fileManager;                
            } else {
                $this->fileManager = $allConfig['file_manager'];
            }

            $lines = LineParser::parse($script);
            $code = [];
            foreach ($lines as $line)
            {                
                $code = array_merge($code, TokenParser::parse($line));
            }

            $this->memoryManager = new MemoryManager($code, $params);
            $this->processor = new Processor($this->memoryManager, $this->fileManager, $allConfig);
            
            $allConfig['memoryManager'] = $this->memoryManager;
            $allConfig['processor'] = $this->processor;            

            return $this->processor->run();
        }

        public static function _renderFrom(string $template, array $params=[], array $config = []): string
        {
            $allConfig = $config + self::defaultConfig();
            
            $path = '\\';
            if (isset($allConfig['template_path'])) {
                $path = $allConfig['template_path'];
            }

            if (!isset($allConfig['file_manager'])) {
                $fileManager = new FileManager($path);
                $allConfig['file_manager'] = $fileManager;
            } else {
                $fileManager = $allConfig['file_manager'];
            }

            $templateData = $fileManager->read($template);
            
            return self::render($templateData, $params, $allConfig);
        }

        public function _irenderFrom(string $template, array $params=[], array $config = []): string
        {
            $allConfig = $config + self::defaultConfig();
            
            $path = '\\';
            if (isset($allConfig['template_path'])) {
                $path = $allConfig['template_path'];
            }

            if (!isset($allConfig['file_manager'])) {
                $fileManager = new FileManager($path);
                $allConfig['file_manager'] = $fileManager;
            } else {
                $fileManager = $allConfig['file_manager'];
            }

            $templateData = $fileManager->read($template);
            
            return $this->_irender($templateData, $params, $allConfig);
        }

        private static function defaultConfig(): array
        {
            return [
                'template_path' => '\\',
                'sandbox' => false,                
            ];
        }
    }