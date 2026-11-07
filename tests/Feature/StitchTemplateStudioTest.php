<?php

namespace Tests\Feature;

use App\Models\GeneratedTemplateDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StitchTemplateStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_open_the_stitch_studio(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'wholesaler']))
            ->get(route('admin.stitch.index'))
            ->assertForbidden();
    }

    public function test_admins_can_view_the_stitch_studio_without_exposing_credentials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.stitch.index'))
            ->assertOk()
            ->assertSeeText('Stitch template studio');

        if (filled(config('services.stitch.key'))) {
            $response->assertDontSee((string) config('services.stitch.key'));
        }
    }

    public function test_admins_can_open_a_private_draft_preview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $draft = GeneratedTemplateDraft::factory()->for($admin)->create([
            'title' => 'Boutique home direction',
            'status' => 'failed',
            'error_message' => 'Stitch was unavailable.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stitch.show', $draft))
            ->assertOk()
            ->assertSeeText('Boutique home direction')
            ->assertSeeText('Stitch was unavailable.');
    }

    public function test_generation_form_validates_before_calling_stitch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.stitch.index'))
            ->post(route('admin.stitch.store'), [
                'title' => 'Too short',
                'prompt' => 'short',
                'device_type' => 'DESKTOP',
            ])
            ->assertRedirect(route('admin.stitch.index'))
            ->assertSessionHasErrors('prompt');

        $this->assertDatabaseCount('generated_template_drafts', 0);
    }
}
