<?php
    namespace Template\MemoryManager;

    use Template\Operations\Operation;

    interface IMemoryManager
    {
        function getVariable(string $name): mixed;

        function getVariableOrParent($name): mixed;

        function getToken(int $plus=0): ?string;

        function progress(int $amount=1): self;

        function getIp(): int;

        function setIp(int $ip): self;

        function done(): bool;

        function addOperation(Operation $operation): self;

        function getOperations(): array;

        function getBlocks(): array;

        function push(mixed $item): self;

        function pop(mixed $item): mixed;
    }