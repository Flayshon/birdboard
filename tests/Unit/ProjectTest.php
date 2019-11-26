<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_has_a_path()
    {
        $project = factory('App\Project')->create();

        $this->assertEquals($project->path(), '/projects/' . $project->id);
    }
    
    /** @test */
    public function it_belongs_to_an_owner()
    {
        $project = factory('App\Project')->create();

        $this->assertInstanceOf('App\User', $project->owner);
    }
    
    /** @test */
    public function it_has_tasks()
    {
        $this->withoutExceptionHandling();

        $project = factory('App\Project')->create();

        $task = $project->addTask('Test task');

        $this->assertTrue($project->tasks->contains($task));
    }
}
