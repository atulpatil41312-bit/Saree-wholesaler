<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\NotificationDelivery;
use App\Models\Shop;
use App\Models\User;
use App\Services\ShopAccessService;
use App\Services\SponsorshipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(
        private ShopAccessService $access,
        private SponsorshipService $sponsorships,
    ) {}

    public function index(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_billing');

        $sponsorships = $shop->isWholesaler()
            ? $shop->sponsorships()->with('retailerShop')->latest('expires_at')->get()
            : collect();

        return view('seller.sponsorships', [
            'shop' => $shop,
            'sponsorships' => $sponsorships,
            'invites' => Invitation::query()
                ->where('sponsor_shop_id', $shop->id)
                ->latest()
                ->get(),
            'terms' => config('wholemarket.sponsorship.allowed_terms', [30, 60, 90]),
            'slotLimit' => (int) ($this->access->limits($shop)['sponsored_retailer_limit'] ?? 0),
            'activeCount' => $this->sponsorships->activeSponsorCount($shop),
        ]);
    }

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_billing');
        [$invitation, $token] = $this->sponsorships->invite(
            $shop,
            $request->user(),
            $request->string('email')->toString(),
            $request->integer('term_days'),
        );

        $link = route('retailer.invitations.show', $token);
        $delivery = NotificationDelivery::query()->where('dedupe_key', "invitation:{$invitation->id}:mail")->first();
        try {
            Mail::raw(
                "You have been invited to launch a retailer website on WholeMarket. Complete setup here: {$link}",
                function ($message) use ($invitation): void {
                    $message->to($invitation->email)->subject('Your WholeMarket retailer website invitation');
                },
            );
            $delivery?->update(['sent_at' => now()]);
        } catch (\Throwable) {
            $delivery?->update(['failed_at' => now()]);
        }

        return back()->with([
            'status' => 'Retailer invitation prepared. Local mail is logged for review.',
            'invitation_link' => $link,
            'invitation_id' => $invitation->id,
        ]);
    }

    public function revoke(Request $request, Invitation $invitation): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_billing');
        abort_unless($invitation->sponsor_shop_id === $shop->id, 404);

        if ($invitation->status === 'pending') {
            $invitation->update(['status' => 'revoked']);
            $this->access->audit($request->user(), $shop, 'sponsorship.invitation-revoked', $invitation);
        }

        return back()->with('status', 'Invitation revoked.');
    }

    public function show(string $token): View
    {
        $invitation = $this->sponsorships->invitationForToken($token);
        abort_if($invitation === null || ! $invitation->isAvailable(), 404);
        $invitation->load('sponsorShop');

        return view('retailer.invitation', compact('invitation', 'token'));
    }

    public function accept(AcceptInvitationRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->sponsorships->invitationForToken($token);
        abort_if($invitation === null, 404);
        $data = $request->validated();

        if (mb_strtolower($data['email']) !== mb_strtolower($invitation->email)) {
            throw ValidationException::withMessages(['email' => 'Use the email address that received this invitation.']);
        }

        $user = $request->user();
        if (! $user instanceof User) {
            if (User::query()->where('email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email already has an account. Sign in first, then open the invitation link again.',
                ]);
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'retailer',
            ]);
        }

        $shop = $this->sponsorships->accept($invitation, $user, [
            'name' => $data['shop_name'],
            'slug' => $data['slug'],
            'template' => $data['template'],
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('seller.dashboard')->with('status', "{$shop->name} is live under your retailer sponsorship.");
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
