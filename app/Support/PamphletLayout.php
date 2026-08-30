<?php

namespace App\Support;

use Illuminate\Validation\Validator;

class PamphletLayout
{
    /**
     * @return array{
     *     heading: array{x: float, y: float, w: float, fontSize: float},
     *     name: array{x: float, y: float, w: float, fontSize: float},
     *     dates: array{x: float, y: float, w: float, fontSize: float},
     *     tribute: array{x: float, y: float, w: float, fontSize: float},
     *     photo: array{x: float, y: float, w: float, h: float}
     * }
     */
    public static function defaults(): array
    {
        return [
            'heading' => ['x' => 8.0, 'y' => 5.0, 'w' => 84.0, 'fontSize' => 7.0],
            'name' => ['x' => 8.0, 'y' => 54.0, 'w' => 84.0, 'fontSize' => 5.0],
            'dates' => ['x' => 8.0, 'y' => 62.0, 'w' => 84.0, 'fontSize' => 3.2],
            'tribute' => ['x' => 10.0, 'y' => 68.0, 'w' => 80.0, 'fontSize' => 3.0],
            'photo' => ['x' => 28.0, 'y' => 22.0, 'w' => 44.0, 'h' => 28.0],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $layout
     * @return array{
     *     heading: array{x: float, y: float, w: float, fontSize: float},
     *     name: array{x: float, y: float, w: float, fontSize: float},
     *     dates: array{x: float, y: float, w: float, fontSize: float},
     *     tribute: array{x: float, y: float, w: float, fontSize: float},
     *     photo: array{x: float, y: float, w: float, h: float}
     * }
     */
    public static function normalize(?array $layout): array
    {
        $defaults = self::defaults();

        if ($layout === null) {
            return $defaults;
        }

        $normalized = $defaults;

        foreach (['heading', 'name', 'dates', 'tribute'] as $key) {
            if (! isset($layout[$key]) || ! is_array($layout[$key])) {
                continue;
            }

            $normalized[$key] = [
                'x' => self::clamp((float) ($layout[$key]['x'] ?? $defaults[$key]['x']), 0, 95),
                'y' => self::clamp((float) ($layout[$key]['y'] ?? $defaults[$key]['y']), 0, 95),
                'w' => self::clamp((float) ($layout[$key]['w'] ?? $defaults[$key]['w']), 10, 100),
                'fontSize' => self::clamp((float) ($layout[$key]['fontSize'] ?? $defaults[$key]['fontSize']), 1.5, 14),
            ];
        }

        if (isset($layout['photo']) && is_array($layout['photo'])) {
            $normalized['photo'] = [
                'x' => self::clamp((float) ($layout['photo']['x'] ?? $defaults['photo']['x']), 0, 95),
                'y' => self::clamp((float) ($layout['photo']['y'] ?? $defaults['photo']['y']), 0, 95),
                'w' => self::clamp((float) ($layout['photo']['w'] ?? $defaults['photo']['w']), 10, 90),
                'h' => self::clamp((float) ($layout['photo']['h'] ?? $defaults['photo']['h']), 10, 90),
            ];
        }

        return $normalized;
    }

    /**
     * Decode a JSON layout string or array from form input.
     *
     * @return array<string, mixed>|null
     */
    public static function decode(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Validate layout structure after basic request rules run.
     */
    public static function validate(Validator $validator, mixed $rawLayout): void
    {
        if ($rawLayout === null || $rawLayout === '') {
            return;
        }

        $layout = self::decode($rawLayout);

        if ($layout === null) {
            $validator->errors()->add('layout', 'The layout must be valid JSON.');

            return;
        }

        $allowedKeys = ['heading', 'name', 'dates', 'tribute', 'photo'];
        $unknown = array_diff(array_keys($layout), $allowedKeys);

        if ($unknown !== []) {
            $validator->errors()->add('layout', 'The layout contains unknown keys.');

            return;
        }

        foreach (['heading', 'name', 'dates', 'tribute'] as $key) {
            if (! array_key_exists($key, $layout)) {
                continue;
            }

            if (! is_array($layout[$key])) {
                $validator->errors()->add('layout', "The {$key} layout block is invalid.");

                continue;
            }

            foreach (['x', 'y', 'w', 'fontSize'] as $field) {
                if (! array_key_exists($field, $layout[$key]) || ! is_numeric($layout[$key][$field])) {
                    $validator->errors()->add('layout', "The {$key}.{$field} value is invalid.");

                    continue;
                }

                $value = (float) $layout[$key][$field];

                if ($field === 'fontSize') {
                    if ($value < 1.5 || $value > 14) {
                        $validator->errors()->add('layout', "The {$key}.fontSize value is out of range.");
                    }

                    continue;
                }

                if ($value < 0 || $value > 100) {
                    $validator->errors()->add('layout', "The {$key}.{$field} value is out of range.");
                }
            }
        }

        if (! array_key_exists('photo', $layout)) {
            return;
        }

        if (! is_array($layout['photo'])) {
            $validator->errors()->add('layout', 'The photo layout block is invalid.');

            return;
        }

        foreach (['x', 'y', 'w', 'h'] as $field) {
            if (! array_key_exists($field, $layout['photo']) || ! is_numeric($layout['photo'][$field])) {
                $validator->errors()->add('layout', "The photo.{$field} value is invalid.");

                continue;
            }

            $value = (float) $layout['photo'][$field];

            if ($value < 0 || $value > 100) {
                $validator->errors()->add('layout', "The photo.{$field} value is out of range.");
            }
        }
    }

    private static function clamp(float $value, float $min, float $max): float
    {
        return round(max($min, min($max, $value)), 2);
    }
}
