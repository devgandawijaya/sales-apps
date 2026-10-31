<?php

namespace Tests\Feature;

use App\Models\LandingSetting;
use App\Models\LandingSlide;
use App\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'TEN-SLIDE',
            'name' => 'PT Distribusi Slide Test',
            'subdomain' => 'slide-test',
            'status' => 'active',
            'plan_tier' => 'enterprise',
        ]);

        TenantContext::setTenant($this->tenant);

        $branch = Branch::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BR-SLD-01',
            'name' => 'Cabang Slide Test',
            'type' => 'HEAD_OFFICE',
            'address' => 'Jl. Slide No. 1',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
        ]);

        $this->user = User::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'role_title' => 'SUPERADMIN',
            'name' => 'Admin Slide',
            'email' => 'admin.slide@test.co.id',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_landing_page_renders_with_slides_and_settings(): void
    {
        LandingSlide::create([
            'title' => 'Armada Truk Logistik Nasional',
            'subtitle' => 'Pengiriman barang aman dan cepat',
            'badge_text' => 'Armada Prima',
            'image_url' => 'https://example.com/truck.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        LandingSetting::set('hero_title', 'Kendali Lengkap Distribusi Barang dari Gudang Hingga Pelanggan');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Armada Truk Logistik Nasional');
        $response->assertSee('Kendali Lengkap Distribusi Barang dari Gudang Hingga Pelanggan');
        $response->assertSee('phobehub.com');
    }

    public function test_authenticated_user_can_access_landing_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get('/landing-settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Halaman Beranda');
        $response->assertSee('+ Tambah Slide Baru');
    }

    public function test_can_create_new_slide(): void
    {
        $response = $this->actingAs($this->user)->post('/landing-settings/slides', [
            'title' => 'Gudang DC Surabaya Baru',
            'subtitle' => 'Pusat distribusi area Jawa Timur',
            'badge_text' => 'Gudang DC',
            'image_url' => 'https://images.unsplash.com/photo-test',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/landing-settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('landing_slides', [
            'title' => 'Gudang DC Surabaya Baru',
            'badge_text' => 'Gudang DC',
            'is_active' => true,
        ]);
    }

    public function test_can_update_existing_slide(): void
    {
        $slide = LandingSlide::create([
            'title' => 'Judul Lama',
            'subtitle' => 'Keterangan Lama',
            'image_url' => 'https://example.com/old.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put("/landing-settings/slides/{$slide->id}", [
            'title' => 'Judul Baru Diperbarui',
            'subtitle' => 'Keterangan Baru',
            'badge_text' => 'Terbaru',
            'image_url' => 'https://example.com/new.jpg',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/landing-settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('landing_slides', [
            'id' => $slide->id,
            'title' => 'Judul Baru Diperbarui',
            'badge_text' => 'Terbaru',
        ]);
    }

    public function test_can_toggle_slide_active_status(): void
    {
        $slide = LandingSlide::create([
            'title' => 'Slide Toggle',
            'image_url' => 'https://example.com/toggle.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post("/landing-settings/slides/{$slide->id}/toggle");
        $response->assertRedirect('/landing-settings');

        $slide->refresh();
        $this->assertFalse((bool) $slide->is_active);
    }

    public function test_can_delete_slide(): void
    {
        $slide = LandingSlide::create([
            'title' => 'Slide Yang Akan Dihapus',
            'image_url' => 'https://example.com/delete.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete("/landing-settings/slides/{$slide->id}");
        $response->assertRedirect('/landing-settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('landing_slides', [
            'id' => $slide->id,
        ]);
    }

    public function test_can_update_hero_content_settings(): void
    {
        $response = $this->actingAs($this->user)->post('/landing-settings/content', [
            'hero_badge' => 'Distributor Unggulan Nasional',
            'hero_title' => 'Sistem Rantai Pasok Terdepan di Indonesia',
            'hero_description' => 'Solusi lengkap pemenuhan kebutuhan FMCG antar-pulau dengan akurasi stok.',
        ]);

        $response->assertRedirect('/landing-settings');
        $response->assertSessionHas('success');

        $this->assertEquals('Distributor Unggulan Nasional', LandingSetting::get('hero_badge'));
        $this->assertEquals('Sistem Rantai Pasok Terdepan di Indonesia', LandingSetting::get('hero_title'));
    }
}

