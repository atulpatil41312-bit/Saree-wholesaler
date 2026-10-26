<?php

namespace App\Http\Requests;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRetailerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'retailer_name' => ['required', 'string', 'max:255'],
            'retailer_phone' => ['required', 'string', 'max:32'],
            'retailer_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'retailer_password' => ['required', 'string', 'min:12', 'confirmed'],
            'shop_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::notIn([
                    'admin', 'api', 'dashboard', 'email', 'enquiries', 'favicon', 'forgot-password',
                    'billing', 'login', 'logout', 'manifest', 'password', 'plans', 'reset-password', 'retailer',
                    'robots', 'seller', 'shops', 'storage', 'sw', 'templates', 'up', 'verify', 'wholesaler',
                ]),
                Rule::unique('shops', 'slug')->where(fn ($query) => $query->where('type', 'retailer')),
            ],
            'term_days' => ['required', 'integer', Rule::in(config('wholemarket.sponsorship.allowed_terms', []))],
            'template' => ['required', 'string', Rule::in(array_keys(Shop::templatesForType('retailer')))],
        ];
    }
}
