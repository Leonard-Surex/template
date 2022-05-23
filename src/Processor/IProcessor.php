<?php
    namespace Template\Processor;

    use Template\FileManager\IFileManager;
    use Template\MemoryManager\IMemoryManager;

    interface IProcessor
    {
        function next(): mixed;

        function run(): ?string;

        function getConfig(): array;

        function getMemoryManager() : IMemoryManager;

        function getFileManager() : IFileManager;
    }