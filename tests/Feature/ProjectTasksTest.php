<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTasksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_add_tasks_to_projects()
    {
        $project = factory('App\Project')->create();
        
        $this->post($project->path() . '/tasks')
            ->assertRedirect('login');
    }
    
    /** @test */
    public function only_the_owner_of_a_project_may_add_tasks()
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $this->post($project->path() . '/tasks', ['body' => 'Test task'])
            ->assertStatus(403);
        
        $this->assertDatabaseMissing('tasks', ['body' => 'Test task']);
    }
    
    /** @test */
    public function only_the_owner_of_a_project_may_update_tasks()
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $task = $project->addTask('Test task');

        $this->patch($task->path(), ['body' => 'changed'])
            ->assertStatus(403);
        
        $this->assertDatabaseMissing('tasks', ['body' => 'changed']);
    }
    
    /** @test */
    public function a_project_can_have_tasks()
    {
        $this->signIn();
        
        $project = factory('App\Project')->create(['owner_id' => auth()->user()]);
        
        $this->post($project->path() . '/tasks', ['body' => 'Test task'])
        ->assertRedirect($project->path());
        
        $this->get($project->path())
        ->assertSee('Test task');
    }
    
    /** @test */
    public function a_task_can_be_updated()
    {
        $this->withoutExceptionHandling();
        
        $this->signIn();

        $project = auth()->user()->projects()->create(
            factory('App\Project')->raw()
        );

        $task = $project->addTask('Test task');

        $this->patch($project->path() . '/tasks/' . $task->id, [
            'body' => 'changed',
            'completed' => true
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'body' => 'changed',
            'completed' => true
        ]);
    }

    /** @test */
    public function a_task_requires_a_body()
    {
        $this->signIn();
        
        $project = factory('App\Project')->create(['owner_id' => auth()->user()]);
        
        $attributes = factory('App\Task')->raw(['body' => '']);

        $this->post($project->path() . '/tasks', $attributes)
            ->assertSessionHasErrors('body');
    }
}