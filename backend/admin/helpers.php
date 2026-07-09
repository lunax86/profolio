<?php

declare(strict_types=1);

/*
 * Pohledové helpery administrace. Naincludováno v admin/index.php před renderem.
 * Cíl: uniformní karty/pole/ikony místo opakovaného markupu ve views.
 */

if (!function_exists('escape')) {
    /** Escape do HTML (nahrazuje opakovanou closure $escape ve views). */
    function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('icon')) {
    /** Inline SVG ikona ze sprite (layout/sprite.php). */
    function icon(string $name, string $classes = 'ic'): string
    {
        return '<svg class="' . escape($classes) . '" aria-hidden="true"><use href="#i-' . escape($name) . '"></use></svg>';
    }
}

if (!function_exists('field')) {
    /**
     * Textové pole (label + input) v jednotném obalu .field.
     *
     * @param array{
     *     type?: string, value?: string, placeholder?: string, sub?: string,
     *     required?: bool, maxlength?: int, minlength?: int, autocomplete?: string,
     *     counter?: bool, id?: string
     * } $options
     */
    function field(string $label, string $name, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $id = $options['id'] ?? 'f-' . $name;
        $attributes = ['type="' . escape($type) . '"', 'name="' . escape($name) . '"', 'id="' . escape($id) . '"'];
        if ($type !== 'file' && $type !== 'password') {
            $attributes[] = 'value="' . escape($options['value'] ?? '') . '"';
        }
        if (($options['placeholder'] ?? '') !== '') {
            $attributes[] = 'placeholder="' . escape($options['placeholder']) . '"';
        }
        if (!empty($options['required'])) {
            $attributes[] = 'required';
        }
        if (isset($options['maxlength'])) {
            $attributes[] = 'maxlength="' . (int) $options['maxlength'] . '"';
        }
        if (isset($options['minlength'])) {
            $attributes[] = 'minlength="' . (int) $options['minlength'] . '"';
        }
        if (isset($options['autocomplete'])) {
            $attributes[] = 'autocomplete="' . escape($options['autocomplete']) . '"';
        }

        $sub = ($options['sub'] ?? '') !== '' ? ' <span class="sub">' . escape($options['sub']) . '</span>' : '';
        $counter = !empty($options['counter']) && isset($options['maxlength'])
            ? '<div class="counter" data-counter="#' . escape($id) . '"></div>'
            : '';

        return '<div class="field">'
            . '<label for="' . escape($id) . '">' . escape($label) . $sub . '</label>'
            . '<input ' . implode(' ', $attributes) . '>'
            . $counter
            . '</div>';
    }
}

if (!function_exists('field_wrap')) {
    /** Obal .field pro netextové ovládací prvky (textarea, select, file, vlastní HTML). */
    function field_wrap(string $label, string $controlHtml, string $sub = ''): string
    {
        $labelHtml = '';
        if ($label !== '' || $sub !== '') {
            $subHtml = $sub !== '' ? ' <span class="sub">' . escape($sub) . '</span>' : '';
            $labelHtml = '<label>' . escape($label) . $subHtml . '</label>';
        }

        return '<div class="field">' . $labelHtml . $controlHtml . '</div>';
    }
}

if (!function_exists('card_open')) {
    /** Otevře kartu s volitelnou hlavičkou a začne .card-body. Uzavři přes card_close() nebo card_foot(). */
    function card_open(string $title = '', string $hint = ''): string
    {
        $head = '';
        if ($title !== '') {
            $hintHtml = $hint !== '' ? '<span class="hint">' . escape($hint) . '</span>' : '';
            $head = '<div class="card-head"><h2>' . escape($title) . '</h2>' . $hintHtml . '</div>';
        }

        return '<div class="card">' . $head . '<div class="card-body">';
    }
}

if (!function_exists('card_close')) {
    /** Uzavře .card-body a .card. */
    function card_close(): string
    {
        return '</div></div>';
    }
}

if (!function_exists('card_foot')) {
    /** Uzavře .card-body, přidá patičku karty s daným HTML a uzavře .card. */
    function card_foot(string $html): string
    {
        return '</div><div class="card-foot">' . $html . '</div></div>';
    }
}
