<?php

namespace Tests\Unit\Models;

use App\Models\MatrixPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrixPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
        ]);
        
        $this->assertInstanceOf(User::class, $position->user);
        $this->assertEquals($user->id, $position->user->id);
    }

    public function test_belongs_to_sponsor(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
        ]);
        
        $this->assertInstanceOf(User::class, $position->sponsor);
        $this->assertEquals($sponsor->id, $position->sponsor->id);
    }

    public function test_has_left_child_relationship(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $leftChild = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => $leftChild->id,
        ]);
        
        $this->assertInstanceOf(User::class, $position->leftChild);
        $this->assertEquals($leftChild->id, $position->leftChild->id);
    }

    public function test_has_middle_child_relationship(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $middleChild = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'middle_child_id' => $middleChild->id,
        ]);
        
        $this->assertInstanceOf(User::class, $position->middleChild);
        $this->assertEquals($middleChild->id, $position->middleChild->id);
    }

    public function test_has_right_child_relationship(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $rightChild = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'right_child_id' => $rightChild->id,
        ]);
        
        $this->assertInstanceOf(User::class, $position->rightChild);
        $this->assertEquals($rightChild->id, $position->rightChild->id);
    }

    public function test_is_level_1_scope(): void
    {
        $sponsor = User::factory()->create();
        
        MatrixPosition::factory()->create([
            'sponsor_id' => $sponsor->id,
            'level' => 1,
        ]);
        
        MatrixPosition::factory()->create([
            'sponsor_id' => $sponsor->id,
            'level' => 2,
        ]);
        
        $level1Positions = MatrixPosition::isLevel1()->get();
        
        $this->assertCount(1, $level1Positions);
        $this->assertEquals(1, $level1Positions->first()->level);
    }

    public function test_is_active_scope(): void
    {
        $sponsor = User::factory()->create();
        
        MatrixPosition::factory()->create([
            'sponsor_id' => $sponsor->id,
            'is_active' => true,
        ]);
        
        MatrixPosition::factory()->create([
            'sponsor_id' => $sponsor->id,
            'is_active' => false,
        ]);
        
        $activePositions = MatrixPosition::isActive()->get();
        
        $this->assertCount(1, $activePositions);
        $this->assertTrue($activePositions->first()->is_active);
    }

    public function test_for_sponsor_scope(): void
    {
        $sponsor1 = User::factory()->create();
        $sponsor2 = User::factory()->create();
        
        MatrixPosition::factory()->create(['sponsor_id' => $sponsor1->id]);
        MatrixPosition::factory()->create(['sponsor_id' => $sponsor1->id]);
        MatrixPosition::factory()->create(['sponsor_id' => $sponsor2->id]);
        
        $sponsor1Positions = MatrixPosition::forSponsor($sponsor1->id)->get();
        
        $this->assertCount(2, $sponsor1Positions);
        $sponsor1Positions->each(function ($position) use ($sponsor1) {
            $this->assertEquals($sponsor1->id, $position->sponsor_id);
        });
    }

    public function test_is_full_method(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => null,
            'middle_child_id' => null,
            'right_child_id' => null,
        ]);
        
        $this->assertFalse($position->isFull());
        
        // Add all children
        $leftChild = User::factory()->create();
        $middleChild = User::factory()->create();
        $rightChild = User::factory()->create();
        
        $position->update([
            'left_child_id' => $leftChild->id,
            'middle_child_id' => $middleChild->id,
            'right_child_id' => $rightChild->id,
        ]);
        
        $this->assertTrue($position->isFull());
    }

    public function test_available_positions_method(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => null,
            'middle_child_id' => null,
            'right_child_id' => null,
        ]);
        
        $available = $position->availablePositions();
        
        $this->assertCount(3, $available);
        $this->assertContains('left', $available);
        $this->assertContains('middle', $available);
        $this->assertContains('right', $available);
        
        // Add left child
        $leftChild = User::factory()->create();
        $position->update(['left_child_id' => $leftChild->id]);
        
        $available = $position->availablePositions();
        
        $this->assertCount(2, $available);
        $this->assertNotContains('left', $available);
        $this->assertContains('middle', $available);
        $this->assertContains('right', $available);
    }

    public function test_get_children_method(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $leftChild = User::factory()->create();
        $middleChild = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => $leftChild->id,
            'middle_child_id' => $middleChild->id,
            'right_child_id' => null,
        ]);
        
        $children = $position->getChildren();
        
        $this->assertCount(2, $children);
        $this->assertEquals($leftChild->id, $children[0]->id);
        $this->assertEquals($middleChild->id, $children[1]->id);
    }

    public function test_get_position_path_method(): void
    {
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 5,
        ]);
        
        $path = $position->getPositionPath();
        
        $this->assertIsArray($path);
        $this->assertArrayHasKey('level', $path);
        $this->assertArrayHasKey('position', $path);
        $this->assertArrayHasKey('coordinates', $path);
        
        $this->assertEquals(2, $path['level']);
        $this->assertEquals(5, $path['position']);
    }

    public function test_can_add_child_method(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'is_active' => true,
            'left_child_id' => null,
        ]);
        
        $this->assertTrue($position->canAddChild());
        
        // Fill all positions
        $leftChild = User::factory()->create();
        $middleChild = User::factory()->create();
        $rightChild = User::factory()->create();
        
        $position->update([
            'left_child_id' => $leftChild->id,
            'middle_child_id' => $middleChild->id,
            'right_child_id' => $rightChild->id,
        ]);
        
        $this->assertFalse($position->canAddChild());
    }

    public function test_inactive_position_cannot_add_child(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'is_active' => false,
            'left_child_id' => null,
        ]);
        
        $this->assertFalse($position->canAddChild());
    }

    public function test_add_child_method(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $child = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => null,
            'middle_child_id' => null,
            'right_child_id' => null,
        ]);
        
        $result = $position->addChild($child->id);
        
        $this->assertTrue($result);
        $position->refresh();
        $this->assertEquals($child->id, $position->left_child_id);
    }

    public function test_add_child_fills_positions_in_order(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $child1 = User::factory()->create();
        $child2 = User::factory()->create();
        $child3 = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => null,
            'middle_child_id' => null,
            'right_child_id' => null,
        ]);
        
        $position->addChild($child1->id);
        $position->addChild($child2->id);
        $position->addChild($child3->id);
        
        $position->refresh();
        
        $this->assertEquals($child1->id, $position->left_child_id);
        $this->assertEquals($child2->id, $position->middle_child_id);
        $this->assertEquals($child3->id, $position->right_child_id);
    }

    public function test_cannot_add_child_when_full(): void
    {
        $user = User::factory()->create();
        $sponsor = User::factory()->create();
        $child1 = User::factory()->create();
        $child2 = User::factory()->create();
        $child3 = User::factory()->create();
        $child4 = User::factory()->create();
        
        $position = MatrixPosition::factory()->create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'left_child_id' => $child1->id,
            'middle_child_id' => $child2->id,
            'right_child_id' => $child3->id,
        ]);
        
        $result = $position->addChild($child4->id);
        
        $this->assertFalse($result);
    }

    public function test_get_matrix_coordinates_method(): void
    {
        $position = MatrixPosition::factory()->create([
            'level' => 2,
            'position' => 5,
        ]);
        
        $coordinates = $position->getMatrixCoordinates();
        
        $this->assertIsArray($coordinates);
        $this->assertArrayHasKey('row', $coordinates);
        $this->assertArrayHasKey('column', $coordinates);
        
        // Level 2, position 5 should be row 2, column 2 (0-indexed)
        $this->assertEquals(2, $coordinates['row']);
        $this->assertIsInt($coordinates['column']);
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'sponsor_id',
            'level',
            'position',
            'left_child_id',
            'middle_child_id',
            'right_child_id',
            'is_active',
            'placed_at',
        ];
        
        $position = new MatrixPosition();
        
        $this->assertEquals($fillable, $position->getFillable());
    }

    public function test_casts_attributes_correctly(): void
    {
        $position = MatrixPosition::factory()->create([
            'is_active' => 1,
        ]);
        
        $this->assertIsBool($position->is_active);
        $this->assertTrue($position->is_active);
    }
}