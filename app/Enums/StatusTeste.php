<?php

namespace App\Enums;

enum StatusTeste: string
{
    case Ativo = '1';
    case Inativo = '0';

    public function label(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Inativo => 'Inativo',
        };
    }

    /**
     * Formato consumido pelos <Select> do PrimeVue no frontend.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'label' => $status->label(),
                'value' => $status->value,
            ],
            self::cases(),
        );
    }
}
