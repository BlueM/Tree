<?php

namespace BlueM\Helper;

class IterableObjectFactory
{
    /**
     * @param array<mixed, mixed> $data
     */
    public static function makeIterableInstance(array $data): \Iterator
    {
        return new class($data) implements \Iterator {
            /**
             * @var array<string, mixed>
             */
            private array $data;

            private int $pos = 0;

            /**
             * @var array<string>
             */
            private array $keys;

            /**
             * @param array<string, mixed> $data
             */
            public function __construct(array $data)
            {
                $this->data = $data;
                $this->keys = array_keys($data);
            }

            public function current(): mixed
            {
                return $this->data[$this->keys[$this->pos]];
            }

            public function next(): void
            {
                ++$this->pos;
            }

            public function key(): mixed
            {
                return $this->keys[$this->pos];
            }

            public function valid(): bool
            {
                return isset($this->keys[$this->pos]);
            }

            public function rewind(): void
            {
                $this->pos = 0;
            }
        };
    }
}
