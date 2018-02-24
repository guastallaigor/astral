<?php

namespace Tests\Unit;

use Tests\TestCase;
use Astral\Lib\GitHubClient;
use Zttp\Zttp;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GitHubClientTest extends TestCase
{
    protected $client;
    protected $sampleStars;

    protected function setUp()
    {
        parent::setUp();

        $this->client = new GitHubClient(env('GITHUB_TEST_ACCESS_TOKEN'));
        $this->sampleStars = json_decode(file_get_contents(__DIR__ . '/../Blobs/stars.json'), true);
    }
    /** @test */
    public function it_fetches_stars_from_the_github_api()
    {
        $stars = $this->client->fetchStars(null, 100);

        $this->assertEquals($this->sampleStars, $stars);
    }

    /** @test */
    public function it_can_limit_the_stars_per_page()
    {
        $stars = $this->client->fetchStars(null, 2);

        $this->assertCount(2, $stars['edges']);
    }

    /** @test */
    public function it_can_pass_a_cursor_to_fetch_a_certain_page_of_stars()
    {
        $cursor = $this->sampleStars['edges'][0]['cursor'];

        $stars = $this->client->fetchStars($cursor, 1);

        $this->assertEquals($this->sampleStars['edges'][1], $stars['edges'][0]);
    }
}