<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreativeSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->user_type === 'opportunity_owner';
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
            'job_id' => ['nullable', 'integer', 'exists:jobs,id'],
            'filters.skills' => ['nullable', 'array'],
            'filters.skills.*' => ['string', 'max:100'],
            'filters.location' => ['nullable', 'string', 'max:100'],
            'filters.experience_level' => ['nullable', 'string', 'in:entry,mid,senior,lead'],
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

    public function jobId(): ?int
    {
        $jobId = $this->input('job_id');
        return $jobId ? (int) $jobId : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return (array) $this->input('filters', []);
    }
}
