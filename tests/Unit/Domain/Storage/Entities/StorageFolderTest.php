<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\Entities;

use App\Domain\Storage\Entities\StorageFolder;
use PHPUnit\Framework\TestCase;

final class StorageFolderTest extends TestCase
{
    public function test_can_create_folder(): void
    {
        $folder = StorageFolder::create('folder-1', 42, null, 'Documents');

        $this->assertEquals('folder-1', $folder->getId());
        $this->assertEquals(42, $folder->getUserId());
        $this->assertNull($folder->getParentId());
        $this->assertEquals('Documents', $folder->getName());
    }

    public function test_create_with_parent_id(): void
    {
        $folder = StorageFolder::create('folder-2', 1, 'parent-abc', 'Subfolder');
        $this->assertEquals('parent-abc', $folder->getParentId());
    }

    public function test_cannot_create_with_empty_name(): void
    {
        $this->expectException(\DomainException::class);
        StorageFolder::create('folder-3', 1, null, '');
    }

    public function test_rename_updates_name(): void
    {
        $folder = StorageFolder::create('folder-4', 1, null, 'OldName');
        $folder->rename('NewName');

        $this->assertEquals('NewName', $folder->getName());
    }

    public function test_cannot_rename_to_empty(): void
    {
        $folder = StorageFolder::create('folder-5', 1, null, 'Valid');

        $this->expectException(\DomainException::class);
        $folder->rename('');
    }

    public function test_move_to_new_parent(): void
    {
        $folder = StorageFolder::create('folder-6', 1, 'parent-a', 'Child');
        $folder->moveTo('parent-b');

        $this->assertEquals('parent-b', $folder->getParentId());
    }

    public function test_move_to_root(): void
    {
        $folder = StorageFolder::create('folder-7', 1, 'parent-a', 'Child');
        $folder->moveTo(null);

        $this->assertNull($folder->getParentId());
    }

    public function test_cannot_move_folder_into_itself(): void
    {
        $folder = StorageFolder::create('folder-8', 1, null, 'Self');

        $this->expectException(\DomainException::class);
        $folder->moveTo('folder-8');
    }

    public function test_update_path_cache(): void
    {
        $folder = StorageFolder::create('folder-9', 1, null, 'Docs');
        $this->assertNull($folder->getPathCache());

        $folder->updatePathCache('/Users/1/Docs');
        $this->assertEquals('/Users/1/Docs', $folder->getPathCache());
    }

    public function test_belongs_to_user(): void
    {
        $folder = StorageFolder::create('folder-10', 42, null, 'Private');

        $this->assertTrue($folder->belongsToUser(42));
        $this->assertFalse($folder->belongsToUser(99));
    }

    public function test_is_root_when_parent_is_null(): void
    {
        $root = StorageFolder::create('folder-11', 1, null, 'Root');
        $child = StorageFolder::create('folder-12', 1, 'folder-11', 'Child');

        $this->assertTrue($root->isRoot());
        $this->assertFalse($child->isRoot());
    }
}
