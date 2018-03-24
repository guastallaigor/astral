<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagsTest extends TestCase
{
    use RefreshDatabase;

    protected $tags;

    protected function setUp()
    {
        parent::setUp();

        $this->login();

        $this->tags = create('Astral\Models\Tag', ['user_id' => auth()->id()], 5);
    }

    /** @test */
    public function it_can_fetch_a_users_tags()
    {
        $this->getJson('/api/tags')
            ->assertStatus(200)
            ->assertJson($this->tags->toArray());
    }

    /** @test */
    public function a_user_can_add_a_new_tag()
    {
        $response = $this->postJson('/api/tags', ['name' => 'Laravel'])
            ->assertStatus(200)
            ->assertJson(['name' => 'Laravel']);
    }

    /** @test */
    public function a_new_tag_must_include_a_name()
    {
        $this->postJson('/api/tags', ['name' => ''])->assertStatus(422);

    }

    /** @test */
    public function tags_can_be_reordered()
    {
        // Shuffle the tags
        $i = 0;
        $tags = $this->tags->shuffle()->map(function ($tag) use (&$i) {
            $tag->sort_order = $i;
            $i++;
            return [
                'name' => $tag->name,
                'id' => $tag->id,
                'sort_order' => $tag->sort_order
            ];
        })->toArray();

        $this->putJson('/api/tags/reorder', ['tags' => $tags])
            ->assertJson($tags)
            ->assertStatus(200);

    }

    /** @test */
    public function reordering_tags_requires_an_id()
    {
        $tags = $this->tags->map(function ($tag) {
            $tag->id = null;

            return $tag;
        })->toArray();

        $this->putJson('/api/tags/reorder', ['tags' => $tags])->assertStatus(422);
    }

    /** @test */
    public function ids_passed_for_reordering_must_exist_in_the_db()
    {
        $tags = $this->tags->map(function ($tag) {
            $tag->id = 1337;

            return $tag;
        })->toArray();

        $this->putJson('/api/tags/reorder', ['tags' => $tags])->assertStatus(422);
    }

    /** @test */
    public function reordering_tags_requires_a_sort_order()
    {
        $tags = $this->tags->map(function ($tag) {
            $tag->sort_order = null;

            return $tag;
        })->toArray();

        $this->putJson('/api/tags/reorder', ['tags' => $tags])->assertStatus(422);
    }

    /** @test */
    public function a_user_can_delete_their_tags()
    {
        $this->withoutExceptionHandling();

        $id = auth()->user()->tags()->first()->id;

        $this->deleteJson("/api/tags/{$id}")->assertStatus(204);
        $this->assertNull(auth()->user()->tags()->find($id));
    }

}
