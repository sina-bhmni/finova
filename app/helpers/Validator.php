<?php
/**
 * FINOVA - Personal Finance Manager
 * کلاس ساده اعتبارسنجی ورودی‌ها (Input Validation)
 */

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "$label نمی‌تواند خالی باشد.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'ایمیل'): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label معتبر نیست.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) < $min) {
            $this->errors[$field] = "$label باید حداقل $min کاراکتر باشد.";
        }
        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label باید یک عدد باشد.";
        }
        return $this;
    }

    public function min(string $field, float $min, string $label): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float)$this->data[$field] < $min) {
            $this->errors[$field] = "$label باید بزرگتر یا مساوی $min باشد.";
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $label): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$otherField] ?? null)) {
            $this->errors[$field] = "$label با مقدار تکراری آن مطابقت ندارد.";
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "مقدار $label نامعتبر است.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors ? array_values($this->errors)[0] : null;
    }
}
