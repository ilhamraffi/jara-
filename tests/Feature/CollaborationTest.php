<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaborationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $member;

    protected User $outsider;

    protected ProjectList $list;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Owner User', 'role' => 'user']);
        $this->member = User::factory()->create(['name' => 'Member User', 'role' => 'user']);
        $this->outsider = User::factory()->create(['name' => 'Outsider User', 'role' => 'user']);

        $this->list = ProjectList::create([
            'owner_id' => $this->owner->id,
            'name' => 'Collaboration Test Project',
            'description' => 'Test project for collaboration',
        ]);
    }

    /**
     * FR-C1: Owner dapat menambahkan pengguna lain sebagai member ke dalam daftarnya
     */
    public function test_owner_can_add_member_to_list(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson("/lists/{$this->list->id}/members", [
                'user_id' => $this->member->id,
                'role' => 'member',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Member added successfully',
                'data' => [
                    'list_id' => $this->list->id,
                    'user_id' => $this->member->id,
                    'role' => 'member',
                ],
            ]);

        $this->assertDatabaseHas('list_members', [
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);
    }

    /**
     * FR-C1: Non-owner tidak dapat menambahkan member ke list
     */
    public function test_non_owner_cannot_add_member(): void
    {
        $response = $this->actingAs($this->outsider)
            ->postJson("/lists/{$this->list->id}/members", [
                'user_id' => $this->member->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'OWNER_ONLY',
            ]);
    }

    /**
     * FR-C1: Tidak dapat menambahkan user yang sudah menjadi member (duplicate)
     */
    public function test_cannot_add_duplicate_member(): void
    {
        ListMember::create([
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson("/lists/{$this->list->id}/members", [
                'user_id' => $this->member->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'code' => 'MEMBER_EXISTS',
            ]);
    }

    /**
     * FR-C2: Owner dapat menghapus member dari daftarnya
     */
    public function test_owner_can_remove_member_from_list(): void
    {
        ListMember::create([
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson("/lists/{$this->list->id}/members/{$this->member->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member removed successfully',
            ]);

        $this->assertDatabaseMissing('list_members', [
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
        ]);
    }

    /**
     * FR-C2: Non-owner tidak dapat menghapus member
     */
    public function test_non_owner_cannot_remove_member(): void
    {
        ListMember::create([
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($this->outsider)
            ->deleteJson("/lists/{$this->list->id}/members/{$this->member->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'OWNER_ONLY',
            ]);
    }

    /**
     * FR-C2: Owner tidak dapat menghapus dirinya sendiri dari kepemilikan list
     */
    public function test_cannot_remove_owner(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson("/lists/{$this->list->id}/members/{$this->owner->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'code' => 'CANNOT_REMOVE_OWNER',
            ]);
    }

    /**
     * FR-C3: Member yang ditambahkan dapat melihat daftar member
     */
    public function test_member_can_view_members_list(): void
    {
        ListMember::create([
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($this->member)
            ->getJson("/lists/{$this->list->id}/members");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment(['user_id' => $this->owner->id, 'role' => 'owner'])
            ->assertJsonFragment(['user_id' => $this->member->id, 'role' => 'member']);
    }

    /**
     * FR-C3: User non-member tidak dapat mengakses daftar member
     */
    public function test_non_member_cannot_view_members_list(): void
    {
        $response = $this->actingAs($this->outsider)
            ->getJson("/lists/{$this->list->id}/members");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCESS_DENIED',
            ]);
    }
}
