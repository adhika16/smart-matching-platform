<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SemanticSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:500'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'semantic_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'scope' => ['nullable', 'string', 'in:jobs,profiles'],
            'filters.category' => ['nullable', 'string', 'max:100'],
            'filters.skills' => ['nullable', 'array'],
            'filters.skills.*' => ['string', 'max:100'],
        ];
    }

    public function queryString(): string
    {
        return (string) $this->input('q', '');
    }

    public function resultLimit(): int
    {
        return (int) ($this->input('limit', 10));
    }

    public function semanticLimit(): int
    {
        return (int) ($this->input('semantic_limit', 20));
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return (array) $this->input('filters', []);
    }

    public function scope(): string
    {
        return (string) $this->input('scope', 'jobs');
    }
}
