<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\RelationshipType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RelationshipTypeTest extends TestCase
{
    #[Test]
    public function parent_named_constructor_creates_parent_type()
    {
        $type = RelationshipType::parent();
        $this->assertEquals('parent', $type->toString());
    }

    #[Test]
    public function child_named_constructor_creates_child_type()
    {
        $type = RelationshipType::child();
        $this->assertEquals('child', $type->toString());
    }

    #[Test]
    public function sibling_named_constructor_creates_sibling_type()
    {
        $type = RelationshipType::sibling();
        $this->assertEquals('sibling', $type->toString());
    }

    #[Test]
    public function spouse_named_constructor_creates_spouse_type()
    {
        $type = RelationshipType::spouse();
        $this->assertEquals('spouse', $type->toString());
    }

    #[Test]
    public function partner_named_constructor_creates_partner_type()
    {
        $type = RelationshipType::partner();
        $this->assertEquals('partner', $type->toString());
    }

    #[Test]
    public function grandparent_named_constructor_creates_grandparent_type()
    {
        $type = RelationshipType::grandparent();
        $this->assertEquals('grandparent', $type->toString());
    }

    #[Test]
    public function grandchild_named_constructor_creates_grandchild_type()
    {
        $type = RelationshipType::grandchild();
        $this->assertEquals('grandchild', $type->toString());
    }

    #[Test]
    public function auntUncle_named_constructor_creates_aunt_uncle_type()
    {
        $type = RelationshipType::auntUncle();
        $this->assertEquals('aunt_uncle', $type->toString());
    }

    #[Test]
    public function nieceNephew_named_constructor_creates_niece_nephew_type()
    {
        $type = RelationshipType::nieceNephew();
        $this->assertEquals('niece_nephew', $type->toString());
    }

    #[Test]
    public function cousin_named_constructor_creates_cousin_type()
    {
        $type = RelationshipType::cousin();
        $this->assertEquals('cousin', $type->toString());
    }

    #[Test]
    public function guardian_named_constructor_creates_guardian_type()
    {
        $type = RelationshipType::guardian();
        $this->assertEquals('guardian', $type->toString());
    }

    #[Test]
    public function ward_named_constructor_creates_ward_type()
    {
        $type = RelationshipType::ward();
        $this->assertEquals('ward', $type->toString());
    }

    #[Test]
    public function fromString_creates_for_all_valid_types()
    {
        foreach (RelationshipType::all() as $value) {
            $type = RelationshipType::fromString($value);
            $this->assertInstanceOf(RelationshipType::class, $type);
            $this->assertEquals($value, $type->toString());
        }
    }

    #[Test]
    public function fromString_throws_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid relationship type: invalid');
        RelationshipType::fromString('invalid');
    }

    #[Test]
    public function equals_returns_true_for_same_type()
    {
        $type1 = RelationshipType::parent();
        $type2 = RelationshipType::fromString('parent');
        $this->assertTrue($type1->equals($type2));
    }

    #[Test]
    public function equals_returns_false_for_different_type()
    {
        $type1 = RelationshipType::parent();
        $type2 = RelationshipType::child();
        $this->assertFalse($type1->equals($type2));
    }

    #[Test]
    public function inverse_of_parent_is_child()
    {
        $this->assertTrue(RelationshipType::parent()->getInverse()->equals(RelationshipType::child()));
    }

    #[Test]
    public function inverse_of_child_is_parent()
    {
        $this->assertTrue(RelationshipType::child()->getInverse()->equals(RelationshipType::parent()));
    }

    #[Test]
    public function inverse_of_sibling_is_sibling()
    {
        $this->assertTrue(RelationshipType::sibling()->getInverse()->equals(RelationshipType::sibling()));
    }

    #[Test]
    public function inverse_of_spouse_is_spouse()
    {
        $this->assertTrue(RelationshipType::spouse()->getInverse()->equals(RelationshipType::spouse()));
    }

    #[Test]
    public function inverse_of_partner_is_partner()
    {
        $this->assertTrue(RelationshipType::partner()->getInverse()->equals(RelationshipType::partner()));
    }

    #[Test]
    public function inverse_of_grandparent_is_grandchild()
    {
        $this->assertTrue(RelationshipType::grandparent()->getInverse()->equals(RelationshipType::grandchild()));
    }

    #[Test]
    public function inverse_of_grandchild_is_grandparent()
    {
        $this->assertTrue(RelationshipType::grandchild()->getInverse()->equals(RelationshipType::grandparent()));
    }

    #[Test]
    public function inverse_of_aunt_uncle_is_niece_nephew()
    {
        $this->assertTrue(RelationshipType::auntUncle()->getInverse()->equals(RelationshipType::nieceNephew()));
    }

    #[Test]
    public function inverse_of_niece_nephew_is_aunt_uncle()
    {
        $this->assertTrue(RelationshipType::nieceNephew()->getInverse()->equals(RelationshipType::auntUncle()));
    }

    #[Test]
    public function inverse_of_cousin_is_cousin()
    {
        $this->assertTrue(RelationshipType::cousin()->getInverse()->equals(RelationshipType::cousin()));
    }

    #[Test]
    public function inverse_of_guardian_is_ward()
    {
        $this->assertTrue(RelationshipType::guardian()->getInverse()->equals(RelationshipType::ward()));
    }

    #[Test]
    public function inverse_of_ward_is_guardian()
    {
        $this->assertTrue(RelationshipType::ward()->getInverse()->equals(RelationshipType::guardian()));
    }

    #[Test]
    public function sibling_spouse_partner_cousin_are_reciprocal()
    {
        $this->assertTrue(RelationshipType::sibling()->isReciprocal());
        $this->assertTrue(RelationshipType::spouse()->isReciprocal());
        $this->assertTrue(RelationshipType::partner()->isReciprocal());
        $this->assertTrue(RelationshipType::cousin()->isReciprocal());
    }

    #[Test]
    public function parent_child_grandparent_grandchild_are_not_reciprocal()
    {
        $this->assertFalse(RelationshipType::parent()->isReciprocal());
        $this->assertFalse(RelationshipType::child()->isReciprocal());
        $this->assertFalse(RelationshipType::grandparent()->isReciprocal());
        $this->assertFalse(RelationshipType::grandchild()->isReciprocal());
    }

    #[Test]
    public function isValid_returns_true_for_all_valid_types()
    {
        foreach (RelationshipType::all() as $value) {
            $this->assertTrue(RelationshipType::isValid($value));
        }
    }

    #[Test]
    public function isValid_returns_false_for_invalid_type()
    {
        $this->assertFalse(RelationshipType::isValid('invalid'));
    }

    #[Test]
    public function all_returns_all_constants()
    {
        $all = RelationshipType::all();
        $this->assertCount(12, $all);
        $this->assertContains('parent', $all);
        $this->assertContains('child', $all);
        $this->assertContains('sibling', $all);
        $this->assertContains('spouse', $all);
        $this->assertContains('partner', $all);
        $this->assertContains('grandparent', $all);
        $this->assertContains('grandchild', $all);
        $this->assertContains('aunt_uncle', $all);
        $this->assertContains('niece_nephew', $all);
        $this->assertContains('cousin', $all);
        $this->assertContains('guardian', $all);
        $this->assertContains('ward', $all);
    }

    #[Test]
    public function getLabel_returns_correct_for_each_type()
    {
        $this->assertEquals('Parent', RelationshipType::parent()->getLabel());
        $this->assertEquals('Child', RelationshipType::child()->getLabel());
        $this->assertEquals('Sibling', RelationshipType::sibling()->getLabel());
        $this->assertEquals('Spouse', RelationshipType::spouse()->getLabel());
        $this->assertEquals('Partner', RelationshipType::partner()->getLabel());
        $this->assertEquals('Grandparent', RelationshipType::grandparent()->getLabel());
        $this->assertEquals('Grandchild', RelationshipType::grandchild()->getLabel());
        $this->assertEquals('Aunt/Uncle', RelationshipType::auntUncle()->getLabel());
        $this->assertEquals('Niece/Nephew', RelationshipType::nieceNephew()->getLabel());
        $this->assertEquals('Cousin', RelationshipType::cousin()->getLabel());
        $this->assertEquals('Guardian', RelationshipType::guardian()->getLabel());
        $this->assertEquals('Ward', RelationshipType::ward()->getLabel());
    }

    #[Test]
    public function isParentChildRelationship_returns_true_for_parent_and_child()
    {
        $this->assertTrue(RelationshipType::parent()->isParentChildRelationship());
        $this->assertTrue(RelationshipType::child()->isParentChildRelationship());
    }

    #[Test]
    public function isParentChildRelationship_returns_false_for_other_types()
    {
        $this->assertFalse(RelationshipType::sibling()->isParentChildRelationship());
        $this->assertFalse(RelationshipType::spouse()->isParentChildRelationship());
    }

    #[Test]
    public function isParentType_returns_true_only_for_parent()
    {
        $this->assertTrue(RelationshipType::parent()->isParentType());
        $this->assertFalse(RelationshipType::child()->isParentType());
        $this->assertFalse(RelationshipType::sibling()->isParentType());
    }

    #[Test]
    public function isGrandparentRelationship_returns_true_for_grandparent_and_grandchild()
    {
        $this->assertTrue(RelationshipType::grandparent()->isGrandparentRelationship());
        $this->assertTrue(RelationshipType::grandchild()->isGrandparentRelationship());
    }

    #[Test]
    public function isGrandparentRelationship_returns_false_for_other_types()
    {
        $this->assertFalse(RelationshipType::parent()->isGrandparentRelationship());
        $this->assertFalse(RelationshipType::child()->isGrandparentRelationship());
    }

    #[Test]
    public function isGrandparentType_returns_true_only_for_grandparent()
    {
        $this->assertTrue(RelationshipType::grandparent()->isGrandparentType());
        $this->assertFalse(RelationshipType::grandchild()->isGrandparentType());
    }

    #[Test]
    public function isSpouseType_returns_true_for_spouse_and_partner()
    {
        $this->assertTrue(RelationshipType::spouse()->isSpouseType());
        $this->assertTrue(RelationshipType::partner()->isSpouseType());
    }

    #[Test]
    public function isSpouseType_returns_false_for_other_types()
    {
        $this->assertFalse(RelationshipType::parent()->isSpouseType());
        $this->assertFalse(RelationshipType::sibling()->isSpouseType());
    }
}
