<?php

namespace Packstub\FormBuilder\Templates;

use Illuminate\Support\Str;

/**
 * The form templates offered by "Use a template": the package's own
 * (resources/templates/*.php, each returning name, description, category
 * and a portable form) plus any directory registered with add().
 */
final class Templates
{
    /** @var array<int, string> */
    protected static array $directories = [];

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $cache = null;

    /**
     * Offer the templates of another directory (files like the built-in ones).
     */
    public static function add(string $directory): void
    {
        self::$directories[] = rtrim($directory, '/');
        self::$cache = null;
    }

    /**
     * @return array<string, array{key: string, name: string, description: string, category: string, form: array<string, mixed>}>
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $templates = [];

        foreach ([__DIR__.'/../../resources/templates', ...self::$directories] as $directory) {
            foreach (glob($directory.'/*.php') ?: [] as $file) {
                $template = include $file;

                if (! is_array($template) || ! isset($template['name'], $template['form'])) {
                    continue;
                }

                $key = $template['key'] ?? Str::slug(pathinfo($file, PATHINFO_FILENAME));
                $templates[$key] = [
                    'key' => $key,
                    'name' => (string) $template['name'],
                    'description' => (string) ($template['description'] ?? ''),
                    'category' => (string) ($template['category'] ?? 'General'),
                    'form' => $template['form'],
                ];
            }
        }

        uasort($templates, fn (array $a, array $b): int => [$a['category'], $a['name']] <=> [$b['category'], $b['name']]);

        return self::$cache = $templates;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * key => "Category · Name", for a select.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::all() as $key => $template) {
            $options[$key] = $template['category'].' · '.$template['name'];
        }

        return $options;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
