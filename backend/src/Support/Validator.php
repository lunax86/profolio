<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Jednoduchá validace vstupů. Pravidla: required, email, max:N, min:N.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $rules pole "pole" => "required|email|max:255"
     */
    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleset) {
            $value = trim((string) ($data[$field] ?? ''));

            foreach (explode('|', $ruleset) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                $failed = match ($name) {
                    'required' => $value === '',
                    'email' => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                    'max' => mb_strlen($value) > (int) $arg,
                    'min' => mb_strlen($value) < (int) $arg,
                    default => false,
                };

                if ($failed) {
                    $this->errors[$field] = $this->message($field, $name, $arg);
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    private function message(string $field, string $rule, ?string $arg): string
    {
        $label = 'Pole ' . $field;

        return match ($rule) {
            'required' => $label . ' je povinné.',
            'email' => $label . ' musí být platný e-mail.',
            'max' => $label . ' může mít nejvýše ' . $arg . ' znaků.',
            'min' => $label . ' musí mít alespoň ' . $arg . ' znaků.',
            default => $label . ' je neplatné.',
        };
    }
}
