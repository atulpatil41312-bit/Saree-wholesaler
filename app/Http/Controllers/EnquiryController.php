<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\NotificationDelivery;
use App\Models\Shop;
use App\Models\User;
use App\Services\ShopAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function __construct(private ShopAccessService $access) {}

    public function store(StoreEnquiryRequest $request, string $type, string $shop): RedirectResponse
    {
        abort_unless(in_array($type, ['wholesaler', 'retailer'], true), 404);
        $shopModel = Shop::query()->ofType($type)->where('slug', $shop)->firstOrFail();
        abort_unless($shopModel->isPubliclyVisible(), 404);

        $data = $request->validated();
        $product = null;
        if (isset($data['product_id'])) {
            $product = $shopModel->products()->find($data['product_id']);
            abort_unless($product !== null, 404);
        }

        $enquiry = $shopModel->enquiries()->create([
            'product_id' => $product?->id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
        ]);
        NotificationDelivery::query()->firstOrCreate(
            ['dedupe_key' => "enquiry:{$enquiry->id}:owner"],
            [
                'shop_id' => $shopModel->id,
                'user_id' => $shopModel->user_id,
                'channel' => 'mail',
                'notification_type' => 'enquiry-received',
                'scheduled_for' => now(),
                'metadata' => ['enquiry_id' => $enquiry->id],
            ],
        );
        $this->access->audit($request->user(), $shopModel, 'enquiry.received', $enquiry);

        return back()->with('status', 'Thank you. Your enquiry has been sent to the shop.');
    }

    public function index(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $enquiries = $shop->enquiries()->with('product')->latest()->paginate(30);

        return view('seller.enquiries', compact('shop', 'enquiries'));
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        abort_unless($enquiry->shop_id === $shop->id, 404);

        $data = $request->validate(['status' => ['required', Rule::in(['new', 'contacted', 'closed'])]]);
        $enquiry->update($data);
        $this->access->audit($request->user(), $shop, 'enquiry.status-updated', $enquiry, $data);

        return back()->with('status', 'Enquiry status updated.');
    }

    private function dashboardShop(Request $request): Shop
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $shop = $user->dashboardShop();
        abort_unless($shop instanceof Shop, 404, 'No shop is associated with this account.');

        return $shop;
    }
}
