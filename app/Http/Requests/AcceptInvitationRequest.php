<?php

namespace App\Http\Requests;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [Rule::requiredIf($this->user() === null), 'nullable', 'string', 'min:12', 'confirmed'],
            'shop_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::notIn(['admin', 'api', 'dashboard', 'login', 'logout', 'retailer', 'seller', 'shops', 'wholesaler']),
                Rule::unique('shops', 'slug')->where('type', 'retailer'),
            ],
            'template' => ['required', 'string', Rule::in(array_keys(Shop::templatesForType('retailer')))],
        ];
    }
}
