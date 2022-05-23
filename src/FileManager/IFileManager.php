<?php
    namespace Template\FileManager;

    interface IFileManager
    {
        function read(string $filename): ?string;
    }