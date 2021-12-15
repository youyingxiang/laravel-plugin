<?php

namespace Yxx\LaravelPlugin\Support;

class Stub
{
    /**
     * The stub path.
     *
     * @var string
     */
    protected string $path;

    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static ?string $basePath = null;

    /**
     * The replacements array.
     *
     * @var array
     */
    protected array $replaces = [];

    /**
     * The constructor.
     *
     * @param  string  $path
     * @param  array  $replaces
     */
    public function __construct(string $path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    /**
     * Create new self instance.
     *
     * @param  string  $path
     * @param  array  $replaces
     * @return self
     */
    public static function create(string $path, array $replaces = []): Stub
    {
        return new static($path, $replaces);
    }

    /**
     * Set stub path.
     *
     * @param  string  $path
     * @return self
     */
    public function setPath(string $path): Stub
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get stub path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return static::getBasePath().$this->path;
    }

    /**
     * Set base path.
     *
     * @param  string  $path
     */
    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    /**
     * Get base path.
     *
     * @return string|null
     */
    public static function getBasePath(): ?string
    {
        return static::$basePath;
    }

    /**
     * Get stub contents.
     *
     * @return mixed|string
     */
    public function getContents(): string
    {
        $contents = file_get_contents($this->getPath());

        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$'.strtoupper($search).'$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get stub contents.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getContents();
    }

    /**
     * Save stub to specific path.
     *
     * @param  string  $path
     * @param  string  $filename
     * @return bool
     */
    public function saveTo(string $path, string $filename)
    {
        return file_put_contents($path.'/'.$filename, $this->getContents());
    }

    /**
     * Set replacements array.
     *
     * @param  array  $replaces
     * @return $this
     */
    public function replace(array $replaces = []): Stub
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * Get replacements.
     *
     * @return array
     */
    public function getReplaces(): array
    {
        return $this->replaces;
    }

    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
