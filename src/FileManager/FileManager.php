<?php
    namespace Template\FileManager;

    use Template\FileManager\IFileManager;

    class FileManager implements IFileManager
    {
        public string $templatePath;

        public function __construct(string $path)
        {
            $this->templatePath = $path;
        }

        public function read(string $filename): ?string
        {
            $full = realpath(getcwd() . "\\" . $this->templatePath . "\\" . $filename);
            
            if (file_exists($full)) {
                $data = file_get_contents($full);                                
                $escaped = $this->escapeQuotes($data); 
                return $escaped;
            }

            return null;
        }

        private function escapeQuotes(string $text): string {
            return str_replace('"', '\\"', $text);
        }
    }