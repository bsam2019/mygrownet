<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\CacheKeyHelper;
use App\Domain\Core\Services\PlatformContextResolver;
use App\Domain\Core\ValueObjects\PlatformContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CacheKeyHelperTest extends TestCase
{
    private CacheKeyHelper $helper;

    #[Test]
    public function prefixed_with_org_id_includes_org_prefix()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $context = PlatformContext::make(
            userId: '1',
            organizationId: '42',
            applicationId: 'app',
        );

        $resolver->expects($this->once())
            ->method('current')
            ->willReturn($context);

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->prefixed('my-key');

        $this->assertEquals('org:42:my-key', $result);
    }

    #[Test]
    public function prefixed_with_null_org_uses_global()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $context = PlatformContext::make(
            userId: '1',
            organizationId: '',
            applicationId: 'app',
        );

        $resolver->expects($this->once())
            ->method('current')
            ->willReturn($context);

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->prefixed('my-key');

        $this->assertEquals('global:my-key', $result);
    }

    #[Test]
    public function prefixed_with_explicit_org_overrides_context()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $resolver->expects($this->never())->method('current');

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->prefixed('my-key', 99);

        $this->assertEquals('org:99:my-key', $result);
    }

    #[Test]
    public function forModule_includes_module_in_key()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $context = PlatformContext::make(
            userId: '1',
            organizationId: '7',
            applicationId: 'app',
        );

        $resolver->expects($this->once())
            ->method('current')
            ->willReturn($context);

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->forModule('stockflow', 'items');

        $this->assertEquals('org:7:stockflow:items', $result);
    }

    #[Test]
    public function forModule_with_explicit_org()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $resolver->expects($this->never())->method('current');

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->forModule('growfinance', 'accounts', 5);

        $this->assertEquals('org:5:growfinance:accounts', $result);
    }

    #[Test]
    public function forModule_without_org_uses_global()
    {
        $resolver = $this->createMock(PlatformContextResolver::class);
        $resolver->expects($this->once())
            ->method('current')
            ->willReturn(null);

        $helper = new CacheKeyHelper($resolver);
        $result = $helper->forModule('test', 'key');

        $this->assertEquals('global:test:key', $result);
    }
}
