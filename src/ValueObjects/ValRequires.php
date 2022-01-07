<?php

namespace Yxx\LaravelPlugin\ValueObjects;

use Illuminate\Support\Arr;

class ValRequires
{
    /**
     * @var ValRequire []
     */
    public array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param  ValRequire  $valRequire
     * @return $this
     */
    public function append(ValRequire $valRequire): ValRequires
    {
        $this->items[] = $valRequire;

        return $this;
    }

    /**
     * @param  callable|null  $callback
     * @return $this
     */
    public function filter(callable $callback = null): ValRequires
    {
        return new static(array_values(array_filter($this->items, $callback)));
    }

    /**
     * @return ValRequires
     */
    public function unique(): ValRequires
    {
        return new static(array_values(array_reduce($this->items, function (array $items, ValRequire $valRequire) {
            $items[$valRequire->name] = $valRequire;

            return $items;
        }, $items = [])));
    }

    /**
     * @param  ValRequires  $valRequires
     * @return $this
     */
    public function merge(ValRequires $valRequires): ValRequires
    {
        $this->items = array_merge($this->items, $valRequires->items);

        return $this;
    }

    /**
     * @param  array  $items
     * @return ValRequires
     */
    public static function make(array $items = []): ValRequires
    {
        return new static($items);
    }

    /**
     * @param  ValRequires  $valRequires
     * @return ValRequires
     */
    public function notIn(ValRequires $valRequires): ValRequires
    {
        return $this->filter(fn (ValRequire $require) => ! in_array($require->name, Arr::pluck($valRequires->toArray(), 'name')));
    }

    /**
     * @param  ValRequires  $valRequires
     * @return $this
     */
    public function In(ValRequires $valRequires): ValRequires
    {
        return $this->filter(fn (ValRequire $require) => in_array($require->name, Arr::pluck($valRequires->toArray(), 'name')));
    }

    /**
     * @return array|ValRequire[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function __toString(): string
    {
        return array_reduce($this->items, fn (string $str, ValRequire $require) => $str .= "\"{$require->name}\" ", $str = '');
    }

    /**
     * @param  array  $items
     * @return ValRequires
     */
    public static function toValRequires(array $items): ValRequires
    {
        $valRequires = ValRequires::make();

        foreach ($items as $name => $v) {
            $valRequires->append(ValRequire::make($name, $v));
        }

        return $valRequires;
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return bool
     */
    public function notEmpty(): bool
    {
        return ! $this->empty();
    }

    /**
     * @param  ValRequires  $valRequires
     * @return bool
     */
    public function equals(ValRequires $valRequires): bool
    {
        return json_encode($this->items, true) === json_encode($valRequires->items, true);
    }
}
