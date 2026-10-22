<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()->with(['versions' => fn ($query) => $query->orderByDesc('version')])->orderBy('sort_order')->get();

        return view('admin.plans', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'alpha_dash', 'max:100', 'unique:plans,key'],
            'name' => ['required', 'string', 'max:255'],
            'shop_type' => ['required', Rule::in(['wholesaler', 'retailer'])],
            'signup_enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Plan::query()->create([
            ...$data,
            'signup_enabled' => (bool) ($data['signup_enabled'] ?? false),
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Plan created. Add a version before assigning it to a shop.');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'signup_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $plan->update([
            ...$data,
            'signup_enabled' => (bool) ($data['signup_enabled'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => $data['sort_order'] ?? $plan->sort_order,
        ]);

        return back()->with('status', 'Plan updated.');
    }

    public function storeVersion(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'price_paise' => ['nullable', 'integer', 'min:0'],
            'billing_interval' => ['required', Rule::in(['monthly', 'quarterly', 'yearly', 'sponsored', 'manual'])],
            'limits' => ['required', 'json'],
            'template_keys' => ['required', 'string', 'max:1000'],
        ]);
        $limits = json_decode($data['limits'], true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($limits)) {
            throw ValidationException::withMessages(['limits' => 'Limits must be a JSON object.']);
        }

        $templateKeys = trim($data['template_keys']) === 'all'
            ? 'all'
            : array_values(array_filter(array_map('trim', explode(',', $data['template_keys']))));
        if ($templateKeys !== 'all' && $templateKeys === []) {
            throw ValidationException::withMessages(['template_keys' => 'Enter `all` or at least one template key.']);
        }

        $plan->versions()->create([
            'version' => ((int) $plan->versions()->max('version')) + 1,
            'price_paise' => $data['price_paise'] ?? null,
            'billing_interval' => $data['billing_interval'],
            'limits' => Arr::except($limits, ['template_keys', 'templates']),
            'template_keys' => $templateKeys,
            'active_from' => now(),
        ]);

        return back()->with('status', 'Plan version added. Existing subscriptions keep their current version.');
    }
}
