<?php

namespace Softonic\GraphQL\Mutation;

use Softonic\GraphQL\Mutation\Traits\MutationObjectHandler;

class FilteredCollection implements MutationObject, \JsonSerializable
{
    use MutationObjectHandler;

    /**
     * @var array<Item>
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    private $hasChanged = false;

    public function __construct(array $arguments = [], array $config = [], bool $hasChanged = false)
    {
        $this->arguments  = $arguments;
        $this->config     = $config;
        $this->hasChanged = $hasChanged;
    }

    public function __get(string $key): Collection
    {
        $items = [];
        foreach ($this->arguments as $argument) {
            $items[] = $argument->{$key};
        }

        return new Collection($items, $this->config[$key]->children);
    }

    public function set(array $data): void
    {
        foreach ($this->arguments as $argument) {
            $argument->set($data);
        }
    }

    public function filter(array $filters): FilteredCollection
    {
        $filteredData = [];
        if ($this->areAllArgumentsCollections()) {
            foreach ($this->arguments as $argument) {
                $filteredItems = $this->filterItems($argument->arguments, $filters);

                $filteredData[] = new FilteredCollection($filteredItems, $this->config);
            }
        } else {
            $filteredItems = $this->filterItems($this->arguments, $filters);

            $filteredData = $filteredItems;
        }

        return new FilteredCollection($filteredData, $this->config);
    }

    public function jsonSerialize(): array
    {
        $items = [];
        foreach ($this->arguments as $item) {
            if ($item->hasChanged()) {
                $items[] = $item->jsonSerialize();
            }
        }

        return $items;
    }

    private function areAllArgumentsCollections(): bool
    {
        return (!empty($this->arguments[0]) && $this->arguments[0] instanceof Collection);
    }

    private function filterItems(array $arguments, array $filters): array
    {
        return array_filter($arguments, function ($item) use ($filters) {
            foreach ($filters as $filterKey => $filterValue) {
                if (!($item->{$filterKey} == $filterValue)) {
                    return false;
                }
            }

            return true;
        });
    }
}